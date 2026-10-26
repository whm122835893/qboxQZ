<?php
declare(strict_types=1);

namespace app\controller;

use think\facade\Db;

/** 活动域：签到/抽奖/空投/龙晶商城 */
class Activity extends Base
{
    /* ---------- 签到 ---------- */

    /** POST /addons/signin/api.index/index */
    public function signIndex()
    {
        $uid = Base::$userId;
        $today = date('Y-m-d');
        $isSignin = (bool)Db::name('check_in_records')->where('user_id', $uid)->where('check_in_date', $today)->count();
        $successions = Db::name('check_in_records')->where('user_id', $uid)->max('consecutive_days') ?: 0;
        $rankRows = Db::name('check_in_records')->field('user_id, MAX(consecutive_days) as days')->group('user_id')->order('days', 'desc')->limit(20)->select()->toArray();
        $ranklist = [];
        foreach ($rankRows as $i => $r) {
            $u = Db::name('users')->where('id', $r['user_id'])->field('username,avatar')->find();
            $ranklist[] = ['user_id' => $r['user_id'], 'days' => (int)$r['days'], 'user' => ['nickname' => $u['username'], 'avatar' => $u['avatar']]];
        }
        return ok([
            'signinscore' => ['s1' => '1', 's2' => '1', 's3' => '1', 's4' => '1', 's5' => '1', 's6' => '1', 's7' => '1', 'sn' => '7'],
            'successions' => (int)$successions, 'is_signin' => $isSignin,
            'self_rank' => 0, 'score' => (int)self::$user->score,
            'ranklist' => $ranklist, 'isfillup' => '1', 'fillupscore' => '100', 'fillupdays' => '3',
            'msg' => $isSignin ? '今日已签到' : '今天签到可获得 1 积分,请点击签到领取积分',
        ], 'successfully !!!');
    }

    /** POST /addons/signin/api.index/doSign */
    public function doSign()
    {
        $uid = Base::$userId;
        $today = date('Y-m-d');
        if (Db::name('check_in_records')->where('user_id', $uid)->where('check_in_date', $today)->count()) {
            return fail('今日已签到');
        }
        // 连续天数：昨日有签到则 +1，否则重置 1
        $yest = Db::name('check_in_records')->where('user_id', $uid)->where('check_in_date', date('Y-m-d', strtotime('-1 day')))->find();
        $days = $yest ? ((int)$yest['consecutive_days'] + 1) : 1;
        $reward = 1; // 实测 signinscore s1~s7 均为 1 积分
        Db::startTrans();
        try {
            Db::name('check_in_records')->insert([
                'user_id' => $uid, 'check_in_date' => $today, 'consecutive_days' => $days,
                'reward_type' => 'points', 'reward_amount' => $reward,
            ]);
            Db::name('users')->where('id', $uid)->inc('score', $reward)->update();
            // 签到空投判定
            $acts = Db::name('airdrop_activities')->where('type', 'checkin')->where('status', 'active')->select()->toArray();
            foreach ($acts as $a) {
                if ($a['checkin_days'] && $days >= (int)$a['checkin_days']) {
                    $this->grantAirdrop($a, $uid);
                }
            }
            Db::commit();
        } catch (\Throwable $e) { Db::rollback(); return fail('签到失败：' . $e->getMessage()); }
        return ok(['reward_score' => $reward, 'consecutive_days' => $days, 'total_score' => Db::name('users')->where('id', $uid)->value('score')], '签到成功');
    }

