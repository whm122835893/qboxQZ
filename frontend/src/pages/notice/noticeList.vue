<template>
  <div class="nl-page">
    <!-- 导航栏（白底，标题 18px/500 #000） -->
    <div class="navbar">
      <img class="navbar-back" src="/static/cw/back_btn.png" alt="" @click="$router.back()" />
      <span class="nav-title">公告列表</span>
    </div>

    <!-- 列表（原站 chunk 实测：本页为公告搜索结果页，仅当路由带 ?keyword= 才请求与渲染；
         直接访问时原站稳定渲染为空白——已实测两次确认，按原样复刻） -->
    <div class="list">
      <div
        v-for="a in list"
        :key="a.id"
        class="nl-item"
        @click="goDetail(a)"
      >
        <div class="nl-col">
          <span class="nl-title">{{ a.title }}</span>
          <div class="nl-time-row">
            <span class="nl-time">{{ formatTime(a.createtime) }}</span>
          </div>
        </div>
      </div>
    </div>

    <!-- 空结果态（原站 chunk 存在该分支：search-empty.png + 暂无内容；空结果状态未被观测到，按 chunk 结构实现并标注推断） -->
    <div v-if="keyword && searched && list.length === 0" class="nl-empty">
      <img class="nl-empty-img" src="/static/empty/search-empty.png" alt="" />
      <span class="nl-empty-text">暂无内容</span>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { request } from '../../api/request'

/**
 * 原站 chunk 反解（pages-notice-noticeList）：
 *  - onLoad(options) 读 keyword 参数 → search(keyword) → getList({search, page:1})
 *  - POST /api/index/afficheList {search, page} ；无 keyword 时页面保持空白（原站实测两次稳定复现）
 *  - 列表项点击 → /pages/notice/noticeDetails?id=<id>&category=<affiche_type>
 */
const route = useRoute()
const router = useRouter()
const keyword = ref('')
const searched = ref(false)
const list = ref([])

function formatTime(ts) {
  if (!ts) return ''
  const d = new Date(Number(ts) * 1000)
  const p = (n) => String(n).padStart(2, '0')
  return `${d.getFullYear()}-${p(d.getMonth() + 1)}-${p(d.getDate())} ${p(d.getHours())}:${p(d.getMinutes())}:${p(d.getSeconds())}`
}

function goDetail(a) {
  router.push(`/pages/notice/noticeDetails?id=${a.id}&category=${a.affiche_type || ''}`)
}

onMounted(async () => {
  const kw = String(route.query.keyword || '')
  if (!kw) return // 原站行为：无关键词时空白
  keyword.value = kw
  const res = await request('/api/index/afficheList', { search: kw, page: 1 })
  if (res.code === 1) list.value = res.data?.data || []
  searched.value = true
})
</script>

<style scoped>
.nl-page {
  min-height: 100vh;
  background: #fff;
}
.navbar {
  position: relative;
  height: 79.7px;
  background: #fff;
  display: flex;
  align-items: center;
  padding: 0 23.6px;
}
.navbar-back {
  width: 16.3px;
  height: 29px;
  object-fit: contain;
}
.nav-title {
  position: absolute;
  left: 50%;
  top: 50%;
  transform: translate(-50%, -50%);
  width: 398.6px;
  text-align: center;
  font-size: 32.6px;
  font-weight: 700;
  color: #000;
  font-family: var(--font-bold);
  white-space: nowrap;
}
/* 列表（原站实测 .list padding 0 30.8，y44 起） */
.list {
  padding: 0 30.8px;
}
/* 卡片（原站实测 padding 32.6 30.8 bg#f2f4f5 r30.8 间距 19.9） */
.nl-item {
  padding: 32.6px 30.8px;
  background: #f2f4f5;
  border-radius: 30.8px;
  margin-bottom: 19.9px;
  display: flex;
  justify-content: space-between;
  align-items: center;
}
.nl-col {
  display: flex;
  flex-direction: column;
  width: 100%;
}
/* 标题（原站实测 16px fw300 #000，两行截断，span 前后空格与原站一致由数据侧保留） */
.nl-title {
  font-size: 29px;
  font-weight: 300;
  color: #000;
  font-family: var(--font-medium);
  width: 100%;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
.nl-time-row {
  display: flex;
  justify-content: flex-end;
  margin-top: 9.1px;
}
.nl-time {
  font-size: 21.7px;
  color: #a3a3a3;
  font-family: var(--font-regular);
}
/* 空结果（原站 chunk：mt 398.6，图宽 264.5，文字 14px #999） */
.nl-empty {
  margin-top: 398.6px;
  display: flex;
  flex-direction: column;
  align-items: center;
}
.nl-empty-img {
  width: 264.5px;
}
.nl-empty-text {
  margin-top: 29px;
  font-size: 25.4px;
  color: #999;
}
</style>
