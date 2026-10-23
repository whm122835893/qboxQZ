<template>
  <div class="coll-page">
    <!-- 导航栏 -->
    <div class="coll-navbar">
      <img class="nav-back" src="/static/cw/back_btn.png" alt="返回" @click="goBack" />
      <span class="nav-title">我的收藏</span>
    </div>

    <div class="coll-container">
      <!-- 搜索框 -->
      <div class="search-bar">
        <input v-model="keyword" class="search-input" type="text" placeholder="输入藏品名称关键词搜索" />
        <img class="search-icon" src="/static/qbox/common-search-black.png" alt="搜索" />
      </div>

      <!-- 两个组合分类卡（原站实测仅 2 张：权益品/数字藏品、盲盒其他/其他个人资产） -->
      <div class="seg-row">
        <div
          v-for="seg in segments"
          :key="seg.types"
          class="seg-card"
          :class="{ selected: seg.types === activeType }"
          @click="switchSeg(seg)"
        >
          <img class="seg-icon" :src="seg.icon" alt="" />
          <div class="seg-text">
            <span class="seg-label" :class="{ on: seg.types === activeType }">{{ seg.label }}</span>
            <span class="seg-sub" :class="{ on: seg.types === activeType }">{{ seg.sub }}</span>
          </div>
        </div>
      </div>
    </div>

    <!-- 列表（mescroll 区域）：空态黑猫 -->
    <div class="list-wrap">
      <div class="list-empty">
        <img class="list-empty-img" src="/static/qbox/common-empty.png" alt="" />
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
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import { useRouter } from 'vue-router'
import { request } from '../../api/request'

const router = useRouter()
const keyword = ref('')
const activeType = ref(2)
const list = ref([])
const showTotop = ref(false)

// 原站实测默认请求 types=2（权益品）；第二组合盲盒/其他按接口语义取 types=1（未观测，已标注）
const segments = [
  { types: 2, label: '权益品', sub: '数字藏品', icon: '/static/qbox/collection-nav-equity.png' },
  { types: 1, label: '盲盒/其他', sub: '其他个人资产', icon: '/static/qbox/collection-nav-blindbox.png' }
]

async function loadList() {
  const res = await request('/api/user_collection/user_collection/antMycollection', {
    types: activeType.value,
    page: 1
  })
  list.value = res.code === 1 && Array.isArray(res.data?.data) ? res.data.data : []
}

function switchSeg(seg) {
  if (seg.types === activeType.value) return
  activeType.value = seg.types
  loadList()
}

function goBack() {
  history.length > 1 ? router.back() : router.replace('/pages/my/my')
}
function onWinScroll() {
  showTotop.value = window.scrollY > 200
}
function scrollTop() {
  window.scrollTo({ top: 0, behavior: 'smooth' })
}

onMounted(() => {
  loadList()
  window.addEventListener('scroll', onWinScroll, { passive: true })
})
onUnmounted(() => {
  window.removeEventListener('scroll', onWinScroll)
})
</script>

<style scoped>
.coll-page {
  min-height: 100vh;
  background-color: #f8f8f9;
}
.coll-navbar {
  position: relative;
  width: 100%;
  height: 79.7px;
  display: flex;
  align-items: center;
}
.nav-back {
  width: 16.3px;
  height: 29px;
  margin-left: 27.2px;
}
.nav-title {
  position: absolute;
  left: 50%;
  top: 50%;
  transform: translate(-50%, -50%);
  font-size: 32.6px;
  font-weight: 700;
  color: #000;
  font-family: var(--font-bold);
}
.coll-container {
  width: 100%;
  display: flex;
  flex-direction: column;
  align-items: center;
  padding: 0 34.4px;
  box-sizing: border-box;
}

/* 搜索框 */
.search-bar {
  width: 677.5px;
  height: 70.7px;
  margin-top: 23.6px;
  background: #eeeded;
  border-radius: 34.4px;
  padding: 0 27.2px;
  box-sizing: border-box;
  display: flex;
  align-items: center;
}
.search-input {
  flex: 1;
  height: 70.7px;
  border: none;
  outline: none;
  background: transparent;
  font-size: 23.6px;
  color: #111;
  font-family: var(--font-regular);
}
.search-input::placeholder {
  color: #b2b2b2;
}
.search-icon {
  width: 34.4px;
  height: 34.4px;
  flex-shrink: 0;
}

/* 组合分类卡（2 张） */
.seg-row {
  width: 677.5px;
  margin-top: 23.6px;
  display: flex;
  gap: 30.8px;
}
.seg-card {
  flex: 1;
  height: 112.3px;
  border: 5.4px solid #fefefe;
  border-radius: 21.7px;
  box-sizing: border-box;
  padding: 0 19.9px;
  display: flex;
  align-items: center;
  background: #f2f2f2;
}
.seg-card.selected {
  background: #fff;
}
.seg-icon {
  width: 70.7px;
  height: 70.7px;
  border-radius: 50%;
  flex-shrink: 0;
}
.seg-text {
  display: flex;
  flex-direction: column;
  gap: 7.2px;
  margin-left: 14.5px;
  min-width: 0;
}
.seg-label {
  font-size: 27.2px;
  font-family: var(--font-bold);
  color: #c8c8c8;
  white-space: nowrap;
}
.seg-label.on {
  color: #111;
}
.seg-sub {
  font-size: 19.9px;
  font-family: var(--font-regular);
  color: #d0d0d0;
  white-space: nowrap;
}
.seg-sub.on {
  color: #8c8c8c;
}

/* 列表区 */
.list-wrap {
  width: 100%;
  display: flex;
  justify-content: center;
  padding: 0 34.4px;
  box-sizing: border-box;
}
.list-empty {
  width: 100%;
  min-height: 478.2px;
  display: flex;
  align-items: center;
  justify-content: center;
}
.list-empty-img {
  width: 282.6px;
  height: 282.6px;
  /* 原站 uni-image 实测：156×156 容器 + background-size:contain → 图等比缩放为 106.7×156 居中显示；
     object-fit:contain 与其完全等价（此前 fill 压扁 / height:auto 放大 均有偏差，已按实测修正） */
  object-fit: contain;
}

/* 回到顶部 */
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