    /** POST /addons/signin/api.index/fillup */
    public function fillup()
    {
        $date = (string)(request()->param('date') ?? '');
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) return fail('日期格式错误');
        $diff = (strtotime($today = date('Y-m-d')) - strtotime($date)) / 86400;
        if ($diff < 1 || $diff > 3) return fail('仅支持补签 3 天内的缺卡');
        if (Db::name('check_in_records')->where('user_id', Base::$userId)->where('check_in_date', $date)->count()) return fail('该日期已签到');
        if ((int)self::$user->score < 100) return fail('积分不足（补签需 100 积分）');
        Db::startTrans();
        try {
            Db::name('check_in_records')->insert([
                'user_id' => Base::$userId, 'check_in_date' => $date, 'consecutive_days' => 1,
                'reward_type' => 'points', 'reward_amount' => 0, 'is_fillup' => 1,
            ]);
            Db::name('users')->where('id', Base::$userId)->dec('score', 100)->update();
            Db::commit();
        } catch (\Throwable $e) { Db::rollback(); return fail('补签失败'); }
        return ok(null, '补签成功');
    }

    /* ---------- 抽奖 ---------- */

    /** POST /api/lottery/info */
    public function lotteryInfo()
    {
        $act = Db::name('lottery_activities')->where('status', 'active')->order('id', 'desc')->find();
        if (!$act) return fail('暂无抽奖活动');
        $prizes = Db::name('lucky_draw_prizes')->where('activity_id', $act['id'])->whereNull('deleted_at')->order('sort_order')->select()->toArray();
        return ok(['activity' => $act, 'prizes' => $prizes], 'successfully !!!');
    }

    /** POST /api/lottery/draw */
    public function draw()
    {
        $aid = (int)(request()->param('activity_id') ?: 0);
        $act = Db::name('lottery_activities')->where('id', $aid)->where('status', 'active')->find();
        if (!$act) return fail('暂无抽奖活动');
        if ($act['end_time'] && strtotime($act['end_time']) < time()) return fail('活动已结束');
        if ($act['limit_per_user'] && Db::name('lucky_draw_records')->where('user_id', Base::$userId)->where('activity_id', $aid)->count() >= $act['limit_per_user']) return fail('已达抽奖次数上限');
        // 消耗
        if ($act['cost_num'] > 0) {
            $field = ['points' => 'score', 'crystal' => 'crystal_yellow'][$act['cost_type']] ?? 'score';
            if ((float)self::$user->$field < (float)$act['cost_num']) return fail('余额不足');
            Db::name('users')->where('id', Base::$userId)->dec($field, (float)$act['cost_num'])->update();
        }
        $prizes = Db::name('lucky_draw_prizes')->where('activity_id', $aid)->whereNull('deleted_at')->lock(true)->select()->toArray();
        $pool = []; $totalW = 0;
        foreach ($prizes as $p) {
            if ((int)$p['won'] >= (int)$p['total']) continue;
            $w = 100; $pool[] = ['p' => $p, 'w' => $w]; $totalW += $w;
        }
        if (!$pool) return fail('奖品已全部抽完');
        $rand = mt_rand(1, $totalW); $hit = null; $acc = 0;
        foreach ($pool as $x) { $acc += $x['w']; if ($rand <= $acc) { $hit = $x['p']; break; } }
        $hit = $hit ?: $pool[0]['p'];
        Db::startTrans();
        try {
            Db::name('lucky_draw_prizes')->where('id', $hit['id'])->inc('won')->update();
            $ucId = null;
            if ($hit['prize_type'] === 'collectible') {
                $code = nextCollectionCode();
                $ucId = Db::name('user_collectibles')->insertGetId([
                    'user_id' => Base::$userId, 'collectible_id' => $hit['collectible_id'],
                    'serial' => (string)$code, 'collection_code' => $code,
                    'source' => 'lucky_draw', 'acquired_price' => 0, 'acquired_at' => date('Y-m-d H:i:s'), 'status' => 'held',
                ]);
            } elseif ($hit['prize_type'] === 'points') {
                Db::name('users')->where('id', Base::$userId)->inc('score', (int)$hit['coin_amount'])->update();
            }
            Db::name('lucky_draw_records')->insert([
                'user_id' => Base::$userId, 'activity_id' => $aid, 'prize_id' => $hit['id'], 'user_collectible_id' => $ucId,
            ]);
            Db::commit();
        } catch (\Throwable $e) { Db::rollback(); return fail('抽奖失败：' . $e->getMessage()); }
        $g = $hit['collectible_id'] ? Db::name('collectibles')->where('id', $hit['collectible_id'])->field('id,name,image')->find() : null;
        return ok(['prize_type' => $hit['prize_type'], 'tier_name' => $hit['tier_name'], 'coin_amount' => $hit['coin_amount'], 'goods' => $g, 'user_collectible_id' => $ucId], '恭喜获得');
    }

    /** POST /api/lottery/statistics */
    public function lotteryStatistics()
    {
        $total = Db::name('lucky_draw_records')->where('user_id', Base::$userId)->count();
        $byType = Db::name('lucky_draw_records')->alias('r')
            ->join('lucky_draw_prizes p', 'p.id=r.prize_id')
            ->where('r.user_id', Base::$userId)->group('p.prize_type')
            ->column('COUNT(*)', 'p.prize_type');
        return ok(['total_draws' => $total, 'by_type' => $byType], 'successfully !!!');
    }

    /** POST /api/lottery/drawRecord */
    public function drawRecord()
    {
        [$page, $rows] = pageParams();
        $q = Db::name('lucky_draw_records')->alias('r')
            ->join('lucky_draw_prizes p', 'p.id=r.prize_id')
            ->where('r.user_id', Base::$userId)
            ->field('r.id, r.created_at, p.tier_name, p.prize_type, p.coin_amount');
        $total = (clone $q)->count();
        return ok(['total' => $total, 'data' => $q->page($page, $rows)->order('r.id', 'desc')->select()->toArray()], 'successfully !!!');
    }

    /* ---------- 龙晶商城 ---------- */

    /** POST /api/crystal_goods/list */
    public function crystalList()
    {
        // 前端消费 res.data.data（分页结构），源站实测请求 {type:'30',page:1,crystal_type:''}
        $rows = Db::name('crystal_goods')->whereNull('deleted_at')->where('status', 'onsale')->order('id')->select()->toArray();
        return ok(['total' => count($rows), 'per_page' => 15, 'current_page' => 1, 'last_page' => count($rows) ? 1 : 0, 'data' => $rows], 'successfully !!!');
    }

    /** POST /api/crystal_goods/crystalDetail */
    public function crystalDetail()
    {
        $g = Db::name('crystal_goods')->whereNull('deleted_at')->where('id', (int)(request()->param('id') ?: 0))->find();
        if (!$g) return fail('商品不存在');
        return ok($g, 'successfully !!!');
    }

    /** POST /api/crystal_goods/exchange */
    public function exchange()
    {
        $p = request()->param();
        $gid = (int)($p['crystal_goods_id'] ?? 0);
        $num = max(1, (int)($p['num'] ?? 1));
        // 源站兑换走钱包余额/龙晶直扣，交易密码可选（提供即校验）
        if (!empty(request()->param('pay_password'))) {
            if ($resp = $this->verifyPayPassword()) return $resp;
        }
        Db::startTrans();
        try {
            $g = Db::name('crystal_goods')->where('id', $gid)->whereNull('deleted_at')->lock(true)->find();
            if (!$g || $g['status'] !== 'onsale') return fail('商品不可兑换');
            if ($g['stock'] - $g['locked_quantity'] < $num) return fail('库存不足');
            $u = Db::name('users')->where('id', Base::$userId)->lock(true)->find();
            $balance = (float)($u[$g['crystal_type']] ?? 0);
            $cost = (float)$g['crystal_price'] * $num;
            if ($balance < $cost) return fail('龙晶余额不足');
            Db::name('users')->where('id', Base::$userId)->dec($g['crystal_type'], $cost)->update();
            Db::name('crystal_goods')->where('id', $g['id'])->inc('locked_quantity', $num)->dec('stock', $num)->update();
            $ucId = null;
            if ($g['collectible_id']) {
                $code = nextCollectionCode();
                $ucId = Db::name('user_collectibles')->insertGetId([
                    'user_id' => Base::$userId, 'collectible_id' => $g['collectible_id'],
                    'serial' => (string)$code, 'collection_code' => $code,
                    'source' => 'purchase', 'acquired_price' => 0, 'acquired_at' => date('Y-m-d H:i:s'), 'status' => 'held',
                ]);
            }
            Db::name('exchange_records')->insert([
                'user_id' => Base::$userId, 'crystal_goods_id' => $gid,
                'crystal_type' => $g['crystal_type'], 'crystal_num' => $cost,
                'collectible_id' => $g['collectible_id'], 'user_collectible_id' => $ucId,
            ]);
            Db::commit();
        } catch (\Throwable $e) { Db::rollback(); return fail('兑换失败：' . $e->getMessage()); }
        return ok(['user_collectible_id' => $ucId], '兑换成功');
    }

    /** POST /api/crystal_goods/exchangeRecord */
    public function exchangeRecord()
    {
        [$page, $rows] = pageParams();
        $q = Db::name('exchange_records')->alias('e')
            ->join('crystal_goods g', 'g.id=e.crystal_goods_id')
            ->where('e.user_id', Base::$userId)
            ->field('e.*, g.name as goods_name, g.image');
        $total = (clone $q)->count();
        return ok(['total' => $total, 'data' => $q->page($page, $rows)->order('e.id', 'desc')->select()->toArray()], 'successfully !!!');
    }

    /* ---------- 空投 ---------- */

    /** GET /api/airdrop/activities */
    public function airdropActivities()
    {
        return ok(Db::name('airdrop_activities')->where('status', 'active')->whereNull('deleted_at')->field('id,name,type,airdrop_mode,quantity_per_user,start_time,end_time,description')->select()->toArray(), 'successfully !!!');
    }

    /** GET /api/airdrop/records */
    public function airdropRecords()
    {
        [$page, $rows] = pageParams();
        $q = Db::name('airdrop_records')->alias('r')
            ->join('airdrop_activities a', 'a.id=r.activity_id')
            ->join('collectibles c', 'c.id=r.collectible_id')
            ->where('r.user_id', Base::$userId)
            ->field('r.id, r.status, r.quantity, r.issued_at, r.created_at, a.name as activity_name, c.name as goods_name, c.image');
        $total = (clone $q)->count();
        return ok(['total' => $total, 'data' => $q->page($page, $rows)->order('r.id', 'desc')->select()->toArray()], 'successfully !!!');
    }

    /** POST /api/airdrop/eligibilities */
    public function airdropEligibilities()
    {
        [$page, $rows] = pageParams();
        $q = Db::name('airdrop_eligibilities')->alias('e')
            ->join('airdrop_activities a', 'a.id=e.activity_id')
            ->where('e.user_id', Base::$userId)->where('e.status', 'eligible')
            ->field('e.*, a.name as activity_name');
        $total = (clone $q)->count();
        return ok(['total' => $total, 'data' => $q->page($page, $rows)->select()->toArray()], 'successfully !!!');
    }

    /** 空投发放（realtime/batch 共用）：生成记录+新资产 */
    private function grantAirdrop(array $act, int $uid): void
    {
        $recordId = Db::name('airdrop_records')->insertGetId([
            'activity_id' => $act['id'], 'user_id' => $uid,
            'phone' => Db::name('users')->where('id', $uid)->value('phone') ?? '',
            'collectible_id' => $act['collectible_id'], 'quantity' => $act['quantity_per_user'],
            'status' => 'pending',
        ]);
        if ($act['airdrop_mode'] === 'realtime') {
            $this->issueAirdrop($recordId);
        } else {
            Db::name('airdrop_eligibilities')->insert([
                'activity_id' => $act['id'], 'user_id' => $uid,
                'phone' => Db::name('users')->where('id', $uid)->value('phone') ?? '',
                'task_type' => $act['type'], 'task_completed_at' => date('Y-m-d H:i:s'), 'status' => 'eligible',
            ]);
        }
    }

    /** 执行发放：pending 记录 → 新资产 */
    public static function issueAirdrop(int $recordId): void
    {
        $r = Db::name('airdrop_records')->where('id', $recordId)->where('status', 'pending')->find();
        if (!$r || !$r['user_id']) return;
        $code = nextCollectionCode();
        $ucId = Db::name('user_collectibles')->insertGetId([
            'user_id' => $r['user_id'], 'collectible_id' => $r['collectible_id'],
            'airdrop_record_id' => $r['id'], 'serial' => (string)$code,
            'collection_code' => $code, 'source' => 'airdrop', 'acquired_price' => 0,
            'acquired_at' => date('Y-m-d H:i:s'), 'status' => 'held',
        ]);
        Db::name('airdrop_records')->where('id', $r['id'])->update(['status' => 'issued', 'user_collectible_id' => $ucId, 'issued_at' => date('Y-m-d H:i:s')]);
        Db::name('chain_tasks')->insert(['task_id' => 'CT' . strtoupper(md5(uniqid())), 'user_collectible_id' => $ucId, 'status' => 0]);
    }

    /* ===== 空态接口（源站实测形态，前端页面如实复刻空态） ===== */

    /** POST /api/user_collection/user_collection/getRecord —— 藏品流转记录（实测空） */
    public function flowRecord()
    {
        [$page, $rows] = pageParams();
        return ok(['total' => 0, 'per_page' => $rows, 'current_page' => $page, 'last_page' => 0, 'data' => []], 'successfully !!!');
    }

    /** POST /api/user/commision_info —— 佣金信息（实测空） */
    public function commisionInfo()
    {
        return ok(['total' => 0, 'data' => []], 'successfully !!!');
    }

    /** POST /api/user/commison_rank —— 佣金排行（实测空） */
    public function commisonRank()
    {
        return ok(['total' => 0, 'data' => []], 'successfully !!!');
    }

    /** POST /api/user/bei_yao_list —— 邀约列表（实测空） */
    public function beiYaoList()
    {
        return ok(['total' => 0, 'data' => []], 'successfully !!!');
    }

    /** POST /api/Activity/category —— 活动分类（实测 data:[]） */
    public function activityCategory()
    {
        return ok([], 'successfully !!!');
    }

    /** POST /api/activity/index —— 活动列表（实测 total:0） */
    public function activityIndex()
    {
        return ok(['total' => 0, 'data' => []], 'successfully !!!');
    }
}
