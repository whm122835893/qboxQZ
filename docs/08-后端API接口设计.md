# QBOX 后端 API 接口设计文档（对接前端 + nft_ 数据库 v3）

> 版本：v1.1　日期：2026-10-26
> 依据：docs/03-API接口文档（67 个源站实测接口）+ docs/06 v3 数据模型（40 张 nft_ 表）+ 前端页面清单
> **范围说明**：按需求确认，**不包含文物展馆（artifacts）与转赠确认（transfers）功能的接口**——转赠在数据层保留 transfers 表与 trade_records(type='gift') 流水（赠送记录页仅读流水展示），不提供发起/确认转赠的操作端点。
>
> **设计原则**
> 1. **沿用源站路径与参数风格**（前端复刻已按源站路径对接，后端零改造成本）；源站缺失的功能（模拟支付、签到执行、盲盒购买、藏品关注、龙晶兑换执行等）按同风格新增，标注 **[新增]**
> 2. 统一响应结构：`{ "code": 1, "data": {...}, "message": "成功" }`；`code=1` 成功、`code=0` 业务失败、`code=401` 未登录、`code=403` 交易密码校验失败、`code=500` 服务器错误
> 3. 认证：除登录/注册/短信/公开内容外均需请求头 `Authorization: Bearer <JWT>`；标注 **🔐 交易密码** 的接口需在 Body 附 `pay_password`（服务端 bcrypt 比对；或凭 /api/user/checkPaypass 签发的 5 分钟票据 ticket）
> 4. 每个端点标注「涉及表」（v3 40 张 nft_ 表）；返回示例为关键端点完整结构，组内同构端点给 data 字段说明

**模块总览：16 域 78 个端点**
认证用户 14 · 钱包 2 · 地址 4 · 内容配置 8 · 藏品分类 4 · 市场 14 · 持仓 3 · 订单支付 7 · 盲盒 5 · 合成 6 · 签到 3 · 抽奖 4 · 龙晶商城 4 · 空投 3 · 邀请 2 · 系统站点 1

---

## 一、认证与用户域（14 个端点）

### 1.1 发送短信验证码
- **POST /api/sms/send**　公开
- Body：`{ phone: string(11) 必填, scene: string 必填(register|login|reset_password) }`
- 逻辑：60s 频控 → 生成 6 位码 → 哈希落库（5 分钟有效）→ 调短信网关（Mock 可假发送）
- 返回：`{ "code": 1, "data": { "expires_in": 300 }, "message": "发送成功" }`　表：verification_codes

### 1.2 注册
- **POST /api/user/register**　公开
- Body：`{ phone 必填, code 必填(短信码), password 必填(bcrypt存储), invite_code 可选(邀请码) }`
- 逻辑：校验验证码 → 创建 users（生成 uid/invite_code）→ 建钱包 → 注册时间窗内触发注册空投 → 绑定 invite_records
- 返回：`{ "code": 1, "data": { "token": "<JWT>", "user": {id, phone, nickname, is_realname} }, "message": "注册成功" }`　表：users/wallets/invite_records/airdrop_*

### 1.3 账号密码登录
- **POST /api/user/login**　公开　Body：`{ phone 必填, password 必填 }`
- 逻辑：bcrypt 校验 → 签发 JWT（7d）→ login_count+1、last_login_at（登录空投窗口判定）
- 返回：`{ "code": 1, "data": { "token": "<JWT>" }, "message": "登录成功" }`　表：users

### 1.4 手机验证码登录
- **POST /api/user/mobilelogin**　公开　Body：`{ phone 必填, code 必填 }`　逻辑：同 1.3 免密（未注册可自动注册）　表：users

### 1.5 获取用户信息
- **POST /api/user/getUserInfo**　需登录　Body：`{}`
- 逻辑：读 users + wallets 汇总
- 返回 data：`{ id, username, nickname, avatar, phone, is_realname, real_name(脱敏"王*绵"), id_card(脱敏), pay_password(0/1), score, money, crystal_yellow, crystal_purple, crystal_blue, level, is_vip, nft_url, huifu_account, transfer_permission, collection_num, collection_total_price, blind_box_num, statistics:{invited_count, buy_count, sale_count} }`　表：users/wallets

### 1.6 修改资料
- **POST /api/user/profile**　需登录
- Body：`{ nickname 可选, avatar 可选(文件URL), gender 可选, birthday 可选, person_intro 可选 }`
- 逻辑：更新 users；头像对象存储直传后回填 URL　表：users

