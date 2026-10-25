<template>
  <!--
    市场订单确认弹窗（源站 pages/market/detail 点击「立即购买」后弹出）
    源站实测 2026-10-25 11:57：
      弹窗内容区 [0,695,750,639]，圆角 32px 32px 0 0，底部固定
      遮罩 rgba(0,0,0,.7)，z-index 10070
      拖动条 [343,719,64,8] #e6e6e6
      关闭 × [684,755,20,40] 36px #b0b0b0
      标题「数字藏品订单信息」32px #111 @[36,775]
      副标「付款后将按平台到款顺序排队发放」22px #8c8c8c @[36,828]
      封面 108×108 @[36,888]；名称 28px @[164,907]；规格 22px #8c8c8c @[164,957]
      「支付方式」28px @[36,1035]；渠道行灰底卡 [36,1086,678,104] #f5f5f5 radius 20
      徽标 48×48 黑圆 #111 @[60,1114]（内「汇」22px #fff @[73,1127]）；名称 26px #111 @[124,1125]
      右侧选中点 18×18 蓝 #0018ff @[672,1129]
      「支付金额」22px #8c8c8c @[36,1233]；¥ 24px + 金额 40px @[54,1265]
      「立即支付」按钮 [434,1222,280,88] radius 44 bg #0018ff，文字 28px #fff @[518,1252]
    说明：源站点击「立即支付」后拉起汇付收银台（第三方域，独立项目不接入），
          故本组件以 emit('submit') 上抛，由页面创建本地订单并跳转我的订单。
  -->
  <div v-if="visible" class="pay-mask" @click="$emit('close')"></div>
  <div v-if="visible" class="pay-sheet">
    <div class="pay-grabber"></div>
    <div class="pay-close" @click="$emit('close')">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
        <path d="M6 6l12 12M18 6L6 18" />
      </svg>
    </div>
    <div class="pay-title">数字藏品订单信息</div>
    <div class="pay-sub">付款后将按平台到款顺序排队发放</div>

    <div class="pay-goods">
      <img class="pay-goods-img" :src="cover" :alt="goodsName" />
      <div class="pay-goods-info">
        <div class="pay-goods-name">{{ goodsName }}</div>
        <div class="pay-goods-spec">流通量{{ flux }}/发行量{{ totalNum }}</div>
      </div>
    </div>

    <div class="pay-method-label">支付方式</div>
    <div class="pay-method">
      <span class="pay-badge">{{ badge }}</span>
      <span class="pay-method-name">{{ methodName }}</span>
      <span class="pay-check" aria-hidden="true">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3.4" stroke-linecap="round" stroke-linejoin="round">
          <path d="M4 12.5l5.2 5.2L20 7" />
        </svg>
      </span>
    </div>

    <div class="pay-amount-label">支付金额</div>
    <div class="pay-amount">
      <span class="pay-amount-symbol">¥</span>
      <span class="pay-amount-num">{{ amount }}</span>
    </div>

    <div class="pay-actions">
      <div class="pay-submit" @click="$emit('submit')">立即支付</div>
    </div>
  </div>
</template>

<script setup>
/**
 * 市场订单确认弹窗（单一职责：只负责展示与上抛交互，不含业务请求）。
 * 几何与配色严格对齐源站实测值（见模板注释）。
 */
const props = defineProps({
  visible: { type: Boolean, default: false },
  cover: { type: String, default: '' },
  goodsName: { type: String, default: '' },
  flux: { type: [Number, String], default: 0 },
  totalNum: { type: [Number, String], default: 0 },
  amount: { type: [Number, String], default: '0.00' },
  /** 支付渠道徽标文字（源站 income_type=huifu → 汇；快捷下单为 A） */
  badge: { type: String, default: '汇' },
  /** 支付渠道名称（源站：汇付 / A钱包） */
  methodName: { type: String, default: '汇付' }
})

defineEmits(['close', 'submit'])

/** 金额格式化：两位小数 */
function fmt(v) {
  const n = Number(v)
  return Number.isFinite(n) ? n.toFixed(2) : '0.00'
}
</script>

