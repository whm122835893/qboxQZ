<?php
declare(strict_types=1);

namespace app\service;

use think\facade\Db;

/**
 * 支付闭环服务（模拟汇付）：
 * 订单 pending → completed，事务内完成：资产迁移 / 挂单回写 / 双向钱包流水 / 上链任务 / 交易流水 / 聚合统计。
 * 幂等：重复调用直接返回成功。
 */
class PayService
{
    public static function completeOrder(int $orderId, string $channel = 'huifu', array $raw = []): array
    {
        $order = Db::name('orders')->where('id', $orderId)->lock(true)->find();
        if (!$order) return ['ok' => false, 'msg' => '订单不存在'];
        if ($order['status'] === 'completed') return ['ok' => true, 'msg' => '支付成功（幂等）'];
        if ($order['status'] !== 'pending') return ['ok' => false, 'msg' => '订单状态不可支付'];

        Db::startTrans();
        try {
            $now = date('Y-m-d H:i:s');
            // 1. 订单完成
            Db::name('orders')->where('id', $orderId)->update([
                'status' => 'completed', 'pay_way' => $channel, 'pay_scene' => 'H5',
                'pay_time' => $now, 'completed_at' => $now,
                'transaction_id' => $order['order_no'],
                'api_create_data' => json_encode($raw ?: ['mock' => true, 'resp_code' => 'C00000'], JSON_UNESCAPED_UNICODE),
            ]);
            // 2. 支付记录
            Db::name('payments')->where('order_id', $orderId)->update([
                'status' => 'success', 'transaction_no' => $order['order_no'],
                'paid_at' => $now, 'raw_response' => json_encode($raw ?: ['mock' => true], JSON_UNESCAPED_UNICODE),
            ]);

            if ($order['source'] === 'market' && $order['resale_listing_id']) {
                // 3. 市场单：资产迁移
                $listing = Db::name('resale_listings')->where('id', $order['resale_listing_id'])->lock(true)->find();
                if ($listing && $listing['status'] !== 'sold') {
                    $uc = Db::name('user_collectibles')->where('id', $listing['user_collectible_id'])->lock(true)->find();
                    if ($uc) {
                        // 归属迁移：同一份藏品的编号/链上身份不变，仅转移持有人（非同质化单行模型）
                        Db::name('user_collectibles')->where('id', $uc['id'])->update([
                            'user_id' => $order['user_id'], 'status' => 'held', 'is_consigned' => 0,
                            'order_id' => $orderId, 'buy_price' => $order['unit_price'],
                            'acquired_price' => $order['unit_price'], 'acquired_at' => $now,
                        ]);
                        // 触发上链过户任务
                        Db::name('chain_tasks')->insert([
                            'task_id' => 'CT' . strtoupper(md5(uniqid())),
                            'user_collectible_id' => $uc['id'], 'status' => 0,
                        ]);
                    }
                    // 4. 挂单回写
                    Db::name('resale_listings')->where('id', $listing['id'])->update([
                        'status' => 'sold', 'buyer_id' => $order['user_id'], 'order_no' => $order['order_no'], 'sold_at' => $now,
                    ]);
                    // 5. 卖家到账 + 双向钱包流水
                    if ($listing['seller_id']) {
                        Db::name('wallets')->where('user_id', $listing['seller_id'])->inc('balance', (float)$order['real_income'])->update();
                        Db::name('wallets')->where('user_id', $listing['seller_id'])->inc('available', (float)$order['real_income'])->update();
                        Db::name('wallet_transactions')->insert([
                            'user_id' => $listing['seller_id'], 'trans_type' => 'reward',
                            'title' => '寄售成交「' . $order['goods_name'] . '」',
                            'direction' => 1, 'amount' => $order['real_income'],
                            'balance_after' => Db::name('wallets')->where('user_id', $listing['seller_id'])->value('balance'),
                            'biz_no' => $order['order_no'],
                        ]);
                    }
                }
            } elseif ($order['source'] === 'release') {
                // 发售购买：生成新凭证
                $ucId = Db::name('user_collectibles')->insertGetId([
                    'user_id' => $order['user_id'], 'collectible_id' => $order['collectible_id'],
                    'order_id' => $orderId, 'serial' => (string)nextCollectionCode(),
                    'collection_code' => nextCollectionCode(), 'source' => 'purchase',
                    'acquired_price' => $order['unit_price'], 'acquired_at' => $now, 'status' => 'held',
                ]);
                Db::name('chain_tasks')->insert([
                    'task_id' => 'CT' . strtoupper(md5(uniqid())), 'user_collectible_id' => $ucId, 'status' => 0,
                ]);
                Db::name('collectibles')->where('id', $order['collectible_id'])->inc('sold')->dec('locked_quantity', $order['quantity'])->update();
            }

            // 6. 交易流水 + 买家流水
            Db::name('trade_records')->insert([
                'collectible_id' => $order['collectible_id'], 'type' => 'deal',
                'collection_code' => Db::name('resale_listings')->where('id', $order['resale_listing_id'])->value('collection_code'),
                'price' => $order['unit_price'], 'num' => $order['quantity'],
                'from_user_id' => $order['seller_id'], 'to_user_id' => $order['user_id'], 'order_no' => $order['order_no'],
            ]);
            Db::name('wallet_transactions')->insert([
                'user_id' => $order['user_id'], 'trans_type' => 'buy',
                'title' => '购买「' . $order['goods_name'] . '」', 'direction' => 2,
                'amount' => $order['total_price'],
                'balance_after' => Db::name('wallets')->where('user_id', $order['user_id'])->value('balance') ?? '0.00',
                'biz_no' => $order['order_no'],
            ]);
            // 7. 聚合统计
            Db::name('collectibles')->where('id', $order['collectible_id'])->inc('deal_num')->update();
            Db::name('users')->where('id', $order['user_id'])->inc('buy_collection_total_price', (float)$order['total_price'])->update();
            Db::commit();
            return ['ok' => true, 'msg' => '支付成功'];
        } catch (\Throwable $e) {
            Db::rollback();
            return ['ok' => false, 'msg' => '支付处理失败：' . $e->getMessage()];
        }
    }
}