### 1.7 设置/修改交易密码
- **POST /api/user/setPayPassword**　需登录；首次免旧密，修改需旧密码或短信
- Body：`{ pay_password 必填(6位，bcrypt), old_pay_password 条件必填, sms_code 条件必填 }`
- 逻辑：哈希落 users.transaction_password，pay_password=1　表：users

### 1.8 校验交易密码（预检）
- **POST /api/user/checkPaypass**　需登录 🔐
- Body：`{ pay_password 必填 }`
- 逻辑：比对哈希；通过签发 5 分钟「交易确认票据」ticket，后续支付/合成/寄售凭 ticket 免重复输入
- 返回：`{ "code":1, "data": { "ticket": "<5min票据>" } }`　表：users

### 1.9 重置登录密码
- **POST /api/user/resetpwd**　公开（需短信）　Body：`{ phone 必填, code 必填, password 必填 }`　表：users

### 1.10 提交实名认证
- **POST /api/user/realname_submit**　需登录 **[新增]**
- Body：`{ real_name 必填, id_card 必填, id_card_front 可选(URL), id_card_back 可选 }`
- 逻辑：写入 users（AES-256/SM4 加密存储），is_realname=0 待审核；接三要素核验自动通过则置 1
- 返回：`{ "code":1, "data": { "status":1, "real_name":"王*绵" }, "message":"认证通过" }`　表：users

### 1.11 邀请排行 / 1.12 邀请绑定信息 / 1.13 我的邀请记录 / 1.14 注销账号
- **POST /api/user/ranking**　需登录　Body：`{}`　→ 邀请人数排行　表：invite_records/users
- **GET /api/user/invite_info**　需登录 **[新增]**　→ 我的邀请码 + 当前生效邀请活动（invite_activities）　表：users/invite_activities
- **POST /api/user/invite_records**　需登录 **[新增]**　Body：`{ page }`　→ 我邀请的人（状态 pending/registered）　表：invite_records
- **POST /api/user/destroy**　需登录 **[新增]**　Body：`{ pay_password 必填 }`　逻辑：无持仓/余额校验 → 软删除 users.deleted_at　表：users

---

## 二、钱包域 [新增]（2 个端点）

- **POST /api/wallet/wallet**　需登录　Body：`{}`　逻辑：读 wallets + users.crystal_*
  - data：`{ balance, available, frozen, points, crystal_yellow, crystal_purple, crystal_blue }`　表：wallets/users
- **POST /api/wallet/transactions**　需登录　Body：`{ page, list_rows 可选, trans_type 可选(recharge|buy|withdraw|reward|refund) }`　→ 流水分页倒序　表：wallet_transactions

---

## 三、收货地址域（4 个端点）

- **POST /api/user/address_list**　需登录　Body：`{}`　→ 列表　表：user_addresses
- **POST /api/user/edit_address**　需登录　Body：`{ id 可选(有=编辑/无=新增), name 必填, phone 必填, province/city/district/address 必填, isdef 可选(1=设默认并清零其它) }`　表：user_addresses
- **POST /api/user/delUserAddress**　需登录　Body：`{ id 必填 }`　表：user_addresses
- **POST /api/user/address_detail**　需登录　Body：`{ id 必填 }`　→ 单条　表：user_addresses

---

## 四、内容与配置域（8 个端点）

- **POST /api/index/getConfig**　公开　Body：`{ key 必填(逗号分隔) }`　→ system_configs 指定键值　表：system_configs
- **GET /api/site/settings**　公开 **[新增]**　→ site_settings 全量（site_name/site_logo/button_* 颜色）　表：site_settings
- **POST /api/banner/show/getBanner**　公开　Body：`{ type 可选 }`　→ 启用轮播按排序　表：banners
- **POST /api/index/afficheList**　公开　Body：`{ type_id 可选, page, list_rows }`　→ 公告分页（置顶优先）　表：announcements
- **POST /api/index/affichetypelist**　公开　→ 8 分类字典（官方/寄售/运营/限价/活动/合成/求购/空投）　表：announcements.subtype
- **GET /api/afficheDetail/ids/{id}**　公开　Path：`id 必填`　→ 详情 + read_count+1　表：announcements
- **POST /api/discover/ant_news/category**　公开　→ 新闻分类　表：announcements
- **POST /api/discover/ant_news/list**　公开　Body：`{ category_id 可选, page }`　→ 新闻分页　表：announcements

