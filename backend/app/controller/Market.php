<?php
declare(strict_types=1);

namespace app\controller;

use think\facade\Db;
use app\service\PayService;

/** 市场域：挂单列表/寄售详情/下单/上架/求购/委托/流水/寄售管理 */
class Market extends Base
{
    /** POST /api/market/market/getMarketGoodsListByGoodsId */
    public function getMarketGoodsListByGoodsId()
    {
        $gid = (int)(request()->param('id') ?: 0);
        if (!$gid) return ok(['list' => [], 'count' => 0, 'ltnum' => 0], '操作成功');
        [$page, $rows] = pageParams();
        $q = Db::name('resale_listings')->alias('r')
            ->join('user_collectibles uc', 'uc.id=r.user_collectible_id')
            ->join('collectibles c', 'c.id=r.collectible_id')
            ->where('r.collectible_id', $gid)->whereIn('r.status', ['selling', 'locked'])
            ->field("r.id, uc.collection_code, r.price, c.edition as total_num, r.series_id, r.status, r.income_type, UNIX_TIMESTAMP(r.created_at) as createtime, '' as img, '' as name");
        $total = (clone $q)->count();
        $list = $q->order('r.price', 'asc')->page($page, $rows)->select()->toArray();
        foreach ($list as &$l) { $l['status'] = $l['status'] === 'locked' ? 4 : 1; $l['income_type'] = [$l['income_type']]; $l['is_img_diff'] = 0; }
        return ok(['list' => $list, 'count' => $total, 'ltnum' => 0], '操作成功');
    }

    /** POST /api/market/market/MarketDetail/ids/{id} */
    public function marketDetail($id)
    {
        $l = Db::name('resale_listings')->alias('r')
            ->join('collectibles c', 'c.id=r.collectible_id')
            ->join('user_collectibles uc', 'uc.id=r.user_collectible_id')
            ->where('r.id', (int)$id)
            ->field("r.*, c.name as goods_name, c.main_image, c.circulate, c.edition, c.purchase_information, c.reprice_limit, uc.user_hash_text, uc.user_hash, uc.rz_code, uc.collection_code as uc_code")
            ->find();
        if (!$l) return json(['code' => 500, 'msg' => 'An error occurred', 'time' => (string)time(), 'data' => null]);
        $seller = Db::name('users')->where('id', $l['seller_id'])->find();
        $series = Db::name('series')->where('series_id', $l['series_id'])->find();
        return ok([
            'id' => (int)$l['id'], 'goods_id' => (int)$l['collectible_id'], 'price' => $l['price'],
            'status' => $l['status'] === 'locked' ? 4 : ($l['status'] === 'selling' ? 1 : 0),
            'goods_name' => $l['goods_name'], 'goods_cover_image' => $l['goods_cover_image'] ?? '',
            'series_id' => (int)$l['series_id'], 'series_name' => $series['series_name'] ?? '',
            'goods_type' => 2, 'income_type' => 'huifu',
            'pay_platform_service_rate' => $l['pay_platform_service_rate'],
            'platform_service_price' => $l['platform_service_price'],
            'creator_service_price' => $l['creator_service_price'], 'real_income' => $l['real_income'],
            'sale_user_id' => (int)$l['seller_id'],
            'user' => ['id' => (int)$l['seller_id'], 'nickname' => 'db****', 'avatar' => '/defautAvatar.png', 'mobile' => maskMobile($seller['phone'] ?? '')],
            'user_collection' => [
                'id' => (int)$l['user_collectible_id'], 'collection_code' => $l['collection_code'],
                'user_hash_text' => $l['user_hash_text'], 'user_hash' => $l['user_hash'], 'rz_code' => $l['rz_code'],
            ],
            'product' => [
                'id' => (int)$l['collectible_id'], 'name' => $l['goods_name'], 'flux' => (int)$l['circulate'],
                'total_num' => (int)$l['edition'], 'main_image' => $l['main_image'], 'listimg' => $l['main_image'],
                'purchase_information' => $l['purchase_information'],
                'publisher' => ['name' => 'qbox', 'company_logo' => '/static/qbox/logo.png'],
            ],
            'main_image' => $l['main_image'],
        ], '商品详情');
    }

