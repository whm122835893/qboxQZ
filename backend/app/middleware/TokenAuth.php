<?php
declare(strict_types=1);

namespace app\middleware;

use app\controller\Base;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

/**
 * Token 认证中间件：解析 JWT（header token 或 Authorization: Bearer），
 * 通过后挂载 Base::$userId / Base::$user 供控制器使用。
 */
class TokenAuth
{
    public function handle($request, \Closure $next)
    {
        $token = $request->header('token', '');
        if (!$token && preg_match('/Bearer\s+(\S+)/i', (string)$request->header('authorization', ''), $m)) {
            $token = $m[1];
        }
        if (!$token) return json(['code' => 401, 'msg' => '未登录', 'time' => (string)time(), 'data' => null]);
        try {
            $payload = JWT::decode($token, new Key(Base::jwtKey(), 'HS256'));
        } catch (\Throwable $e) {
            return json(['code' => 401, 'msg' => '登录已过期', 'time' => (string)time(), 'data' => null]);
        }
        $user = \app\model\User::where('id', $payload->uid)->whereNull('deleted_at')->find();
        if (!$user || $user->status != 1) {
            return json(['code' => 401, 'msg' => '账号不可用', 'time' => (string)time(), 'data' => null]);
        }
        Base::$userId = (int)$user->id;
        Base::$user = $user;
        return $next($request);
    }
}