---

## 五、藏品与分类域（4 个端点）

### 5.1 板块列表
- **POST /api/index/getSeries**　公开（登录附加关注态）　Body：`{ type 可选(=10) }`
- 返回 data：`[ { series_id:-2, series_name:"我的关注", is_def:0, flag:0, pattern:1, resale_put_start_time, resale_put_end_time, resale_buy_start_time, resale_buy_end_time } ]`　表：series

### 5.2 自定义分类
- **GET /api/index/customSeriesList**　公开　→ `{ custom_series:[], platform_series:[] }`　表：series

### 5.3 藏品商品详情（发售详情页）
- **POST /api/box/collection/ProductDetail/ids/{id}**　登录可选　Path：`id 必填`
- 逻辑：collectibles 直读聚合冗余 + 当前用户 is_like；无效 id → `{code:0,"message":"找不到该商品，或者已经下架了"}`
- 返回 data：`{ id, name, main_image, listimg, total_num, flux, min_price, max_price, market_fluctuate_new, reprice_limit, purchase_information, release_date, status:"onsale", publisher:{name,company_logo}, intro, is_like }`　表：collectibles/user_favorites

### 5.4 藏品关注/取消关注 **[新增]**
- **POST /api/user_collection/favorite**　需登录　Body：`{ collectible_id 必填, action 必填(1关注/0取消) }`
- 逻辑：维护 user_favorites（UNIQUE 防重）+ collectibles.is_like 反规范化　表：user_favorites/collectibles

---

## 六、市场域（14 个端点）

### 6.1 市场列表
- **POST /api/market/market/getMarketList**　需登录
- Body：`{ goods_type 必填(=2), page 必填, list_rows 必填, series_id 必填(-1/11/2/-2/0), add_deal_num 可选 }`
- 逻辑：-2 → user_favorites JOIN；0/空 → 全部；其余按板块；聚合冗余直读；is_out=1 标记退市
- 返回 data：`[ { name, series_id, id, listimg, total_num, flux, deal_num, min_price, market_fluctuate_new, is_out, product:{id,name,listimg,total_num} } ]`　表：collectibles/user_favorites

### 6.2 市场热搜榜
- **POST /api/market/market/marketrebang**　需登录　→ collectibles 按 hot_count TOP6　表：collectibles

### 6.3 藏品详情
- **POST /api/market/market/marketGoodsDetail**　需登录
- Body：`{ id 必填, type 必填(=2) }`　无效 id → `{code:500,"message":"An error occurred"}`
- 返回 data：`{ id, name, flux, total_num, min_price, max_price, main_image, listimg, reprice_limit, purchase_information, series:{id,name}, sell, buy, is_out, is_like }`　表：collectibles/series

### 6.4 挂单列表
- **POST /api/market/market/getMarketGoodsListByGoodsId**　需登录
- Body：`{ id 必填, page 必填, sort 必填(price), type 必填(=2), order 必填(asc) }`
- 返回 data：`{ list:[ { id:16515, collection_code:387, price:"9999", total_num:500, status:1, income_type:["huifu"] } ], count, ltnum }`　表：resale_listings/user_collectibles

### 6.5 寄售详情
- **POST /api/market/market/MarketDetail/ids/{id}**　需登录
- Path：`id 必填(挂单ID)`　逻辑：resale_listings JOIN collectibles/user_collectibles/users（卖家脱敏）；locked 态返回锁单；校验购买时段（series.resale_buy_time）
- 返回 data：`{ id, goods_id, price, status(1在售/4锁单), goods_name, goods_cover_image, series_name, income_type, pay_platform_service_rate, platform_service_price, real_income, user:{nickname:"db****",avatar,mobile:"130****9956"}, user_collection:{collection_code, user_hash_text, rz_code}, product:{ flux, total_num, purchase_information, publisher:{name,company_logo} } }`　表：resale_listings/collectibles/user_collectibles/users/series

