<template>
  <!-- 内嵌网页容器（pages/webview/webview）：需携带 url 参数进入；
       原站直接访问（无 url 参数）渲染纯白空白页（2026-10-23 实测截图），如实复刻空白壳 -->
  <div class="wv-page"></div>
</template>

<script setup>
/**
 * 原站 chunk 反解：web-view 容器页，src 来自路由 url 参数；无参数 → 白屏（实测）
 */
</script>

<style scoped>
.wv-page {
  min-height: 100vh;
  background: #fff;
}
</style>
