<?php
declare(strict_types=1);

namespace app\controller;

use think\facade\Db;

/** 内容与配置域：getConfig/站点设置/轮播/公告/新闻/板块 */
class Index extends Base
{
    /** POST /api/index/getConfig */
    public function getConfig()
    {
        $keys = (string)(request()->param('key') ?? '');
        if ($keys === '') return fail('缺少参数 key');
        $rows = Db::name('system_configs')->whereIn('config_key', explode(',', $keys))->column('config_value', 'config_key');
        return ok($rows, '基本配置');
    }

    /** GET /api/site/settings */
    public function siteSettings()
    {
        return ok(Db::name('site_settings')->column('setting_value', 'setting_key'), '站点配置');
    }

    /** POST /api/banner/show/getBanner */
    public function getBanner()
    {
        return ok(Db::name('banners')->whereNull('deleted_at')->where('is_active', 1)
            ->order('sort_order')->field('id,image,description,url,jump_type')->select()->toArray(), '轮播图');
    }

    /** POST /api/index/afficheList */
    public function afficheList()
    {
        [$page, $rows] = pageParams();
        $q = Db::name('announcements')->whereNull('deleted_at')->where('type', 'notice');
        if ($sub = (string)(request()->param('subtype') ?: '')) $q->where('subtype', $sub);
        $total = (clone $q)->count();
        $list = $q->order('is_top', 'desc')->order('sort_order', 'desc')->order('created_at', 'desc')
            ->page($page, $rows)->field('id,title,summary,cover_image,subtype,is_top,read_count,created_at')->select()->toArray();
        return ok(['total' => $total, 'per_page' => $rows, 'current_page' => $page, 'data' => $list], '公告列表');
    }

    /** POST /api/index/affichetypelist */
    public function affichetypelist()
    {
        return ok([
            ['id' => 1, 'name' => '官方'], ['id' => 2, 'name' => '寄售'], ['id' => 3, 'name' => '运营'],
            ['id' => 4, 'name' => '限价'], ['id' => 5, 'name' => '活动'], ['id' => 6, 'name' => '合成'],
            ['id' => 7, 'name' => '求购'], ['id' => 8, 'name' => '空投'],
        ], '查询成功');
    }

    /** GET /api/afficheDetail/ids/{id} */
    public function afficheDetail($id)
    {
        $row = Db::name('announcements')->whereNull('deleted_at')->where('id', (int)$id)->find();
        if (!$row) return fail('公告不存在');
        Db::name('announcements')->where('id', $row['id'])->inc('read_count')->update();
        return ok($row, '详情');
    }

    /** POST /api/discover/ant_news/category */
    public function newsCategory()
    {
        return ok([['id' => 1, 'name' => '新闻中心']], 'successfully !!!');
    }

    /** POST /api/discover/ant_news/list */
    public function newsList()
    {
        [$page, $rows] = pageParams();
        $q = Db::name('announcements')->whereNull('deleted_at')->where('type', 'news');
        $total = (clone $q)->count();
        $list = $q->order('created_at', 'desc')->page($page, $rows)
            ->field('id,title,cover_image as image,subtype as category,created_at')->select()->toArray();
        return ok(['total' => $total, 'per_page' => $rows, 'current_page' => $page, 'last_page' => (int)ceil(max(1, $total) / $rows), 'data' => $list], 'successfully !!!');
    }

    /** POST /api/index/getSeries */
    public function getSeries()
    {
        $rows = Db::name('series')->order('sort_order')->field('series_id,series_name,is_def,flag,pattern,resale_put_start_time,resale_put_end_time,resale_buy_start_time,resale_buy_end_time')->select()->toArray();
        if (Base::$userId) {
            $favSeries = Db::name('user_favorites')->alias('f')
                ->join('collectibles c', 'c.id=f.collectible_id')
                ->where('f.user_id', Base::$userId)->column('DISTINCT c.category_id');
            foreach ($rows as &$r) {
                if ((int)$r['series_id'] === -2) $r['fav_series'] = $favSeries;
            }
        }
        return ok($rows, '系列');
    }

    /** GET /api/index/customSeriesList */
    public function customSeriesList()
    {
        return ok(['custom_series' => [], 'platform_series' => []], 'successfully!!!');
    }
}
