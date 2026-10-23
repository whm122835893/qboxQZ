<template>
  <div class="task-page">
    <!-- 导航栏：返回 + 居中标题（原站 u-navbar 实测 44px 高、标题 18px） -->
    <div class="task-navbar">
      <img class="nav-back" src="/static/cw/back_btn.png" alt="返回" @click="goBack" />
      <span class="nav-title">活动中心</span>
    </div>

    <div class="task-container">
      <!-- 活动卡横滑区（实测：合成活动跳转、抽奖活动无跳转） -->
      <div class="cards-scroll">
        <div
          v-for="c in cards"
          :key="c.title"
          class="act-card-wrap"
          @click="goCard(c)"
        >
          <div class="act-card">
            <span class="act-title">{{ c.title }}</span>
            <span class="act-sub">{{ c.sub }}</span>
          </div>
          <img class="act-mascot" :src="c.mascot" alt="" />
        </div>
      </div>
      <div class="cards-dots">
        <span class="cards-dot on"></span>
      </div>

      <!-- 热门活动 -->
      <div class="hot-section">
        <div class="hot-header">
          <img class="hot-header-icon" src="/static/qbox/common-hot.png" alt="" />
          <span class="hot-header-title">热门活动</span>
        </div>
        <div class="hot-body">
          <template v-if="hotThemes.length > 0">
            <div v-for="t in hotThemes" :key="t.id" class="hot-theme">{{ t.name }}</div>
          </template>
          <div v-else class="hot-empty">
            <img class="hot-empty-img" src="/static/qbox/common-empty.png" alt="" />
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { request } from '../../api/request'

const router = useRouter()

// 卡片数据与跳转行为均来自原站实测（抽奖活动实测无跳转）
const cards = [
  { title: '抽奖活动', sub: '抽取专属好礼', mascot: '/static/qbox/activity-lottery.png', path: '' },
  { title: '合成活动', sub: '藏品合成活动', mascot: '/static/qbox/activity-compose.png', path: '/pages/compound/compound?thm=1' }
]

// 热门活动列表（synthesis/themeList 实测；图片非法的条目原站不渲染）
const hotThemes = ref([])

function goCard(c) {
  if (c.path) router.push(c.path)
}
function goBack() {
  history.length > 1 ? router.back() : router.replace('/pages/index/index')
}

onMounted(async () => {
  const res = await request('/api/synthesis/themeList', { thm: '1', type: 1 })
  if (res.code === 1 && Array.isArray(res.data)) {
    // 原站实测：image 非法（如占位 "1"）的主题不渲染，页面呈现空态
    hotThemes.value = res.data.filter(t => typeof t.image === 'string' && (t.image.startsWith('/') || t.image.startsWith('http')))
  }
})
</script>

<style scoped>
.task-page {
  min-height: 100vh;
  background-color: #f8f8f8;
}
.task-navbar {
  position: fixed;
  left: 0;
  top: 0;
  z-index: 10;
  width: 100%;
  height: 79.7px;
  display: flex;
  align-items: center;
  background: transparent;
}
.nav-back {
  width: 16.3px;
  height: 29px;
  margin-left: 27.2px;
}
.nav-title {
  position: absolute;
  left: 50%;
  transform: translateX(-50%);
  width: 398.5px;
  text-align: center;
  font-size: 32.6px;
  font-weight: 700;
  color: #000;
  font-family: var(--font-bold);
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}
.task-container {
  width: 100%;
  display: flex;
  flex-direction: column;
  align-items: center;
  padding: 358.7px 34.4px 79.7px;
  box-sizing: border-box;
  background-color: #f8f8f8;
  background-image: url('/static/qbox/home-bg.png');
  background-repeat: no-repeat;
  background-size: 100%;
  background-position: center top;
}

/* ---- 活动卡横滑 ---- */
.cards-scroll {
  width: 677.5px;
  display: flex;
  align-items: flex-end;
  gap: 29px;
  padding-top: 23.6px;
  overflow: visible;
}
.act-card-wrap {
  position: relative;
  width: 324.3px;
  height: 170.3px;
  flex-shrink: 0;
  overflow: visible;
}
.act-card {
  width: 324.3px;
  height: 170.3px;
  background: #fff;
  border-radius: 25.4px;
  padding: 27.2px 23.6px;
  box-sizing: border-box;
  display: flex;
  flex-direction: column;
  gap: 12px;
}
.act-title {
  font-size: 30.8px;
  color: #111;
  font-family: var(--font-bold);
}
.act-sub {
  font-size: 23.6px;
  color: #8c8c8c;
  font-family: var(--font-regular);
}
.act-mascot {
  position: absolute;
  right: 0;
  bottom: 14.5px;
  width: 99.6px;
  height: 188.4px;
  z-index: 1;
  pointer-events: none;
}
.cards-dots {
  margin-top: 20px;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 12px;
}
.cards-dot {
  height: 5.4px;
  border-radius: 1.8px;
  width: 23.6px;
  background: #111;
}

/* ---- 热门活动 ---- */
.hot-section {
  width: 677.5px;
  display: flex;
  flex-direction: column;
  margin-top: 30.8px;
}
.hot-header {
  display: flex;
  align-items: center;
  gap: 8px;
}
.hot-header-icon {
  width: 34.4px;
  height: 34.4px;
}
.hot-header-title {
  font-size: 30.8px;
  color: #111;
  font-family: var(--font-bold);
}
.hot-body {
  width: 100%;
  margin-top: 23.6px;
}
.hot-empty {
  width: 100%;
  min-height: 478.2px;
  display: flex;
  align-items: center;
  justify-content: center;
}
.hot-empty-img {
  width: 282.6px;
  height: 282.6px;
  /* 原站 uni-image 实测：156×156 容器 + background-size:contain → 图等比缩放为 106.7×156 居中显示；
     object-fit:contain 与其完全等价（此前 fill 压扁 / height:auto 放大 均有偏差，已按实测修正） */
  object-fit: contain;
}
.hot-theme {
  margin-bottom: 21.7px;
  background: #fff;
  border-radius: 25.4px;
  padding: 27.2px;
  font-size: 30.8px;
  color: #111;
  font-family: var(--font-medium);
}
</style>
