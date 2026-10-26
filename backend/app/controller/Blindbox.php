<?php
declare(strict_types=1);

namespace app\controller;

use think\facade\Db;

/** 盲盒域：发售日历/详情/购买/开启/记录 */
class Blindbox extends Base
{
    /** POST /api/box/blind_box/list */
    public function list()
    {
        $rows = Db::name('blind_boxes')->whereNull('deleted_at')->order('id', 'desc')->select()->toArray();
        return ok($rows, '发售日历');
    }

    /** POST /api/box/blind_box/BoxDetail/ids/{id} */
    public function boxDetail($id)
    {
        $b = Db::name('blind_boxes')->whereNull('deleted_at')->where('id', (int)$id)->find();
        if (!$b) return fail('找不到该盲盒，或者已经下架了');
        $b['items'] = Db::name('blind_box_items')->alias('i')
            ->join('collectibles c', 'c.id=i.prize_collectible_id')
            ->where('i.blind_box_id', $b['id'])->whereNull('i.deleted_at')
            ->field('i.id,i.probability,i.quantity_limit,i.quantity_distributed,c.name,c.image,c.min_price')->select()->toArray();
        return ok($b, 'successfully !!!');
    }

    /** POST /api/box/blind_box/buy */
    public function buy()
    {
        $p = request()->param();
        $bid = (int)($p['blind_box_id'] ?? 0);
        $num = max(1, (int)($p['num'] ?? 1));
        if (!$bid) return fail('缺少参数');
        if ($resp = $this->requireRealname()) return $resp;
        if ($resp = $this->verifyPayPassword()) return $resp;
        Db::startTrans();
        try {
            $b = Db::name('blind_boxes')->where('id', $bid)->lock(true)->find();
            if (!$b || $b['status'] !== 'onsale') return fail('盲盒不可购买');
            $mine = Db::name('user_blind_boxes')->where('user_id', Base::$userId)->where('blind_box_id', $bid)->count();
            if ($b['limit_per_user'] && $mine + $num > $b['limit_per_user']) return fail('超出每人限购数量');
            $orderNo = genOrderNo();
            $orderId = Db::name('orders')->insertGetId([
                'order_no' => $orderNo, 'user_id' => Base::$userId, 'collectible_id' => $b['collectible_id'],
                'unit_price' => $b['price'], 'quantity' => $num, 'total_price' => round($b['price'] * $num, 2),
                'status' => 'pending', 'source' => 'release', 'goods_name' => $b['name'],
                'goods_cover_image' => $b['image'], 'expires_at' => date('Y-m-d H:i:s', time() + payTimeLimit()),
            ]);
            Db::name('payments')->insert(['order_id' => $orderId, 'user_id' => Base::$userId, 'amount' => round($b['price'] * $num, 2), 'payment_method' => 'huifu', 'status' => 'pending']);
            Db::name('blind_boxes')->where('id', $bid)->inc('sale_num', $num)->update();
            for ($i = 0; $i < $num; $i++) {
                Db::name('user_blind_boxes')->insert(['user_id' => Base::$userId, 'blind_box_id' => $bid, 'order_id' => $orderId, 'status' => 1]);
            }
            Db::name('users')->where('id', Base::$userId)->inc('blind_box_num', $num)->update();
            Db::commit();
        } catch (\Throwable $e) { Db::rollback(); return fail('购买失败：' . $e->getMessage()); }
        return ok(['order_id' => $orderId, 'order_no' => $orderNo], '购买成功，可前往开启');
    }

    /** POST /api/user_collection/user_collection/openbox */
    public function openbox()
    {
        // 前端源站参数名：openid（用户盲盒持有记录 id）
        $ubId = (int)(request()->param('user_blind_box_id') ?: (request()->param('openid') ?: 0));
        $ub = Db::name('user_blind_boxes')->where('id', $ubId)->where('user_id', Base::$userId)->where('status', 1)->find();
        if (!$ub) return fail('盲盒不存在或已开启');
        $r = $this->doOpen($ub);
        return $r['ok'] ? ok($r['data'], '拆盒成功') : fail($r['msg']);
    }

