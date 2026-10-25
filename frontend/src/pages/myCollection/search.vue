<template>
  <!--
    我的收藏- 搜索（pages/myCollection/search）
    源站登录态实测（750 视口，2026-10-25）：
      - 导航栏高 44，标题「我的收藏」居中（34px/700 #000），左侧返回箭头
      - 搜索胶囊 x36 y44 w678 h72，圆角 36，底色 #f5f5f5
      - 输入区 x64 起，placeholder「输入藏品名称关键词搜索」灰字
      - 放大镜图标在胶囊右侧（源站 u-input search 形态）
      - 无关键词时结果区为空白
  -->
  <div class="cs-page">
    <div class="navbar">
      <span class="navbar-back" @click="$router.back()">
        <svg viewBox="0 0 24 24" fill="none"><path d="M15 4 7 12l8 8" stroke="#000" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"/></svg>
      </span>
      <span class="nav-title">我的收藏</span>
    </div>

    <!-- 搜索胶囊 -->
    <div class="search-box">
      <input v-model="keyword" class="search-input" placeholder="输入藏品名称关键词搜索" />
      <img class="search-icon" src="/static/qbox/common-search-black.png" alt="搜索" />
    </div>

    <!-- 结果列表（无关键词时源站渲染空白区） -->
  </div>
</template>

<script setup>
import { ref } from 'vue'

/**
 * 原站 DOM 实测（2026-10-25 登录态）：
 *  - u-navbar 高 44，标题「我的收藏」（u-navbar__content__title）
 *  - 搜索胶囊 x36 y44 w678 h72（gap-pf-row gap-pf-y-12 gap-pf-x-12）
 *  - u-input--square 位于 x64 w574，输入文字 24px，placeholder「输入藏品名称关键词搜索」
 *  - 无关键词时结果区空白
 */
const keyword = ref('')
</script>

<style scoped>
.cs-page {
  min-height: 100vh;
  background: #fff;
  padding: 0 36px;
  box-sizing: border-box;
}

/* 导航栏：源站 u-navbar 高 44 */
.navbar {
  position: relative;
  width: 100%;
  height: 44px;
  display: flex;
  align-items: center;
  justify-content: center;
}
.navbar-back {
  position: absolute;
  left: 12px;
  top: 50%;
  transform: translateY(-50%);
  width: 40px;
  height: 40px;
  display: flex;
  align-items: center;
  justify-content: center;
}
.navbar-back svg {
  width: 24px;
  height: 24px;
}
.nav-title {
  font-size: 34px;
  font-weight: 700;
  color: #000;
  font-family: var(--font-medium);
  line-height: 49px;
}

/* 搜索胶囊：源站 x36 y44 w678 h72，底色 #f5f5f5 */
.search-box {
  margin-top: 0;
  width: 678px;
  height: 72px;
  box-sizing: border-box;
  padding: 0 24px 0 28px;
  background: #f5f5f5;
  border-radius: 36px;
  display: flex;
  align-items: center;
}
.search-input {
  flex: 1;
  min-width: 0;
  height: 100%;
  border: none;
  outline: none;
  background: transparent;
  font-size: 24px;
  color: #4a4a4a;
  font-family: var(--font-regular);
}
.search-input::placeholder {
  color: #c0c4cc;
}
.search-icon {
  width: 32px;
  height: 32px;
  object-fit: contain;
  flex-shrink: 0;
}
</style>
