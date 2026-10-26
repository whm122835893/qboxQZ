# init.sql 自检报告（v3 数据模型 × 30 条业务约束）

> 自检对象：`sql/init.sql`（初版 931 行）　对照：`docs/06-数据库实体模型设计-v3-对齐司南规范.md` + 司南 24 条业务约束 + QBOX 6 条补充约束
> 自检方式：程序化比对（information_schema 实测 × 设计文档字段清单）+ MariaDB 10.11 真实库全量执行回归
> 结论：**发现 5 类问题，全部已修正**；修正后脚本 984 行，真实库执行 exit=0。

## 一、字段齐全性检查 —— 发现 3 处遗漏 ✅ 已修正

| # | 表 | 遗漏字段 | 依据 | 修正 |
|---|----|----------|------|------|
| 1 | nft_users | yao_count / xin_count / user_ore / medals_number / team_id / rabbit_team_status / rabbit_person_status / collection_total_price / buy_collection_total_price（9 个） | v3 §3.1「兔子链路/勋章（预留）」+ getUserInfo 实测字段 | 已补齐，类型 DECIMAL(12,2)/DECIMAL(16,6)/TINYINT/INT UNSIGNED，DEFAULT 0 |
| 2 | nft_collectibles | huifu_count / yibao_count / ll_count / hot_count（4 个渠道计数） | v3 §3.8 + marketGoodsDetail 实测响应 | 已补齐 INT UNSIGNED DEFAULT 0 |
| 3 | nft_resale_listings | pay_platform_service_rate / platform_service_price / creator_service_price / real_income（挂单侧服务费四件套） | v3 §4.1 + MarketDetail 实测（0.05/499.95/0.00/9499.05） | 已补齐，并注释 real_income=price−fee_amount |

其余 37 张表与 v3 设计文档逐字段比对无遗漏（含 orders 47 字段、user_collectibles 24 字段、airdrop 域 4 表全量）。

## 二、数据类型检查 —— 1 处命名修正 ✅

