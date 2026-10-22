<template>
  <div class="notice-page">
    <div class="notice-container">
      <!-- 顶部双 Tab：平台公告 / 新闻发声 -->
      <div class="top-tabs">
        <span class="top-tab" :class="{ on: topTab === 'affiche' }" @click="switchTopTab('affiche')"> 平台公告 </span>
        <span class="top-tab" :class="{ on: topTab === 'news' }" @click="switchTopTab('news')"> 新闻发声 </span>
      </div>

      <!-- 搜索框（点击跳搜索页，原站实测） -->
      <div class="search-bar" @click="goSearch">
        <img class="search-icon" src="/static/qbox/common-search-gray.png" alt="" />
        <span class="search-placeholder">请输入关键词搜索</span>
      </div>

      <!-- 分类横滑卡（全部 + affichetypelist / ant_news/category） -->
      <div class="cat-scroll">
        <div
          v-for="(c, i) in categories"
          :key="c.id"
          class="cat-card"
          :class="{ selected: c.id === selectedCatId }"
          @click="selectCat(c)"
        >
          <!-- 全部/官方 为文字盒（原站 DOM 实测）：选中深色渐变、未选中 #bdbdbd -->
          <span v-if="c.text" class="cat-box" :class="{ dark: c.id === selectedCatId }">{{ c.text }}</span>
          <img v-else class="cat-box-img" :src="c.icon" alt="" />
          <span class="cat-label" :class="{ on: c.id === selectedCatId }">{{ c.name }}</span>
        </div>
      </div>

      <!-- 列表 -->
      <div class="notice-list">
        <div v-for="n in list" :key="n.id" class="notice-item" @click="goDetail(n)">
          <div class="notice-item-left">
            <span class="notice-item-title">{{ n.title }}</span>
            <div class="notice-item-meta">
              <span class="notice-item-badge">{{ n.categoryName }}</span>
              <span v-if="n.dateText" class="notice-item-date">{{ n.dateText }}</span>
            </div>
          </div>
          <div class="notice-item-thumb">
            <img class="notice-item-thumb-img" :src="n.thumb" alt="" />
          </div>
        </div>
      </div>
    </div>

    <!-- 回到顶部 -->
    <img
      v-show="showTotop"
      class="totop"
      src="/static/mescroll-totop.png"
      alt="回到顶部"
      @click="scrollTop"
    />

    <TabBar active="discover" />
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import { useRouter } from 'vue-router'
import { request } from '../../api/request'
import TabBar from '../../components/TabBar.vue'

const router = useRouter()

const topTab = ref('affiche') // affiche | news
const categories = ref([])
const selectedCatId = ref(0)
const list = ref([])
const showTotop = ref(false)

// 分类图标映射（图片资源下载自原站 /static/qbox/notice-cat-*.png）
const AFFICHE_ICONS = {
  寄售: '/static/qbox/notice-cat-consign.png',
  运营: '/static/qbox/notice-cat-operation.png',
  限价: '/static/qbox/notice-cat-limit.png',
  活动: '/static/qbox/notice-cat-activity.png',
  合成: '/static/qbox/notice-cat-compose.png',
  求购: '/static/qbox/notice-cat-beg.png',
  空投: '/static/qbox/notice-cat-airdrop.png'
}
// 列表缩略图兜底（原站实测：官方公告无 image 字段时显示官方横幅图）
function thumbOf(item, categoryName) {
  if (item.image) return item.image
  if (AFFICHE_ICONS[categoryName]) return AFFICHE_ICONS[categoryName]
  return '/static/qbox/notice-banner-official.png'
}

function fmtDate(ts) {
  if (!ts) return ''
  const d = new Date(ts * 1000)
  const p = (n) => String(n).padStart(2, '0')
  return `${d.getFullYear()}/${p(d.getMonth() + 1)}/${p(d.getDate())} ${p(d.getHours())}:${p(d.getMinutes())}`
}

async function switchTopTab(t) {
  topTab.value = t
  selectedCatId.value = 0
  await loadCategories()
  await loadList()
}

async function loadCategories() {
  if (topTab.value === 'affiche') {
    const res = await request('/api/index/affichetypelist')
    const types = res.code === 1 && Array.isArray(res.data) ? res.data : []
    categories.value = [
      { id: 0, name: '全部', text: '全部' },
      ...types.map(t => ({
        id: t.id,
        name: t.name.trim(),
        // 原站 DOM 实测：官方分类为文字盒，其余为图片
        ...(t.name.trim() === '官方' ? { text: '官方' } : { icon: AFFICHE_ICONS[t.name.trim()] })
      }))
    ]
  } else {
    const res = await request('/api/discover/ant_news/category')
    const types = res.code === 1 && Array.isArray(res.data) ? res.data : []
    categories.value = [{ id: 0, name: '全部', text: '全部' }, ...types.map(t => ({ id: t.id, name: t.name, icon: '' }))]
  }
}

async function loadList() {
  const path = topTab.value === 'affiche' ? '/api/index/afficheList' : '/api/discover/ant_news/list'
  const res = await request(path, { page: 1, category: selectedCatId.value, list_rows: 25 })
  const rows = res.code === 1 && Array.isArray(res.data?.data) ? res.data.data : []
  list.value = rows.map(r => {
    const categoryName = topTab.value === 'affiche'
      ? (categories.value.find(c => c.id === Number(r.type))?.name || (r.affiche_type ?? ''))
      : '新闻发声'
    return {
      id: r.id,
      title: r.title,
      categoryName,
      dateText: fmtDate(Number(r.createtime)),
      thumb: thumbOf(r, categoryName),
      raw: r
    }
  })
}

