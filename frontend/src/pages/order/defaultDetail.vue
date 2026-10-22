<template>
  <div class="dd-page">
    <!-- 加载态（原站直接访问实测：orderDetail 返回「找不到该订单或已被删除」→ 持续「加载中...」+ 深色 toast） -->
    <div class="loading-wrap">
      <div class="spinner"></div>
      <span class="loading-text">加载中...</span>
    </div>
    <div v-if="showToast" class="uni-toast">找不到该订单或已被删除</div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import { request } from '../../api/request'

/**
 * 原站 chunk 反解（pages-order-defaultDetail）：
 *  - 默认订单详情页，依赖 order id（orderType: 4 = 兑换记录入口等）；直接访问 →
 *    /api/order/order/orderDetail/ids/{id} 返回 {code:0,msg:找不到该订单或已被删除}（实测）
 *    → 页面保持「加载中...」spinner + 深色 uni toast（实测截图）
 */
const route = useRoute()
const showToast = ref(false)

onMounted(async () => {
  await request('/api/order/order/orderDetail/ids/' + (route.query.id || 'undefined'), {})
  showToast.value = true
  setTimeout(() => { showToast.value = false }, 1500)
})
</script>

<style scoped>
.dd-page {
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
  background: rgba(40, 40, 40, 0.9);
  color: #fff;
  font-size: 14px;
  padding: 10px 16px;
  border-radius: 8px;
  z-index: 999;
  white-space: nowrap;
}
</style>