| 检查项 | 结果 |
|--------|------|
| 金额 | 全部 DECIMAL(10,2)/(12,2)/(12,4)，无 FLOAT/DOUBLE ✅ |
| 状态 | 33 个 ENUM（订单/挂单/持仓来源/空投状态机等），布尔用 TINYINT(1)+COMMENT ✅ |
| 时间 | 全部 DATETIME(3) 毫秒精度（司南决策#9），无 TIMESTAMP（规避 2038）✅ |
| JSON | api_create_data/pay_result_data/tags/condition_config 等 9 处用 JSON 类型 ✅ |
| 修正项 | `nft_collectibles.'3d_url_type'` 初版误用单引号列名 → 已改为反引号 \`3d_url_type\`（源站字段名以数字开头，不能用 ENUM/裸标识符） |

## 三、约束到位性检查 —— 补齐 9 个缺失外键 ✅

| # | 缺失外键 | 修正 |
|---|----------|------|
| 1 | user_collectibles.task_id → chain_tasks.task_id | 已补（SET NULL，任务标识为 UNIQUE VARCHAR 可作外键目标） |
| 2 | user_collectibles.airdrop_record_id → airdrop_records.id | 已补（SET NULL） |
| 3 | lucky_draw_prizes.activity_id → lottery_activities.id | 已补（RESTRICT，D9 决策落地） |
| 4 | orders.batch_order_id → orders.id（自引用） | 已补（SET NULL） |
| 5 | resale_listings.buyer_id → users.id | 已补（SET NULL，成交回填） |
| 6 | airdrop_records.user_id → users.id | 已补（SET NULL，先手机号后注册场景） |
| 7 | airdrop_eligibilities.user_id → users.id | 已补（SET NULL） |
| 8 | exchange_records.collectible_id → collectibles.id | 已补（SET NULL） |
| 9 | exchange_records.user_collectible_id → user_collectibles.id | 已补（SET NULL） |

外键总数 58 → **67**。行为约定：主数据 RESTRICT、强从属日志/明细 CASCADE、可空回填引用 SET NULL，全部显式 `ON DELETE + ON UPDATE CASCADE`。
唯一索引实测 17 个（uk_users_phone / uk_orders_no / uk_uc_serial / uk_cir_user_date / uk_ae_activity_phone 等），业务唯一键全覆盖。
NOT NULL 复核：业务必填列（phone/username/order_no/serial/price 等）全部 NOT NULL；无 NOT NULL 无 DEFAULT 且可空的冲突。

## 四、默认值与注释 —— 0 缺失 ✅

- `information_schema.columns` 实测：**无注释列 = 0**（40 表全部列有中文 COMMENT）
- 有默认值语义的字段均带 DEFAULT；可空字段显式 NULL；枚举默认值与业务初态一致（如 status='pending'、mint_status='pending'）
- 表级 COMMENT 全部存在并含业务语义说明

## 五、命名一致性 —— 2 处语义修正 ✅

| # | 问题 | 修正 |
|---|------|------|
| 1 | 索引前缀撞车：nft_transfers 与 nft_trade_records 都用 `idx_tr_*` / `fk_tr_*`，语义混淆 | transfers → `idx_transfer_*` / `fk_transfer_*`；trade_records → `idx_trade_*` / `fk_trade_*` |
| 2 | `docs/06` 编号笔误「39 张表」实际为 40 张（39a system_configs + 39b site_settings 各算一张） | 文档勘误，SQL 无需改动 |

其余：40 表统一 `nft_` 前缀；字段 snake_case；主键 `id`；外键 `{ref}_id`；索引 `idx_/uk_/fk_{缩写}_{列}` 规则统一 ✅。

## 六、业务约束抽查（SQL 层可落地的部分）

| 约束 | SQL 落地验证 |
|------|--------------|
| 签到每天一次 | UNIQUE uk_cir_user_date(user_id, check_in_date) ✅ |
| 一人仅被邀请一次 | UNIQUE uk_ir_invitee(invitee_id) ✅ |
| 订单号/藏品编号全局唯一 | uk_orders_no / uk_uc_serial(collectible_id, serial) ✅ |
| 空投资格同活动同手机号一次 | UNIQUE uk_ae_activity_phone ✅ |
| 快照同活动同实例一次 | UNIQUE uk_as_activity_user_uc ✅ |
| 钱包 1:1 | UNIQUE uk_wallets_user ✅ |
| 库存锁定 | collectibles.locked_quantity + blind_box_items.quantity_distributed + crystal_goods.locked_quantity ✅（应用层原子扣减） |
| 外键 RESTRICT 拦截 | 实测删除有钱包的用户 → ERROR 1451 拦截 ✅ |
| 实名/交易密码前置、状态机流转、盲盒概率校验、防死循环 | 应用层约束（字段与结构已就位） |

## 七、修正后回归验证（MariaDB 10.11 真实库）

| 项 | 结果 |
|----|------|
| 全量执行 | exit=0，无错误 |
| 表数量 | 40 |
| 外键 | 58 → **67** |
| ENUM 列 | 33 |
| 无注释列 | 0 |
| 唯一约束 | 17 |
| 字段抽查 | 新增字段（yao_count/collection_total_price 等）INSERT/SELECT 正常 |
| 测试数据 | 已清理，库为空表结构 |

## 八、遗留说明（非问题）

1. `nft_series.series_id` 保留 AUTO_INCREMENT INT：源站特殊板块值（-2/-1/0）以显式 INSERT 写入负 id，MySQL 允许；已建 UNIQUE code。
2. `nft_orders.seller_id`、`nft_user_collectibles.buy_price`、`check_in_records.reward_related_id`（多态）等冗余/多态字段按设计文档保留为逻辑外键（无物理 FK），注释已说明。
3. `collectibles.category_id` 因板块含负数值不建物理外键（注释已说明），代码层校验。
4. 敏感字段（real_name/id_card/transaction_password）按安全规范在注释中强制要求加密/哈希存储，SQL 层无法强制。
