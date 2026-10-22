<template>
  <div class="ir-page">
    <div class="navbar">
      <img class="navbar-back" src="/static/cw/back_btn.png" alt="返回" @click="$router.back()" />
      <span class="nav-title">邀请列表</span>
    </div>

    <!-- Tabs（有效邀请/无效邀请，选中加粗下划线） -->
    <div class="tabs">
      <div v-for="(t, i) in tabs" :key="t" class="tab-item" @click="current = i">
        <span class="tab-text" :class="{ 'tab-text-active': current === i }">{{ t }}</span>
        <div v-if="current === i" class="tab-line"></div>
      </div>
    </div>

    <!-- 空态（search-empty 146×142 渲染 y116；暂无内容 14px #999） -->
    <div class="empty">
      <img class="empty-img" src="/static/empty/search-empty.png" alt="" />
      <span class="empty-text">暂无内容</span>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'

/**
 * 原站 DOM 实测（2026-10-23）：标题 18px 700 #000；tabs 有效邀请/无效邀请；
 * 空态图 146×142 渲染（y116）、暂无内容 14px #999（y262）
 */
const tabs = ['有效邀请', '无效邀请']
const current = ref(0)
</script>

<style scoped>
.ir-page {
  min-height: 100vh;
  background: #fff;
}
.navbar {
  position: relative;
  width: 100%;
  height: 79.7px;
  display: flex;
  align-items: center;
  justify-content: center;
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
  font-weight: 700;
  color: #000;
}
.tabs {
  margin-top: 12px;
  display: flex;
  justify-content: center;
  gap: 90px;
}
.tab-item {
  display: flex;
  flex-direction: column;
  align-items: center;
}
.tab-text {
  font-size: 29px;
  color: #333;
}
.tab-text-active {
  font-weight: 700;
}
.tab-line {
  margin-top: 6px;
  width: 47.1px;
  height: 5.4px;
  background: #0018ff;
  border-radius: 2px;
}
.empty {
  margin-top: 66px;
  display: flex;
  flex-direction: column;
  align-items: center;
}
.empty-img {
  width: 264.7px;
  height: 257.5px;
  object-fit: contain;
}
.empty-text {
  margin-top: 25px;
  font-size: 25.4px;
  color: #999;
}
</style>
