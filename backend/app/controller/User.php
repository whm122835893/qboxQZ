<?php
declare(strict_types=1);

namespace app\controller;

use app\model\User as UserModel;
use think\facade\Db;

/** 用户域：注册/登录/信息/资料/交易密码/实名/邀请/注销 */
class User extends Base
{
    /** POST /api/sms/send */
    public function sendSms()
    {
        $p = request()->param();
        $phone = (string)($p['phone'] ?? ($p['mobile'] ?? ''));
        $scene = (string)($p['scene'] ?? ($p['event'] ?? 'login'));
        if (!preg_match('/^1\d{10}$/', $phone)) return fail('手机号格式错误');
        $last = Db::name('verification_codes')->where('phone', $phone)->where('scene', $scene)->order('id', 'desc')->find();
        if ($last && (time() - strtotime($last['sent_at'])) < 60) return fail('发送过于频繁，请60秒后重试');
        $code = (string)mt_rand(100000, 999999);
        Db::name('verification_codes')->insert([
            'phone' => $phone, 'scene' => $scene, 'code' => password_hash($code, PASSWORD_DEFAULT),
            'expires_at' => date('Y-m-d H:i:s', time() + 300), 'sent_at' => date('Y-m-d H:i:s'),
        ]);
        // 生产环境此处调短信网关；本地 Mock 直接返回（注释掉线上务必移除）
        // mock_code 仅在调试模式下返回（生产接短信网关后移除）
        $data = ['expires_in' => 300];
        if (env('APP_DEBUG', false)) $data['mock_code'] = $code;
        return ok($data, '发送成功');
    }

    /** 校验短信验证码（一次性核销） */
    private function checkSms(string $phone, string $scene, string $code): ?string
    {
        $row = Db::name('verification_codes')
            ->where('phone', $phone)->where('scene', $scene)
            ->where('expires_at', '>', date('Y-m-d H:i:s'))->whereNull('used_at')
            ->order('id', 'desc')->find();
        if (!$row || !password_verify($code, $row['code'])) return '验证码错误或已过期';
        Db::name('verification_codes')->where('id', $row['id'])->update(['used_at' => date('Y-m-d H:i:s')]);
        return null;
    }

    /** POST /api/user/register */
    public function register()
    {
        $p = request()->param();
        // 源站参数名兼容：mobile||phone、inviter_code||invite_code
        $p['phone'] = $p['phone'] ?? ($p['mobile'] ?? '');
        $p['invite_code'] = $p['invite_code'] ?? ($p['inviter_code'] ?? '');
        foreach (['phone', 'code', 'password'] as $f) {
            if (empty($p[$f])) return fail("缺少参数 {$f}");
        }
        if (!preg_match('/^1\d{10}$/', (string)$p['phone'])) return fail('手机号格式错误');
        if (UserModel::where('phone', $p['phone'])->find()) return fail('该手机号已注册');
        if ($err = $this->checkSms((string)$p['phone'], 'register', (string)$p['code'])) return fail($err);

        $uid = 'U' . strtoupper(substr(md5($p['phone'] . uniqid()), 0, 8));
        $inviteCode = strtoupper(substr(md5(uniqid()), 0, 8));
        Db::startTrans();
        try {
            $user = UserModel::create([
                'phone' => $p['phone'], 'username' => 'Q' . strtoupper(substr(md5(uniqid()), 0, 8)),
                'uid' => $uid, 'invite_code' => $inviteCode,
                'password_hash' => password_hash((string)$p['password'], PASSWORD_DEFAULT),
                'invitee_bound' => (int)!empty($p['invite_code']),
                'last_login_at' => date('Y-m-d H:i:s'), 'login_count' => 1,
            ]);
            Db::name('wallets')->insert(['user_id' => $user->id, 'brand' => 'huifu']);
            // 邀请绑定（一人仅被邀请一次）
            if (!empty($p['invite_code'])) {
                $inviter = UserModel::where('invite_code', $p['invite_code'])->find();
                if ($inviter) {
                    Db::name('invite_records')->insert([
                        'inviter_id' => $inviter->id, 'invitee_id' => $user->id,
                        'invite_code' => $p['invite_code'], 'status' => 'registered',
                    ]);
                }
            }
            Db::commit();
        } catch (\Throwable $e) {
            Db::rollback();
            return fail('注册失败：' . $e->getMessage());
        }
        return ok(['token' => $this->issueToken((int)$user->id), 'user' => [
            'id' => $user->id, 'phone' => maskMobile($user->phone), 'nickname' => $user->username, 'is_realname' => 0,
        ]], '注册成功');
    }

