<template>
  <div class="rec-page">
    <!-- 导航栏 -->
    <div class="navbar">
      <img class="navbar-back" src="/static/cw/back_btn.png" alt="返回" @click="$router.back()" />
      <span class="nav-title">开盒记录</span>
    </div>

    <!-- 记录列表（原站 plr-16 ptb-12 行：昵称+时间 | 抽中+名称） -->
    <div v-for="item in list" :key="item.id" class="rec-row">
      <div class="rec-left">
        <span class="rec-nickname">{{ item.userss?.nickname }}</span>
        <span class="rec-time">{{ formatDate(item.createtime) }}</span>
      </div>
      <div class="rec-right">
        <span class="rec-hit">抽中</span>
        <span class="rec-name">{{ item.collections?.name }}</span>
      </div>
    </div>

    <!-- 空态（原站 w-loading emptyType=search：search-empty.png + 暂无内容） -->
    <div v-if="loading === 'empty'" class="rec-empty">
      <img class="rec-empty-img" src="/static/empty/search-empty.png" alt="" />
      <span class="rec-empty-text">暂无内容</span>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import { useRoute } from 'vue-router'
import { request } from '../../api/request'

/**
 * 原站 chunk 反解（pages-blindBox-openRecord 模块 d54c）：
 *  - onLoad(id)：search.blind_box_id = id → getOpenRecord({blind_box_id, page:1, list_rows:20})
 *  - onReachBottom：page++ 加载更多；total==0 → 空态（emptyType search）
 * 直接访问无 id 时原站同样请求并返回空列表（抓包实录）。
 */
const route = useRoute()
const list = ref([])
const loading = ref('none')
const search = { blind_box_id: route.query.id || null, page: 1, list_rows: 20 }

function formatDate(ts) {
  if (!ts) return ''
  const ms = ts > 1e12 ? ts : ts * 1000
  const d = new Date(ms)
  const p = (n) => String(n).padStart(2, '0')
  return `${d.getFullYear()}-${p(d.getMonth() + 1)}-${p(d.getDate())} ${p(d.getHours())}:${p(d.getMinutes())}`
}

async function getRecord(params) {
  if (loading.value === 'loading') return
  loading.value = 'loading'
  const res = await request('/api/box/blind_box/openlog', params)
  loading.value = 'none'
  if (res.code === 1 && res.data) {
    if (res.data.total === 0) loading.value = 'empty'
    if (res.data.data?.length) {
      list.value.push(...res.data.data)
      Object.assign(search, params)
    }
  }
}

function loadMore() {
  const next = { ...search, page: search.page + 1 }
  getRecord(next)
}

function onScroll() {
  if (window.innerHeight + window.scrollY >= document.body.scrollHeight - 40) loadMore()
}

onMounted(() => {
  getRecord({ ...search })
  window.addEventListener('scroll', onScroll)
})
onUnmounted(() => window.removeEventListener('scroll', onScroll))
</script>

<style scoped>
.rec-page {
  min-height: 100vh;
  background: #fff;
}
/* ---- 导航栏 ---- */
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
  font-weight: 500;
  color: #000;
  font-family: var(--font-medium);
}
/* ---- 记录行（plr-16 ptb-12） ---- */
.rec-row {
  /* plr-16 ptb-12 */
  padding: 24px 32px;
  display: flex;
  align-items: center;
  justify-content: space-between;
}
.rec-left {
  display: flex;
  flex-direction: column;
}
.rec-nickname {
  font-size: 28px;
  color: #000;
  font-family: var(--font-regular);
}
.rec-time {
  margin-top: 8px;
  font-size: 24px;
  color: #7a7a7a;
  font-family: var(--font-regular);
}
.rec-right {
  font-size: 28px;
}
.rec-hit {
  color: #7a7a7a;
  margin-right: 4px;
}
.rec-name {
  color: #000;
}
/* ---- 空态 ---- */
.rec-empty {
  display: flex;
  flex-direction: column;
  align-items: center;
  margin-top: 7px;
}
.rec-empty-img {
  width: 265px;
  object-fit: contain;
}
.rec-empty-text {
  margin-top: 16px;
  font-size: 24px;
  color: #999;
  font-family: var(--font-regular);
}
</style>
