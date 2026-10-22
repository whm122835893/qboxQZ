<template>
  <div class="home-page">
    <div class="home-container">
      <!-- Banner 轮播：getBanner type=1 -->
      <div class="banner-box">
        <div class="banner-swiper" @touchstart.passive="onTouchStart" @touchend.passive="onTouchEnd">
          <div class="banner-track" :style="trackStyle">
            <img
              v-for="(b, i) in banners"
              :key="i"
              class="banner-img"
              :src="b"
              alt="banner"
            />
          </div>
        </div>
        <div v-if="banners.length > 1" class="banner-indicator">
          <span
            v-for="(b, i) in banners"
            :key="i"
            class="banner-dot"
            :class="{ on: i === bannerIndex }"
          ></span>
        </div>
      </div>

      <!-- 公告栏：afficheList -->
      <div class="notice-bar">
        <span class="notice-badge">公告</span>
        <div class="notice-scroll">
          <div class="notice-viewport">
            <div class="notice-inner" :style="noticeStyle">
              <span v-for="(n, i) in notices" :key="i" class="notice-item">{{ n }}</span>
            </div>
          </div>
        </div>
        <img class="notice-more" src="/static/cw/index_more.png" alt="更多" @click="goNotice" />
      </div>

      <!-- 活动卡片横滑区（实测渲染文案与跳转，来源 diypage 菜单组 + 原站点击实测） -->
      <div class="cards-section">
        <div class="cards-scroll" @scroll.passive="onCardsScroll">
          <div
            v-for="(c, i) in cards"
            :key="i"
            class="act-card"
            @click="goCard(c)"
          >
            <div class="act-card-top">
              <span class="act-card-title">{{ c.title }}</span>
              <span class="act-card-pill">
                <img class="act-card-pill-icon" src="/static/qbox/home-right-icon.png" alt="" />
              </span>
            </div>
            <span class="act-card-sub">{{ c.sub }}</span>
          </div>
        </div>
        <div class="cards-dots">
          <span
            v-for="p in cardPages"
            :key="p"
            class="cards-dot"
            :class="{ on: p - 1 === cardIndex }"
          ></span>
        </div>
      </div>

      <!-- 热门藏品 -->
      <div class="hot-section">
        <div class="hot-header">
          <img class="hot-header-icon" src="/static/qbox/common-hot.png" alt="" />
          <span class="hot-header-title">热门藏品</span>
        </div>
        <div class="hot-box">
          <div class="hot-empty">
            <img class="hot-empty-img" src="/static/qbox/common-empty.png" alt="" />
          </div>
        </div>
      </div>
    </div>

    <!-- 回到顶部（滚动后出现，原站 mescroll-totop 实测位置 right 11px bottom 66px） -->
    <img
      v-show="showTotop"
      class="totop"
      src="/static/mescroll-totop.png"
      alt="回到顶部"
      @click="scrollTop"
    />

    <TabBar active="home" />
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { useRouter } from 'vue-router'
import { request } from '../../api/request'
import TabBar from '../../components/TabBar.vue'

const router = useRouter()

// ---- Banner ----
const banners = ref([])
const bannerIndex = ref(0)
let bannerTimer = null

// ---- 公告 ----
const notices = ref([])
const noticeIndex = ref(0)
let noticeTimer = null

// ---- 活动卡片（标题/副标题/跳转路径均为原站实测） ----
const cards = [
  { title: '热门活动', sub: '参与精彩活动', path: '/pages/compound/task' },
  { title: '邀请好友', sub: '邀请好友享福利', path: '/pages/invitation/invitation' },
  { title: '签到活动', sub: '福利多多', path: '/pages/sign/index' },
  // 原站实测该卡片无跳转（活动未开放）；现接通抽奖页（diypage 配置的抽奖转盘路径），经用户确认
  { title: '抽奖活动', sub: '参与抽奖赢好礼', path: '/pages/lottery/lottery?id=1' },
  { title: '积分活动', sub: '兑换热门藏品', path: '/pages/shop/shop' }
]
const cardIndex = ref(0)
// 指示条按页分组（原站实测 5 张卡显示 3 个指示点：ceil(5/2)）
const cardPages = computed(() => Math.ceil(cards.length / 2))

const showTotop = ref(false)

const trackStyle = computed(() => ({
  transform: `translateX(-${bannerIndex.value * 100}%)`
}))
const noticeStyle = computed(() => ({
  transform: `translateY(-${noticeIndex.value * 100}%)`
}))

async function loadData() {
  const [bannerRes, afficheRes] = await Promise.all([
    request('/api/banner/show/getBanner', { type: 1 }),
    request('/api/index/afficheList')
  ])
  if (bannerRes.code === 1 && Array.isArray(bannerRes.data?.data)) {
    banners.value = bannerRes.data.data.map(b => b.image)
    if (banners.value.length > 1) {
      bannerTimer = setInterval(() => {
        bannerIndex.value = (bannerIndex.value + 1) % banners.value.length
      }, 3000)
    }
  }
  if (afficheRes.code === 1 && Array.isArray(afficheRes.data?.data)) {
    notices.value = afficheRes.data.data.map(a => a.title)
    if (notices.value.length > 1) {
      noticeTimer = setInterval(() => {
        noticeIndex.value = (noticeIndex.value + 1) % notices.value.length
      }, 3000)
    }
  }
}

function goNotice() {
  router.push('/pages/notice/notice')
}
function goCard(c) {
  if (c.path) router.push(c.path)
}