    /** POST /api/user/login */
    public function login()
    {
        $p = request()->param();
        // 源站参数名兼容：account（登录页）|| phone
        $account = (string)($p['account'] ?? ($p['phone'] ?? ''));
        if ($account === '' || empty($p['password'])) return fail('缺少参数');
        $user = UserModel::where('phone', $account)->whereNull('deleted_at')->find();
        if (!$user || !password_verify((string)$p['password'], (string)($user->password_hash ?? ''))) {
            return fail('手机号或密码错误');
        }
        if ($user->status != 1) return fail('账号已被禁用');
        UserModel::where('id', $user->id)->inc('login_count')->update(['last_login_at' => date('Y-m-d H:i:s')]);
        // 源站实测（2026-10-24）：token 位于 data.userinfo.token
        $token = $this->issueToken((int)$user->id);
        return ok(['token' => $token, 'userinfo' => $this->userinfoPayload($user, $token)], '登录成功');
    }

    /** 源站 getUserInfo 同构的用户信息载荷 */
    private function userinfoPayload($u, ?string $token = null): array
    {
        $wallet = Db::name('wallets')->where('user_id', $u->id)->find();
        $data = [
            'id' => (int)$u->id, 'username' => $u->username, 'nickname' => $u->username,
            'avatar' => $u->avatar ?: '/defautAvatar.png', 'phone' => maskMobile($u->phone),
            'real_name_authentication' => (int)$u->is_realname,
            'is_realname' => (int)$u->is_realname,
            'real_name' => $u->is_realname ? mb_substr((string)$u->real_name, 0, 1) . '*' . mb_substr((string)$u->real_name, -1) : '',
            'id_card' => $u->id_card ? substr((string)$u->id_card, 0, 3) . '******' . substr((string)$u->id_card, -3) : '',
            'pay_password' => $u->transaction_password ? 1 : 0,
            'score' => (int)$u->score, 'money' => $wallet['balance'] ?? '0.00',
            'crystal_yellow' => $u->crystal_yellow, 'crystal_purple' => $u->crystal_purple, 'crystal_blue' => $u->crystal_blue,
            'level' => (int)$u->level, 'is_vip' => (int)$u->is_vip, 'nft_url' => (string)$u->nft_url,
            'huifu_account' => (int)$u->huifu_account, 'transfer_permission' => (int)$u->transfer_permission,
            'collection_num' => (int)$u->collection_num, 'collection_total_price' => (string)$u->collection_total_price,
            'blind_box_num' => (int)$u->blind_box_num,
            'statistics' => ['invited_count' => Db::name('invite_records')->where('inviter_id', $u->id)->count(), 'buy_count' => 0, 'sale_count' => 0],
        ];
        if ($token !== null) $data['token'] = $token;
        return $data;
    }

    /** POST /api/user/mobilelogin */
    public function mobilelogin()
    {
        $p = request()->param();
        // 源站参数名兼容：mobile||phone、captcha||code
        $phone = (string)($p['mobile'] ?? ($p['phone'] ?? ''));
        $code = (string)($p['captcha'] ?? ($p['code'] ?? ''));
        if ($phone === '' || $code === '') return fail('缺少参数');
        if ($err = $this->checkSms($phone, 'login', $code)) return fail($err);
        $user = UserModel::where('phone', $phone)->whereNull('deleted_at')->find();
        if (!$user) return fail('账号不存在，请先注册');
        if ($user->status != 1) return fail('账号已被禁用');
        UserModel::where('id', $user->id)->inc('login_count')->update(['last_login_at' => date('Y-m-d H:i:s')]);
        return ok(['token' => $this->issueToken((int)$user->id)], '登录成功');
    }

    /** POST /api/user/getUserInfo */
    public function getUserInfo()
    {
        return ok($this->userinfoPayload(self::$user), '个人信息');
    }

    /** POST /api/user/profile */
    public function profile()
    {
        $p = request()->param();
        $allow = ['username' => 'nickname', 'avatar' => 'avatar'];
        $upd = [];
        if (!empty($p['nickname'])) $upd['username'] = (string)$p['nickname'];
        if (!empty($p['avatar'])) $upd['avatar'] = (string)$p['avatar'];
        if (!empty($p['person_intro'])) $upd['person_intro'] = (string)$p['person_intro'];
        if (!$upd) return fail('无可更新字段');
        UserModel::where('id', self::$userId)->update($upd);
        return ok(null, '修改成功');
    }

    /** POST /api/user/setPayPassword */
    public function setPayPassword()
    {
        $p = request()->param();
        $pwd = (string)($p['pay_password'] ?? '');
        if (!preg_match('/^\d{6}$/', $pwd)) return fail('交易密码为6位数字');
        $has = (bool)(self::$user->transaction_password ?? '');
        if ($has) {
            // 修改：需旧密码或短信
            $old = (string)($p['old_pay_password'] ?? '');
            $sms = (string)($p['sms_code'] ?? '');
            if ($old === '' && $sms === '') return fail('请输入旧交易密码或短信验证码');
            if ($old !== '' && !password_verify($old, (string)self::$user->transaction_password)) return fail('旧交易密码错误');
        }
        UserModel::where('id', self::$userId)->update(['transaction_password' => password_hash($pwd, PASSWORD_DEFAULT)]);
        return ok(null, '交易密码设置成功');
    }