    /** POST /api/market/market/createOrder —— 下单（锁单） */
    public function createOrder()
    {
        $p = request()->param();
        $listingId = (int)($p['market_order_id'] ?? 0);
        if (!$listingId) return fail('缺少挂单ID');
        if ($resp = $this->requireRealname()) return $resp;
        // 源站下单走汇付收银台，不采集交易密码；密码可选（提供即校验）
        if (!empty(request()->param('pay_password'))) {
            if ($resp = $this->verifyPayPassword()) return $resp;
        }

        Db::startTrans();
        try {
            $l = Db::name('resale_listings')->where('id', $listingId)->lock(true)->find();
            if (!$l || !in_array($l['status'], ['selling', 'locked'])) return fail('该挂单不可购买');
            $c = Db::name('collectibles')->where('id', $l['collectible_id'])->find();
            if ((int)$c['is_out'] === 1) return fail('该藏品已退市');
            // 时段校验
            $series = Db::name('series')->where('series_id', $l['series_id'])->find();
            if ($series && $series['resale_buy_start_time'] !== '00:00:00') {
                $t = date('H:i:s');
                if ($t < $series['resale_buy_start_time'] || $t > $series['resale_buy_end_time']) {
                    return fail('当前不在购买时段内');
                }
            }
            $orderNo = genOrderNo();
            $orderId = Db::name('orders')->insertGetId([
                'order_no' => $orderNo, 'user_id' => Base::$userId,
                'collectible_id' => $l['collectible_id'], 'resale_listing_id' => $l['id'],
                'unit_price' => $l['price'], 'quantity' => 1, 'total_price' => $l['price'],
                'status' => 'pending', 'source' => 'market', 'seller_id' => $l['seller_id'],
                'goods_name' => $c['name'], 'goods_cover_image' => $c['main_image'] ?: $c['image'],
                'series_id' => $l['series_id'], 'series_name' => $series['series_name'] ?? '',
                'pay_platform_service_rate' => $l['pay_platform_service_rate'],
                'platform_service_price' => $l['platform_service_price'],
                'creator_service_price' => $l['creator_service_price'], 'real_income' => $l['real_income'],
                'collection_codes' => json_encode([(string)$l['collection_code']]),
                'c_goods_id' => $l['collectible_id'], 'related_collectible_id' => $l['collectible_id'],
                'expires_at' => date('Y-m-d H:i:s', time() + payTimeLimit()),
            ]);
            Db::name('payments')->insert(['order_id' => $orderId, 'user_id' => Base::$userId, 'amount' => $l['price'], 'payment_method' => 'huifu', 'status' => 'pending']);
            Db::name('resale_listings')->where('id', $l['id'])->update(['status' => 'locked']);
            Db::name('user_collectibles')->where('id', $l['user_collectible_id'])->update(['status' => 'frozen']);
            Db::commit();
        } catch (\Throwable $e) {
            Db::rollback();
            return fail('下单失败：' . $e->getMessage());
        }
        return ok(['order_id' => $orderId, 'order_no' => $orderNo, 'total_price' => $l['price'], 'expires_at' => date('Y-m-d H:i:s', time() + payTimeLimit())], '下单成功，待支付');
    }

