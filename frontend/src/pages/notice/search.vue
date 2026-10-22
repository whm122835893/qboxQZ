<template>
  <div class="ns-page">
    <!-- 顶部：返回 + 搜索框（原站 u-search：action「搜索」宽 0 不可见，触发为键盘确认） -->
    <div class="top-row">
      <img class="top-back" src="/static/qbox/common-arrow-right-black.png" alt="" @click="$router.back()" />
      <div class="search-box">
        <img class="search-icon" src="/static/qbox/common-search-gray.png" alt="" />
        <input
          v-model="inputText"
          class="search-input"
          type="text"
          placeholder="请输入搜索内容"
          confirm-type="search"
          @focus="hasFocus = true"
          @blur="onBlur"
          @keyup.enter="doSearch(inputText)"
        />
      </div>
      <!-- 原站存在「搜索」action 文字但宽 0 不可见（showAction=false），不渲染，行为由回车承担 -->
    </div>

    <!-- 搜索历史（原站实测：localStorage notice-history，≤10条，聚焦且有历史时显示；chips 样式未被观测到，按风格推断并标注） -->
    <div v-if="showHistory" class="history-wrap">
      <div class="history-title-row">
        <span class="history-title">搜索历史</span>
      </div>
      <div class="history-chips">
        <span v-for="(h, i) in history" :key="i" class="history-chip" @click="doSearch(h)">{{ h }}</span>
      </div>
    </div>

    <!-- 搜索结果列表（原站 chunk 实测：白透明卡 + 分类tag + 右缩略图；tagBg 寄售/默认 #ff7800、运营 #9aa0b8） -->
    <div v-if="list.length" class="result-list">
      <div v-for="a in list" :key="a.id" class="result-card" @click="goDetail(a)">
        <div class="rc-left">
          <span class="rc-title">{{ a.title }}</span>
          <div class="rc-meta">
            <span class="rc-tag" :style="{ background: tagBg(a) }">{{ a.affiche_type || '公告' }}</span>
            <span class="rc-time">{{ formatTime(a.createtime) }}</span>
          </div>
        </div>
        <div class="rc-thumb" :style="{ background: a.image ? 'transparent' : '#e8e8e8' }">
          <img v-if="a.image" class="rc-thumb-img" :src="a.image" alt="" />
        </div>
      </div>
    </div>

    <!-- 空态（原站实测：284rpx 正方 aspectFit = contain） -->
    <div v-if="searched && !list.length" class="ns-empty">
      <img class="ns-empty-img" src="/static/qbox/common-empty.png" alt="" />
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { request } from '../../api/request'

/**
 * 原站 chunk 反解（pages-notice-search）：
 *  - 搜索历史存 localStorage「notice-history」，最多 10 条、unshift 去重；聚焦且有历史时显示
 *  - search(词) → setHistory → afficheList {search, page}；无关键词 toast「搜索内容不可为空」
 *  - 列表项点击 → /pages/notice/noticeDetails?id&category；tagBg：含"寄售"或默认 #ff7800、含"运营" #9aa0b8
 *  - 时间格式 YYYY/MM/DD HH:mm；空态 284rpx aspectFit(=contain)
 *  - onLoad 支持 ?keyword= 直接搜索；触底 loadMore（page++）
 */
const router = useRouter()
const route = useRoute()
const inputText = ref('')
const hasFocus = ref(false)
const searched = ref(false)
const list = ref([])
const history = ref([])
const page = ref(1)
const loading = ref(false)

const showHistory = computed(() => history.value.length > 0 && hasFocus.value)

function getHistory() {
  const raw = localStorage.getItem('notice-history')
  history.value = raw ? JSON.parse(raw) : []
}
function setHistory(word) {
  const arr = [...history.value]
  if (arr.length > 9) arr.splice(10, 1)
  const idx = arr.indexOf(word)
  if (idx > -1) arr.splice(idx, 1)
  arr.unshift(word)
  localStorage.setItem('notice-history', JSON.stringify(arr))
  history.value = arr
}

function tagBg(a) {
  if (a.affiche_type && a.affiche_type.indexOf('运营') > -1) return '#9aa0b8'
  return '#ff7800' // 原站逻辑：含"寄售"或默认均为 #ff7800
}

function formatTime(ts) {
  if (!ts) return ''
  if (!(typeof ts === 'number' || /^\d+$/.test(String(ts)))) return ts
  const d = new Date(Number(ts) * 1000)
  const p = (n) => String(n).padStart(2, '0')
  return `${d.getFullYear()}/${p(d.getMonth() + 1)}/${p(d.getDate())} ${p(d.getHours())}:${p(d.getMinutes())}`
}

async function doSearch(word) {
  const w = String(word ?? '').trim()
  if (!w) return toast('搜索内容不可为空')
  inputText.value = w
  page.value = 1
  list.value = []
  setHistory(w)
  hasFocus.value = false
  loading.value = true
  const res = await request('/api/index/afficheList', { search: w, page: page.value })
  if (res.code === 1) list.value = res.data?.data || []
  searched.value = true
  loading.value = false
}