async function selectCat(c) {
  selectedCatId.value = c.id
  await loadList()
}

function goSearch() {
  router.push('/pages/notice/search')
}
function goDetail(n) {
  // 原站实测跳转：/pages/notice/noticeDetails?id=3&category=官方
  if (topTab.value === 'affiche') {
    router.push({ path: '/pages/notice/noticeDetails', query: { id: n.id, category: n.categoryName } })
  } else {
    router.push({ path: '/pages/notice/newsDetails', query: { id: n.id } })
  }
}

function onWinScroll() {
  showTotop.value = window.scrollY > 200
}
function scrollTop() {
  window.scrollTo({ top: 0, behavior: 'smooth' })
}

onMounted(async () => {
  await loadCategories()
  await loadList()
  window.addEventListener('scroll', onWinScroll, { passive: true })
})
onUnmounted(() => {
  window.removeEventListener('scroll', onWinScroll)
})
</script>

<style scoped>
.notice-page {
  min-height: 100vh;
  background-color: #f8f8f9;
}
.notice-container {
  width: 100%;
  display: flex;
  flex-direction: column;
  align-items: center;
  padding: 23.6px 34.4px 219.2px;
  box-sizing: border-box;
  background-color: #f8f8f9;
}

/* ---- 顶部双 Tab ---- */
.top-tabs {
  width: 677.5px;
  display: flex;
  align-items: flex-end;
  justify-content: center;
  gap: 48px;
}
.top-tab {
  font-weight: 400;
  font-size: 30.8px;
  font-family: 'MiSans-Semibold', sans-serif;
  color: #8c8c8c;
}
.top-tab.on {
  font-weight: 600;
  color: #000;
}

/* ---- 搜索框 ---- */
.search-bar {
  width: 677.5px;
  margin-top: 27.2px;
  height: 70.7px;
  background: #eeeded;
  border-radius: 34.4px;
  padding: 0 23.6px;
  box-sizing: border-box;
  display: flex;
  align-items: center;
  gap: 12px;
}
.search-icon {
  width: 30.8px;
  height: 30.8px;
}
.search-placeholder {
  font-weight: 300;
  font-size: 23.6px;
  color: #7c7c7c;
  font-family: var(--font-regular);
}

/* ---- 分类横滑卡 ---- */
.cat-scroll {
  width: 677.5px;
  height: 126.8px;
  margin-top: 14.5px;
  display: flex;
  align-items: center;
  gap: 12px;
  overflow-x: auto;
  padding: 14.5px 7.2px;
  box-sizing: border-box;
  scrollbar-width: none;
}
.cat-scroll::-webkit-scrollbar {
  display: none;
}
.cat-card {
  flex-shrink: 0;
  box-sizing: border-box;
  background: linear-gradient(0deg, #fdfdfd, #eee);
  border-radius: 18.1px;
  border: 5.4px solid #fefefe;
  padding: 16.3px;
  display: flex;
  align-items: center;
  gap: 12px;
}
.cat-card.selected {
  background: linear-gradient(0deg, #fdfdfd, #eee);
  box-shadow: rgba(0, 0, 0, 0.23) 0 1.8px 10.9px 0;
}
.cat-box {
  width: 63.4px;
  height: 63.4px;
  border-radius: 10.9px;
  flex-shrink: 0;
  display: flex;
  align-items: center;
  justify-content: center;
  background: #bdbdbd;
  font-weight: normal;
  font-size: 23.6px;
  color: #fbf2d5;
  font-family: 'AlimamaFangYuanTiVF-Thin', var(--font-regular);
}
.cat-box.dark {
  background: linear-gradient(49deg, #161616, #362c1f, #222);
}
.cat-box-img {
  width: 63.4px;
  height: 63.4px;
  border-radius: 10.9px;
  flex-shrink: 0;
  opacity: 0.55;
}
.cat-label {
  font-size: 25.4px;
  line-height: 1.2;
  text-align: center;
  font-family: 'MiSans-Semibold', sans-serif;
  color: #bfbfbf;
  white-space: nowrap;
}
.cat-label.on {
  color: #000;
}

/* ---- 列表 ---- */
.notice-list {
  width: 686.6px;
  margin-top: 27.2px;
  display: flex;
  flex-direction: column;
  gap: 20px;
}
.notice-item {
  width: 686.6px;
  height: 170.3px;
  background: rgba(255, 255, 255, 0.71);
  border-radius: 18.1px;
  padding: 19.9px 23.6px;
  box-sizing: border-box;
  display: flex;
  align-items: center;
  gap: 14px;
}
.notice-item-left {
  flex: 1;
  min-width: 0;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
  height: 100%;
}
.notice-item-title {
  line-height: 1.4;
  font-weight: 600;
  font-size: 23.6px;
  color: #181818;
  font-family: 'MiSans-Semibold', sans-serif;
  display: -webkit-box;
  -webkit-box-orient: vertical;
  -webkit-line-clamp: 2;
  overflow: hidden;
}
.notice-item-meta {
  display: flex;
  align-items: center;
  gap: 16px;
}
.notice-item-badge {
  padding: 3.6px 16.3px;
  background: #2e72f2;
  border-radius: 10.9px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 500;
  font-size: 18.1px;
  color: #fff;
  font-family: var(--font-medium);
}
.notice-item-date {
  font-weight: 300;
  font-size: 23.6px;
  color: #444;
  font-family: var(--font-regular);
}
.notice-item-thumb {
  width: 237.3px;
  height: 135.9px;
  border-radius: 14.5px;
  overflow: hidden;
  flex-shrink: 0;
  background: #e8e8e8;
}
.notice-item-thumb-img {
  width: 100%;
  height: 100%;
  object-fit: cover;
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