    /** POST /api/user/checkPaypass —— 校验并签发5分钟交易票据 */
    public function checkPaypass()
    {
        if ($resp = $this->verifyPayPassword()) return $resp;
        $ticket = md5(uniqid('pay', true));
        cache('pay_ticket:' . self::$userId, $ticket, 300);
        return ok(['ticket' => $ticket], '校验通过');
    }

    /** POST /api/user/realname_submit */
    public function realnameSubmit()
    {
        $p = request()->param();
        if (empty($p['real_name']) || empty($p['id_card'])) return fail('缺少参数');
        if (!preg_match('/^\d{17}[\dXx]$/', (string)$p['id_card'])) return fail('身份证号格式错误');
        // 生产环境接入三要素核验；本地直接通过
        UserModel::where('id', self::$userId)->update([
            'real_name' => $p['real_name'], 'id_card' => $p['id_card'], 'is_realname' => 1,
            'real_name_time' => date('Y-m-d H:i:s'),
        ]);
        return ok(['status' => 1, 'real_name' => mb_substr((string)$p['real_name'], 0, 1) . '*'], '认证通过');
    }

    /** POST /api/user/resetpwd */
    public function resetpwd()
    {
        $p = request()->param();
        if (empty($p['phone']) || empty($p['code']) || empty($p['password'])) return fail('缺少参数');
        if ($err = $this->checkSms((string)$p['phone'], 'reset_password', (string)$p['code'])) return fail($err);
        UserModel::where('phone', $p['phone'])->update(['password_hash' => password_hash((string)$p['password'], PASSWORD_DEFAULT)]);
        return ok(null, '密码重置成功');
    }

    /** POST /api/user/realName —— 实名认证提交（源站路径，certification 页使用；模拟三要素核验即过） */
    public function realName()
    {
        $p = request()->param();
        if (empty($p['real_name']) || empty($p['id_card'])) return fail('请填写姓名与身份证号');
        $idCard = (string)$p['id_card'];
        if (!preg_match('/^\d{17}[\dXx]$/', $idCard)) return fail('身份证号格式错误');
        UserModel::where('id', self::$userId)->update([
            'real_name' => (string)$p['real_name'],
            'id_card' => $idCard,
            'is_realname' => 1,
            'real_name_time' => date('Y-m-d H:i:s'),
        ]);
        return ok(['status' => 1], '认证成功');
    }

    /** POST /api/user/ranking */
    public function ranking()
    {
        $rows = Db::name('invite_records')->alias('ir')
            ->join('users u', 'u.id=ir.inviter_id')
            ->field('ir.inviter_id, COUNT(*) AS cnt, u.username, u.avatar')
            ->group('ir.inviter_id')->order('cnt', 'desc')->limit(50)->select()->toArray();
        $rank = array_map(fn($i, $r) => ['rank' => $i + 1, 'user_id' => $r['inviter_id'], 'count' => $r['cnt'], 'nickname' => $r['username'], 'avatar' => $r['avatar']], array_keys($rows), $rows);
        $mine = 0;
        foreach ($rank as $r) if ($r['user_id'] === self::$userId) { $mine = $r['rank']; break; }
        return ok(['ranklist' => $rank, 'self_rank' => $mine]);
    }

    /** GET /api/user/invite_info */
    public function inviteInfo()
    {
        $act = Db::name('invite_activities')->where('status', 'enabled')->whereNull('deleted_at')->order('id', 'desc')->find();
        return ok([
            'invite_code' => self::$user->invite_code,
            'invited_count' => Db::name('invite_records')->where('inviter_id', self::$userId)->count(),
            'activity' => $act ? ['name' => $act['name'], 'start_time' => $act['start_time'], 'end_time' => $act['end_time']] : null,
        ]);
    }

    /** POST /api/user/invite_records */
    public function inviteRecords()
    {
        [$page, $rows] = pageParams();
        $q = Db::name('invite_records')->alias('ir')
            ->join('users u', 'u.id=ir.invitee_id')
            ->field('ir.id, ir.status, ir.created_at, u.username, u.avatar')
            ->where('ir.inviter_id', self::$userId);
        $total = (clone $q)->count();
        $rows = $q->page($page, $rows)->order('ir.id', 'desc')->select()->toArray();
        return ok(['total' => $total, 'data' => $rows]);
    }

    /** POST /api/user/destroy */
    public function destroy()
    {
        if ($resp = $this->verifyPayPassword()) return $resp;
        $hasAsset = Db::name('user_collectibles')->where('user_id', self::$userId)->whereIn('status', ['held', 'consigned', 'frozen'])->count();
        if ($hasAsset) return fail('存在持有藏品，无法注销');
        UserModel::where('id', self::$userId)->update(['deleted_at' => date('Y-m-d H:i:s'), 'status' => 0]);
        return ok(null, '注销成功');
    }
}
