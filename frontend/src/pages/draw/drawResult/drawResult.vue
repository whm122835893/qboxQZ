<template>
  <div class="dr-page">
    <!-- 导航栏 -->
    <div class="navbar">
      <img class="navbar-back" src="/static/cw/back_btn.png" alt="返回" @click="$router.back()" />
      <span class="nav-title">中签结果</span>
    </div>

    <!-- 加载态（原站直接访问实测：居中 circle spinner + 加载中...，无数据时持续该状态） -->
    <div class="loading-wrap">
      <div class="spinner"></div>
      <span class="loading-text">加载中...</span>
    </div>
  </div>
</template>

<script setup>
/**
 * 原站 chunk 反解（pages-draw-drawResult-drawResult）：
 *  - 中签结果页，依赖活动记录 id 参数；直接访问无数据 → 原站持续显示「加载中...」
 *    （u-loading-icon circle + 文字，2026-10-23 实测截图）
 */
</script>

<style scoped>
.dr-page {
  min-height: 100vh;
  background: #f5f5f5;
}
.navbar {
  position: relative;
  width: 100%;
  height: 79.7px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: #fff;
}
.navbar-back {
  position: absolute;
  left: 23.6px;
  top: 50%;
  transform: translateY(-50%);
  width: 17px;
  height: auto;
}
.nav-title {
  font-size: 32.6px;
  font-weight: 500;
  color: #000;
  font-family: var(--font-medium);
}
.loading-wrap {
  display: flex;
  flex-direction: column;
  align-items: center;
  margin-top: 566px;
}
.spinner {
  width: 30px;
  height: 30px;
  border: 3px solid #ddd;
  border-top-color: transparent;
  border-radius: 50%;
  animation: spin 0.8s linear infinite;
}
@keyframes spin {
  to { transform: rotate(360deg); }
}
.loading-text {
  margin-top: 16px;
  font-size: 21.7px;
  color: #c0c0c0;
}
</style>
