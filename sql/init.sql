-- =====================================================================
-- QBOX 数字藏品平台 · 数据库初始化脚本 init.sql
-- 依据：docs/06-数据库实体模型设计-v3-对齐司南规范.md（39 张表）
-- 引擎：InnoDB / 字符集 utf8mb4 / 排序规则 utf8mb4_unicode_ci
-- 说明：
--   1) 状态字段使用 ENUM（用户要求，Mock 字符串合理枚举化），扩展需 ALTER TABLE
--   2) 金额统一 DECIMAL(10,2)，时间统一 DATETIME(3) 毫秒精度（司南决策 #9）
--   3) 软删除统一 deleted_at DATETIME NULL（司南决策 #6/#7）
--   4) 外键 ON DELETE 以 RESTRICT 为主（强从属日志/明细用 CASCADE）
--   5) Mock 仅作字段语义参考，长度/类型按生产规模重新标定，未照搬
-- =====================================================================
SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- =====================================================================
-- 一、用户域（6 表）
-- =====================================================================

-- 1. 用户表
CREATE TABLE `nft_users` (
  `id`                   BIGINT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '用户ID',
  `phone`                VARCHAR(11)     NOT NULL                COMMENT '手机号，登录账号',
  `username`             VARCHAR(50)     NOT NULL                COMMENT '用户名/昵称',
  `avatar`               VARCHAR(255)    NOT NULL DEFAULT ''     COMMENT '头像URL',
  `uid`                  VARCHAR(10)     NOT NULL                COMMENT '站内展示UID',
  `invite_code`          VARCHAR(16)     NOT NULL                COMMENT '我的邀请码，注册链接?code=绑定',
  `is_realname`          TINYINT(1)      NOT NULL DEFAULT 0      COMMENT '实名标志 0否 1是（购买/寄售/转赠前置）',
  `real_name`            VARCHAR(255)    NULL            COMMENT '真实姓名 AES-256/SM4加密存储',
  `id_card`              VARCHAR(255)    NULL            COMMENT '身份证号 加密存储',
  `password_hash`        VARCHAR(255)    NULL            COMMENT '登录密码 bcrypt/scrypt哈希，禁止明文',
  `person_intro`         VARCHAR(255)    NOT NULL DEFAULT ''     COMMENT '个人简介',
  `real_name_time`       DATETIME(3)     NULL            COMMENT '实名认证通过时间',
  `invitee_bound`        TINYINT(1)      NOT NULL DEFAULT 0      COMMENT '是否已绑定邀请关系',
  `transaction_password` VARCHAR(255)    NULL            COMMENT '交易密码 bcrypt/scrypt哈希，禁止明文',
  `status`               TINYINT         NOT NULL DEFAULT 1      COMMENT '账号状态 1正常 0禁用',
  `transfer_permission`  TINYINT(1)      NOT NULL DEFAULT 0      COMMENT '转赠权限开关',
  `market_open`          TINYINT(1)      NOT NULL DEFAULT 1      COMMENT '市场功能开关',
  `is_white`             TINYINT(1)      NOT NULL DEFAULT 0      COMMENT '白名单用户',
  `score`                INT UNSIGNED    NOT NULL DEFAULT 0      COMMENT '积分余额（签到奖励）',
  `crystal_yellow`       DECIMAL(12,4)   NOT NULL DEFAULT 0      COMMENT '黄龙晶余额',
  `crystal_purple`       DECIMAL(12,4)   NOT NULL DEFAULT 0      COMMENT '紫龙晶余额',
  `crystal_blue`         DECIMAL(12,4)   NOT NULL DEFAULT 0      COMMENT '蓝龙晶余额',
  `level`                TINYINT         NOT NULL DEFAULT 1      COMMENT '会员等级',
  `is_vip`               TINYINT(1)      NOT NULL DEFAULT 0      COMMENT '是否VIP',
  `vip_expire_at`        DATETIME(3)     NULL            COMMENT 'VIP到期时间',
  `huifu_account`        TINYINT(1)      NOT NULL DEFAULT 0      COMMENT '汇付开户状态 0未开户 1已开户',
  `nft_url`              VARCHAR(128)    NULL            COMMENT '区块链地址',
  `yao_count`            DECIMAL(12,2)   NOT NULL DEFAULT 0      COMMENT '药数量（兔子链路预留）',
  `xin_count`            DECIMAL(12,2)   NOT NULL DEFAULT 0      COMMENT '心数量（兔子链路预留）',
  `user_ore`             DECIMAL(16,6)   NOT NULL DEFAULT 0      COMMENT '矿石数量（兔子链路预留）',
  `medals_number`        DECIMAL(12,2)   NOT NULL DEFAULT 0      COMMENT '勋章数量（预留）',
  `team_id`              INT UNSIGNED    NOT NULL DEFAULT 0      COMMENT '所属团队ID（预留）',
  `rabbit_team_status`   TINYINT         NOT NULL DEFAULT 0      COMMENT '兔子团队状态（预留）',
  `rabbit_person_status` TINYINT         NOT NULL DEFAULT 0      COMMENT '兔子个人状态（预留）',
  `collection_total_price` DECIMAL(12,2) NOT NULL DEFAULT 0.00   COMMENT '持仓市值（冗余统计）',
  `buy_collection_total_price` DECIMAL(12,2) NOT NULL DEFAULT 0.00 COMMENT '累计购入藏品金额（冗余统计）',
  `collection_num`       INT UNSIGNED    NOT NULL DEFAULT 0      COMMENT '持仓数量（冗余统计）',
  `blind_box_num`        INT UNSIGNED    NOT NULL DEFAULT 0      COMMENT '持有盲盒数量（冗余统计）',
  `last_login_at`        DATETIME(3)     NULL            COMMENT '最后登录时间（登录空投依赖）',
  `login_count`          INT UNSIGNED    NOT NULL DEFAULT 0      COMMENT '累计登录次数',
  `deleted_at`           DATETIME(3)     NULL            COMMENT '软删除时间',
  `created_at`           DATETIME(3)     NOT NULL DEFAULT CURRENT_TIMESTAMP(3) COMMENT '创建时间',
  `updated_at`           DATETIME(3)     NOT NULL DEFAULT CURRENT_TIMESTAMP(3) ON UPDATE CURRENT_TIMESTAMP(3) COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_users_phone` (`phone`),
  UNIQUE KEY `uk_users_uid` (`uid`),
  UNIQUE KEY `uk_users_invite_code` (`invite_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='用户表';

-- 2. 钱包表
CREATE TABLE `nft_wallets` (
  `id`            BIGINT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '钱包ID',
  `user_id`       BIGINT UNSIGNED NOT NULL                COMMENT '用户ID',
  `balance`       DECIMAL(12,2)   NOT NULL DEFAULT 0.00   COMMENT '总资产（元）',
  `available`     DECIMAL(12,2)   NOT NULL DEFAULT 0.00   COMMENT '可用余额',
  `frozen`        DECIMAL(12,2)   NOT NULL DEFAULT 0.00   COMMENT '冻结金额（待支付占用）',
  `points`        DECIMAL(12,2)   NOT NULL DEFAULT 0.00   COMMENT '平台币余额',
  `brand`         VARCHAR(16)     NOT NULL DEFAULT 'huifu' COMMENT '第三方支付品牌',
  `created_at`    DATETIME(3)     NOT NULL DEFAULT CURRENT_TIMESTAMP(3) COMMENT '创建时间',
  `updated_at`    DATETIME(3)     NOT NULL DEFAULT CURRENT_TIMESTAMP(3) ON UPDATE CURRENT_TIMESTAMP(3) COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_wallets_user` (`user_id`),
  CONSTRAINT `fk_wallets_user` FOREIGN KEY (`user_id`) REFERENCES `nft_users`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='钱包表（与用户1:1，余额支付扣款依据）';

-- 3. 钱包流水表（纯日志表，无 updated_at）
CREATE TABLE `nft_wallet_transactions` (
  `id`            BIGINT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '流水ID',
  `user_id`       BIGINT UNSIGNED NOT NULL                COMMENT '用户ID',
  `trans_type`    ENUM('recharge','buy','withdraw','reward','refund') NOT NULL COMMENT '交易类型 充值/购买/提现/奖励/退款',
  `title`         VARCHAR(64)     NOT NULL                COMMENT '明细标题',
  `direction`     TINYINT(1)      NOT NULL                COMMENT '方向 1收入 2支出',
  `amount`        DECIMAL(12,2)   NOT NULL                COMMENT '金额（绝对值）',
  `balance_after` DECIMAL(12,2)   NOT NULL                COMMENT '交易后余额快照（对账用）',
  `biz_no`        VARCHAR(64)     NULL            COMMENT '关联业务单号（订单号/空投ID等）',
  `created_at`    DATETIME(3)     NOT NULL DEFAULT CURRENT_TIMESTAMP(3) COMMENT '创建时间',
  PRIMARY KEY (`id`),
  KEY `idx_wtx_user_created` (`user_id`, `created_at`),
  KEY `idx_wtx_biz_no` (`biz_no`),
  CONSTRAINT `fk_wtx_user` FOREIGN KEY (`user_id`) REFERENCES `nft_users`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='钱包流水表（纯日志）';

-- 4. 用户关注表（纯关系日志）
CREATE TABLE `nft_user_favorites` (
  `id`            BIGINT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '关注ID',
  `user_id`       BIGINT UNSIGNED NOT NULL                COMMENT '用户ID',
  `collectible_id` INT UNSIGNED NOT NULL                COMMENT '藏品ID',
  `created_at`    DATETIME(3)     NOT NULL DEFAULT CURRENT_TIMESTAMP(3) COMMENT '关注时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_uf_user_collectible` (`user_id`, `collectible_id`),
  CONSTRAINT `fk_uf_user` FOREIGN KEY (`user_id`) REFERENCES `nft_users`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_uf_collectible` FOREIGN KEY (`collectible_id`) REFERENCES `nft_collectibles`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='用户关注（收藏）表';

-- 5. 短信验证码表（纯日志表）
CREATE TABLE `nft_verification_codes` (
  `id`         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '记录ID',
  `phone`      VARCHAR(11)     NOT NULL                COMMENT '接收手机号',
  `scene`      ENUM('register','login','reset_password') NOT NULL COMMENT '使用场景',
  `code`       VARCHAR(255)    NOT NULL                COMMENT '验证码（哈希存储）',
  `expires_at` DATETIME(3)     NOT NULL                COMMENT '过期时间（发送+5分钟）',
  `used_at`    DATETIME(3)     NULL            COMMENT '核销时间（一次性）',
  `ip`         VARCHAR(45)     NULL            COMMENT '请求IP（风控/频控）',
  `sent_at`    DATETIME(3)     NOT NULL                COMMENT '发送时间（60s内禁止重发）',
  `created_at` DATETIME(3)     NOT NULL DEFAULT CURRENT_TIMESTAMP(3) COMMENT '创建时间',
  PRIMARY KEY (`id`),
  KEY `idx_vc_phone_scene` (`phone`, `scene`),
  KEY `idx_vc_expires` (`expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='短信验证码表（纯日志）';

-- 6. 邀请关系记录表
CREATE TABLE `nft_invite_records` (
  `id`                       BIGINT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '记录ID',
  `inviter_id`               BIGINT UNSIGNED NOT NULL                COMMENT '邀请人用户ID',
  `invitee_id`               BIGINT UNSIGNED NOT NULL                COMMENT '被邀请人用户ID（唯一，一人仅被邀请一次）',
  `invite_code`              VARCHAR(16)     NOT NULL                COMMENT '注册时使用的邀请码',
  `status`                   ENUM('pending','registered') NOT NULL DEFAULT 'pending' COMMENT '已邀请/已注册',
  `inviter_airdrop_record_id` BIGINT UNSIGNED NULL            COMMENT '邀请人奖励空投记录',
  `invitee_airdrop_record_id` BIGINT UNSIGNED NULL            COMMENT '被邀请人奖励空投记录',
  `created_at`               DATETIME(3)     NOT NULL DEFAULT CURRENT_TIMESTAMP(3) COMMENT '创建时间',
  `updated_at`               DATETIME(3)     NOT NULL DEFAULT CURRENT_TIMESTAMP(3) ON UPDATE CURRENT_TIMESTAMP(3) COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_ir_invitee` (`invitee_id`),
  KEY `idx_ir_inviter` (`inviter_id`),
  CONSTRAINT `fk_ir_inviter` FOREIGN KEY (`inviter_id`) REFERENCES `nft_users`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `fk_ir_invitee` FOREIGN KEY (`invitee_id`) REFERENCES `nft_users`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='邀请关系记录表';

-- =====================================================================
-- 二、藏品域（4 表）
-- =====================================================================

-- 7. 市场板块表
CREATE TABLE `nft_series` (
  `series_id`                INT            NOT NULL AUTO_INCREMENT COMMENT '板块ID（源站含特殊值：-2我的关注 -1推荐 0全部 11破茧 2生肖系列，故不用BIGINT自增语义）',
  `series_name`              VARCHAR(100)   NOT NULL                COMMENT '板块名称',
  `code`                     VARCHAR(20)    NOT NULL                COMMENT '英文标识（对齐分类code语义）',
  `is_def`                   TINYINT(1)     NOT NULL DEFAULT 0      COMMENT '是否默认板块',
  `flag`                     TINYINT        NOT NULL DEFAULT 0      COMMENT '业务标记',
  `pattern`                  TINYINT        NOT NULL DEFAULT 1      COMMENT '展示模式',
  `icon`                     VARCHAR(50)    NULL            COMMENT '板块图标',
  `sort_order`               INT            NOT NULL DEFAULT 0      COMMENT '排序',
  `resale_put_start_time`    CHAR(8)        NOT NULL DEFAULT '00:00:00' COMMENT '寄售上架开始时段 HH:mm:ss',
  `resale_put_end_time`      CHAR(8)        NOT NULL DEFAULT '00:00:00' COMMENT '寄售上架结束时段',
  `resale_buy_start_time`    CHAR(8)        NOT NULL DEFAULT '00:00:00' COMMENT '购买开始时段',
  `resale_buy_end_time`      CHAR(8)        NOT NULL DEFAULT '00:00:00' COMMENT '购买结束时段',
  `created_at`               DATETIME(3)    NOT NULL DEFAULT CURRENT_TIMESTAMP(3) COMMENT '创建时间',
  `updated_at`               DATETIME(3)    NOT NULL DEFAULT CURRENT_TIMESTAMP(3) ON UPDATE CURRENT_TIMESTAMP(3) COMMENT '更新时间',
  PRIMARY KEY (`series_id`),
  UNIQUE KEY `uk_series_code` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='市场板块表（分类+寄售/购买时段配置）';

-- 8. 藏品主表
CREATE TABLE `nft_collectibles` (
  `id`                     INT UNSIGNED   NOT NULL AUTO_INCREMENT COMMENT '藏品ID（列表→详情URL ?id= 唯一凭据）',
  `category_id`            INT            NOT NULL DEFAULT 0      COMMENT '板块ID（逻辑外键→nft_series.series_id，含负数特殊值故不建物理外键）',
  `name`                   VARCHAR(100)   NOT NULL                COMMENT '藏品名称',
  `subtitle`               VARCHAR(100)   NULL            COMMENT '副标题/系列名',
  `image`                  VARCHAR(255)   NOT NULL                COMMENT '封面图',
  `main_image`             VARCHAR(255)   NULL            COMMENT '详情主图（可带CDN缩放参数）',
  `introduce_image`        TEXT           NULL            COMMENT '图文介绍（HTML）',
  `gradient`               VARCHAR(100)   NULL            COMMENT '卡片渐变兜底色',
  `icon`                   VARCHAR(50)    NULL            COMMENT '列表小图标',
  `price`                  DECIMAL(10,2)  NOT NULL DEFAULT 0.00   COMMENT '发售价',
  `edition`                INT UNSIGNED   NOT NULL DEFAULT 0      COMMENT '发行总量',
  `circulate`              INT UNSIGNED   NOT NULL DEFAULT 0      COMMENT '流通量',
  `sold`                   INT UNSIGNED   NOT NULL DEFAULT 0      COMMENT '已售量',
  `locked_quantity`        INT UNSIGNED   NOT NULL DEFAULT 0      COMMENT '待支付锁定数量（高并发库存锁定）',
  `min_price`              DECIMAL(10,2)  NULL            COMMENT '地板价（市场聚合冗余，定时汇总）',
  `max_price`              DECIMAL(10,2)  NULL            COMMENT '最高价（聚合冗余）',
  `market_fluctuate_new`   DECIMAL(10,2)  NOT NULL DEFAULT 0      COMMENT '涨跌幅（聚合冗余，红涨绿跌）',
  `deal_num`               INT UNSIGNED   NOT NULL DEFAULT 0      COMMENT '成交量（聚合冗余）',
  `market_value`           BIGINT UNSIGNED NOT NULL DEFAULT 0     COMMENT '市值（聚合冗余）',
  `sell_count`             INT UNSIGNED   NOT NULL DEFAULT 0      COMMENT '在售挂单数（聚合冗余）',
  `buy_count`              INT UNSIGNED   NOT NULL DEFAULT 0      COMMENT '求购数（聚合冗余）',
  `huifu_count`            INT UNSIGNED   NOT NULL DEFAULT 0      COMMENT '汇付渠道计数（聚合冗余）',
  `yibao_count`            INT UNSIGNED   NOT NULL DEFAULT 0      COMMENT '易宝渠道计数（聚合冗余）',
  `ll_count`               INT UNSIGNED   NOT NULL DEFAULT 0      COMMENT '连连渠道计数（聚合冗余）',
  `hot_count`              INT UNSIGNED   NOT NULL DEFAULT 0      COMMENT '热度计数（聚合冗余）',
  `reprice_limit`          DECIMAL(10,2)  NOT NULL DEFAULT 0.00   COMMENT '寄售定价上限',
  `purchase_information`   TEXT           NULL            COMMENT '购买须知（空则前端用默认文案）',
  `release_date`           DATETIME(3)    NULL            COMMENT '发售时间',
  `onsale_at`              DATETIME(3)    NULL            COMMENT '开售时间',
  `off_sale_at`            DATETIME(3)    NULL            COMMENT '下架时间',
  `openresale_time`        BIGINT UNSIGNED NULL            COMMENT '开寄售时间戳（源站秒级）',
  `new_time`               BIGINT UNSIGNED NULL            COMMENT '更新时间戳（源站秒级）',
  `batch_pay`              TINYINT(1)     NOT NULL DEFAULT 0      COMMENT '批量付开关',
  `batch_pay_num`          INT UNSIGNED   NOT NULL DEFAULT 9      COMMENT '批量付份数阈值',
  `collection_level`       VARCHAR(32)    NOT NULL DEFAULT ''     COMMENT '藏品等级',
  `is_out`                 TINYINT(1)     NOT NULL DEFAULT 0      COMMENT '是否退市（1=已退市，禁止交易）',
  `is_img_diff`            TINYINT(1)     NOT NULL DEFAULT 0      COMMENT '图差标记',
  `batch_sell`             TINYINT(1)     NOT NULL DEFAULT 1      COMMENT '支持批量寄售',
  `batch_pay_flag`         TINYINT(1)     NOT NULL DEFAULT 0      COMMENT '支持批量购买',
  `prepaymen_pay`          TINYINT(1)     NOT NULL DEFAULT 0      COMMENT '预售标记',
  `status`                 ENUM('upcoming','onsale','soldout') NOT NULL DEFAULT 'upcoming' COMMENT '发售状态（决策#1：sold_out并入status）',
  `issuer`                 VARCHAR(50)    NOT NULL DEFAULT 'qbox' COMMENT '发行方',
  `creator`                VARCHAR(50)    NOT NULL DEFAULT 'qbox' COMMENT '创作者',
  `brand`                  VARCHAR(50)    NOT NULL DEFAULT 'qbox' COMMENT '品牌',
  `album`                  VARCHAR(50)    NULL            COMMENT '所属专辑',
  `contract`               VARCHAR(100)   NULL            COMMENT '合约地址',
  `chain_type`             VARCHAR(20)    NULL            COMMENT '链类型',
  `token_standard`         VARCHAR(20)    NULL            COMMENT '令牌标准 ERC-721/ERC-1155',
  `cert_id`                VARCHAR(50)    NULL            COMMENT '认证标识模板',
  `serial_no`              VARCHAR(50)    NULL            COMMENT '编号规则',
  `tag`                    VARCHAR(50)    NULL            COMMENT '标签',
  `market_tag`             VARCHAR(50)    NULL            COMMENT '市场标签',
  `is_release`             TINYINT(1)     NOT NULL DEFAULT 0      COMMENT '是否发布',
  `featured`               TINYINT(1)     NOT NULL DEFAULT 0      COMMENT '是否推荐',
  `description`            TEXT           NULL            COMMENT '藏品介绍',
  `intro`                  TEXT           NULL            COMMENT '发售详情页介绍文案',
  `chain_key`              VARCHAR(32)    NULL            COMMENT '藏品链上标识',
  `3d_url_type`            VARCHAR(20)    NOT NULL DEFAULT 'img'  COMMENT '3D展示类型',
  `threed_url`             VARCHAR(255)   NULL            COMMENT '3D展示地址',
  `is_demo`                TINYINT(1)     NOT NULL DEFAULT 0      COMMENT '演示数据标记（1=不进真实交易）',
  `deleted_at`             DATETIME(3)    NULL            COMMENT '软删除时间',
  `created_at`             DATETIME(3)    NOT NULL DEFAULT CURRENT_TIMESTAMP(3) COMMENT '创建时间',
  `updated_at`             DATETIME(3)    NOT NULL DEFAULT CURRENT_TIMESTAMP(3) ON UPDATE CURRENT_TIMESTAMP(3) COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `idx_col_series_minprice` (`category_id`, `min_price`),
  KEY `idx_col_status_release` (`status`, `is_release`),
  KEY `idx_col_isout_status` (`is_out`, `status`),
  KEY `idx_col_featured` (`featured`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='藏品主表（发售/盲盒/市场/合成产物统一主表）';

-- 9. 盲盒表（藏品 1:1 扩展）
CREATE TABLE `nft_blind_boxes` (
  `id`             INT UNSIGNED   NOT NULL AUTO_INCREMENT COMMENT '盲盒ID',
  `collectible_id` INT UNSIGNED   NOT NULL                COMMENT '藏品ID（UNIQUE，盲盒即藏品扩展）',
  `name`           VARCHAR(200)   NOT NULL                COMMENT '盲盒名称',
  `description`    VARCHAR(500)   NULL            COMMENT '描述',
  `image`          VARCHAR(255)   NULL            COMMENT '主图',
  `price`          DECIMAL(10,2)  NOT NULL DEFAULT 0.00   COMMENT '单盒售价',
  `total_num`      INT UNSIGNED   NOT NULL DEFAULT 0      COMMENT '发售总量',
  `sale_num`       INT UNSIGNED   NOT NULL DEFAULT 0      COMMENT '已售数量',
  `limit_per_user` INT UNSIGNED   NOT NULL DEFAULT 1      COMMENT '每人限购',
  `start_time`     DATETIME(3)    NULL            COMMENT '发售开始时间',
  `end_time`       DATETIME(3)    NULL            COMMENT '发售结束时间',
  `is_openable`    TINYINT(1)     NOT NULL DEFAULT 1      COMMENT '是否可开启（0=禁止开启）',
  `rule_text`      TEXT           NULL            COMMENT '规则说明',
  `status`         ENUM('upcoming','onsale','soldout') NOT NULL DEFAULT 'upcoming' COMMENT '发售状态',
  `deleted_at`     DATETIME(3)    NULL            COMMENT '软删除时间',
  `created_at`     DATETIME(3)    NOT NULL DEFAULT CURRENT_TIMESTAMP(3) COMMENT '创建时间',
  `updated_at`     DATETIME(3)    NOT NULL DEFAULT CURRENT_TIMESTAMP(3) ON UPDATE CURRENT_TIMESTAMP(3) COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_bb_collectible` (`collectible_id`),
  CONSTRAINT `fk_bb_collectible` FOREIGN KEY (`collectible_id`) REFERENCES `nft_collectibles`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='盲盒表（藏品1:1扩展）';

-- 10. 盲盒奖品池配置表
CREATE TABLE `nft_blind_box_items` (
  `id`                    INT UNSIGNED   NOT NULL AUTO_INCREMENT COMMENT '奖品池ID',
  `blind_box_id`          INT UNSIGNED   NOT NULL                COMMENT '盲盒ID',
  `prize_collectible_id`  INT UNSIGNED   NOT NULL                COMMENT '可开出的藏品ID',
  `probability`           DECIMAL(5,4)   NOT NULL DEFAULT 0.0000 COMMENT '中奖概率（0~1）',
  `quantity_limit`        INT UNSIGNED   NULL            COMMENT '该奖品限量（NULL=不限）',
  `quantity_distributed`  INT UNSIGNED   NOT NULL DEFAULT 0      COMMENT '已开出数量',
  `deleted_at`            DATETIME(3)    NULL            COMMENT '逻辑删除时间（禁止物理删除）',
  `created_at`            DATETIME(3)    NOT NULL DEFAULT CURRENT_TIMESTAMP(3) COMMENT '创建时间',
  `updated_at`            DATETIME(3)    NOT NULL DEFAULT CURRENT_TIMESTAMP(3) ON UPDATE CURRENT_TIMESTAMP(3) COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `idx_bbi_blind_box` (`blind_box_id`),
  CONSTRAINT `fk_bbi_box` FOREIGN KEY (`blind_box_id`) REFERENCES `nft_blind_boxes`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `fk_bbi_prize` FOREIGN KEY (`prize_collectible_id`) REFERENCES `nft_collectibles`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='盲盒奖品池配置表';

CREATE TABLE IF NOT EXISTS `nft_user_blind_boxes` (
  `id`            BIGINT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '持有记录ID',
  `user_id`       BIGINT UNSIGNED NOT NULL                COMMENT '用户ID',
  `blind_box_id`  INT UNSIGNED   NOT NULL                COMMENT '盲盒ID',
  `order_id`      BIGINT UNSIGNED NULL            COMMENT '购买来源订单ID',
  `status`        TINYINT        NOT NULL DEFAULT 1      COMMENT '1未开启/2已开启',
  `opentime`      DATETIME(3)    NULL            COMMENT '开启时间',
  `created_at`    DATETIME(3)    NOT NULL DEFAULT CURRENT_TIMESTAMP(3) COMMENT '获得时间',
  `updated_at`    DATETIME(3)    NOT NULL DEFAULT CURRENT_TIMESTAMP(3) ON UPDATE CURRENT_TIMESTAMP(3) COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `idx_ubb_user_status` (`user_id`, `status`),
  KEY `idx_ubb_box` (`blind_box_id`),
  CONSTRAINT `fk_ubb_user` FOREIGN KEY (`user_id`) REFERENCES `nft_users`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `fk_ubb_box` FOREIGN KEY (`blind_box_id`) REFERENCES `nft_blind_boxes`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='用户盲盒持有表';

CREATE TABLE IF NOT EXISTS `nft_blind_box_open_record` (
  `id`                  BIGINT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '开启记录ID',
  `user_id`             BIGINT UNSIGNED NOT NULL                COMMENT '用户ID',
  `blind_box_id`        INT UNSIGNED   NOT NULL                COMMENT '盲盒ID',
  `collectible_id`      INT UNSIGNED   NOT NULL                COMMENT '开出藏品ID',
  `user_collectible_id` BIGINT UNSIGNED NULL            COMMENT '新持仓凭证ID',
  `created_at`          DATETIME(3)    NOT NULL DEFAULT CURRENT_TIMESTAMP(3) COMMENT '开启时间',
  PRIMARY KEY (`id`),
  KEY `idx_bor_user_created` (`user_id`, `created_at`),
  KEY `idx_bor_box` (`blind_box_id`),
  CONSTRAINT `fk_bor_user` FOREIGN KEY (`user_id`) REFERENCES `nft_users`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `fk_bor_box` FOREIGN KEY (`blind_box_id`) REFERENCES `nft_blind_boxes`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='盲盒开启记录表';

-- =====================================================================
-- 三、资产与交易域（5 表）
-- =====================================================================

-- 11. 用户藏品表（每份一行，非同质化）
CREATE TABLE `nft_user_collectibles` (
  `id`                 BIGINT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '持仓凭证ID',
  `user_id`            BIGINT UNSIGNED NOT NULL                COMMENT '持有人ID',
  `collectible_id`     INT UNSIGNED   NOT NULL                COMMENT '藏品ID',
  `order_id`           BIGINT UNSIGNED NULL            COMMENT '购买来源订单ID（source=purchase时回填）',
  `blind_box_item_id`  INT UNSIGNED   NULL            COMMENT '盲盒来源奖品池ID（source=blindbox时回填）',
  `airdrop_record_id`  BIGINT UNSIGNED NULL            COMMENT '空投来源记录ID（source=airdrop时回填，溯源决策#13）',
  `serial`             VARCHAR(20)    NOT NULL                COMMENT '藏品编号（全局唯一，如 387）',
  `collection_code`    INT UNSIGNED   NOT NULL DEFAULT 0      COMMENT '藏品编号数值形态（与serial同值，供索引/查询）',
  `source`             ENUM('purchase','blindbox','transfer','airdrop','synthesis','lucky_draw') NOT NULL COMMENT '获得来源（决策#2）',
  `acquired_price`     DECIMAL(10,2)  NOT NULL DEFAULT 0.00   COMMENT '入手价（非购买来源存0，决策#8）',
  `acquired_at`        DATETIME(3)    NOT NULL                COMMENT '入库时间',
  `is_consigned`       TINYINT(1)     NOT NULL DEFAULT 0      COMMENT '寄售反规范化标记（决策#7）',
  `status`             ENUM('held','consigned','frozen','transferred','consumed') NOT NULL DEFAULT 'held' COMMENT '持有/寄售中/冻结(转赠或锁单)/已转出/已消耗',
  `user_hash`          VARCHAR(64)    NULL            COMMENT '完整合约哈希',
  `user_hash_text`     VARCHAR(16)    NULL            COMMENT '合约哈希脱敏展示（如 5Td4Ao****）',
  `rz_code`            INT UNSIGNED   NULL            COMMENT '认证标识',
  `task_id`            VARCHAR(64)    NULL            COMMENT '上链任务ID（FK nft_chain_tasks.task_id）',
  `buy_price`          DECIMAL(10,2)  NOT NULL DEFAULT 0.00   COMMENT '成本价冗余',
  `tx_hash`            VARCHAR(100)   NULL            COMMENT '链上交易哈希',
  `block_number`       BIGINT UNSIGNED NULL            COMMENT '区块高度',
  `token_id`           VARCHAR(100)   NULL            COMMENT '链上TokenID',
  `mint_status`        ENUM('pending','minting','minted','failed') NULL DEFAULT 'pending' COMMENT '铸造状态',
  `created_at`         DATETIME(3)    NOT NULL DEFAULT CURRENT_TIMESTAMP(3) COMMENT '创建时间',
  `updated_at`         DATETIME(3)    NOT NULL DEFAULT CURRENT_TIMESTAMP(3) ON UPDATE CURRENT_TIMESTAMP(3) COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_uc_serial` (`collectible_id`, `serial`),
  KEY `idx_uc_user_status` (`user_id`, `status`),
  KEY `idx_uc_user_collectible` (`user_id`, `collectible_id`),
  KEY `idx_uc_order` (`order_id`),
  KEY `idx_uc_airdrop_record` (`airdrop_record_id`),
  KEY `idx_uc_task` (`task_id`),
  CONSTRAINT `fk_uc_user` FOREIGN KEY (`user_id`) REFERENCES `nft_users`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `fk_uc_collectible` FOREIGN KEY (`collectible_id`) REFERENCES `nft_collectibles`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `fk_uc_order` FOREIGN KEY (`order_id`) REFERENCES `nft_orders`(`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_uc_blindboxitem` FOREIGN KEY (`blind_box_item_id`) REFERENCES `nft_blind_box_items`(`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='用户藏品表（每份藏品一行，数字藏品非同质化）';

-- 12. 订单表
CREATE TABLE `nft_orders` (
  `id`                          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '订单ID',
  `order_no`                    VARCHAR(32)     NOT NULL                COMMENT '订单号（全局唯一，2026MMDDHHMMSS+14位随机）',
  `user_id`                     BIGINT UNSIGNED NOT NULL                COMMENT '买家用户ID',
  `collectible_id`              INT UNSIGNED   NOT NULL                COMMENT '藏品ID',
  `resale_listing_id`           BIGINT UNSIGNED NULL            COMMENT '寄售挂单ID（市场单必填）',
  `unit_price`                  DECIMAL(10,2)  NOT NULL                COMMENT '单价',
  `quantity`                    INT UNSIGNED   NOT NULL DEFAULT 1      COMMENT '数量',
  `total_price`                 DECIMAL(10,2)  NOT NULL                COMMENT '应付总额',
  `status`                      ENUM('pending','completed','cancelled') NOT NULL DEFAULT 'pending' COMMENT '待支付/已完成/已取消（只允许 pending→completed、pending→cancelled）',
  `source`                      ENUM('release','market') NOT NULL       COMMENT '来源：发售购买/市场挂单购买',
  `seller_id`                   BIGINT UNSIGNED NULL            COMMENT '卖家ID（市场单，冗余）',
  `artel_order`                 VARCHAR(32)    NULL            COMMENT '来源业务模型标识',
  `goods_name`                  VARCHAR(100)   NULL            COMMENT '藏品名称快照（冗余）',
  `goods_cover_image`           VARCHAR(255)   NULL            COMMENT '藏品封面快照（冗余）',
  `series_id`                   INT            NULL            COMMENT '板块ID快照（冗余）',
  `series_name`                 VARCHAR(100)   NULL            COMMENT '板块名称快照（冗余）',
  `pay_type`                    TINYINT        NULL            COMMENT '支付方式编码',
  `pay_way`                     VARCHAR(16)    NULL            COMMENT '支付渠道（如 huifu；空展示--）',
  `pay_time`                    DATETIME(3)    NULL            COMMENT '支付时间（空则前端取下单时间，源站字段名）',
  `pay_scene`                   VARCHAR(8)     NULL            COMMENT '支付场景（H5）',
  `paid_at`                     DATETIME(3)    NULL            COMMENT '支付时间（空则展示下单时间）',
  `transaction_id`              VARCHAR(64)    NULL            COMMENT '三方交易流水号',
  `real_trans_amt`              DECIMAL(10,2)  NULL            COMMENT '实付金额',
  `pay_platform_service_rate`   DECIMAL(5,4)   NOT NULL DEFAULT 0.0500 COMMENT '平台服务费率',
  `platform_service_price`      DECIMAL(10,2)  NOT NULL DEFAULT 0.00   COMMENT '平台服务费',
  `creator_service_price`       DECIMAL(10,2)  NOT NULL DEFAULT 0.00   COMMENT '创作者服务费',
  `real_income`                 DECIMAL(10,2)  NOT NULL DEFAULT 0.00   COMMENT '卖家实际到账',
  `api_create_data`             JSON           NULL            COMMENT '三方下单响应报文（含收银台URL）',
  `pay_result_data`             JSON           NULL            COMMENT '支付成功回调报文',
  `pay_err_data`                JSON           NULL            COMMENT '支付失败回调报文',
  `huifu_pay_type`              VARCHAR(16)    NULL            COMMENT '汇付子类型',
  `completed_at`                DATETIME(3)    NULL            COMMENT '完成时间',
  `cancelled_at`                DATETIME(3)    NULL            COMMENT '取消时间',
  `cancel_reason`               VARCHAR(100)   NULL            COMMENT '取消原因（超时/手动）',
  `expires_at`                  DATETIME(3)    NOT NULL                COMMENT '待支付截止时间（超时自动取消）',
  `airdrop_record_id`           BIGINT UNSIGNED NULL            COMMENT '空投记录ID（空投单溯源）',
  `uc_ids`                      VARCHAR(64)    NULL            COMMENT '空投持仓ID串（源站空投单实证）',
  `collection_codes`            JSON           NULL            COMMENT '成交藏品编号数组',
  `related_collectible_id`      INT UNSIGNED   NULL            COMMENT '市场单关联藏品ID',
  `c_goods_id`                  BIGINT UNSIGNED NOT NULL DEFAULT 0     COMMENT '市场单关联藏品ID（源站字段名）',
  `batch_order_id`              BIGINT UNSIGNED NULL            COMMENT '批量付主订单ID',
  `is_rush`                     TINYINT(1)     NOT NULL DEFAULT 0      COMMENT '是否抢购单',
  `crystal_type`                VARCHAR(16)    NULL            COMMENT '龙晶抵扣类型',
  `crystal_number`              DECIMAL(12,4)  NOT NULL DEFAULT 0      COMMENT '龙晶抵扣数量',
  `kongtou_id`                  BIGINT UNSIGNED NULL            COMMENT '空投活动ID',
  `surplus_time_text`           INT            NOT NULL DEFAULT 0      COMMENT '支付剩余时长（秒）',
  `version`                     TINYINT        NOT NULL DEFAULT 0      COMMENT '数据版本',
  `is_confirmed`                TINYINT(1)     NOT NULL DEFAULT 0      COMMENT '确认标记',
  `created_at`                  DATETIME(3)    NOT NULL DEFAULT CURRENT_TIMESTAMP(3) COMMENT '下单时间',
  `updated_at`                  DATETIME(3)    NOT NULL DEFAULT CURRENT_TIMESTAMP(3) ON UPDATE CURRENT_TIMESTAMP(3) COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_orders_no` (`order_no`),
  KEY `idx_orders_user_status` (`user_id`, `status`),
  KEY `idx_orders_user_created` (`user_id`, `created_at`),
  KEY `idx_orders_resale_listing` (`resale_listing_id`),
  KEY `idx_orders_expires` (`expires_at`),
  KEY `idx_orders_source_status` (`source`, `status`),
  KEY `idx_orders_collectible` (`collectible_id`),
  CONSTRAINT `fk_orders_user` FOREIGN KEY (`user_id`) REFERENCES `nft_users`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `fk_orders_collectible` FOREIGN KEY (`collectible_id`) REFERENCES `nft_collectibles`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `fk_orders_listing` FOREIGN KEY (`resale_listing_id`) REFERENCES `nft_resale_listings`(`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='订单表（发售购买+市场挂单购买，每笔挂单独立订单）';

-- 13. 支付记录表（与订单 1:1）
CREATE TABLE `nft_payments` (
  `id`              BIGINT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '支付记录ID',
  `order_id`        BIGINT UNSIGNED NOT NULL                COMMENT '订单ID（1:1唯一）',
  `user_id`         BIGINT UNSIGNED NOT NULL                COMMENT '付款用户ID',
  `amount`          DECIMAL(10,2)   NOT NULL                COMMENT '支付金额',
  `payment_method`  ENUM('balance','alipay','wechat','huifu') NOT NULL COMMENT '支付方式（huifu=汇付收银台）',
  `pay_scene`       VARCHAR(8)      NULL            COMMENT '支付场景（H5）',
  `transaction_no`  VARCHAR(64)     NULL            COMMENT '三方交易流水号',
  `status`          ENUM('pending','success','failed','refunded') NOT NULL DEFAULT 'pending' COMMENT '支付状态',
  `raw_request`     JSON            NULL            COMMENT '三方下单原始报文',
  `raw_response`    JSON            NULL            COMMENT '三方回调原始报文',
  `paid_at`         DATETIME(3)     NULL            COMMENT '支付成功时间',
  `created_at`      DATETIME(3)     NOT NULL DEFAULT CURRENT_TIMESTAMP(3) COMMENT '创建时间',
  `updated_at`      DATETIME(3)     NOT NULL DEFAULT CURRENT_TIMESTAMP(3) ON UPDATE CURRENT_TIMESTAMP(3) COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_payments_order` (`order_id`),
  KEY `idx_payments_user_status` (`user_id`, `status`),
  CONSTRAINT `fk_payments_order` FOREIGN KEY (`order_id`) REFERENCES `nft_orders`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `fk_payments_user` FOREIGN KEY (`user_id`) REFERENCES `nft_users`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='支付记录表';

-- 14. 寄售挂单表
CREATE TABLE `nft_resale_listings` (
  `id`                         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '挂单ID（详情页路由 ?id=）',
  `seller_id`                  BIGINT UNSIGNED NOT NULL                COMMENT '卖家用户ID',
  `collectible_id`             INT UNSIGNED   NOT NULL                COMMENT '藏品ID（不复制快照，JOIN取实时数据，决策#4）',
  `user_collectible_id`        BIGINT UNSIGNED NOT NULL                COMMENT '持仓凭证ID（在售期间唯一）',
  `collection_code`            INT UNSIGNED   NOT NULL                COMMENT '藏品编号（冗余展示 #387）',
  `series_id`                  INT            NULL            COMMENT '板块ID（冗余）',
  `goods_type`                 TINYINT        NOT NULL DEFAULT 2      COMMENT '品类（2=权益品）',
  `price`                      DECIMAL(10,2)  NOT NULL                COMMENT '寄售价（地板价=MIN selling）',
  `fee_rate`                   DECIMAL(5,4)   NOT NULL DEFAULT 0.0500 COMMENT '手续费率',
  `fee_amount`                 DECIMAL(10,2)  NOT NULL DEFAULT 0.00   COMMENT '手续费 = price × fee_rate',
  `actual_amount`              DECIMAL(10,2)  NOT NULL DEFAULT 0.00   COMMENT '预计到账 = price − fee_amount',
  `pay_platform_service_rate`  DECIMAL(5,4)   NOT NULL DEFAULT 0.0500 COMMENT '平台服务费率',
  `platform_service_price`     DECIMAL(10,2)  NOT NULL DEFAULT 0.00   COMMENT '平台服务费（冗余=price×rate）',
  `creator_service_price`      DECIMAL(10,2)  NOT NULL DEFAULT 0.00   COMMENT '创作者服务费',
  `real_income`                DECIMAL(10,2)  NOT NULL DEFAULT 0.00   COMMENT '卖家实际到账 = price − fee_amount',
  `income_type`                VARCHAR(32)    NOT NULL DEFAULT 'huifu' COMMENT '支付渠道（展示为支付钱包：A钱包/汇付）',
  `status`                     ENUM('selling','locked','sold','cancelled') NOT NULL DEFAULT 'selling' COMMENT '在售/锁单中(买家打开购买弹窗)/已售/已取消',
  `listed_at`                  DATETIME(3)    NOT NULL                COMMENT '挂单时间',
  `sold_at`                    DATETIME(3)    NULL            COMMENT '成交时间',
  `buyer_id`                   BIGINT UNSIGNED NULL            COMMENT '买家用户ID（成交后回填）',
  `order_no`                   VARCHAR(32)    NULL            COMMENT '成交订单号（回填）',
  `rush_status`                TINYINT(1)     NOT NULL DEFAULT 0      COMMENT '抢购标记',
  `created_at`                 DATETIME(3)    NOT NULL DEFAULT CURRENT_TIMESTAMP(3) COMMENT '创建时间',
  `updated_at`                 DATETIME(3)    NOT NULL DEFAULT CURRENT_TIMESTAMP(3) ON UPDATE CURRENT_TIMESTAMP(3) COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `idx_rl_collectible_status` (`collectible_id`, `status`, `price`),
  KEY `idx_rl_seller_status` (`seller_id`, `status`),
  KEY `idx_rl_user_collectible` (`user_collectible_id`),
  CONSTRAINT `fk_rl_seller` FOREIGN KEY (`seller_id`) REFERENCES `nft_users`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `fk_rl_collectible` FOREIGN KEY (`collectible_id`) REFERENCES `nft_collectibles`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `fk_rl_user_collectible` FOREIGN KEY (`user_collectible_id`) REFERENCES `nft_user_collectibles`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='寄售挂单表（取消后3分钟冷却由应用层控制）';

-- 15. 转赠记录表
CREATE TABLE `nft_transfers` (
  `id`                   BIGINT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '转赠ID',
  `from_user_id`         BIGINT UNSIGNED NOT NULL                COMMENT '转出方用户ID',
  `to_user_id`           BIGINT UNSIGNED NOT NULL                COMMENT '受赠方用户ID（决策#11 支持双向查询）',
  `to_phone`             VARCHAR(11)     NOT NULL                COMMENT '受赠手机号',
  `to_nickname`          VARCHAR(50)     NULL            COMMENT '受赠人昵称快照',
  `collectible_id`       INT UNSIGNED   NOT NULL                COMMENT '藏品ID',
  `user_collectible_id`  BIGINT UNSIGNED NOT NULL                COMMENT '转赠资产持仓ID',
  `price`                DECIMAL(10,2)  NOT NULL DEFAULT 0.00   COMMENT '转赠价（流水展示用）',
  `status`               ENUM('pending','accepted','rejected','cancelled') NOT NULL DEFAULT 'pending' COMMENT '待确认/已接受/已拒绝/已取消',
  `pay_password_verified` TINYINT(1)    NOT NULL DEFAULT 0      COMMENT '转出方交易密码是否已验证',
  `created_at`           DATETIME(3)    NOT NULL DEFAULT CURRENT_TIMESTAMP(3) COMMENT '发起时间',
  `confirmed_at`         DATETIME(3)    NULL            COMMENT '对方确认时间',
  `updated_at`           DATETIME(3)    NOT NULL DEFAULT CURRENT_TIMESTAMP(3) ON UPDATE CURRENT_TIMESTAMP(3) COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `idx_transfer_from_user` (`from_user_id`),
  KEY `idx_transfer_to_user` (`to_user_id`),
  KEY `idx_transfer_status_created` (`status`, `created_at`),
  CONSTRAINT `fk_transfer_from` FOREIGN KEY (`from_user_id`) REFERENCES `nft_users`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `fk_transfer_to` FOREIGN KEY (`to_user_id`) REFERENCES `nft_users`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `fk_transfer_collectible` FOREIGN KEY (`collectible_id`) REFERENCES `nft_collectibles`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `fk_transfer_user_collectible` FOREIGN KEY (`user_collectible_id`) REFERENCES `nft_user_collectibles`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='转赠记录表（发起→对方确认→资产迁移）';

-- =====================================================================
-- 四、市场域（3 表 · QBOX 特有）
-- =====================================================================

-- 16. 求购单表
CREATE TABLE `nft_beg_buy_orders` (
  `id`            BIGINT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '求购单ID',
  `user_id`       BIGINT UNSIGNED NOT NULL                COMMENT '求购人ID',
  `collectible_id` INT UNSIGNED NOT NULL                COMMENT '藏品ID',
  `want_price`    DECIMAL(10,2)   NOT NULL                COMMENT '求购价',
  `num`           INT UNSIGNED    NOT NULL DEFAULT 1      COMMENT '求购数量',
  `status`        ENUM('buying','dealt','cancelled') NOT NULL DEFAULT 'buying' COMMENT '求购中/已成交/已取消',
  `remark`        VARCHAR(255)    NULL            COMMENT '留言',
  `created_at`    DATETIME(3)     NOT NULL DEFAULT CURRENT_TIMESTAMP(3) COMMENT '创建时间',
  `updated_at`    DATETIME(3)     NOT NULL DEFAULT CURRENT_TIMESTAMP(3) ON UPDATE CURRENT_TIMESTAMP(3) COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `idx_bbo_user_status` (`user_id`, `status`),
  KEY `idx_bbo_collectible` (`collectible_id`),
  CONSTRAINT `fk_bbo_user` FOREIGN KEY (`user_id`) REFERENCES `nft_users`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `fk_bbo_collectible` FOREIGN KEY (`collectible_id`) REFERENCES `nft_collectibles`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='求购单表';

-- 17. 委托购买单表
CREATE TABLE `nft_entrust_orders` (
  `id`            BIGINT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '委托单ID',
  `user_id`       BIGINT UNSIGNED NOT NULL                COMMENT '委托人ID',
  `collectible_id` INT UNSIGNED NOT NULL                COMMENT '藏品ID',
  `entrust_price` DECIMAL(10,2)   NOT NULL                COMMENT '委托价上限',
  `status`        ENUM('entrusting','dealt','cancelled') NOT NULL DEFAULT 'entrusting' COMMENT '委托中/已成交/已取消',
  `created_at`    DATETIME(3)     NOT NULL DEFAULT CURRENT_TIMESTAMP(3) COMMENT '创建时间',
  `updated_at`    DATETIME(3)     NOT NULL DEFAULT CURRENT_TIMESTAMP(3) ON UPDATE CURRENT_TIMESTAMP(3) COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `idx_eo_user_status` (`user_id`, `status`),
  KEY `idx_eo_collectible` (`collectible_id`),
  CONSTRAINT `fk_eo_user` FOREIGN KEY (`user_id`) REFERENCES `nft_users`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `fk_eo_collectible` FOREIGN KEY (`collectible_id`) REFERENCES `nft_collectibles`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='委托购买单表';

-- 18. 市场交易流水表（纯日志表）
CREATE TABLE `nft_trade_records` (
  `id`              BIGINT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '流水ID',
  `collectible_id`  INT UNSIGNED   NOT NULL                COMMENT '藏品ID',
  `type`            ENUM('deal','gift') NOT NULL DEFAULT 'deal' COMMENT '成交/赠送',
  `collection_code` INT UNSIGNED   NULL            COMMENT '藏品编号（赠送流水展示 #2378）',
  `price`           DECIMAL(10,2)  NOT NULL DEFAULT 0.00   COMMENT '单价',
  `num`             INT UNSIGNED   NOT NULL DEFAULT 1      COMMENT '数量',
  `from_user_id`    BIGINT UNSIGNED NULL            COMMENT '卖家/赠送人',
  `to_user_id`      BIGINT UNSIGNED NULL            COMMENT '买家/受赠人',
  `order_no`        VARCHAR(32)    NULL            COMMENT '关联订单号',
  `created_at`      DATETIME(3)    NOT NULL DEFAULT CURRENT_TIMESTAMP(3) COMMENT '流水时间（前端换算相对时间）',
  PRIMARY KEY (`id`),
  KEY `idx_trade_collectible_type_created` (`collectible_id`, `type`, `created_at`),
  CONSTRAINT `fk_trade_collectible` FOREIGN KEY (`collectible_id`) REFERENCES `nft_collectibles`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='市场交易流水表（纯日志）';

-- =====================================================================
-- 五、运营活动域（8 表）
-- =====================================================================

-- 19. 合成活动表
CREATE TABLE `nft_synthesis_activities` (
  `id`                     INT UNSIGNED   NOT NULL AUTO_INCREMENT COMMENT '活动ID',
  `type`                   ENUM('limit','permanent') NOT NULL COMMENT '限时/永久合成',
  `title`                  VARCHAR(100)   NOT NULL                COMMENT '活动名称',
  `start_time`             DATETIME(3)    NULL            COMMENT '开始时间',
  `end_time`               DATETIME(3)    NULL            COMMENT '结束时间',
  `rules`                  TEXT           NOT NULL                COMMENT '活动规则文案',
  `result_collectible_id`  INT UNSIGNED   NOT NULL                COMMENT '产物藏品ID（不得出现在本活动材料中，防死循环）',
  `per_user_limit`         INT            NOT NULL DEFAULT 0      COMMENT '每人限合成次数（0=不限）',
  `total_limit`            INT UNSIGNED   NULL            COMMENT '总份数限制',
  `used_count`             INT UNSIGNED   NOT NULL DEFAULT 0      COMMENT '已合成次数',
  `image`                  VARCHAR(255)   NULL            COMMENT '活动封面',
  `created_at`             DATETIME(3)    NOT NULL DEFAULT CURRENT_TIMESTAMP(3) COMMENT '创建时间',
  `updated_at`             DATETIME(3)    NOT NULL DEFAULT CURRENT_TIMESTAMP(3) ON UPDATE CURRENT_TIMESTAMP(3) COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `idx_sa_status_time` (`start_time`, `end_time`),
  CONSTRAINT `fk_sa_result` FOREIGN KEY (`result_collectible_id`) REFERENCES `nft_collectibles`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='合成活动表';

-- 20. 合成材料子表
CREATE TABLE `nft_synthesis_materials` (
  `id`             BIGINT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '材料ID',
  `activity_id`    INT UNSIGNED   NOT NULL                COMMENT '活动ID',
  `collectible_id` INT UNSIGNED   NOT NULL                COMMENT '材料藏品ID',
  `count`          INT UNSIGNED   NOT NULL DEFAULT 1      COMMENT '需要数量',
  `created_at`     DATETIME(3)    NOT NULL DEFAULT CURRENT_TIMESTAMP(3) COMMENT '创建时间',
  PRIMARY KEY (`id`),
  KEY `idx_sm_activity` (`activity_id`),
  CONSTRAINT `fk_sm_activity` FOREIGN KEY (`activity_id`) REFERENCES `nft_synthesis_activities`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_sm_collectible` FOREIGN KEY (`collectible_id`) REFERENCES `nft_collectibles`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='合成材料子表';

-- 21. 合成记录表
CREATE TABLE `nft_synthesis_records` (
  `id`         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '合成记录ID',
  `user_id`    BIGINT UNSIGNED NOT NULL                COMMENT '用户ID',
  `activity_id` INT UNSIGNED  NOT NULL                COMMENT '活动ID',
  `result_user_collectible_id` BIGINT UNSIGNED NULL COMMENT '产物持仓凭证ID（成功后回填）',
  `status`     TINYINT        NOT NULL DEFAULT 0      COMMENT '0处理中/1成功/2失败',
  `created_at` DATETIME(3)    NOT NULL DEFAULT CURRENT_TIMESTAMP(3) COMMENT '创建时间',
  `updated_at` DATETIME(3)    NOT NULL DEFAULT CURRENT_TIMESTAMP(3) ON UPDATE CURRENT_TIMESTAMP(3) COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `idx_sr_user_activity` (`user_id`, `activity_id`),
  CONSTRAINT `fk_sr_user` FOREIGN KEY (`user_id`) REFERENCES `nft_users`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `fk_sr_activity` FOREIGN KEY (`activity_id`) REFERENCES `nft_synthesis_activities`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='合成记录表';

-- 22. 合成消耗明细表（纯日志表）
CREATE TABLE `nft_synthesis_record_items` (
  `id`                   BIGINT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '明细ID',
  `synthesis_record_id`  BIGINT UNSIGNED NOT NULL                COMMENT '合成记录ID',
  `user_collectible_id`  BIGINT UNSIGNED NOT NULL                COMMENT '消耗的持仓凭证ID',
  `created_at`           DATETIME(3)    NOT NULL DEFAULT CURRENT_TIMESTAMP(3) COMMENT '创建时间',
  PRIMARY KEY (`id`),
  KEY `idx_sri_record` (`synthesis_record_id`),
  CONSTRAINT `fk_sri_record` FOREIGN KEY (`synthesis_record_id`) REFERENCES `nft_synthesis_records`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_sri_uc` FOREIGN KEY (`user_collectible_id`) REFERENCES `nft_user_collectibles`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='合成消耗明细表（纯日志）';

-- 23. 抽奖活动表
CREATE TABLE `nft_lottery_activities` (
  `id`             INT UNSIGNED   NOT NULL AUTO_INCREMENT COMMENT '期数ID（奖品池activity_id引用）',
  `name`           VARCHAR(200)   NOT NULL                COMMENT '活动名称',
  `start_time`     DATETIME(3)    NULL            COMMENT '开始时间',
  `end_time`       DATETIME(3)    NULL            COMMENT '结束时间',
  `cost_type`      ENUM('points','crystal','free','ticket') NOT NULL DEFAULT 'points' COMMENT '消耗类型',
  `cost_num`       DECIMAL(12,4)  NOT NULL DEFAULT 0      COMMENT '单次消耗数量',
  `limit_per_user` INT UNSIGNED   NOT NULL DEFAULT 0      COMMENT '每人限次（0=不限）',
  `status`         ENUM('draft','active','paused','ended') NOT NULL DEFAULT 'draft' COMMENT '活动状态',
  `created_at`     DATETIME(3)    NOT NULL DEFAULT CURRENT_TIMESTAMP(3) COMMENT '创建时间',
  `updated_at`     DATETIME(3)    NOT NULL DEFAULT CURRENT_TIMESTAMP(3) ON UPDATE CURRENT_TIMESTAMP(3) COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `idx_la_status_time` (`status`, `start_time`, `end_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='抽奖活动表';

-- 24. 抽奖奖品池表
CREATE TABLE `nft_lucky_draw_prizes` (
  `id`             INT UNSIGNED   NOT NULL AUTO_INCREMENT COMMENT '奖品ID',
  `activity_id`    INT UNSIGNED   NOT NULL                COMMENT '抽奖活动/期数ID',
  `tier_name`      VARCHAR(20)    NOT NULL                COMMENT '奖档名（普通/稀有/史诗/传说）',
  `prize_type`     ENUM('collectible','points','none') NOT NULL DEFAULT 'collectible' COMMENT '藏品/平台币/谢谢参与',
  `collectible_id` INT UNSIGNED   NULL            COMMENT '藏品奖品ID（prize_type=collectible时必填）',
  `coin_amount`    DECIMAL(12,2)  NULL            COMMENT '平台币奖励金额（prize_type=points时使用）',
  `total`          INT UNSIGNED   NOT NULL                COMMENT '奖品总量',
  `won`            INT UNSIGNED   NOT NULL DEFAULT 0      COMMENT '已被抽中数',
  `sort_order`     INT            NOT NULL DEFAULT 0      COMMENT '转盘顺序',
  `deleted_at`     DATETIME(3)    NULL            COMMENT '逻辑删除时间',
  `created_at`     DATETIME(3)    NOT NULL DEFAULT CURRENT_TIMESTAMP(3) COMMENT '创建时间',
  `updated_at`     DATETIME(3)    NOT NULL DEFAULT CURRENT_TIMESTAMP(3) ON UPDATE CURRENT_TIMESTAMP(3) COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `idx_ldp_activity` (`activity_id`),
  CONSTRAINT `fk_ldp_collectible` FOREIGN KEY (`collectible_id`) REFERENCES `nft_collectibles`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='抽奖奖品池表';

-- 25. 抽奖记录表（纯日志表）
CREATE TABLE `nft_lucky_draw_records` (
  `id`                   BIGINT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '记录ID',
  `user_id`              BIGINT UNSIGNED NOT NULL                COMMENT '用户ID',
  `activity_id`          INT UNSIGNED   NULL            COMMENT '抽奖活动ID',
  `prize_id`             INT UNSIGNED   NOT NULL                COMMENT '奖品ID',
  `user_collectible_id`  BIGINT UNSIGNED NULL            COMMENT '获得藏品凭证ID（可空：谢谢参与无资产）',
  `created_at`           DATETIME(3)    NOT NULL DEFAULT CURRENT_TIMESTAMP(3) COMMENT '抽奖时间',
  PRIMARY KEY (`id`),
  KEY `idx_ldr_user_created` (`user_id`, `created_at`),
  CONSTRAINT `fk_ldr_user` FOREIGN KEY (`user_id`) REFERENCES `nft_users`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `fk_ldr_prize` FOREIGN KEY (`prize_id`) REFERENCES `nft_lucky_draw_prizes`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='抽奖记录表（纯日志）';

-- 26. 签到记录表
CREATE TABLE `nft_check_in_records` (
  `id`               BIGINT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '签到ID',
  `user_id`          BIGINT UNSIGNED NOT NULL                COMMENT '用户ID',
  `check_in_date`    DATE           NOT NULL                COMMENT '签到日期（每人每天仅一条）',
  `consecutive_days` INT UNSIGNED   NOT NULL DEFAULT 1      COMMENT '连续签到天数',
  `reward_type`      ENUM('none','collectible','points','draw_chance') NOT NULL DEFAULT 'none' COMMENT '奖励类型',
  `reward_amount`    INT            NOT NULL DEFAULT 0      COMMENT '奖励数量（积分/次数）',
  `reward_related_id` BIGINT UNSIGNED NULL            COMMENT '奖励关联ID（藏品ID/活动ID）',
  `reward_description` VARCHAR(255)  NULL            COMMENT '奖励描述',
  `is_fillup`        TINYINT(1)     NOT NULL DEFAULT 0      COMMENT '是否补签（扣100积分、限3天内）',
  `created_at`       DATETIME(3)    NOT NULL DEFAULT CURRENT_TIMESTAMP(3) COMMENT '签到时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_cir_user_date` (`user_id`, `check_in_date`),
  KEY `idx_cir_user_created` (`user_id`, `created_at`),
  CONSTRAINT `fk_cir_user` FOREIGN KEY (`user_id`) REFERENCES `nft_users`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='签到记录表';

-- 27. 邀请活动配置表
CREATE TABLE `nft_invite_activities` (
  `id`                      INT UNSIGNED   NOT NULL AUTO_INCREMENT COMMENT '活动ID',
  `name`                    VARCHAR(100)   NOT NULL                COMMENT '活动名称',
  `status`                  ENUM('disabled','enabled') NOT NULL DEFAULT 'disabled' COMMENT '总开关（禁用时禁止产生新邀请奖励）',
  `start_time`              DATETIME(3)    NULL            COMMENT '开始时间',
  `end_time`                DATETIME(3)    NULL            COMMENT '结束时间',
  `inviter_collectible_id`  INT UNSIGNED   NULL            COMMENT '邀请人奖励藏品ID',
  `inviter_quantity`        INT UNSIGNED   NOT NULL DEFAULT 1      COMMENT '邀请人奖励数量',
  `invitee_collectible_id`  INT UNSIGNED   NULL            COMMENT '被邀请人奖励藏品ID',
  `invitee_quantity`        INT UNSIGNED   NOT NULL DEFAULT 1      COMMENT '被邀请人奖励数量',
  `airdrop_mode`            ENUM('realtime','batch') NOT NULL DEFAULT 'realtime' COMMENT '奖励发放模式',
  `total_limit`             INT UNSIGNED   NULL            COMMENT '总邀请奖励上限',
  `used_count`              INT UNSIGNED   NOT NULL DEFAULT 0      COMMENT '已使用数量',
  `description`             TEXT           NULL            COMMENT '活动说明',
  `deleted_at`              DATETIME(3)    NULL            COMMENT '逻辑删除时间',
  `created_at`              DATETIME(3)    NOT NULL DEFAULT CURRENT_TIMESTAMP(3) COMMENT '创建时间',
  `updated_at`              DATETIME(3)    NOT NULL DEFAULT CURRENT_TIMESTAMP(3) ON UPDATE CURRENT_TIMESTAMP(3) COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `idx_ia_status` (`status`),
  CONSTRAINT `fk_ia_inviter_gift` FOREIGN KEY (`inviter_collectible_id`) REFERENCES `nft_collectibles`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `fk_ia_invitee_gift` FOREIGN KEY (`invitee_collectible_id`) REFERENCES `nft_collectibles`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='邀请活动配置表';

-- =====================================================================
-- 六、空投域（4 表）
-- =====================================================================

-- 28. 空投活动表
CREATE TABLE `nft_airdrop_activities` (
  `id`                      INT UNSIGNED   NOT NULL AUTO_INCREMENT COMMENT '空投活动ID',
  `name`                    VARCHAR(100)   NOT NULL                COMMENT '活动名称',
  `type`                    ENUM('direct','hold','checkin','register','login','invite') NOT NULL COMMENT '空投类型',
  `status`                  ENUM('draft','active','paused','ended') NOT NULL DEFAULT 'draft' COMMENT '活动状态（仅active可产生资格/发放）',
  `airdrop_mode`            ENUM('realtime','batch') NOT NULL DEFAULT 'realtime' COMMENT '实时发放/批量发放',
  `collectible_id`          INT UNSIGNED   NOT NULL                COMMENT '空投目标藏品ID',
  `quantity_per_user`       INT UNSIGNED   NOT NULL DEFAULT 1      COMMENT '每人空投数量',
  `total_limit`             INT UNSIGNED   NULL            COMMENT '总限量（NULL=不限）',
  `issued_count`            INT UNSIGNED   NOT NULL DEFAULT 0      COMMENT '已发放数量',
  `start_time`              DATETIME(3)    NULL            COMMENT '活动开始时间',
  `end_time`                DATETIME(3)    NULL            COMMENT '活动结束时间',
  `snapshot_at`             DATETIME(3)    NULL            COMMENT '快照时间（仅hold类型）',
  `snapshot_collectible_id` INT UNSIGNED   NULL            COMMENT '快照目标藏品ID（仅hold类型）',
  `checkin_days`            INT UNSIGNED   NULL            COMMENT '要求连续签到天数（仅checkin类型）',
  `condition_config`        JSON           NULL            COMMENT '扩展条件配置',
  `description`             TEXT           NULL            COMMENT '活动说明',
  `deleted_at`              DATETIME(3)    NULL            COMMENT '逻辑删除时间',
  `created_at`              DATETIME(3)    NOT NULL DEFAULT CURRENT_TIMESTAMP(3) COMMENT '创建时间',
  `updated_at`              DATETIME(3)    NOT NULL DEFAULT CURRENT_TIMESTAMP(3) ON UPDATE CURRENT_TIMESTAMP(3) COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `idx_aa_type_status` (`type`, `status`),
  KEY `idx_aa_start_end` (`start_time`, `end_time`),
  CONSTRAINT `fk_aa_collectible` FOREIGN KEY (`collectible_id`) REFERENCES `nft_collectibles`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `fk_aa_snapshot_collectible` FOREIGN KEY (`snapshot_collectible_id`) REFERENCES `nft_collectibles`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='空投活动表';

-- 29. 持有藏品快照表（纯日志表）
CREATE TABLE `nft_airdrop_snapshots` (
  `id`                  BIGINT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '快照ID',
  `activity_id`         INT UNSIGNED   NOT NULL                COMMENT '空投活动ID',
  `user_id`             BIGINT UNSIGNED NOT NULL                COMMENT '用户ID',
  `collectible_id`      INT UNSIGNED   NOT NULL                COMMENT '快照时持有的藏品ID',
  `user_collectible_id` BIGINT UNSIGNED NOT NULL                COMMENT '具体藏品实例ID',
  `snapshot_at`         DATETIME(3)    NOT NULL                COMMENT '快照时间',
  `created_at`          DATETIME(3)    NOT NULL DEFAULT CURRENT_TIMESTAMP(3) COMMENT '创建时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_as_activity_user_uc` (`activity_id`, `user_id`, `user_collectible_id`),
  KEY `idx_as_activity` (`activity_id`),
  CONSTRAINT `fk_as_activity` FOREIGN KEY (`activity_id`) REFERENCES `nft_airdrop_activities`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_as_user` FOREIGN KEY (`user_id`) REFERENCES `nft_users`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `fk_as_uc` FOREIGN KEY (`user_collectible_id`) REFERENCES `nft_user_collectibles`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='持有藏品快照表（纯日志）';

-- 30. 空投记录表
CREATE TABLE `nft_airdrop_records` (
  `id`                  BIGINT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '空投记录ID',
  `activity_id`         INT UNSIGNED   NOT NULL                COMMENT '空投活动ID',
  `user_id`             BIGINT UNSIGNED NULL            COMMENT '用户ID（先填手机号后注册时可为空）',
  `phone`               VARCHAR(11)     NOT NULL                COMMENT '接收手机号',
  `collectible_id`      INT UNSIGNED   NOT NULL                COMMENT '空投的藏品ID',
  `user_collectible_id` BIGINT UNSIGNED NULL            COMMENT '发放后生成的藏品实例ID（发放后回填）',
  `quantity`            INT UNSIGNED   NOT NULL DEFAULT 1      COMMENT '数量',
  `status`              ENUM('pending','issued','failed') NOT NULL DEFAULT 'pending' COMMENT '发放状态',
  `issued_at`           DATETIME(3)    NULL            COMMENT '实际发放时间',
  `created_at`          DATETIME(3)    NOT NULL DEFAULT CURRENT_TIMESTAMP(3) COMMENT '创建时间',
  `updated_at`          DATETIME(3)    NOT NULL DEFAULT CURRENT_TIMESTAMP(3) ON UPDATE CURRENT_TIMESTAMP(3) COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `idx_ar_activity_phone` (`activity_id`, `phone`),
  KEY `idx_ar_user` (`user_id`),
  KEY `idx_ar_status` (`status`),
  CONSTRAINT `fk_ar_activity` FOREIGN KEY (`activity_id`) REFERENCES `nft_airdrop_activities`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `fk_ar_collectible` FOREIGN KEY (`collectible_id`) REFERENCES `nft_collectibles`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `fk_ar_uc` FOREIGN KEY (`user_collectible_id`) REFERENCES `nft_user_collectibles`(`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='空投记录表';

-- 31. 空投资格记录表（batch 模式）
CREATE TABLE `nft_airdrop_eligibilities` (
  `id`                BIGINT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '资格ID',
  `activity_id`       INT UNSIGNED   NOT NULL                COMMENT '空投活动ID',
  `user_id`           BIGINT UNSIGNED NULL            COMMENT '用户ID',
  `phone`             VARCHAR(11)     NOT NULL                COMMENT '完成任务时的手机号',
  `task_type`         ENUM('hold','checkin','register','login','invite') NOT NULL COMMENT '完成任务类型',
  `task_completed_at` DATETIME(3)    NOT NULL                COMMENT '任务完成时间',
  `status`            ENUM('eligible','issued') NOT NULL DEFAULT 'eligible' COMMENT '待发放/已发放',
  `airdrop_record_id` BIGINT UNSIGNED NULL            COMMENT '关联空投记录ID',
  `created_at`        DATETIME(3)    NOT NULL DEFAULT CURRENT_TIMESTAMP(3) COMMENT '创建时间',
  `updated_at`        DATETIME(3)    NOT NULL DEFAULT CURRENT_TIMESTAMP(3) ON UPDATE CURRENT_TIMESTAMP(3) COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_ae_activity_phone` (`activity_id`, `phone`),
  KEY `idx_ae_activity_status` (`activity_id`, `status`),
  CONSTRAINT `fk_ae_activity` FOREIGN KEY (`activity_id`) REFERENCES `nft_airdrop_activities`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `fk_ae_record` FOREIGN KEY (`airdrop_record_id`) REFERENCES `nft_airdrop_records`(`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='空投资格记录表（batch模式）';

-- =====================================================================
-- 七、区块链域（1 表 · QBOX 特有）
-- =====================================================================

-- 32. 上链任务表
CREATE TABLE `nft_chain_tasks` (
  `id`                   BIGINT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '任务自增ID',
  `task_id`              VARCHAR(64)     NOT NULL                COMMENT '上链任务标识（全局唯一）',
  `user_collectible_id`  BIGINT UNSIGNED NOT NULL                COMMENT '持仓凭证ID',
  `type`                 VARCHAR(32)     NULL            COMMENT '任务类型',
  `tx_hash`              VARCHAR(128)    NULL            COMMENT '链上交易哈希',
  `status`               TINYINT         NOT NULL DEFAULT 0      COMMENT '0处理中/1上链成功/2失败',
  `class_id`             VARCHAR(64)     NULL            COMMENT '合约类别',
  `nft_id`               VARCHAR(64)     NULL            COMMENT 'NFT ID',
  `message`              VARCHAR(255)    NULL            COMMENT '消息',
  `tag`                  VARCHAR(32)     NULL            COMMENT '标记',
  `api_data`             JSON            NULL            COMMENT '链上接口原始数据',
  `created_at`           DATETIME(3)     NOT NULL DEFAULT CURRENT_TIMESTAMP(3) COMMENT '创建时间',
  `updated_at`           DATETIME(3)     NOT NULL DEFAULT CURRENT_TIMESTAMP(3) ON UPDATE CURRENT_TIMESTAMP(3) COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_ct_task_id` (`task_id`),
  KEY `idx_ct_user_collectible` (`user_collectible_id`),
  CONSTRAINT `fk_ct_uc` FOREIGN KEY (`user_collectible_id`) REFERENCES `nft_user_collectibles`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='上链任务表';

-- =====================================================================
-- 八、内容域（4 表）
-- =====================================================================

CREATE TABLE IF NOT EXISTS `nft_user_addresses` (
  `id`          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '地址ID',
  `user_id`     BIGINT UNSIGNED NOT NULL                COMMENT '用户ID',
  `name`        VARCHAR(50)     NOT NULL                COMMENT '收货人姓名',
  `phone`       VARCHAR(11)     NOT NULL                COMMENT '收货人手机号',
  `province`    VARCHAR(50)     NOT NULL DEFAULT ''     COMMENT '省',
  `city`        VARCHAR(50)     NOT NULL DEFAULT ''     COMMENT '市',
  `district`    VARCHAR(50)     NOT NULL DEFAULT ''     COMMENT '区',
  `address`     VARCHAR(255)    NOT NULL                COMMENT '详细地址',
  `is_default`  TINYINT(1)      NOT NULL DEFAULT 0      COMMENT '默认地址（同用户唯一）',
  `deleted_at`  DATETIME(3)     NULL            COMMENT '软删除时间',
  `created_at`  DATETIME(3)     NOT NULL DEFAULT CURRENT_TIMESTAMP(3) COMMENT '创建时间',
  `updated_at`  DATETIME(3)     NOT NULL DEFAULT CURRENT_TIMESTAMP(3) ON UPDATE CURRENT_TIMESTAMP(3) COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `idx_ua_user` (`user_id`, `is_default`),
  CONSTRAINT `fk_ua_user` FOREIGN KEY (`user_id`) REFERENCES `nft_users`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='收货地址表';

-- 33. 文物展馆表
CREATE TABLE `nft_artifacts` (
  `id`          INT UNSIGNED   NOT NULL AUTO_INCREMENT COMMENT '文物ID',
  `name`        VARCHAR(100)   NOT NULL                COMMENT '文物名称',
  `dynasty`     VARCHAR(50)    NOT NULL                COMMENT '朝代',
  `image`       VARCHAR(255)   NOT NULL                COMMENT '图片',
  `img_height`  INT            NOT NULL DEFAULT 150    COMMENT '展示高度（瀑布流布局）',
  `material`    VARCHAR(50)    NOT NULL                COMMENT '材质',
  `period`      VARCHAR(100)   NOT NULL                COMMENT '年代',
  `size`        VARCHAR(100)   NULL            COMMENT '尺寸',
  `origin`      VARCHAR(100)   NULL            COMMENT '出土地/来源',
  `story`       TEXT           NOT NULL                COMMENT '文物故事',
  `tags`        JSON           NULL            COMMENT '标签（JSON数组）',
  `deleted_at`  DATETIME(3)    NULL            COMMENT '软删除时间',
  `created_at`  DATETIME(3)    NOT NULL DEFAULT CURRENT_TIMESTAMP(3) COMMENT '创建时间',
  `updated_at`  DATETIME(3)    NOT NULL DEFAULT CURRENT_TIMESTAMP(3) ON UPDATE CURRENT_TIMESTAMP(3) COMMENT '更新时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='文物展馆表';

-- 34. 公告/新闻合并表
CREATE TABLE `nft_announcements` (
  `id`          INT UNSIGNED   NOT NULL AUTO_INCREMENT COMMENT '公告ID',
  `title`       VARCHAR(200)   NOT NULL                COMMENT '标题',
  `summary`     VARCHAR(500)   NULL            COMMENT '摘要',
  `content`     TEXT           NULL            COMMENT '正文（HTML）',
  `cover_image` VARCHAR(255)   NULL            COMMENT '头图',
  `type`        ENUM('notice','news') NOT NULL         COMMENT '公告/新闻（决策#5 合并）',
  `subtype`     VARCHAR(20)    NULL            COMMENT '子分类（官方/寄售/运营/限价/活动/合成/求购/空投）',
  `tag_color`   VARCHAR(20)    NULL            COMMENT '标签颜色',
  `is_top`      TINYINT(1)     NOT NULL DEFAULT 0      COMMENT '是否置顶',
  `read_count`  INT UNSIGNED   NOT NULL DEFAULT 0      COMMENT '阅读数',
  `sort_order`  INT            NOT NULL DEFAULT 0      COMMENT '排序',
  `deleted_at`  DATETIME(3)    NULL            COMMENT '软删除时间',
  `created_at`  DATETIME(3)    NOT NULL DEFAULT CURRENT_TIMESTAMP(3) COMMENT '创建时间',
  `updated_at`  DATETIME(3)    NOT NULL DEFAULT CURRENT_TIMESTAMP(3) ON UPDATE CURRENT_TIMESTAMP(3) COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `idx_ann_type_created` (`type`, `created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='公告/新闻合并表';

-- 35. 轮播图表
CREATE TABLE `nft_banners` (
  `id`          INT UNSIGNED   NOT NULL AUTO_INCREMENT COMMENT '轮播ID',
  `image`       VARCHAR(255)   NOT NULL                COMMENT '图片',
  `description` VARCHAR(100)   NULL            COMMENT '描述',
  `url`         VARCHAR(255)   NULL            COMMENT '跳转链接',
  `jump_type`   TINYINT        NOT NULL DEFAULT 0      COMMENT '跳转方式 0无 1内链 2外链',
  `sort_order`  INT            NOT NULL DEFAULT 0      COMMENT '排序',
  `is_active`   TINYINT(1)     NOT NULL DEFAULT 1      COMMENT '是否启用',
  `deleted_at`  DATETIME(3)    NULL            COMMENT '软删除时间',
  `created_at`  DATETIME(3)    NOT NULL DEFAULT CURRENT_TIMESTAMP(3) COMMENT '创建时间',
  `updated_at`  DATETIME(3)    NOT NULL DEFAULT CURRENT_TIMESTAMP(3) ON UPDATE CURRENT_TIMESTAMP(3) COMMENT '更新时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='轮播图表';

-- 36. 社区群表
CREATE TABLE `nft_community_groups` (
  `id`          INT UNSIGNED   NOT NULL AUTO_INCREMENT COMMENT '群ID',
  `icon`        VARCHAR(255)   NOT NULL                COMMENT '群图标',
  `name`        VARCHAR(50)    NOT NULL                COMMENT '群名称',
  `description` VARCHAR(255)   NULL            COMMENT '群描述',
  `qr_code`     VARCHAR(255)   NOT NULL                COMMENT '二维码图',
  `sort_order`  INT            NOT NULL DEFAULT 0      COMMENT '排序',
  `is_active`   TINYINT(1)     NOT NULL DEFAULT 1      COMMENT '是否启用',
  `deleted_at`  DATETIME(3)    NULL            COMMENT '软删除时间',
  `created_at`  DATETIME(3)    NOT NULL DEFAULT CURRENT_TIMESTAMP(3) COMMENT '创建时间',
  `updated_at`  DATETIME(3)    NOT NULL DEFAULT CURRENT_TIMESTAMP(3) ON UPDATE CURRENT_TIMESTAMP(3) COMMENT '更新时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='社区群表';

-- =====================================================================
-- 九、商城域（2 表 · QBOX 特有）
-- =====================================================================

-- 37. 龙晶兑换商品表
CREATE TABLE `nft_crystal_goods` (
  `id`              INT UNSIGNED   NOT NULL AUTO_INCREMENT COMMENT '商品ID',
  `name`            VARCHAR(200)   NOT NULL                COMMENT '商品名称',
  `image`           VARCHAR(255)   NOT NULL                COMMENT '商品图',
  `crystal_type`    VARCHAR(16)    NOT NULL DEFAULT 'crystal_no' COMMENT '消耗龙晶类型（黄/紫/蓝）',
  `crystal_price`   DECIMAL(12,4)  NOT NULL DEFAULT 0.0000 COMMENT '所需龙晶数量',
  `collectible_id`  INT UNSIGNED   NULL            COMMENT '兑换藏品ID（NULL=实物/其他）',
  `stock`           INT UNSIGNED   NOT NULL DEFAULT 0      COMMENT '库存',
  `locked_quantity` INT UNSIGNED   NOT NULL DEFAULT 0      COMMENT '锁定库存（兑换待处理）',
  `dhxz`            VARCHAR(500)   NULL            COMMENT '兑换须知',
  `zysx`            VARCHAR(500)   NULL            COMMENT '自提说明',
  `status`          ENUM('upcoming','onsale','soldout') NOT NULL DEFAULT 'onsale' COMMENT '上架状态',
  `deleted_at`      DATETIME(3)    NULL            COMMENT '逻辑删除时间',
  `created_at`      DATETIME(3)    NOT NULL DEFAULT CURRENT_TIMESTAMP(3) COMMENT '创建时间',
  `updated_at`      DATETIME(3)    NOT NULL DEFAULT CURRENT_TIMESTAMP(3) ON UPDATE CURRENT_TIMESTAMP(3) COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `idx_cg_status` (`status`),
  CONSTRAINT `fk_cg_collectible` FOREIGN KEY (`collectible_id`) REFERENCES `nft_collectibles`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='龙晶兑换商品表';

-- 38. 兑换记录表（纯日志表）
CREATE TABLE `nft_exchange_records` (
  `id`                   BIGINT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '兑换记录ID',
  `user_id`              BIGINT UNSIGNED NOT NULL                COMMENT '用户ID',
  `crystal_goods_id`     INT UNSIGNED   NOT NULL                COMMENT '兑换商品ID',
  `crystal_type`         VARCHAR(16)    NOT NULL                COMMENT '消耗龙晶类型',
  `crystal_num`          DECIMAL(12,4)  NOT NULL DEFAULT 0.0000 COMMENT '消耗数量',
  `collectible_id`       INT UNSIGNED   NULL            COMMENT '兑换藏品ID',
  `user_collectible_id`  BIGINT UNSIGNED NULL            COMMENT '新持仓凭证ID',
  `order_id`             BIGINT UNSIGNED NULL            COMMENT '关联订单ID（详情复用订单详情页）',
  `created_at`           DATETIME(3)    NOT NULL DEFAULT CURRENT_TIMESTAMP(3) COMMENT '兑换时间',
  PRIMARY KEY (`id`),
  KEY `idx_er_user_created` (`user_id`, `created_at`),
  CONSTRAINT `fk_er_user` FOREIGN KEY (`user_id`) REFERENCES `nft_users`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `fk_er_goods` FOREIGN KEY (`crystal_goods_id`) REFERENCES `nft_crystal_goods`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `fk_er_order` FOREIGN KEY (`order_id`) REFERENCES `nft_orders`(`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='兑换记录表（纯日志）';

-- =====================================================================
-- 十、系统配置域（2 表）
-- =====================================================================

-- 39a. 系统参数表
CREATE TABLE `nft_system_configs` (
  `id`           INT UNSIGNED   NOT NULL AUTO_INCREMENT COMMENT '配置ID',
  `config_key`   VARCHAR(50)    NOT NULL                COMMENT '配置键（如 pay_time_limit/fastOrder_check/risk_warning）',
  `config_value` TEXT           NOT NULL                COMMENT '配置值',
  `description`  VARCHAR(200)   NULL            COMMENT '配置说明',
  `created_at`   DATETIME(3)    NOT NULL DEFAULT CURRENT_TIMESTAMP(3) COMMENT '创建时间',
  `updated_at`   DATETIME(3)    NOT NULL DEFAULT CURRENT_TIMESTAMP(3) ON UPDATE CURRENT_TIMESTAMP(3) COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_sc_key` (`config_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='系统参数表（运营参数，禁止硬编码）';

-- 39b. 网站全局配置表
CREATE TABLE `nft_site_settings` (
  `id`            INT UNSIGNED   NOT NULL AUTO_INCREMENT COMMENT '配置ID',
  `setting_key`   VARCHAR(50)    NOT NULL                COMMENT '配置键',
  `setting_value` TEXT           NOT NULL                COMMENT '配置值',
  `setting_group` ENUM('basic','theme','button','seo') NOT NULL DEFAULT 'basic' COMMENT '分组：基础/主题/按钮/SEO',
  `description`   VARCHAR(200)   NULL            COMMENT '配置说明',
  `updated_at`    DATETIME(3)    NOT NULL DEFAULT CURRENT_TIMESTAMP(3) ON UPDATE CURRENT_TIMESTAMP(3) COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_ss_key` (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='网站全局配置表';

-- 预置 site_settings 配置项（对齐司南规范）
INSERT INTO `nft_site_settings` (`setting_key`, `setting_value`, `setting_group`, `description`) VALUES
('site_name', 'QBOX', 'basic', '网站名称'),
('site_logo', '/static/logo.png', 'basic', '网站Logo'),
('button_purchase_color', '#0018ff', 'button', '购买按钮颜色'),
('button_order_color', '#0018ff', 'button', '下单按钮颜色'),
('button_login_color', '#0018ff', 'button', '注册登录按钮颜色'),
('button_auth_color', '#0018ff', 'button', '实名认证按钮颜色');

-- =====================================================================
-- 补充外键（被引用表建表顺序靠后的，统一在此追加；不改变字段定义）
-- =====================================================================

-- 用户藏品 → 上链任务（task_id 可空）
ALTER TABLE `nft_user_collectibles`
  ADD CONSTRAINT `fk_uc_task` FOREIGN KEY (`task_id`) REFERENCES `nft_chain_tasks`(`task_id`) ON DELETE SET NULL ON UPDATE CASCADE;

-- 用户藏品 → 空投记录（溯源）
ALTER TABLE `nft_user_collectibles`
  ADD CONSTRAINT `fk_uc_airdrop_record` FOREIGN KEY (`airdrop_record_id`) REFERENCES `nft_airdrop_records`(`id`) ON DELETE SET NULL ON UPDATE CASCADE;

-- 抽奖奖品池 → 抽奖活动
ALTER TABLE `nft_lucky_draw_prizes`
  ADD CONSTRAINT `fk_ldp_activity` FOREIGN KEY (`activity_id`) REFERENCES `nft_lottery_activities`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE;

-- 订单 → 批量付主订单（自引用）
ALTER TABLE `nft_orders`
  ADD CONSTRAINT `fk_orders_batch` FOREIGN KEY (`batch_order_id`) REFERENCES `nft_orders`(`id`) ON DELETE SET NULL ON UPDATE CASCADE;

-- 寄售挂单 → 买家（成交后回填）
ALTER TABLE `nft_resale_listings`
  ADD CONSTRAINT `fk_rl_buyer` FOREIGN KEY (`buyer_id`) REFERENCES `nft_users`(`id`) ON DELETE SET NULL ON UPDATE CASCADE;

-- 空投记录 / 空投资格 → 用户（可空，用户注销时置空保留记录）
ALTER TABLE `nft_airdrop_records`
  ADD CONSTRAINT `fk_ar_user` FOREIGN KEY (`user_id`) REFERENCES `nft_users`(`id`) ON DELETE SET NULL ON UPDATE CASCADE;
ALTER TABLE `nft_airdrop_eligibilities`
  ADD CONSTRAINT `fk_ae_user` FOREIGN KEY (`user_id`) REFERENCES `nft_users`(`id`) ON DELETE SET NULL ON UPDATE CASCADE;

-- 兑换记录 → 藏品 / 新持仓凭证
ALTER TABLE `nft_exchange_records`
  ADD CONSTRAINT `fk_er_collectible` FOREIGN KEY (`collectible_id`) REFERENCES `nft_collectibles`(`id`) ON DELETE SET NULL ON UPDATE CASCADE;
ALTER TABLE `nft_exchange_records`
  ADD CONSTRAINT `fk_er_user_collectible` FOREIGN KEY (`user_collectible_id`) REFERENCES `nft_user_collectibles`(`id`) ON DELETE SET NULL ON UPDATE CASCADE;

SET FOREIGN_KEY_CHECKS = 1;
-- =====================================================================
-- 结束：共 39 张表 + site_settings 预置数据
-- =====================================================================
