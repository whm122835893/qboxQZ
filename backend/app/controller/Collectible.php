<?php
declare(strict_types=1);

namespace app\controller;

use think\facade\Db;

/** 藏品与分类域：市场列表/热搜/详情/商品详情/关注/板块 */
class Collectible extends Base
{
    /** 聚合冗余字段 → 前端行结构 */
    private function rowMap(array $c, int $uid = 0): array
    {
        return [
            'name' => $c['name'], 'series_id' => (int)$c['category_id'], 'id' => (int)$c['id'],
            'listimg' => $c['image'], 'total_num' => (int)$c['edition'], 'flux' => (int)$c['circulate'],
            'deal_num' => (int)$c['deal_num'], 'min_price' => $c['min_price'],
            'market_fluctuate_new' => (float)$c['market_fluctuate_new'], 'is_out' => (int)$c['is_out'],
            'is_like' => $uid ? (int)Db::name('user_favorites')->where('user_id', $uid)->where('collectible_id', $c['id'])->count() : 0,
            'product' => ['id' => (int)$c['id'], 'name' => $c['name'], 'listimg' => $c['image'] . '?image_process=resize,l_350', 'total_num' => (int)$c['edition']],
        ];
    }

    /** POST /api/market/market/getMarketList */
    public function getMarketList()
    {
        $p = request()->param();
        $sid = (int)($p['series_id'] ?? 0);
        [$page, $rows] = pageParams();
        $q = Db::name('collectibles')->whereNull('deleted_at')->where('is_demo', 0);
        if ($sid === -2) {
            // 我的关注
            $favs = Db::name('user_favorites')->where('user_id', Base::$userId)->column('collectible_id');
            $q->whereIn('id', $favs ?: [0]);
        } elseif ($sid > 0 || $sid === -1) {
            $q->where('category_id', $sid);
        }
        $total = (clone $q)->count();
        $list = $q->order('min_price', 'desc')->page($page, $rows)->select()->toArray();
        return ok(array_map(fn($c) => $this->rowMap($c, Base::$userId), $list), '操作成功');
    }

    /** POST /api/market/market/marketrebang */
    public function marketrebang()
    {
        $rows = Db::name('collectibles')->whereNull('deleted_at')->where('is_demo', 0)
            ->order('hot_count', 'desc')->limit(6)
            ->field('id,name,image as listimg,edition as total_num,circulate as flux,min_price,is_like')->select()->toArray();
        return ok($rows, 'ok');
    }

    /** POST /api/market/market/marketGoodsDetail */
    public function marketGoodsDetail()
    {
        $id = (int)(request()->param('id') ?: 0);
        $c = Db::name('collectibles')->whereNull('deleted_at')->where('id', $id)->find();
        if (!$c) return json(['code' => 500, 'msg' => 'An error occurred', 'time' => (string)time(), 'data' => null]);
        $series = Db::name('series')->where('series_id', $c['category_id'])->find();
        $data = [
            'id' => (int)$c['id'], 'flux' => (int)$c['circulate'], 'face_status' => 0,
            'series_id' => (int)$c['category_id'], 'name' => $c['name'],
            'introduce_image' => $c['introduce_image'] ?? '', 'main_image' => $c['main_image'] ?: $c['image'],
            'total_num' => (int)$c['edition'], 'price' => $c['price'], 'release_time' => $c['release_date'] ? strtotime($c['release_date']) : 0,
            'listimg' => $c['image'], 'purchase_information' => $c['purchase_information'],
            'batch_pay' => (int)$c['batch_pay'], 'batch_pay_num' => (int)$c['batch_pay_num'],
            'reprice_limit' => $c['reprice_limit'], 'is_like' => (int)Db::name('user_favorites')->where('user_id', Base::$userId)->where('collectible_id', $id)->count(),
            'sell' => (int)$c['sell_count'], 'min_price' => $c['min_price'], 'is_out' => (int)$c['is_out'],
            'buy' => (int)$c['buy_count'], 'max_price' => $c['max_price'], 'is_demo' => (int)$c['is_demo'],
            'series' => ['id' => (int)$c['category_id'], 'name' => $series['series_name'] ?? '', 'pattern' => 1],
        ];
        return ok($data, '操作成功');
    }

    /** POST /api/box/collection/ProductDetail/ids/{id} */
    public function productDetail($id)
    {
        $c = Db::name('collectibles')->whereNull('deleted_at')->where('id', (int)$id)->find();
        if (!$c) return fail('找不到该商品，或者已经下架了');
        $data = [
            'id' => (int)$c['id'], 'name' => $c['name'], 'main_image' => $c['main_image'] ?: $c['image'],
            'listimg' => $c['image'], 'total_num' => (int)$c['edition'], 'flux' => (int)$c['circulate'],
            'min_price' => $c['min_price'], 'price' => $c['price'], 'reprice_limit' => $c['reprice_limit'],
            'purchase_information' => $c['purchase_information'], 'intro' => $c['intro'],
            'publisher' => ['name' => $c['creator'], 'company_logo' => '/static/qbox/logo.png'],
            'status' => $c['status'], 'is_like' => (int)Db::name('user_favorites')->where('user_id', Base::$userId)->where('collectible_id', $id)->count(),
        ];
        return ok($data, 'successfully !!!');
    }

    /** POST /api/user_collection/favorite */
    public function favorite()
    {
        $p = request()->param();
        $cid = (int)($p['collectible_id'] ?? 0);
        $action = (int)($p['action'] ?? 1);
        if (!Db::name('collectibles')->where('id', $cid)->find()) return fail('藏品不存在');
        if ($action === 1) {
            $exists = Db::name('user_favorites')->where('user_id', Base::$userId)->where('collectible_id', $cid)->count();
            if (!$exists) Db::name('user_favorites')->insert(['user_id' => Base::$userId, 'collectible_id' => $cid]);
        } else {
            Db::name('user_favorites')->where('user_id', Base::$userId)->where('collectible_id', $cid)->delete();
        }
        return ok(['collectible_id' => $cid, 'is_like' => $action], $action ? '关注成功' : '已取消关注');
    }
}