async function loadMore() {
  if (loading.value || !inputText.value) return
  loading.value = true
  page.value++
  const res = await request('/api/index/afficheList', { search: inputText.value, page: page.value })
  if (res.code === 1) list.value = list.value.concat(res.data?.data || [])
  loading.value = false
}

function onBlur() {
  // 原站实测：失焦 100ms 后收起历史
  setTimeout(() => { hasFocus.value = false }, 100)
}

function goDetail(a) {
  router.push(`/pages/notice/noticeDetails?id=${a.id}&category=${a.affiche_type || ''}`)
}

function toast(msg) {
  const el = document.createElement('div')
  el.textContent = msg
  el.style.cssText = 'position:fixed;top:18%;left:50%;transform:translateX(-50%);background:rgba(0,0,0,0.75);color:#fff;font-size:14px;padding:9px 18px;border-radius:8px;z-index:99999;white-space:nowrap;'
  document.body.appendChild(el)
  setTimeout(() => el.remove(), 2000)
}

function onScroll() {
  if (window.innerHeight + window.scrollY >= document.body.offsetHeight - 50) loadMore()
}

onMounted(() => {
  getHistory()
  // 原站 onLoad：支持 ?keyword= 直接搜索
  const kw = String(route.query.keyword || '')
  if (kw) doSearch(kw)
  window.addEventListener('scroll', onScroll, { passive: true })
})
onUnmounted(() => window.removeEventListener('scroll', onScroll))
</script>

<style scoped>
.ns-page {
  min-height: 100vh;
  background: #f8f8f9;
}
/* 顶部行（原站实测 padding 79.7 34.4，back 23.6 rotate180） */
.top-row {
  width: 100%;
  padding: 79.7px 34.4px;
  box-sizing: border-box;
  display: flex;
  flex-direction: row;
  align-items: center;
}
.top-back {
  width: 23.6px;
  height: 23.6px;
  transform: rotate(180deg);
  flex-shrink: 0;
}
/* 搜索框（原站实测 flex1 h70.7 r34.4 bg#eee padding 0 10.9 0 7.2 ml14.5，icon 39.9，input h58） */
.search-box {
  flex: 1 1 0%;
  height: 70.7px;
  background: #eeeded;
  border-radius: 34.4px;
  padding: 0 10.9px 0 7.2px;
  margin-left: 14.5px;
  box-sizing: border-box;
  display: flex;
  align-items: center;
}
.search-icon {
  width: 39.9px;
  height: 39.9px;
  flex-shrink: 0;
}
.search-input {
  flex: 1;
  min-width: 0;
  height: 58px;
  border: none;
  outline: none;
  background: transparent;
  font-size: 25.4px;
  color: #000;
  font-family: var(--font-regular);
}
.search-input::placeholder {
  color: #909399;
}
/* 搜索历史（chips 样式为推断，原站无历史数据未观测到） */
.history-wrap {
  width: 686.7px;
  margin: 27.2px auto 0;
}
.history-title-row {
  display: flex;
}
.history-title {
  font-size: 25.4px;
  font-weight: 600;
  color: #181818;
  font-family: var(--font-medium);
}
.history-chips {
  margin-top: 21.7px;
  display: flex;
  flex-wrap: wrap;
  gap: 14.5px;
}
.history-chip {
  background: #eeeded;
  border-radius: 25.4px;
  padding: 9.1px 21.7px;
  font-size: 23.6px;
  color: #444;
  font-family: var(--font-regular);
}
/* 结果列表（原站实测：卡片 688×171 r19 bg rgba(255,255,255,.71) padding 20 24，间距 36） */
.result-list {
  width: 686.7px;
  margin: 27.2px auto 0;
  display: flex;
  flex-direction: column;
  gap: 20px; /* 原站 gap-pf-y-20 = 20rpx 设计间距 */
}
.result-card {
  width: 688px;
  height: 171px;
  background: rgba(255, 255, 255, 0.71);
  border-radius: 19px;
  padding: 20px 24px;
  box-sizing: border-box;
  display: flex;
  align-items: center;
}
.rc-left {
  flex: 1;
  min-width: 0;
  display: flex;
  flex-direction: column;
}
.rc-title {
  font-weight: 600;
  font-size: 21px;
  color: #181818;
  line-height: 1.4;
  font-family: var(--font-bold);
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}
.rc-meta {
  margin-top: 16px;
  display: flex;
  align-items: center;
}
.rc-tag {
  padding: 4px 12px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  font-weight: 500;
  font-size: 19px;
  color: #fff;
  font-family: var(--font-regular);
}
.rc-time {
  margin-left: 16px;
  font-weight: 300;
  font-size: 16px;
  color: #444;
  font-family: var(--font-regular);
}
.rc-thumb {
  width: 238px;
  height: 137px;
  border-radius: 16px;
  overflow: hidden;
  flex-shrink: 0;
  margin-left: 20px;
}
.rc-thumb-img {
  width: 238px;
  height: 137px;
  object-fit: cover;
}
/* 空态（原站实测 284×284 aspectFit） */
.ns-empty {
  width: 100%;
  min-height: 480px;
  display: flex;
  align-items: center;
  justify-content: center;
}
.ns-empty-img {
  width: 284px;
  height: 284px;
  object-fit: contain;
}
</style>
