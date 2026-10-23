<template>
  <!-- 灵感墙详情（pages/notice/wallDetails）：依赖 wall id 参数；
       原站直接访问（无参数）持续显示「加载中...」（12px #c8c8c8 居中，实测），如实复刻 -->
  <div class="wd-page">
    <div class="loading-wrap">
      <div class="spinner"></div>
      <span class="loading-text">加载中...</span>
    </div>
  </div>
</template>

<script setup>
/**
 * 原站 DOM 实测：加载中 12px rgb(200,200,200)，y382（屏中偏上），持续 loading
 */
</script>

<style scoped>
.wd-page {
  min-height: 100vh;
  background: #f8f8f8;
}
.loading-wrap {
  display: flex;
  flex-direction: column;
  align-items: center;
  margin-top: 660px;
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
  font-size: 21.7px;
  color: #c8c8c8;
}
</style>
