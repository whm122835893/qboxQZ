<?php
declare(strict_types=1);

namespace app\controller;

use Firebase\JWT\JWT;

/**
 * API 基类：JWT 签发/解析、登录用户上下文、交易密码校验、通用分页。
 * 所有业务控制器继承本类。
 */
abstract class Base
{
    /** JWT 签名密钥（生产环境放 env） */
    /** JWT 密钥：env('JWT_KEY') 可覆盖（生产必改强随机） */
    public static function jwtKey(): string
    {
        return (string)env('JWT_KEY', 'qbox-nft-api-jwt-secret-key-2026-0126xyz');
    }
    public const JWT_TTL = 604800; // 7天

    /** 当前登录用户ID（TokenAuth 中间件挂载） */
    public static int $userId = 0;
    /** 当前登录用户行 */
    public static ?\think\Model $user = null;

    /**
     * 校验交易密码 / 交易票据。
     * 支持：Body.pay_password（bcrypt 比对）或 Body.ticket（checkPaypass 签发的临时票据，存缓存5分钟）。
     */
    protected function verifyPayPassword(): ?\think\Response
    {
        $p = request()->param();
        $ticket = (string)($p['ticket'] ?? '');
        if ($ticket !== '' && cache('pay_ticket:' . self::$userId) === $ticket) {
            return null; // 票据有效
        }
        $pwd = (string)($p['pay_password'] ?? '');
        if ($pwd === '') {
            return json(['code' => 403, 'msg' => '请输入交易密码', 'time' => (string)time(), 'data' => null]);
        }
        $hash = (string)(self::$user->transaction_password ?? '');
        if ($hash === '' || !password_verify($pwd, $hash)) {
            return json(['code' => 403, 'msg' => '交易密码错误', 'time' => (string)time(), 'data' => null]);
        }
        return null;
    }

    /** 实名前置校验（购买/寄售/求购/委托） */
    protected function requireRealname(): ?\think\Response
    {
        if ((int)(self::$user->is_realname ?? 0) !== 1) {
            return json(['code' => 0, 'msg' => '请先完成实名认证', 'time' => (string)time(), 'data' => null]);
        }
        return null;
    }

    /** 签发 JWT */
    protected function issueToken(int $uid): string
    {
        $now = time();
        $payload = [
            'uid' => $uid,
            'iat' => $now,
            'exp' => $now + self::JWT_TTL,
        ];
        return JWT::encode($payload, self::jwtKey(), 'HS256');
    }
}
