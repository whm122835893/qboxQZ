<?php
declare(strict_types=1);

namespace app\controller;

use think\facade\Db;

/** 合成域：活动/玩法/材料/提交合成/记录 */
class Synthesis extends Base
{
    /** POST /api/synthesis/themeList */
    public function themeList()
    {
        $rows = Db::name('synthesis_activities')->order('id', 'desc')->select()->toArray();
        foreach ($rows as &$r) {
            // 源站实测（2026-10-24 抓包）：name 字段 + 时间文本 'Y/m/d H:i'
            $r['name'] = $r['title'];
            $r['start_time'] = $r['start_time'] ? date('Y/m/d H:i', strtotime($r['start_time'])) : '';
            $r['end_time'] = $r['end_time'] ? date('Y/m/d H:i', strtotime($r['end_time'])) : '';
            $r['status'] = ($r['start_time'] && strtotime($r['start_time']) > time()) ? '未开始' : (($r['end_time'] && strtotime($r['end_time']) < time()) ? '已结束' : '进行中');
            $r['status_text'] = $r['status'];
        }
        return ok($rows, 'successfully !!!');
    }

    /** POST /api/synthesis/themeinfo */
    public function themeinfo()
    {
        $a = Db::name('synthesis_activities')->where('id', (int)(request()->param('theme_id') ?: 0))->find();
        if (!$a || ($a['end_time'] && strtotime($a['end_time']) < time())) return fail('未开启');
        return ok($a, 'successfully !!!');
    }

    /** POST /api/synthesis/playInfo */
    public function playInfo()
    {
        $tid = (int)(request()->param('theme_id') ?: 0);
        $a = Db::name('synthesis_activities')->where('id', $tid)->find();
        if (!$a) return fail('玩法不存在');
        $materials = Db::name('synthesis_materials')->alias('m')
            ->join('collectibles c', 'c.id=m.collectible_id')
            ->where('m.activity_id', $tid)
            ->field('m.collectible_id as goods_id, c.name, c.image, m.count')->select()->toArray();
        $myHeld = Db::name('user_collectibles')->alias('uc')
            ->join('collectibles c', 'c.id=uc.collectible_id')
            ->whereIn('uc.collectible_id', array_column($materials, 'goods_id') ?: [0])
            ->where('uc.user_id', Base::$userId)->where('uc.status', 'held')
            ->field('uc.id as user_collectible_id, uc.collectible_id as goods_id, uc.collection_code, c.name, c.image')->select()->toArray();
        $result = Db::name('collectibles')->where('id', $a['result_collectible_id'])->field('id,name,main_image as image')->find();
        return ok(['activity' => $a, 'materials' => $materials, 'result' => $result, 'my_held' => $myHeld], 'successfully !!!');
    }

    /** POST /api/synthesis/selectCollection */
    public function selectCollection()
    {
        $p = request()->param();
        $rows = Db::name('user_collectibles')->alias('uc')
            ->join('collectibles c', 'c.id=uc.collectible_id')
            ->where('uc.user_id', Base::$userId)->where('uc.status', 'held')
            ->where('uc.collectible_id', (int)($p['goods_id'] ?? 0))
            ->field('uc.id as user_collectible_id, uc.collection_code, c.name, c.image')->select()->toArray();
        return ok($rows, 'successfully !!!');
    }

    /** POST /api/synthesis/synthesizeNow */
    public function synthesizeNow()
    {
        $p = request()->param();
        $tid = (int)($p['theme_id'] ?? 0);
        $ids = array_map('intval', (array)($p['user_collectible_ids'] ?? []));
        if (!$tid || !$ids) return fail('缺少参数');
        if ($resp = $this->verifyPayPassword()) return $resp;
        $a = Db::name('synthesis_activities')->where('id', $tid)->lock(true)->find();
        if (!$a || ($a['end_time'] && strtotime($a['end_time']) < time())) return fail('活动未开启或已结束');
        if ($a['per_user_limit'] && Db::name('synthesis_records')->where('user_id', Base::$userId)->where('activity_id', $tid)->count() >= $a['per_user_limit']) return fail('已达每人合成次数上限');
        if ($a['total_limit'] && (int)$a['used_count'] >= (int)$a['total_limit']) return fail('活动总量已用完');
        // 防死循环
        $mats = Db::name('synthesis_materials')->where('activity_id', $tid)->select()->toArray();
        foreach ($mats as $m) if ((int)$m['collectible_id'] === (int)$a['result_collectible_id']) return fail('合成配置错误');
        // 材料数量校验
        $need = [];
        foreach ($mats as $m) $need[$m['collectible_id']] = (int)$m['count'];
        $ucs = Db::name('user_collectibles')->where('user_id', Base::$userId)->whereIn('id', $ids)->whereIn('status', ['held'])->select()->toArray();
        $got = [];
        foreach ($ucs as $uc) $got[$uc['collectible_id']] = ($got[$uc['collectible_id']] ?? 0) + 1;
        if (count($ucs) !== count($ids)) return fail('存在不可用的材料凭证');
        foreach ($need as $gid => $cnt) if (($got[$gid] ?? 0) < $cnt) return fail('材料不足');

        Db::startTrans();
        try {
            $recordId = Db::name('synthesis_records')->insertGetId([
                'user_id' => Base::$userId, 'activity_id' => $tid, 'status' => 1,
            ]);
            foreach ($ids as $ucId) {
                Db::name('user_collectibles')->where('id', $ucId)->update(['status' => 'consumed']);
                Db::name('synthesis_record_items')->insert(['synthesis_record_id' => $recordId, 'user_collectible_id' => $ucId]);
            }
            $code = nextCollectionCode();
            $outUcId = Db::name('user_collectibles')->insertGetId([
                'user_id' => Base::$userId, 'collectible_id' => $a['result_collectible_id'],
                'serial' => (string)$code, 'collection_code' => $code,
                'source' => 'synthesis', 'acquired_price' => 0, 'acquired_at' => date('Y-m-d H:i:s'), 'status' => 'held',
            ]);
            Db::name('synthesis_records')->where('id', $recordId)->update(['result_user_collectible_id' => $outUcId]);
            Db::name('synthesis_activities')->where('id', $tid)->inc('used_count')->update();
            Db::name('chain_tasks')->insert(['task_id' => 'CT' . strtoupper(md5(uniqid())), 'user_collectible_id' => $outUcId, 'status' => 0]);
            Db::commit();
        } catch (\Throwable $e) { Db::rollback(); return fail('合成失败：' . $e->getMessage()); }
        return ok(['record_id' => $recordId], '合成成功');
    }

    /** POST /api/synthesis/recordList */
    public function recordList()
    {
        [$page, $rows] = pageParams();
        $rows = Db::name('synthesis_records')->where('user_id', Base::$userId)->page($page, $rows)->order('id', 'desc')->select()->toArray();
        return ok(['total' => count($rows), 'data' => $rows], 'successfully !!!');
    }

    /** POST /api/synthesis/recordInfo */
    public function recordInfo()
    {
        $r = Db::name('synthesis_records')->where('id', (int)(request()->param('record_id') ?: 0))->where('user_id', Base::$userId)->find();
        if (!$r) return fail('记录不存在');
        $r['items'] = Db::name('synthesis_record_items')->where('synthesis_record_id', $r['id'])->select()->toArray();
        return ok($r, 'successfully !!!');
    }
}