### 6.6 市场下单（创建待支付订单）
- **POST /api/market/market/createOrder**　需登录 🔐
- Body：`{ market_order_id 必填(挂单ID), pay_password 必填 }`
- 逻辑（事务）：实名校验 → 交易密码校验 → 挂单 selling 校验并置 **locked** → 建 orders(pending, source='market', expires_at=now+pay_time_limit) + payments(pending)。资产迁移延迟到支付回调成功
- 返回：`{ "code":1, "data": { order_id, order_no, total_price:"9999.00", expires_at }, "message":"下单成功，待支付" }`　表：orders/resale_listings/payments/users

### 6.7 发起求购
- **POST /api/market/trade/createBegBuyOrder**　需登录 🔐　Body：`{ collectible_id 必填, want_price 必填, num 可选, remark 可选 }`　表：beg_buy_orders
### 6.8 求购单列表
- **POST /api/market/trade/begBuyOrderList**　需登录　Body：`{ page, status 可选, keywords 可选 }`　表：beg_buy_orders
### 6.9 求购·出售信息
- **POST /api/market/trade/getSellCollectionInfo**　需登录　Body：`{ beg_order_id 必填 }`　→ 卖家出售所需藏品信息　表：beg_buy_orders/collectibles
### 6.10 委托购买列表
- **POST /api/market/Prepayment/myOrderlist**　需登录　Body：`{ page, status }`　表：entrust_orders
### 6.11 委托·藏品详情
- **POST /api/market/Prepayment/collectionDetail**　需登录　Body：`{ goods_id 必填 }`　表：entrust_orders/collectibles/series

### 6.12 交易流水（成交/赠送展示）
- **POST /api/market/trade/tradingInformation**　需登录
- Body：`{ goods_id 必填, status 必填(1成交/4赠送), page 必填 }`
- 逻辑：trade_records 按 type 过滤分页；time 由 created_at 换算（分钟前/小时前/天前）。**赠送流水为历史展示数据（transfers 功能不开放，流水由兑换/空投等来源写入 type='gift' 场景保留兼容）**
- 返回 data：`{ list:[ { price:"100.00", collection_code:2378, time:"5小时前" } ], transaction_record_number }`　表：trade_records

### 6.13 我的寄售
- **POST /api/order/order/myConsignment**　需登录　Body：`{ page }`　→ resale_listings.seller_id=me 分页　表：resale_listings

### 6.14 挂单上架（寄售）**[新增]**
- **POST /api/market/market/consign**　需登录 🔐
- Body：`{ user_collectible_id 必填, price 必填(≤reprice_limit), income_type 可选(huifu), pay_password 必填 }`
- 逻辑（事务）：凭证 held 校验 → 建 resale_listings(selling, fee_amount=price×fee_rate, actual_amount=price−fee) → 凭证 consigned/is_consigned=1 → 校验 series 寄售时段
- 返回：`{ "code":1, "data": { listing_id, fee_amount, actual_amount }, "message":"上架成功" }`　表：resale_listings/user_collectibles/users

---

## 七、持仓域（3 个端点）

### 7.1 我的收藏（仓库）
- **POST /api/user_collection/user_collection/antMycollection**　需登录
- Body：`{ page, types 可选(=2), status 可选 }`
- 逻辑：user_collectibles WHERE user_id=me 分页
- 返回 data：`{ total, per_page, current_page, data:[ { id, goods_id, collection_code, user_hash_text, rz_code, status, goods:{name, main_image} } ], current_count, current_consignment }`　表：user_collectibles/collectibles

### 7.2 持仓详情
- **POST /api/user_collection/user_collection/detail/ids/{id}**　需登录　Path：`id 必填`
- 逻辑：归属校验 → 详情 + 链上状态（chain_tasks.mint_status/tx_hash）　表：user_collectibles/chain_tasks

### 7.3 藏品关注/取消
- 见 5.4（归入藏品域）

---

## 八、订单与支付域（7 个端点）

### 8.1 订单列表
- **POST /api/order/order/orderList**　需登录
- Body：`{ page 必填, status 必填(0全部/1批量付/2已完成/3进行中/7待支付), keywords 可选 }`
- 逻辑：orders WHERE user_id=me 倒序分页
- 返回 data：`{ total, data:[ { id, order_no, order_status, total_price, goods_name, goods_cover_image, num, createtime, order_type } ] }`　表：orders

### 8.2 市场订单详情
- **POST /api/order/order/MarketOrderDetail/ids/{id}**　需登录　Path：`id 必填`　→ 订单 + 服务费四件套 + 收银台信息；无 id/无效 → `{code:0,"message":"订单不存在"}`　表：orders/payments