    /** POST /api/user_collection/user_collection/openBoxs —— 批量 */
    public function openBoxs()
    {
        $ids = (array)(request()->param('user_blind_box_ids') ?: (request()->param('openid') ? [request()->param('openid')] : []));
        $results = [];
        foreach ($ids as $id) {
            $ub = Db::name('user_blind_boxes')->where('id', (int)$id)->where('user_id', Base::$userId)->where('status', 1)->find();
            if ($ub) { $r = $this->doOpen($ub); if ($r['ok']) $results[] = $r['data']; }
        }
        return ok($results, '开盒完成');
    }

    private function doOpen(array $ub): array
    {
        Db::startTrans();
        try {
            $items = Db::name('blind_box_items')->where('blind_box_id', $ub['blind_box_id'])->whereNull('deleted_at')->lock(true)->select()->toArray();
            if (!$items) throw new \Exception('奖品池为空');
            // 概率加权随机（过滤已发完的）
            $pool = [];
            foreach ($items as $i) {
                if ($i['quantity_limit'] && $i['quantity_distributed'] >= $i['quantity_limit']) continue;
                $pool[] = ['id' => $i['id'], 'w' => (float)$i['probability'] * 10000];
            }
            if (!$pool) throw new \Exception('奖品已全部发放');
            $totalW = array_sum(array_column($pool, 'w'));
            $rand = mt_rand(1, (int)max(1, $totalW));
            $hit = null; $acc = 0;
            foreach ($pool as $p) { $acc += $p['w']; if ($rand <= $acc) { $hit = $p['id']; break; } }
            $hit = $hit ?: $pool[0]['id'];
            $item = Db::name('blind_box_items')->where('id', $hit)->find();
            // 消耗盲盒
            Db::name('user_blind_boxes')->where('id', $ub['id'])->update(['status' => 2, 'opentime' => date('Y-m-d H:i:s')]);
            Db::name('users')->where('id', Base::$userId)->dec('blind_box_num')->update();
            // 新资产
            $code = nextCollectionCode();
            $ucId = Db::name('user_collectibles')->insertGetId([
                'user_id' => Base::$userId, 'collectible_id' => $item['prize_collectible_id'],
                'blind_box_item_id' => $item['id'], 'serial' => (string)$code,
                'collection_code' => $code, 'source' => 'blindbox', 'acquired_price' => 0,
                'acquired_at' => date('Y-m-d H:i:s'), 'status' => 'held',
            ]);
            Db::name('blind_box_items')->where('id', $item['id'])->inc('quantity_distributed')->update();
            Db::name('blind_box_open_record')->insert([
                'user_id' => Base::$userId, 'blind_box_id' => $ub['blind_box_id'],
                'collectible_id' => $item['prize_collectible_id'], 'user_collectible_id' => $ucId,
            ]);
            Db::name('chain_tasks')->insert(['task_id' => 'CT' . strtoupper(md5(uniqid())), 'user_collectible_id' => $ucId, 'status' => 0]);
            $g = Db::name('collectibles')->where('id', $item['prize_collectible_id'])->field('id,name,main_image,image')->find();
            Db::commit();
            return ['ok' => true, 'data' => ['goods' => ['id' => (int)$g['id'], 'name' => $g['name'], 'main_image' => $g['main_image'] ?: $g['image']], 'user_collectible_id' => $ucId, 'collection_code' => $code]];
        } catch (\Throwable $e) {
            Db::rollback();
            return ['ok' => false, 'msg' => '开盒失败：' . $e->getMessage()];
        }
    }

    /** POST /api/box/blind_box/openlog */
    public function openlog()
    {
        [$page, $rows] = pageParams();
        $q = Db::name('blind_box_open_record')->alias('o')
            ->join('collectibles c', 'c.id=o.collectible_id')
            ->where('o.user_id', Base::$userId)
            ->field('o.*, c.name as goods_name, c.image');
        $total = (clone $q)->count();
        return ok(['total' => $total, 'data' => $q->page($page, $rows)->order('o.id', 'desc')->select()->toArray()], 'successfully !!!');
    }
}
