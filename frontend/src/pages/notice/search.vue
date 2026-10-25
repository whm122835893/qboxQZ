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
      <!-- 源站实测（2026-10-25）：u-search__action 文字「搜索」位于 x=702，14px #303133，
           点击触发搜索（回车同效） -->
      <span class="search-action" @click="doSearch(inputText)">搜索</span>
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

<style scoped src="../../styles/pages/notice-search.css"></style>
