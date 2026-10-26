# QBOX 后端 API（ThinkPHP 8）

对接前端（Vue3 复刻项目）的源站同构 API。接口契约见 `../docs/08-后端API接口设计.md`，数据库见 `../sql/init.sql`（40 张 nft_ 表）。

## 环境
- PHP >= 8.0（含 mbstring/pdo_mysql/bcmath/json）
- MySQL 8 / MariaDB 10.11

## 快速启动
```bash
composer install                       # 安装依赖（含 firebase/php-jwt）
mysql -u root < ../sql/init.sql        # 建库建表（40 张 nft_ 前缀表）
php think run --host 127.0.0.1 --port 8000
# API 根路径：http://127.0.0.1:8000/api/...
```

## 前后端联调
- 前端 `.env.local` 写 `VITE_USE_MOCK=false`，vite proxy `/api` → 8000
- 种子数据：`docs/09-前后端联调报告.md`（13 藏品 + 6 挂单 + 双账号）
- 测试账号：买家 17587881293/whm981004；卖家 13600000001（持 6 条挂单）
- 联调结论：登录→市场→购买→订单→支付→资产迁移→仓库入账 全链路通（详见报告）

## 目录
```
app/
├─ common.php            统一响应 ok()/fail()、订单号/藏品编号生成、相对时间
├─ middleware/TokenAuth  JWT 认证（header token 或 Authorization: Bearer）
├─ controller/
│  ├─ Base.php           JWT签发/交易密码校验(bcrypt+5min票据)/实名前置
│  ├─ User.php           注册/登录/信息/资料/交易密码/实名/邀请/注销
│  ├─ Index.php          配置/站点设置/轮播/公告/新闻/板块
│  ├─ Wallet.php         钱包资产/流水
│  ├─ Address.php        收货地址 CRUD
│  ├─ Collectible.php    市场列表/热搜/藏品详情/商品详情/关注
│  ├─ Market.php         挂单列表/寄售详情/下单(锁单)/上架/求购/委托/流水/模拟支付回调
│  ├─ Order.php          订单列表/详情/模拟支付/取消/VIP单
│  ├─ Mycollection.php   仓库/持仓详情
│  ├─ Blindbox.php       发售日历/详情/购买/开启(概率加权)/记录
│  ├─ Synthesis.php      合成活动/玩法/材料校验/提交/记录
│  ├─ Activity.php       签到/补签/抽奖/龙晶商城/空投
│  └─ Sms.php            短信验证码（Mock 码直返，生产接网关）
└─ service/PayService.php 支付闭环：资产归属迁移/挂单回写/双向钱包流水/上链任务/交易流水/聚合统计（幂等）
```

## 冒烟测试（已全部通过）
```
1. POST /api/sms/send                    → mock_code
2. POST /api/user/register               → JWT
3. POST /api/user/getUserInfo            → 用户+钱包聚合
4. POST /api/market/market/getMarketList → 板块列表
5. POST /api/market/market/getMarketGoodsListByGoodsId → 挂单列表（价格升序）
6. POST /api/market/market/MarketDetail/ids/1 → 寄售详情（服务费/编号/合约脱敏）
7. POST /api/market/market/createOrder   → 挂单 locked + 凭证 frozen
8. POST /api/order/order/payOrder        → completed + 资产归属迁移 + 卖家到账 + 上链任务 + 交易流水
```
测试账号：买家 13700000001/abc12345（交易密码 123456）；卖家 13600000001。

## 安全要点（生产必改）
- `Base::JWT_KEY` 换成环境变量强随机密钥
- 短信 `mock_code` 移除；真实接汇付时 `PayService` 替换为验签回调
- 开启 HTTPS；敏感操作记审计日志
