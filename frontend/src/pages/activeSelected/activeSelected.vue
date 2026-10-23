<template>
  <div class="as-page">
    <div class="navbar">
      <img class="navbar-back" src="/static/cw/back_btn.png" alt="返回" @click="$router.back()" />
      <span class="nav-title">活动中心</span>
    </div>

    <!-- 胶囊 Tabs（最新活动/抽奖中心，16px 白字深色胶囊 y84） -->
    <div class="capsule-tabs">
      <span
        v-for="(t, i) in tabs"
        :key="t"
        class="capsule"
        :class="{ 'capsule-active': current === i }"
        @click="switchTab(i)"
      >{{ t }}</span>
    </div>

    <!-- 空态（search-empty 146×142 y254；暂无内容 14px #999 y400；activity/index 空实测） -->
    <div class="empty">
      <img class="empty-img" src="/static/empty/search-empty.png" alt="" />
      <span class="empty-text">暂无内容</span>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { request } from '../../api/request'

/**
 * 原站 DOM 实测（2026-10-23）：标题 18px 700 #000；胶囊 tabs 16px 白字（最新活动/抽奖中心）；
 * 空态图 146×142（y254）、暂无内容 14px #999（y400）；
 * /api/Activity/category → data:[]（实测）、/api/activity/index → total:0（实测）
 */
const tabs = ['最新活动', '抽奖中心']
const current = ref(0)

function switchTab(i) {
  current.value = i
}
request('/api/Activity/category', {})
</script>

<style scoped>
.as-page {
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
.capsule-tabs {
  margin-top: 73px;
  display: flex;
  justify-content: space-between;
  padding: 0 33px;
}
.capsule {
  padding: 4px 18px;
  border-radius: 22px;
  background: #1a1a1a;
  font-size: 29px;
  color: #fff;
}
.capsule-active {
  background: #0018ff;
}
.empty {
  margin-top: 237px;
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
