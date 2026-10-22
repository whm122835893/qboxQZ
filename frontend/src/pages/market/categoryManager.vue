<template>
  <!-- 分类管理（pages/market/categoryManager）：原站直接访问实测请求超时，
       渲染灰色提示文字「The connection timed out, click the screen to try again.」（点击重试），如实复刻 -->
  <div class="cm-page" @click="reload">
    <span class="timeout-text">The connection timed out, click the screen to try again.</span>
  </div>
</template>

<script setup>
import { ref } from 'vue'

/**
 * 原站 chunk 反解（pages-market-categoryManager）：
 *  - 分类管理页（依赖藏品分类数据）；直接访问实测请求超时 →
 *    uni 请求错误提示文字（灰色，点击屏幕重试），如实复刻
 */
const reloading = ref(false)
function reload() {
  /* 原站点击屏幕重新请求；数据依赖藏品分类，mock 下维持超时提示 */
}
</script>

<style scoped>
/* 原站实测：uni-app 框架 .uni-async-error 提示（浏览器默认 serif 字体、浅灰），
   文字 bbox x31-381 y102-117（@414 渲染） */
.cm-page {
  min-height: 100vh;
  background: #fff;
  padding: 175px 0 0 56.2px;
  box-sizing: border-box;
}
.timeout-text {
  font-size: 29px;
  color: #a0a0a0;
  font-family: serif;
}
</style>