### 8.3 商城订单详情
- **POST /api/order/order/orderDetail/ids/{id}**　需登录　Path：`id 必填`　→ 同构（商城单）；无效 → `{code:0,"message":"找不到该订单或已被删除"}`　表：orders

### 8.4 订单支付（模拟支付）**[新增]**
- **POST /api/order/order/payOrder**　需登录 🔐
- Body：`{ order_id 必填, pay_password 必填 }`
- 逻辑（事务，模拟汇付闭环）：
  1. pending 且未过 expires_at 校验（交易密码或 ticket）
  2. orders → completed、pay_way='huifu'、pay_time
  3. payments → success（raw_response 写模拟回执）
  4. 市场单：**资产迁移**——旧凭证 status='transferred'，新建买家凭证（source='purchase', acquired_price=挂单价, collection_code 继承）→ 触发 chain_tasks
  5. resale_listings → sold + buyer_id + order_no；买家 wallets 扣款（若有）+ 卖家 real_income 入账 + wallet_transactions 双向流水
  6. trade_records 写成交流水；collectibles 聚合统计更新
- 返回：`{ "code":1, "data": { order_status:"completed" }, "message":"支付成功" }`　表：orders/payments/user_collectibles/resale_listings/wallets/wallet_transactions/chain_tasks/trade_records/collectibles

### 8.5 模拟支付回调 **[新增]**
- **POST /api/payment/mock/notify**　公开（内部签名校验）
- Body：`{ order_no 必填, trans_amt 必填, resp_code 必填("C00000") }`
- 逻辑：等价 8.4 第 2~6 步；**幂等**（重复回调直接返回成功）；真实接汇付时替换为验签回调　表：同 8.4

### 8.6 取消订单
- **POST /api/order/order/cancelOrder**　需登录　Body：`{ order_id 必填 }`
- 逻辑：pending → cancelled；解锁 collectibles.locked_quantity；市场单挂单回 'selling'　表：orders/collectibles/resale_listings

### 8.7 VIP 订单
- **POST /api/order/createVIPOrder**　需登录 🔐　Body：`{ vip_type 必填 }`　→ orders(source='vip') + users.is_vip　表：orders/users

---

## 九、盲盒域（5 个端点）

### 9.1 发售日历（盲盒列表）
- **POST /api/box/blind_box/list**　公开　Body：`{}`
- 返回 data：`[ { id, name, image, price, total_num, sale_num, start_time, end_time, status:"onsale", limit_per_user } ]`　表：blind_boxes

### 9.2 盲盒详情
- **POST /api/box/blind_box/BoxDetail/ids/{id}**　公开　Path：`id 必填`
- 逻辑：盲盒 + 奖池概率公示（blind_box_items 未删除项）+ collectibles 联动
- 无效 id → `{code:0,"message":"找不到该盲盒，或者已经下架了"}`　表：blind_boxes/blind_box_items/collectibles

### 9.3 购买盲盒 **[新增]**
- **POST /api/box/blind_box/buy**　需登录 🔐
- Body：`{ blind_box_id 必填, num 必填(≤limit_per_user), pay_password 必填 }`
- 逻辑（事务）：库存 locked_quantity 扣减 → orders(source='release') → user_blind_boxes（未开启）→ payments
- 返回：`{ "code":1, "data": { order_id, user_blind_box_id }, "message":"购买成功，可前往开启" }`　表：blind_boxes/orders/user_blind_boxes/payments

### 9.4 开启盲盒
- **POST /api/user_collection/user_collection/openbox**　需登录
- Body：`{ user_blind_box_id 必填 }`（批量 openBoxs 传数组）
- 逻辑（事务）：is_openable 校验 → blind_box_items.probability 加权随机（quantity_distributed < quantity_limit 原子校验）→ **盲盒凭证置 consumed + 新资产 source='blindbox'**（开盒即消耗）→ open_record → 触发 chain_tasks
- 返回：`{ "code":1, "data": { goods:{id, name, main_image}, user_collectible_id, collection_code }, "message":"开盒成功" }`　表：user_blind_boxes/blind_box_items/user_collectibles/chain_tasks

### 9.5 开启记录
- **POST /api/box/blind_box/openlog**　需登录　Body：`{ page }`　→ 开启记录分页　表：blind_box_open_record

---

## 十、合成域（6 个端点）

