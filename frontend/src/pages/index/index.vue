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
          <!-- 正在发售中的藏品（样式 1:1 实测自源站 2026-10-24 登录抓取：
               300rpx 方卡/圆角31/底部 #383838 信息条/名称 #c8c8c8 Bold/白色价格/右侧状态胶囊；
               源站胶囊实测仅见灰色"未开售"，"发售中"用源站主蓝 #0018ff 表示） -->
          <div class="hot-grid">
            <div
              v-for="g in hotGoods"
              :key="g.id"
              class="hot-goods-card"
              @click="goGoods(g)"
            >
              <img class="hg-cover" :src="g.cover" :alt="g.goods_name" />
              <div class="hg-bar">
                <div class="hg-left">
                  <span class="hg-name">{{ g.goods_name }}</span>
                  <div class="hg-price-row">
                    <span class="hg-cny">¥</span>
                    <span class="hg-price">{{ g.floor_price }}</span>
                  </div>
                </div>
                <span class="hg-status">发售中</span>
              </div>
            </div>
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

// ---- 热门藏品：正在发售中的藏品（演示数据，仅用于样式预览） ----
// 封面使用原站真实上传图（/uploads/20261013/KUUKIE 黑熊）；字段结构对应 getMarketList 实测
const hotGoods = ref([
  {
    id: 1,
    goods_name: 'KUUKIE 怪物口袋·黑熊',
    cover: '/uploads/20261013/891305447d1a31e997fd7a0e078488a5.png',
    total_num: 5000,
    circ_num: 3268,
    floor_price: '39.90'
  }
])

function goGoods(g) {
  // 源站实测：首页热门藏品卡 → /pages/product/collection?id=x&batch_id=x（藏品发售详情）
  router.push('/pages/product/collection?id=' + g.id + '&batch_id=' + g.id)
}

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

<style scoped src="../../styles/pages/index-index-p0.css"></style>
<style scoped src="../../styles/pages/index-index-p1.css"></style>