// 卡片滚动同步指示条
function onCardsScroll(e) {
  // 每页 2 张卡（页宽 = 2×卡宽 + 间距），与原站指示条逻辑一致
  const el = e.target
  const first = el.firstChild
  const pageW = first ? first.offsetWidth * 2 + 29 : 1
  cardIndex.value = Math.min(cardPages.value - 1, Math.round(el.scrollLeft / pageW))
}

// banner 触摸切换
let touchX = 0
function onTouchStart(e) {
  touchX = e.changedTouches[0].clientX
}
function onTouchEnd(e) {
  if (banners.value.length < 2) return
  const dx = e.changedTouches[0].clientX - touchX
  if (dx > 40) bannerIndex.value = Math.max(0, bannerIndex.value - 1)
  if (dx < -40) bannerIndex.value = Math.min(banners.value.length - 1, bannerIndex.value + 1)
}

function onWinScroll() {
  showTotop.value = window.scrollY > 200
}
function scrollTop() {
  window.scrollTo({ top: 0, behavior: 'smooth' })
}

onMounted(() => {
  loadData()
  window.addEventListener('scroll', onWinScroll, { passive: true })
})
onUnmounted(() => {
  clearInterval(bannerTimer)
  clearInterval(noticeTimer)
  window.removeEventListener('scroll', onWinScroll)
})
</script>

<style scoped>
/* 页面背景：home-bg.png 含顶部 "THIS IS QBOX 欢迎来到Qbox" 标题艺术字（原站同款） */
.home-page {
  min-height: 100vh;
  background-color: #f8f8f8;
}
.home-container {
  position: relative;
  z-index: 2;
  min-height: 100vh;
  display: flex;
  flex-direction: column;
  align-items: center;
  padding: 244.6px 36.2px 23.6px;
  box-sizing: border-box;
  background-color: #f8f8f8;
  background-image: url('/static/qbox/home-bg.png');
  background-repeat: no-repeat;
  background-size: 100% auto;
  background-position: center top;
}

/* ---- Banner ---- */
.banner-box {
  width: 677.5px;
  height: 253.6px;
}
.banner-swiper {
  width: 100%;
  height: 231.1px;
  border-radius: 32.6px;
  overflow: hidden;
}
.banner-track {
  display: flex;
  height: 100%;
  transition: transform 0.4s ease;
}
.banner-img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  flex-shrink: 0;
  border-radius: 32.6px;
}
.banner-indicator {
  height: 22.5px;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
}
.banner-dot {
  width: 13px;
  height: 13px;
  border-radius: 50%;
  background: rgba(255, 255, 255, 0.4);
}
.banner-dot.on {
  background: #fff;
}

/* ---- 公告栏 ---- */
.notice-bar {
  margin-top: 23.6px;
  width: 677.5px;
  height: 58px;
  background: #fff;
  border-radius: 18.1px;
  overflow: hidden;
  display: flex;
  align-items: center;
  padding: 0 23.6px;
  box-sizing: border-box;
}
.notice-badge {
  width: 47.3px;
  height: 36.2px;
  background: #393939;
  border-radius: 5.4px;
  font-size: 18.1px;
  color: #fff;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}
.notice-scroll {
  flex: 1;
  margin-left: 16px;
  padding-right: 24px;
  overflow: hidden;
}
.notice-viewport {
  height: 23.6px;
  overflow: hidden;
}
.notice-inner {
  transition: transform 0.4s ease;
}
.notice-item {
  display: block;
  height: 23.6px;
  line-height: 23.6px;
  font-size: 23.6px;
  color: #939393;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}
.notice-more {
  width: 34.4px;
  height: 34.4px;
  flex-shrink: 0;
}

/* ---- 活动卡片 ---- */
.cards-section {
  margin-top: 23.6px;
  width: 677.5px;
}
.cards-scroll {
  display: flex;
  gap: 29px;
  overflow-x: auto;
  scrollbar-width: none;
}
.cards-scroll::-webkit-scrollbar {
  display: none;
}
.act-card {
  width: 324.3px;
  height: 170.3px;
  flex-shrink: 0;
  background-image: url('/static/qbox/home-card-bg.png');
  background-size: 100% 100%;
  background-repeat: no-repeat;
  box-sizing: border-box;
  padding: 24px;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
}
.act-card-top {
  display: flex;
  align-items: center;
  justify-content: space-between;
}
.act-card-title {
  font-size: 30.8px;
  color: #111;
  font-family: var(--font-medium);
}
.act-card-pill {
  width: 59.8px;
  height: 36.2px;
  background: #343434;
  border-radius: 18.1px;
  display: flex;
  align-items: center;
  justify-content: center;
}
.act-card-pill-icon {
  width: 30.8px;
  height: 18.1px;
}
.act-card-sub {
  font-size: 23.6px;
  color: #8c8c8c;
  font-family: var(--font-regular);
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
  width: 39.9px;
  background: #d8d8d8;
  transition: width 0.3s;
}
.cards-dot.on {
  width: 23.6px;
  background: #111;
}

/* ---- 热门藏品 ---- */
.hot-section {
  margin-top: 23.6px;
  width: 677.5px;
}
.hot-header {
  display: flex;
  align-items: center;
  gap: 8px;
  height: 47.1px;
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
.hot-box {
  margin-top: 23.6px;
  width: 677.5px;
  border-radius: 25.4px;
  padding: 25.4px;
  box-sizing: border-box;
  background: transparent;
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

/* ---- 回到顶部 ---- */
.totop {
  position: fixed;
  right: 19.9px;
  bottom: 119.6px;
  width: 70.7px;
  height: 72.5px;
  border-radius: 50%;
  z-index: 9990;
}
</style>