- **POST /api/synthesis/themeList**　需登录　Body：`{}`　→ 活动列表（limit/permanent + 状态）　表：synthesis_activities/collectibles
- **POST /api/synthesis/themeinfo**　需登录　Body：`{ theme_id 必填 }`　→ 活动详情；未开启 → `{code:0,"message":"未开启"}`　表：synthesis_activities
- **POST /api/synthesis/playInfo**　需登录　Body：`{ theme_id 必填 }`
  - 逻辑：材料清单（synthesis_materials JOIN collectibles）+ 产物 + 我持有的可选材料凭证
  - 返回 data：`{ materials:[ { goods_id, name, image, count } ], result:{ id, name, image }, my_held:[ { user_collectible_id, goods_id, collection_code } ] }`
- **POST /api/synthesis/selectCollection**　需登录　Body：`{ theme_id 必填, goods_id 必填 }`　→ 我持有该材料的具体凭证（供勾选）
- **POST /api/synthesis/synthesizeNow**　需登录 🔐　Body：`{ theme_id 必填, user_collectible_ids 必填(数组，与材料数量匹配), pay_password 必填 }`
  - 逻辑（事务）：活动窗口 / per_user_limit / total_limit 校验 → 材料数量与凭证 held 校验 → **防死循环：result_collectible_id 不得在 materials** → 凭证 consumed → synthesis_records + record_items → 产物凭证 source='synthesis' → used_count+1
  - 返回：`{ "code":1, "data": { record_id }, "message":"合成成功" }`　表：synthesis_*/user_collectibles/chain_tasks
- **POST /api/synthesis/recordList**　需登录　Body：`{ page }`　→ 合成记录分页　表：synthesis_records
- **POST /api/synthesis/recordInfo**　需登录　Body：`{ record_id 必填 }`　→ 记录详情（含消耗明细 record_items）；无效 → `{code:0,"message":"记录不存在"}`　表：synthesis_records/record_items

---

## 十一、签到域 [新增]（3 个端点）

- **POST /addons/signin/api.index/index**　需登录　Body：`{}`
  - 逻辑：本月签到记录 + sign_config 档位 + 连续天数 + 排行聚合
  - 返回 data：`{ signinscore:{s1:"1",…,s7:"1"}, successions:1, is_signin:false, score:1, self_rank:1964, ranklist:[{user_id, days, user:{nickname, avatar}}], isfillup:"1", fillupscore:100, fillupdays:3, msg:"今天签到可获得 1 积分" }`　表：check_in_records/sign_config/users
- **POST /addons/signin/api.index/doSign**　需登录 **[新增]**　Body：`{}`
  - 逻辑（事务）：UNIQUE(user_id, 今日) 幂等 → 连续天数计算 → 按档位发积分（users.score + wallet_transactions(reward)）→ 签到空投判定（consecutive_days ≥ airdrop_activities.checkin_days 且 active）
  - 返回：`{ "code":1, "data": { reward_score:1, consecutive_days:3, total_score:12 }, "message":"签到成功" }`　表：check_in_records/users/wallet_transactions/airdrop_*
- **POST /addons/signin/api.index/fillup**　需登录 **[新增]**　Body：`{ date 必填(3 天内缺口) }`
  - 逻辑：扣 100 积分（实测 fillupscore）→ 补记 is_fillup=1　表：check_in_records/users

---

## 十二、抽奖域（4 个端点）

- **POST /api/lottery/info**　需登录　Body：`{}`　→ 当前 active 抽奖活动（lottery_activities）+ 奖池公示（lucky_draw_prizes 按 sort_order，含剩余数）
  - 无活动 → `{code:0,"message":"暂无抽奖活动"}`　表：lottery_activities/lucky_draw_prizes
- **POST /api/lottery/draw**　需登录 **[新增]**　Body：`{ activity_id 必填 }`
  - 逻辑（事务）：活动 active + 每人限次 + 消耗扣减（cost_type）→ prize_type 加权随机（won < total）→ collectible 奖：新建 user_collectibles(source='lucky_draw') + lucky_draw_records；points 奖：钱包/积分入账 + 流水；none：仅记录
  - 返回：`{ "code":1, "data": { prize_type:"collectible", goods:{id,name,image}, user_collectible_id }, "message":"恭喜获得" }`　表：lottery_activities/lucky_draw_prizes/lucky_draw_records/user_collectibles/users