<style scoped>
.pay-mask {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.7);
  z-index: 10070;
}
.pay-sheet {
  position: fixed;
  left: 0;
  right: 0;
  bottom: 0;
  height: 639px;
  background: #fff;
  border-radius: 32px 32px 0 0;
  z-index: 10071;
  box-sizing: border-box;
  font-family: var(--font-regular);
}
/* 以下 top/left 均相对弹窗顶部（源站绝对 y 减 695） */
.pay-grabber {
  position: absolute;
  left: 343px;
  top: 24px;
  width: 64px;
  height: 8px;
  border-radius: 4px;
  background: #e6e6e6;
}
.pay-close {
  position: absolute;
  right: 46px;
  top: 55px;
  width: 40px;
  height: 40px;
  color: #b0b0b0;
  display: flex;
  align-items: center;
  justify-content: center;
}
.pay-close svg {
  width: 36px;
  height: 36px;
}
.pay-title {
  position: absolute;
  left: 36px;
  top: 80px;
  font-size: 32px;
  font-weight: 400;
  color: #111;
  font-family: var(--font-medium);
  line-height: 32px;
}
.pay-sub {
  position: absolute;
  left: 36px;
  top: 133px;
  font-size: 22px;
  color: #8c8c8c;
  line-height: 22px;
}
.pay-goods {
  position: absolute;
  left: 36px;
  top: 193px;
  display: flex;
  align-items: flex-start;
}
.pay-goods-img {
  width: 108px;
  height: 108px;
  border-radius: 12px;
  object-fit: cover;
  background: #f2f2f2;
  flex-shrink: 0;
}
.pay-goods-info {
  margin-left: 20px;
  padding-top: 19px;
  min-width: 0;
}
.pay-goods-name {
  font-size: 28px;
  color: #111;
  line-height: 28px;
}
.pay-goods-spec {
  margin-top: 22px;
  font-size: 22px;
  color: #8c8c8c;
  line-height: 22px;
}
.pay-method-label {
  position: absolute;
  left: 36px;
  top: 340px;
  font-size: 28px;
  color: #111;
  line-height: 28px;
}
.pay-method {
  position: absolute;
  left: 36px;
  top: 391px;
  width: 678px;
  height: 104px;
  display: flex;
  align-items: center;
  /* 源站实测：渠道行灰底卡 #f5f5f5 圆角 20 */
  background: #f5f5f5;
  border-radius: 20px;
}
.pay-badge {
  /* 源站实测：48×48 黑圆 @[60,1114]，内「汇」22px #fff @[73,1127] */
  width: 48px;
  height: 48px;
  border-radius: 50%;
  background: #111;
  color: #fff;
  font-size: 22px;
  line-height: 22px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
  margin-left: 24px;
}
.pay-method-name {
  margin-left: 16px;
  font-size: 26px;
  color: #111;
}
.pay-check {
  /* 源站实测：18×18 蓝色选中点 @[672,1129] */
  width: 18px;
  height: 18px;
  border-radius: 50%;
  background: #0018ff;
  color: #fff;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
  margin-left: auto;
  margin-right: 24px;
}
.pay-check svg {
  width: 12px;
  height: 12px;
}
.pay-arrow {
  margin-left: auto;
  margin-right: 25px;
  width: 12px;
  height: 12px;
  color: #8c8c8c;
}
.pay-amount-label {
  position: absolute;
  left: 36px;
  top: 538px;
  font-size: 22px;
  color: #8c8c8c;
  line-height: 22px;
}
.pay-amount {
  position: absolute;
  left: 36px;
  top: 570px;
  display: flex;
  align-items: center;
  gap: 2px;
}
.pay-amount-symbol {
  font-size: 24px;
  color: #111;
}
.pay-amount-num {
  font-size: 40px;
  color: #111;
  line-height: 40px;
}
.pay-actions {
  position: absolute;
  right: 36px;
  top: 527px;
}
.pay-submit {
  width: 280px;
  height: 88px;
  border-radius: 44px;
  background: #0018ff;
  color: #fff;
  font-size: 28px;
  display: flex;
  align-items: center;
  justify-content: center;
}
</style>
