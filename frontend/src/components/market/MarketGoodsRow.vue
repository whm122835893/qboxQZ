<template>
  <!--
    市场藏品行（源站 pages/market/market 列表行）
    源站实测 2026-10-24：行高 148rpx，封面 108×108 圆角 12，
    左列为封面 + 名称（退市时名称下方红色「已退市」），
    右三列为 地板价 / 成交量（发行·流通·成交 三行） / 涨跌幅（涨跌箭头图 + 百分比）。
    单一职责：只渲染一行并上抛点击事件，不含数据请求与业务判断。
  -->
  <div
    class="goods-row"
    :class="{ 'goods-row-out': goods.is_out === 1 }"
    @click="$emit('select', goods)"
  >
    <div class="gr-left">
      <div class="gr-cover-box">
        <img class="gr-cover" :src="goods.cover" :alt="goods.goods_name" />
      </div>
      <div class="gr-name-col">
        <span class="gr-name">{{ goods.goods_name }}</span>
        <span v-if="goods.is_out === 1" class="gr-out">已退市</span>
      </div>
    </div>
    <span class="gr-col gr-floor">{{ goods.is_out === 1 ? '--' : goods.floor_price }}</span>
    <div class="gr-col gr-deal">
      <span class="gr-deal-line">
        <i>发行</i><b>&nbsp;{{ goods.issued }}</b>
      </span>
      <span class="gr-deal-line">
        <i>流通</i><b>&nbsp;{{ goods.circ }}</b>
      </span>
      <span class="gr-deal-line">
        <i>成交</i><b>&nbsp;{{ goods.deal_num }}</b>
      </span>
    </div>
    <span class="gr-change" :class="goods.price_change >= 0 ? 'up' : 'down'">
      <img
        :src="goods.price_change >= 0 ? '/static/qbox/market-change-up.png' : '/static/qbox/market-change-down.png'"
        alt=""
      />
      <!-- 源站实测 2026-10-25：百分比 22rpx，容器 110rpx 内不够宽时按 pre-line 换行（如 3233.00% → 3233.0 / 0%） -->
      <span class="gr-change-text">{{ Number(goods.price_change).toFixed(2) }}%</span>
    </span>
  </div>
</template>

<script setup>
/**
 * 市场藏品行（单一职责组件）。
 * props: goods —— 源站 getMarketList 返回的单条藏品
 * emits: select —— 用户点击该行，父组件负责跳转
 */
defineProps({
  goods: { type: Object, required: true }
})
defineEmits(['select'])
</script>

<style scoped src="../../styles/components/market-goods-row.css"></style>
