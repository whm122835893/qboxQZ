<?php
declare(strict_types=1);

namespace app\controller;

use think\facade\Db;

/** 收货地址域 */
class Address extends Base
{
    public function list()
    {
        return ok(Db::name('user_addresses')->where('user_id', Base::$userId)->order('isdef', 'desc')->select()->toArray(), '地址列表');
    }
    public function edit()
    {
        $p = request()->param();
        foreach (['name', 'phone', 'address'] as $f) if (empty($p[$f])) return fail("缺少参数 {$f}");
        $data = ['name' => $p['name'], 'phone' => $p['phone'],
            'province' => (string)($p['province'] ?? ''), 'city' => (string)($p['city'] ?? ''),
            'district' => (string)($p['district'] ?? ''), 'address' => $p['address'],
            'is_default' => (int)($p['isdef'] ?? 0)];
        if ((int)($p['isdef'] ?? 0) === 1) Db::name('user_addresses')->where('user_id', Base::$userId)->update(['is_default' => 0]);
        if (!empty($p['id'])) {
            Db::name('user_addresses')->where('id', (int)$p['id'])->where('user_id', Base::$userId)->update($data);
            return ok(null, '修改成功');
        }
        Db::name('user_addresses')->insert(array_merge($data, ['user_id' => Base::$userId]));
        return ok(null, '新增成功');
    }
    public function del()
    {
        Db::name('user_addresses')->where('id', (int)(request()->param('id') ?: 0))->where('user_id', Base::$userId)->delete();
        return ok(null, '删除成功');
    }
    public function detail()
    {
        return ok(Db::name('user_addresses')->where('id', (int)(request()->param('id') ?: 0))->where('user_id', Base::$userId)->find(), '详情');
    }
}
