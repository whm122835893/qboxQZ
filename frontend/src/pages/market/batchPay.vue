<template>
  <!-- 批量支付（pages/market/batchPay）：需携带订单参数进入；
       原站直接访问渲染纯白空白页（实测截图），如实复刻空白壳 -->
  <div class="bp-page"></div>
</template>

<script setup>
/**
 * 原站 chunk 反解（pages-market-batchPay）：
 *  - 批量支付页（/order/pay/batchdopay 等接口，依赖订单参数）；
 *    直接访问实测纯白空白，按规则7不臆造
 */
</script>

<style scoped>
.bp-page {
  min-height: 100vh;
  background: #fff;
}
</style>
