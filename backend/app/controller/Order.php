<?php
declare(strict_types=1);

namespace app\controller;

use think\facade\Db;
use app\service\PayService;

/** 订单域：列表/详情/支付/取消 */
class Order extends Base
{
    /** POST /api/order/order/orderList */
    public function orderList()
    {
        $p = request()->param();
        [$page, $rows] = pageParams();
        $st = (int)($p['status'] ?? 0);
        $q = Db::name('orders')->where('user_id', Base::$userId);
        if (in_array($st, [2, 7])) $q->where('status', $st === 7 ? 'pending' : 'completed');
        elseif ($st === 3) $q->where('status', 'cancelled');
        elseif ($st === 1) $q->whereIn('status', ['pending']);
        if ($kw = (string)($p['keywords'] ?? '')) {
            $q->where(function ($w) use ($kw) { $w->whereLike('goods_name', "%$kw%")->whereOr('order_no', 'like', "%$kw%"); });
        }
        $total = (clone $q)->count();
        $list = $q->page($page, $rows)->order('id', 'desc')->select()->toArray();
        $map = ['pending' => 1, 'completed' => 2, 'cancelled' => 3];
        foreach ($list as &$o) {
            $o['order_status'] = $map[$o['status']] ?? 1;
            $o['order_sn'] = $o['order_no'];
            $o['order_type'] = $o['source'] === 'market' ? '2' : '3';
            $o['goods_cover_image'] = $o['goods_cover_image'] ?: '';
        }
        return ok(['total' => $total, 'per_page' => $rows, 'current_page' => $page, 'last_page' => (int)ceil(max(1, $total) / $rows), 'data' => $list], '订单列表');
    }

    private function detail(int $id): ?array
    {
        $o = Db::name('orders')->where('id', $id)->where('user_id', Base::$userId)->find();
        if (!$o) return null;
        $o['order_sn'] = $o['order_no'];
        $o['order_type'] = $o['source'] === 'market' ? '2' : '3';
        $map = ['pending' => 1, 'completed' => 2, 'cancelled' => 3];
        $o['order_status'] = $map[$o['status']] ?? 1;
        if (empty($o['pay_time'])) $o['pay_time'] = null;
        return $o;
    }

    /** POST /api/order/order/MarketOrderDetail/ids/{id} */
    public function marketOrderDetail($id)
    {
        $o = $this->detail((int)$id);
        if (!$o || $o['source'] !== 'market') return fail('订单不存在');
        return ok($o, '订单详情');
    }

    /** POST /api/order/order/orderDetail/ids/{id} */
    public function orderDetail($id)
    {
        $o = $this->detail((int)$id);
        if (!$o) return fail('找不到该订单或已被删除');
        return ok($o, '订单详情');
    }

    /** POST /api/order/order/payOrder —— 模拟支付 */
    public function payOrder()
    {
        $p = request()->param();
        $orderId = (int)($p['order_id'] ?? 0);
        $o = Db::name('orders')->where('id', $orderId)->where('user_id', Base::$userId)->find();
        if (!$o) return fail('订单不存在');
        // 源站流程走汇付收银台，前端不采集交易密码；密码可选（提供即校验）
        if (!empty(request()->param('pay_password'))) {
            if ($resp = $this->verifyPayPassword()) return $resp;
        }
        if (strtotime($o['expires_at']) < time() && $o['status'] === 'pending') {
            Db::name('orders')->where('id', $o['id'])->update(['status' => 'cancelled', 'cancelled_at' => date('Y-m-d H:i:s'), 'cancel_reason' => '超时未支付']);
            return fail('订单已超时');
        }
        $r = PayService::completeOrder((int)$o['id'], 'huifu', ['mock' => true, 'resp_code' => 'C00000']);
        if (!$r['ok']) return fail($r['msg']);
        $fresh = Db::name('orders')->where('id', $o['id'])->find();
        $map = ['pending' => 1, 'completed' => 2, 'cancelled' => 3];
        return ok(['order_status' => $map[$fresh['status']]], '支付成功');
    }

    /** POST /api/order/order/cancelOrder */
    public function cancelOrder()
    {
        $orderId = (int)(request()->param('order_id') ?: 0);
        $o = Db::name('orders')->where('id', $orderId)->where('user_id', Base::$userId)->find();
        if (!$o || $o['status'] !== 'pending') return fail('订单不可取消');
        Db::startTrans();
        try {
            Db::name('orders')->where('id', $o['id'])->update(['status' => 'cancelled', 'cancelled_at' => date('Y-m-d H:i:s'), 'cancel_reason' => '用户取消']);
            if ($o['resale_listing_id']) {
                $l = Db::name('resale_listings')->where('id', $o['resale_listing_id'])->find();
                if ($l && $l['status'] === 'locked') {
                    Db::name('resale_listings')->where('id', $l['id'])->update(['status' => 'selling']);
                    Db::name('user_collectibles')->where('id', $l['user_collectible_id'])->update(['status' => 'consigned']);
                }
            } else {
                Db::name('collectibles')->where('id', $o['collectible_id'])->dec('locked_quantity', $o['quantity'])->update();
            }
            Db::commit();
        } catch (\Throwable $e) { Db::rollback(); return fail('取消失败'); }
        return ok(null, '取消成功');
    }

    /** POST /api/order/createVIPOrder */
    public function createVIPOrder()
    {
        $type = (int)(request()->param('vip_type') ?: 1);
        $orderNo = genOrderNo();
        $id = Db::name('orders')->insertGetId([
            'order_no' => $orderNo, 'user_id' => Base::$userId, 'collectible_id' => 0,
            'unit_price' => 0, 'quantity' => 1, 'total_price' => 0,
            'status' => 'completed', 'source' => 'vip', 'expires_at' => date('Y-m-d H:i:s'),
        ]);
        return ok(['order_id' => $id, 'order_no' => $orderNo], '下单成功');
    }

    /** POST /api/order/order/saleMarketOrderDetail —— 市场订单详情（源站路径，marketDetail 页使用） */
    public function saleMarketOrderDetail()
    {
        $id = (int)(request()->param('id') ?: 0);
        $o = $this->detail($id);
        if (!$o || $o['source'] !== 'market') return fail('订单不存在');
        return ok($o, '订单详情');
    }

    /** POST /api/order/order/giveOrderDetail/ids/{id} */
    public function giveOrderDetail($id)
    {
        $t = Db::name('transfers')->where('id', (int)$id)->find();
        if (!$t) return fail('记录不存在');
        return ok($t, 'successfully !!!');
    }
}
