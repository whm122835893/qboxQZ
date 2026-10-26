<?php
declare(strict_types=1);

namespace app\controller;

use think\facade\Db;

/** 持仓域：我的收藏/持仓详情 */
class Mycollection extends Base
{
    /** POST /api/user_collection/user_collection/antMycollection */
    public function antMycollection()
    {
        [$page, $rows] = pageParams();
        $q = Db::name('user_collectibles')->alias('uc')
            ->join('collectibles c', 'c.id=uc.collectible_id')
            ->where('uc.user_id', Base::$userId)
            ->whereIn('uc.status', ['held', 'consigned', 'frozen'])
            ->field('uc.*, c.name as goods_name, c.main_image, c.image');
        $total = (clone $q)->count();
        $list = $q->page($page, $rows)->order('uc.id', 'desc')->select()->toArray();
        foreach ($list as &$r) {
            $r['goods'] = ['id' => (int)$r['collectible_id'], 'name' => $r['goods_name'], 'main_image' => $r['main_image'] ?: $r['image']];
            $r['goods_id'] = (int)$r['collectible_id'];
            $r['types'] = 2;
            $r['mint_status'] = $r['mint_status'] ?: 'pending';
        }
        return ok(['total' => $total, 'per_page' => $rows, 'current_page' => $page, 'last_page' => (int)ceil(max(1, $total) / $rows),
            'data' => $list, 'current_count' => $total, 'current_consignment' => Db::name('user_collectibles')->where('user_id', Base::$userId)->where('is_consigned', 1)->count()], '获取成功');
    }

    /** POST /api/user_collection/user_collection/detail/ids/{id} */
    public function detail($id)
    {
        $uc = Db::name('user_collectibles')->where('id', (int)$id)->where('user_id', Base::$userId)->find();
        if (!$uc) return fail('记录不存在');
        $task = Db::name('chain_tasks')->where('user_collectible_id', $uc['id'])->order('id', 'desc')->find();
        $uc['task'] = $task ?: null;
        $uc['goods'] = Db::name('collectibles')->where('id', $uc['collectible_id'])->field('id,name,main_image,image')->find();
        return ok($uc, '获取成功');
    }
}