- **POST /api/lottery/statistics**　需登录　Body：`{ activity_id 可选 }`　→ 我的抽奖统计（次数/中奖分布）　表：lucky_draw_records
- **POST /api/lottery/drawRecord**（含 draw/drawLog 同构）　需登录　Body：`{ page, type 可选 }`　→ 中奖记录分页　表：lucky_draw_records/lucky_draw_prizes

---

## 十三、龙晶商城域（4 个端点）

- **POST /api/crystal_goods/list**　需登录　Body：`{ page }`　→ 兑换商品列表（onsale）　表：crystal_goods
- **POST /api/crystal_goods/crystalDetail**　需登录　Body：`{ id 必填 }`　→ 商品详情（含兑换须知 dhxz/自提说明 zysx）　表：crystal_goods
- **POST /api/crystal_goods/exchange**　需登录 **[新增]**　Body：`{ crystal_goods_id 必填, num 必填, pay_password 必填 🔐 }`
  - 逻辑（事务）：users.crystal_{type} 余额扣减 → crystal_goods stock 锁定扣减 → collectibles 兑换入账（新建 user_collectibles source='purchase'? 统一 source='airdrop'? 采用 source='lucky_draw' 不合适 → **source='purchase', acquired_price=0**）→ exchange_records + 可选 order(orderType=4 详情兼容)
  - 返回：`{ "code":1, "data": { user_collectible_id, collection_code }, "message":"兑换成功" }`　表：crystal_goods/users/user_collectibles/exchange_records
- **POST /api/crystal_goods/exchangeRecord**　需登录　Body：`{ page }`　→ 兑换记录分页　表：exchange_records

---

## 十四、空投域 [新增]（3 个端点）

- **GET /api/airdrop/activities**　需登录　→ 进行中/历史空投活动列表（不暴露 condition_config 内部参数）　表：airdrop_activities
- **GET /api/airdrop/records**　需登录　Body：`{ page }`　→ 我的空投记录（含来源活动名/藏品/状态）　表：airdrop_records/airdrop_activities/collectibles
- **POST /api/airdrop/eligibilities**　需登录　Body：`{ page }`　→ 我的待发放资格（batch 模式）　表：airdrop_eligibilities

> 空投产生不在用户端点：注册/登录/签到/邀请任务在各自业务事务内写入 airdrop_eligibilities（batch）或直接发放（realtime），详见 docs/06 约束 #10~#17。

---

## 十五、邀请域（2 个端点）

- **POST /api/user/invite_records**　需登录　Body：`{ page }`　→ 我邀请的人（状态 pending/registered + 奖励发放态）　表：invite_records/airdrop_records
- **GET /api/user/invite_info**　需登录　→ 我的邀请码 + 当前 invite_activities 活动配置摘要 + 已邀人数　表：users/invite_activities/invite_records

---

## 十六、系统站点域（1 个端点）

- **GET /api/site/settings**　公开　→ site_settings 分组返回（basic/theme/button/seo）　表：site_settings

---

## 附：端点统计与实现优先级建议

| 域 | 端点数 | 优先级 |
|----|--------|--------|
| 认证用户 | 14+4（合并行） | P0（登录/信息/交易密码/实名） |
| 市场域 | 14 | **P0**（下单链路核心：列表→详情→下单→支付） |
| 订单支付 | 7 | **P0**（模拟支付闭环） |
| 持仓 | 3 | P0（仓库） |
| 藏品分类 | 4 | P0（详情页） |
| 内容配置 | 8 | P1 |
| 盲盒 | 5 | P1 |
| 钱包 | 2 | P1 |
| 地址 | 4 | P1 |
| 合成 | 6 | P2 |
| 签到 | 3 | P2 |
| 抽奖 | 4 | P2 |
| 龙晶商城 | 4 | P2 |
| 空投 | 3 | P2（任务触发为主，端点只读） |
| 邀请 | 2 | P2 |
| 系统站点 | 1 | P1 |
| **合计** | **78**（含 1.11~1.14、6.7~6.11 等合并行内的端点） | |

**安全清单**（全部端点强制）：HTTPS；JWT 过期校验；🔒 交易密码接口 bcrypt 校验 + 5 分钟 ticket 复用；挂单/合成/兑换的库存与状态校验全部走数据库事务 + 行锁（SELECT ... FOR UPDATE）；敏感操作（后台改库存/下架/执行空投）记审计日志。
