<?php
declare(strict_types=1);

namespace app\controller;

use think\facade\Db;

/** 钱包域 */
class Wallet extends Base
{
    /** POST /api/wallet/wallet */
    public function wallet()
    {
        $w = Db::name('wallets')->where('user_id', Base::$userId)->find();
        return ok([
            'balance' => $w['balance'] ?? '0.00', 'available' => $w['available'] ?? '0.00',
            'frozen' => $w['frozen'] ?? '0.00', 'points' => $w['points'] ?? '0.00',
            'crystal_yellow' => self::$user->crystal_yellow, 'crystal_purple' => self::$user->crystal_purple, 'crystal_blue' => self::$user->crystal_blue,
        ], 'successfully !!!');
    }

    /** POST /api/wallet/transactions */
    public function transactions()
    {
        [$page, $rows] = pageParams();
        $q = Db::name('wallet_transactions')->where('user_id', Base::$userId);
        if ($t = (string)(request()->param('trans_type') ?: '')) $q->where('trans_type', $t);
        $total = (clone $q)->count();
        return ok(['total' => $total, 'data' => $q->page($page, $rows)->order('id', 'desc')->select()->toArray()], 'successfully !!!');
    }
}
