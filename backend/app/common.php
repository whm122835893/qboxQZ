<?php
use think\Response;
use think\facade\Db;

/** 统一成功响应（源站格式 {code:1,msg,time,data}） */
function ok($data = null, string $msg = 'successfully !!!'): Response
{
    return json(['code' => 1, 'msg' => $msg, 'time' => (string)time(), 'data' => $data]);
}

/** 统一失败响应 */
function fail(string $msg = '操作失败', int $code = 0, $data = null): Response
{
    return json(['code' => $code, 'msg' => $msg, 'time' => (string)time(), 'data' => $data]);
}

/** 手机号脱敏 */
function maskMobile(?string $m): string
{
    return $m ? substr($m, 0, 3) . '****' . substr($m, -4) : '';
}

/** 生成订单号 2026MMDDHHMMSS + 21位数字（对齐源站规则） */
function genOrderNo(): string
{
    return date('YmdHis') . str_pad((string)mt_rand(0, 99999999), 8, '0', STR_PAD_LEFT) . str_pad((string)mt_rand(0, 999999), 6, '0', STR_PAD_LEFT);
}

/** 生成唯一藏品编号（全站递增） */
function nextCollectionCode(): int
{
    $row = Db::name('user_collectibles')->max('collection_code');
    return (int)$row + 1;
}

/** 相对时间（tradingInformation 展示规则：<1h分钟前 / <24h小时前 / 天前） */
function relativeTime(int $ts): string
{
    $diff = time() - $ts;
    if ($diff < 3600) return max(1, (int)floor($diff / 60)) . '分钟前';
    if ($diff < 86400) return floor($diff / 3600) . '小时前';
    return floor($diff / 86400) . '天前';
}

/** 分页参数 */
function pageParams(): array
{
    $page = max(1, (int)(request()->param('page') ?: 1));
    $rows = min(50, max(1, (int)(request()->param('list_rows') ?: 15)));
    return [$page, $rows];
}

/** 读系统配置（system_configs），带默认值兜底（禁止业务代码散落硬编码） */
function sysConfig(string $key, string $default = ''): string
{
    static $cache = [];
    if (!array_key_exists($key, $cache)) {
        $cache[$key] = (string)(\think\facade\Db::name('system_configs')->where('config_key', $key)->value('config_value') ?? '');
    }
    return $cache[$key] !== '' ? $cache[$key] : $default;
}

/** 平台服务费率（system_configs.platform_service_rate，默认 0.05） */
function platformFeeRate(): float
{
    return (float)sysConfig('platform_service_rate', '0.05');
}

/** 支付时限（秒）（system_configs.pay_time_limit，默认 300） */
function payTimeLimit(): int
{
    return (int)sysConfig('pay_time_limit', '300');
}

/** 卖家昵称脱敏（首字+****，与源站 db**** 形态一致） */
function maskNickname(?string $n): string
{
    $n = trim((string)$n);
    if ($n === '') return 'db****';
    return mb_substr($n, 0, 1) . '****';
}
