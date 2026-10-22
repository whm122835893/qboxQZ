<template>
  <!-- 底部悬浮胶囊 TabBar：尺寸/配色均取自原站实测（414px 视口测量值 × 750/414 转设计值） -->
  <div class="tabbar-spacer"></div>
  <div class="tabbar-fixed">
    <div class="tabbar-bar">
      <div
        v-for="item in tabs"
        :key="item.path"
        class="tabbar-item"
        :class="{ active: active === item.key }"
        @click="switchTab(item)"
      >
        <img class="tabbar-icon" :src="active === item.key ? item.activeIcon : item.icon" :alt="item.label" />
        <span class="tabbar-text">{{ item.label }}</span>
      </div>
    </div>
  </div>
</template>

<script setup>
import { useRouter } from 'vue-router'

defineProps({
  active: { type: String, default: 'home' }
})
const router = useRouter()

// 图标资源下载自原站 /static/tabBar/*
const tabs = [
  { key: 'home', label: '首页', path: '/pages/index/index', icon: '/static/tabBar/home.png', activeIcon: '/static/tabBar/home-active.png' },
  { key: 'market', label: '市场', path: '/pages/market/market', icon: '/static/tabBar/market.png', activeIcon: '/static/tabBar/market-active.png' },
  { key: 'discover', label: '发现', path: '/pages/notice/notice', icon: '/static/tabBar/discover.png', activeIcon: '/static/tabBar/discover-active.png' },
  { key: 'mine', label: '我的', path: '/pages/my/my', icon: '/static/tabBar/mine.png', activeIcon: '/static/tabBar/mine-active.png' }
]

function switchTab(item) {
  router.replace(item.path)
}
</script>

<style scoped>
.tabbar-spacer {
  height: calc(148.6px + env(safe-area-inset-bottom));
}
.tabbar-fixed {
  position: fixed;
  left: 0;
  right: 0;
  bottom: 0;
  z-index: 1000;
  padding-bottom: calc(23.6px + env(safe-area-inset-bottom));
  display: flex;
  align-items: center;
  justify-content: center;
  pointer-events: none;
}
.tabbar-bar {
  width: 684.9px;
  height: 108.7px;
  background: #fff;
  border-radius: 54.3px;
  box-sizing: border-box;
  padding: 0 16.3px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  pointer-events: auto;
  box-shadow: rgba(0, 0, 0, 0.05) 0 9.1px 23.6px 5.4px;
}
.tabbar-item {
  height: 74.3px;
  border-radius: 36.2px;
  display: flex;
  align-items: center;
  justify-content: center;
  box-sizing: border-box;
  flex: 1 1 0%;
  gap: 3.6px;
  flex-direction: column;
  background: transparent;
}
.tabbar-item.active {
  flex: 0 0 auto;
  min-width: 166.7px;
  padding-left: 27.2px;
  padding-right: 27.2px;
  gap: 10.9px;
  flex-direction: row;
  background: rgb(0, 0, 0);
}
.tabbar-icon {
  width: 39.9px;
  height: 39.9px;
  flex-shrink: 0;
}
.tabbar-text {
  font-size: 16.3px;
  font-family: 'MiSans-Medium', sans-serif;
  line-height: 1;
  white-space: nowrap;
  color: rgb(113, 113, 113);
}
.tabbar-item.active .tabbar-text {
  color: #fff;
  margin-left: 5.4px;
}
</style>