    /** POST /api/market/market/consign —— 挂单上架 */
    public function consign()
    {
        $p = request()->param();
        $ucId = (int)($p['user_collectible_id'] ?? 0);
        $price = (float)($p['price'] ?? 0);
        if (!$ucId || $price <= 0) return fail('缺少参数');
        if ($resp = $this->requireRealname()) return $resp;
        // 与源站一致：寄售走收银台语义，交易密码可选（提供即校验）
        if (!empty(request()->param('pay_password'))) {
            if ($resp = $this->verifyPayPassword()) return $resp;
        }

        $uc = Db::name('user_collectibles')->where('id', $ucId)->where('user_id', Base::$userId)->find();
        if (!$uc || $uc['status'] !== 'held') return fail('藏品不可寄售');
        $c = Db::name('collectibles')->where('id', $uc['collectible_id'])->find();
        if ((float)$price > (float)$c['reprice_limit']) return fail('超过寄售定价上限 ' . $c['reprice_limit']);
        $series = Db::name('series')->where('series_id', $c['category_id'])->find();
        if ($series && $series['resale_put_start_time'] !== '00:00:00') {
            $t = date('H:i:s');
            if ($t < $series['resale_put_start_time'] || $t > $series['resale_put_end_time']) return fail('当前不在寄售上架时段内');
        }
        $feeRate = platformFeeRate();
        Db::startTrans();
        try {
            $lid = Db::name('resale_listings')->insertGetId([
                'seller_id' => Base::$userId, 'collectible_id' => $uc['collectible_id'],
                'user_collectible_id' => $ucId, 'collection_code' => $uc['collection_code'],
                'series_id' => $c['category_id'], 'price' => $price,
                'fee_rate' => $feeRate, 'fee_amount' => round($price * $feeRate, 2),
                'actual_amount' => round($price * (1 - $feeRate), 2),
                'pay_platform_service_rate' => $feeRate,
                'platform_service_price' => round($price * $feeRate, 2),
                'real_income' => round($price * (1 - $feeRate), 2),
                'status' => 'selling', 'listed_at' => date('Y-m-d H:i:s'),
            ]);
            Db::name('user_collectibles')->where('id', $ucId)->update(['status' => 'consigned', 'is_consigned' => 1]);
            Db::name('collectibles')->where('id', $uc['collectible_id'])->inc('sell_count')->update();
            Db::commit();
        } catch (\Throwable $e) {
            Db::rollback();
            return fail('上架失败：' . $e->getMessage());
        }
        return ok(['listing_id' => $lid, 'fee_amount' => round($price * $feeRate, 2), 'actual_amount' => round($price * (1 - $feeRate), 2)], '上架成功');
    }

    /** POST /api/market/trade/createBegBuyOrder */
    public function createBegBuyOrder()
    {
        $p = request()->param();
        $cid = (int)($p['collectible_id'] ?? 0);
        if (!$cid || empty($p['want_price'])) return fail('缺少参数');
        if ($resp = $this->requireRealname()) return $resp;
        $id = Db::name('beg_buy_orders')->insertGetId([
            'user_id' => Base::$userId, 'collectible_id' => $cid,
            'want_price' => $p['want_price'], 'num' => (int)($p['num'] ?: 1),
            'remark' => (string)($p['remark'] ?? ''), 'status' => 'buying',
        ]);
        return ok(['order_id' => $id], '求购发布成功');
    }

    /** POST /api/market/trade/begBuyOrderList */
    public function begBuyOrderList()
    {
        [$page, $rows] = pageParams();
        $q = Db::name('beg_buy_orders')->alias('b')
            ->join('collectibles c', 'c.id=b.collectible_id')
            ->where('b.user_id', Base::$userId)
            ->field('b.*, c.name as goods_name, c.image');
        if ($kw = (string)(request()->param('keywords') ?: '')) $q->whereLike('c.name', '%' . $kw . '%');
        $total = (clone $q)->count();
        return ok(['total' => $total, 'data' => $q->page($page, $rows)->order('b.id', 'desc')->select()->toArray()], '订单信息');
    }

    /** POST /api/market/trade/getSellCollectionInfo */
    public function getSellCollectionInfo()
    {
        $bid = (int)(request()->param('beg_order_id') ?: 0);
        $b = Db::name('beg_buy_orders')->where('id', $bid)->find();
        if (!$b) return fail('求购单不存在');
        $c = Db::name('collectibles')->where('id', $b['collectible_id'])->find();
        return ok(['beg' => $b, 'collectible' => $c], 'successfully !!!');
    }

