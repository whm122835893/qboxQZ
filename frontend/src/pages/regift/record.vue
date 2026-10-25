<template>
  <!--
    转赠记录（pages/regift/record）
    源站登录态实测（750 视口，2026-10-25）：
      - 导航栏高 44，标题「转赠记录」居中（34px/700 #000），左侧返回箭头
      - Tab 行 x0 y44 w750 h48，两项各 w375：「我转赠的」（选中，蓝色下划线）/「我收到的」（灰字）
      - 空态区 x0 y172 w750 h327，图 265×258（x243 y172）+「暂无内容」x323 y430
      - 该账号无转赠记录，故无列表卡
  -->
  <div class="rr-page">
    <div class="navbar">
      <span class="navbar-back" @click="$router.back()">
        <svg viewBox="0 0 24 24" fill="none"><path d="M15 4 7 12l8 8" stroke="#000" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"/></svg>
      </span>
      <span class="nav-title">转赠记录</span>
    </div>

    <!-- Tab 行 -->
    <div class="tabs">
      <span
        v-for="t in tabs"
        :key="t"
        class="tab-item"
        :class="{ on: t === active }"
        @click="active = t"
      >{{ t }}</span>
    </div>

    <!-- 空态 -->
    <div class="empty">
      <img class="empty-img" src="/static/empty/search-empty.png" alt="" />
      <span class="empty-text">暂无内容</span>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'

/**
 * 原站 DOM 实测（2026-10-25 登录态）：
 *  - u-navbar 高 44，标题「转赠记录」
 *  - u-tabs x0 y44 w750 h48；两项各 w375，文字 30px（选中 #0018ff + 蓝色下划线）
 *  - 空态区 x0 y172 w750 h327；图 265×258 + 「暂无内容」x323 y430
 *  - 该账号无转赠记录（列表区为空）
 */
const tabs = ['我转赠的', '我收到的']
const active = ref('我转赠的')
</script>

<style scoped>
.rr-page {
  min-height: 100vh;
  background: #fff;
}

/* 导航栏 */
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

/* Tab 行 */
.tabs {
  width: 100%;
  height: 48px;
  display: flex;
}
.tab-item {
  flex: 1;
  height: 48px;
  font-size: 30px;
  color: #999;
  font-family: var(--font-regular);
  text-align: center;
  position: relative;
  line-height: 48px;
}
.tab-item.on {
  color: #0018ff;
  font-weight: 600;
}
.tab-item.on::after {
  content: '';
  position: absolute;
  left: 50%;
  transform: translateX(-50%);
  bottom: 0;
  width: 48px;
  height: 6px;
  border-radius: 3px;
  background: #0018ff;
}

/* 空态 */
.empty {
  margin-top: 80px;
  width: 100%;
  height: 327px;
  display: flex;
  flex-direction: column;
  align-items: center;
}
.empty-img {
  width: 265px;
  height: 258px;
  object-fit: contain;
}
.empty-text {
  margin-top: 0;
  font-size: 28px;
  color: #999;
  font-family: var(--font-regular);
}
</style>
