<template>
  <!--
    快捷下单弹窗（源站 pages/market/marketGoodsList 底部「快捷下单」点击后弹出）
    源站实测 2026-10-25 11:58：
      弹窗内容区 [0,759,750,575]，圆角 32px 32px 0 0，底部固定
      遮罩 rgba(0,0,0,.7)，z-index 10070
      拖动条 [343,783,64,8] #e6e6e6
      封面 108×108 @[36,827]
      规格「流通量0/发行量0」22px #8c8c8c @[164,875]
      「支付方式」28px @[36,970]；渠道行灰底卡 [36,1021,678,104] #f5f5f5 radius 20
      徽标 48×48 黑圆 #111 @[60,1049]（内「A」22px #fff @[77,1062]）；名称 26px #111 @[124,1060]
      右侧选中点 18×18 蓝 #0018ff @[666,1064]
      提示「※请不要频繁锁单，否则会触发阶梯禁购规则」22px #ff8a00 @[36,1158]
      「确认」按钮 [36,1214,678,88] radius 44 bg #0018ff，文字 30px #fff @[345,1236]
  -->
  <div v-if="visible" class="qk-mask" @click="$emit('close')"></div>
  <div v-if="visible" class="qk-sheet">
    <div class="qk-grabber"></div>

    <div class="qk-goods">
      <img class="qk-goods-img" :src="cover" alt="" />
      <div class="qk-goods-info">
        <div class="qk-goods-spec">流通量{{ flux }}/发行量{{ totalNum }}</div>
      </div>
    </div>

    <div class="qk-method-label">支付方式</div>
    <div class="qk-method">
      <span class="qk-badge">{{ badge }}</span>
      <span class="qk-method-name">{{ methodName }}</span>
      <span class="qk-check" aria-hidden="true">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3.4" stroke-linecap="round" stroke-linejoin="round">
          <path d="M4 12.5l5.2 5.2L20 7" />
        </svg>
      </span>
    </div>

    <div class="qk-tip">※请不要频繁锁单，否则会触发阶梯禁购规则</div>

    <div class="qk-actions">
      <div class="qk-confirm" @click="$emit('confirm')">确认</div>
    </div>
  </div>
</template>

<script setup>
/**
 * 快捷下单弹窗（单一职责：只负责展示与上抛交互，不含业务请求）。
 * 几何与配色严格对齐源站实测值（见模板注释）。
 */
defineProps({
  visible: { type: Boolean, default: false },
  cover: { type: String, default: '' },
  flux: { type: [Number, String], default: 0 },
  totalNum: { type: [Number, String], default: 0 },
  /** 支付渠道徽标文字（源站 income_type=huifu → 徽标 A + A钱包） */
  badge: { type: String, default: 'A' },
  /** 支付渠道名称 */
  methodName: { type: String, default: 'A钱包' }
})

defineEmits(['close', 'confirm'])
</script>

<style scoped>
.qk-mask {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.7);
  z-index: 10070;
}
.qk-sheet {
  position: fixed;
  left: 0;
  right: 0;
  bottom: 0;
  height: 575px;
  background: #fff;
  border-radius: 32px 32px 0 0;
  z-index: 10071;
  box-sizing: border-box;
  font-family: var(--font-regular);
}
/* 以下 top 均相对弹窗顶部（源站绝对 y 减 759） */
.qk-grabber {
  position: absolute;
  left: 343px;
  top: 24px;
  width: 64px;
  height: 8px;
  border-radius: 4px;
  background: #e6e6e6;
}
.qk-goods {
  position: absolute;
  left: 36px;
  top: 68px;
  display: flex;
  align-items: flex-start;
}
.qk-goods-img {
  width: 108px;
  height: 108px;
  border-radius: 12px;
  object-fit: cover;
  background: #f2f2f2;
  flex-shrink: 0;
}
.qk-goods-info {
  margin-left: 20px;
  padding-top: 48px;
  min-width: 0;
}
.qk-goods-spec {
  font-size: 22px;
  color: #8c8c8c;
  line-height: 22px;
}
.qk-method-label {
  position: absolute;
  left: 36px;
  top: 211px;
  font-size: 28px;
  color: #111;
  line-height: 28px;
}
.qk-method {
  position: absolute;
  left: 36px;
  top: 262px;
  width: 678px;
  height: 104px;
  display: flex;
  align-items: center;
  /* 源站实测：渠道行灰底卡 #f5f5f5 圆角 20 */
  background: #f5f5f5;
  border-radius: 20px;
}
.qk-badge {
  /* 源站实测：48×48 黑圆 @[60,1049]，内「A」22px #fff @[77,1062] */
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
.qk-method-name {
  margin-left: 16px;
  font-size: 26px;
  color: #111;
}
.qk-check {
  /* 源站实测：18×18 蓝色选中点 @[666,1064] */
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
  margin-right: 30px;
}
.qk-check svg {
  width: 12px;
  height: 12px;
}
.qk-tip {
  position: absolute;
  left: 36px;
  top: 399px;
  font-size: 22px;
  color: #ff8a00;
  line-height: 22px;
}
.qk-actions {
  position: absolute;
  left: 36px;
  top: 455px;
  width: 678px;
}
.qk-confirm {
  width: 100%;
  height: 88px;
  border-radius: 44px;
  background: #0018ff;
  color: #fff;
  font-size: 30px;
  display: flex;
  align-items: center;
  justify-content: center;
}
</style>