    /** POST /api/market/Prepayment/myOrderlist */
    public function myOrderlist()
    {
        [$page, $rows] = pageParams();
        $rows = Db::name('entrust_orders')->where('user_id', Base::$userId)->page($page, $rows)->order('id', 'desc')->select()->toArray();
        return ok(['total' => count($rows), 'data' => $rows], '订单信息');
    }

    /** POST /api/market/Prepayment/collectionDetail */
    public function prepayCollectionDetail()
    {
        $gid = (int)(request()->param('goods_id') ?: 0);
        $c = Db::name('collectibles')->whereNull('deleted_at')->where('id', $gid)->find();
        if (!$c) return fail('藏品ID不能为空');
        return ok($c, '操作成功');
    }

    /** POST /api/market/trade/tradingInformation */
    public function tradingInformation()
    {
        $p = request()->param();
        $cid = (int)($p['goods_id'] ?? 0);
        $type = (int)($p['status'] ?? 1) === 4 ? 'gift' : 'deal';
        [$page, $rows] = pageParams();
        $q = Db::name('trade_records')->where('collectible_id', $cid)->where('type', $type);
        $total = (clone $q)->count();
        $list = $q->order('created_at', 'desc')->page($page, $rows)->select()->toArray();
        return ok([
            'list' => array_map(fn($r) => ['price' => $r['price'], 'collection_code' => $r['collection_code'], 'time' => relativeTime(strtotime($r['created_at']))], $list),
            'transaction_record_number' => Db::name('trade_records')->where('collectible_id', $cid)->count(),
            'current_buying' => 0, 'current_sale' => 0,
        ], '交易信息');
    }

    /** POST /api/order/order/myConsignment */
    public function myConsignment()
    {
        [$page, $rows] = pageParams();
        $rows = Db::name('resale_listings')->where('seller_id', Base::$userId)->page($page, $rows)->order('id', 'desc')->select()->toArray();
        return ok(['total' => count($rows), 'data' => $rows], 'successfully !!!');
    }

    /** POST /api/market/Prepayment/createOrder —— 批量付（待支付订单逐单完成，batchPay 页使用） */
    public function batchPayOrder()
    {
        $p = request()->param();
        $ids = array_filter(array_map('intval', (array)($p['order_ids'] ?? [])));
        if (!$ids && !empty($p['order_id'])) $ids = [(int)$p['order_id']];
        if (!$ids) return fail('缺少订单参数');
        $done = 0; $last = '';
        foreach ($ids as $oid) {
            $o = Db::name('orders')->where('id', $oid)->where('user_id', Base::$userId)->find();
            if (!$o || $o['status'] !== 'pending') continue;
            if (strtotime($o['expires_at']) < time()) {
                Db::name('orders')->where('id', $o['id'])->update(['status' => 'cancelled', 'cancelled_at' => date('Y-m-d H:i:s'), 'cancel_reason' => '超时未支付']);
                continue;
            }
            $r = PayService::completeOrder((int)$o['id'], 'huifu', ['mock' => true, 'batch' => true]);
            if ($r['ok']) { $done++; $last = '支付成功'; } else { $last = $r['msg']; }
        }
        return $done > 0 ? ok(['paid' => $done], $last ?: '支付成功') : fail($last ?: '无可支付订单');
    }

    /** POST /api/payment/mock/notify —— 模拟支付回调（幂等） */
    public function mockNotify()
    {
        $p = request()->param();
        $orderNo = (string)($p['order_no'] ?? '');
        if (!$orderNo || (string)($p['resp_code'] ?? '') !== 'C00000') return fail('回调参数错误');
        $order = Db::name('orders')->where('order_no', $orderNo)->find();
        if (!$order) return fail('订单不存在');
        $r = PayService::completeOrder((int)$order['id'], 'huifu', $p);
        return $r['ok'] ? ok(null, $r['msg']) : fail($r['msg']);
    }
}
