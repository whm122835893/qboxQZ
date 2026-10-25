<template>
  <!--
    市场挂单列表（源站 pages/market/marketGoodsList）
    源站实测 2026-10-25 11:58：
      每行卡片 684×124，白底 radius 20 padding 24/28，flex space-between center
      左侧：状态胶囊（锁单中，灰底 #f3f3f3 radius 8 高 32）+ 编号 24px Bold #111
      右侧：价格 32px #111 + 支付钱包 20px #8c8c8c（income_type=huifu → A钱包）
      行间距 24px
      点击行 → /pages/market/detail?id={挂单id}
  -->
  <div v-if="rows.length" class="sell-list">
    <div v-for="s in rows" :key="s.id" class="sell-row" @click="$emit('select', s)">
      <div class="sell-left">
        <div class="sell-title-row">
          <span v-if="s.status === 4" class="sell-status">锁单中</span>
          <span class="sell-goods-name">{{ goodsName }}</span>
        </div>
        <span class="sell-code">#{{ s.collection_code }}</span>
      </div>
      <div class="sell-right">
        <span class="sell-price">¥ {{ s.price }}</span>
        <span class="sell-pay">支付钱包：{{ walletName(s.income_type) }}</span>
      </div>
    </div>
  </div>
  <div v-else class="empty">
    <img class="empty-img" src="/static/qbox/common-empty.png" alt="" />
  </div>
</template>

<script setup>
/**
 * 市场挂单列表（单一职责：纯展示 + 上抛选中事件，不含业务请求）。
 * 几何与配色严格对齐源站实测值（见模板注释）。
 */
defineProps({
  rows: { type: Array, default: () => [] },
  goodsName: { type: String, default: '' }
})

defineEmits(['select'])

/** 源站 income_type 语义：["huifu"] → A钱包（其他渠道按原值回退展示） */
function walletName(incomeType) {
  const t = Array.isArray(incomeType) ? incomeType[0] : incomeType
  if (t === 'huifu') return 'A钱包'
  return t || 'A钱包'
}
</script>

<style scoped>
.sell-list {
  margin-top: 38px;
  display: flex;
  flex-direction: column;
  gap: 24px;
}
.sell-row {
  /* 源站实测：卡片 684×124 白底 radius 20 padding 24/28 */
  box-sizing: border-box;
  height: 124px;
  background: #fff;
  border-radius: 20px;
  padding: 24px 28px;
  display: flex;
  align-items: center;
  justify-content: space-between;
}
.sell-left {
  display: flex;
  flex-direction: column;
  gap: 12px;
}
.sell-title-row {
  display: flex;
  align-items: center;
  gap: 12px;
}
.sell-goods-name {
  font-size: 26px;
  line-height: 1.4;
  font-weight: 600;
  color: #111;
}
.sell-status {
  padding: 0 10px;
  height: 32px;
  line-height: 32px;
  border-radius: 8px;
  background: #f3f3f3;
  color: #4a4a4a;
  font-size: 26px;
  display: flex;
  align-items: center;
}
.sell-code {
  font-size: 24px;
  line-height: 1.5;
  font-weight: 700;
  color: #111;
}
.sell-right {
  display: flex;
  flex-direction: column;
  align-items: flex-end;
  gap: 8px;
}
.sell-price {
  font-size: 32px;
  line-height: 1.2;
  color: #111;
}
.sell-pay {
  font-size: 20px;
  line-height: 1.2;
  color: #8c8c8c;
}
.empty {
  margin-top: 90px;
  display: flex;
  flex-direction: column;
  align-items: center;
}
.empty-img {
  width: 282.6px;
  height: 282.6px;
  object-fit: contain;
}
</style>
