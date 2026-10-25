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

    <!-- 列表（mescroll 区域）：有持仓展示持仓卡（样式 1:1 实测自源站 2026-10-24），空态黑猫 -->
    <div class="list-wrap">
      <div v-if="list.length" class="coll-grid">
        <div
          v-for="it in list"
          :key="it.id"
          class="coll-card"
          @click="openDetail(it)"
        >
          <div class="cc-cover-box">
            <img class="cc-cover" :src="it.cover" :alt="it.goods_name" />
            <span class="cc-badge">
              <img class="cc-badge-icon" src="/static/qbox/mine-collection.png" alt="" />
              <span class="cc-badge-num">{{ it.num }}</span>
            </span>
          </div>
          <span class="cc-name">{{ it.goods_name }}</span>
          <span class="cc-hold">持有{{ it.num }}</span>
        </div>
      </div>
      <div v-else class="list-empty">
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

    <!-- 持仓弹窗（样式 1:1 实测自源站 2026-10-24：无拖拽条，顶部右上角关闭按钮独立一行；
         标题行=名称+灰底"发行 N"胶囊+右侧批量寄售；"持有:N个"蓝灰 #6e7f9a；
         明细行：封面 94.2 方图 + 编号 + 购入价格(#9a9a9a) + 去寄售，行间距 39.9） -->
    <div v-if="showDetail" class="mask" @click.self="showDetail = false">
      <div class="popup">
        <div class="pp-topbar">
          <i class="pp-handle"></i>
          <span class="pp-close" @click="showDetail = false">×</span>
        </div>
        <div class="pp-head">
          <div class="pp-head-left">
            <div class="pp-title-row">
              <span class="pp-title">{{ current.goods_name }}</span>
              <span class="pp-issue">发行 {{ current.issue_num }}</span>
            </div>
            <span class="pp-hold">持有:{{ current.num }}个</span>
          </div>
          <span class="pp-batch-btn" @click="goConsign">批量寄售</span>
        </div>
        <div class="pp-list">
          <div v-for="it in current.items" :key="it.no" class="pp-row" @click="goItemDetail(it)">
            <img class="pp-row-cover" :src="current.cover" :alt="current.goods_name" />
            <div class="pp-row-info">
              <span class="pp-no">{{ it.no }}</span>
              <span class="pp-price">购入价格: {{ it.buy_price }}</span>
            </div>
            <span class="pp-sell-btn" @click.stop="goConsign">去寄售</span>
          </div>
        </div>
      </div>
    </div>
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
const showDetail = ref(false)
const current = ref({})

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
  const data = res.code === 1 && Array.isArray(res.data?.data) ? res.data.data : []
  // 接口为空时注入演示持仓（仅用于样式预览；封面取自原站真实上传图 /uploads/20261013）
  // items 为持分明细（源站弹窗实测：按编号逐条列出，含购入价格与去寄售按钮）
  list.value = data.length
    ? data
    : [
        {
          id: 1,
          goods_name: 'QBOX数字徽章',
          cover: '/uploads/20261022/7c48bc4f036e99b7a7df990ceeeff459.png',
          num: 2,
          issue_num: 5000,
          items: [
            { no: '#000026', buy_price: '--' },
            { no: '#000027', buy_price: '--' }
          ]
        }
      ]
}

// 源站实测：点击持仓卡 → 弹出底部明细弹窗（不跳转路由）
function openDetail(it) {
  current.value = it
  showDetail.value = true
}

// 去寄售/批量寄售：源站实测路由 ?pageType=2&id=持仓id（寄售提交页）
function goConsign() {
  showDetail.value = false
  router.push('/pages/consignments/consignments?pageType=2&id=' + (current.value.id || ''))
}

// 源站实测：点击弹窗明细行（藏品编号）→ 跳转持仓藏品详情 collectionDetails?id=xxx
function goItemDetail(it) {
  showDetail.value = false
  router.push('/pages/myCollection/collectionDetails?id=' + (current.value.id || '') + '&no=' + encodeURIComponent(it.no))
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

<style scoped src="../../styles/pages/myCollection-myCollection-p0.css"></style>
<style scoped src="../../styles/pages/myCollection-myCollection-p1.css"></style>
