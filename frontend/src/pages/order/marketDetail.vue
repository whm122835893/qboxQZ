<template>
  <div class="md-page">
    <!-- 加载态（原站直接访问实测：saleMarketOrderDetail 返回「订单不存在」→ 页面持续「加载中...」spinner + uni toast） -->
    <div class="loading-wrap">
      <div class="spinner"></div>
      <span class="loading-text">加载中...</span>
    </div>
    <div v-if="showToast" class="uni-toast">订单不存在</div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { request } from '../../api/request'

/**
 * 原站 chunk 反解（pages-order-marketDetail）：
 *  - 依赖市场订单 id 参数；直接访问 → saleMarketOrderDetail 返回 {code:0,msg:订单不存在}（实测）
 *    → 页面保持「加载中...」spinner（无数据渲染），并弹出 uni toast（实测截图）
 */
const showToast = ref(false)

onMounted(async () => {
  await request('/api/order/order/saleMarketOrderDetail', {})
  showToast.value = true
  setTimeout(() => { showToast.value = false }, 1500)
})
</script>

<style scoped>
.md-page {
  min-height: 100vh;
  background: #f8f8f8;
}
.loading-wrap {
  display: flex;
  flex-direction: column;
  align-items: center;
  margin-top: 348px;
}
.spinner {
  width: 20px;
  height: 20px;
  border: 2.5px solid #ddd;
  border-top-color: transparent;
  border-radius: 50%;
  animation: spin 0.8s linear infinite;
}
@keyframes spin {
  to { transform: rotate(360deg); }
}
.loading-text {
  margin-top: 12px;
  font-size: 14px;
  color: #c8c8c8;
}
.uni-toast {
  position: fixed;
  top: 45%;
  left: 50%;
  transform: translateX(-50%);
  background: rgba(153, 153, 153, 0.9);
  color: #fff;
  font-size: 14px;
  padding: 9px 16px;
  border-radius: 8px;
  z-index: 999;
  white-space: nowrap;
}
</style>
