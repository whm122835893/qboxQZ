<template>
  <div class="order-page">
    <!-- 头部（原站实测：pt20 + 导航行 h48 + 可滚动 tabs + 搜索图标） -->
    <div class="order-head">
      <div class="nav-row">
        <img class="nav-back" src="/static/qbox/common-arrow-right-black.png" alt="返回" @click="goBack" />
        <span class="nav-title">我的订单</span>
        <span class="nav-spacer"></span>
      </div>
      <div class="tabs-row">
        <div class="tabs-scroll" ref="tabsScroll">
          <div
            v-for="t in tabs"
            :key="t.status"
            class="tab-item"
            :class="{ on: t.status === activeStatus }"
            :ref="el => setTabRef(el, t.status)"
            @click="switchTab(t.status)"
          >
            <span class="tab-text" :class="{ on: t.status === activeStatus }">{{ t.label }}</span>
          </div>
          <span class="tab-line" :style="lineStyle"></span>
        </div>
        <img
          class="tabs-search"
          src="/static/qbox/common-search-black.png"
          alt="搜索"
          @click="scrollToActive"
        />
      </div>
      <div class="search-row">
        <div class="search-bar">
          <input
            v-model="keyword"
            class="search-input"
            type="text"
            placeholder="输入藏品名称或订单号搜索"
            @keyup.enter="loadList"
          />
          <img class="search-icon" src="/static/qbox/common-search-gray.png" alt="" @click="loadList" />
        </div>
      </div>
    </div>

    <!-- 列表：空态黑猫 -->
    <div class="list-area">
      <div v-if="list.length" class="order-list">
        <div v-for="(o, i) in list" :key="i" class="order-item">{{ o.order_no || o.id }}</div>
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
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, nextTick } from 'vue'
import { useRouter } from 'vue-router'
import { request } from '../../api/request'

const router = useRouter()
const keyword = ref('')
const activeStatus = ref(0)
const list = ref([])
const showTotop = ref(false)
const tabsScroll = ref(null)
const tabRefs = {}
const activeIndex = ref(0)

// Tab 与 status 映射：全部/已完成/批量付/待支付/进行中/已取消 = 0/2/1/7/3/3（逐 Tab 抓包实测，进行中与已取消原站均传 3）
const tabs = [
  { label: '全部', status: 0 },
  { label: '已完成', status: 2 },
  { label: '批量付', status: 1 },
  { label: '待支付', status: 7 },
  { label: '进行中', status: 3 },
  { label: '已取消', status: 3 }
]

const lineStyle = ref({ transform: 'translateX(31px)' })

function setTabRef(el, status) {
  if (el) tabRefs[status] = el
}

function updateLine() {
  const el = tabRefs[activeStatus.value]
  if (el) {
    lineStyle.value = { transform: `translateX(${el.offsetLeft + el.offsetWidth / 2 - 16}px)` }
  }
}

async function loadList() {
  const res = await request('/api/order/order/orderList', {
    page: 1,
    status: activeStatus.value,
    keywords: keyword.value
  })
  list.value = res.code === 1 && Array.isArray(res.data?.data) ? res.data.data : []
}

function switchTab(status) {
  if (activeStatus.value === status) return
  activeIndex.value = tabs.findIndex(t => t.status === status)
  activeStatus.value = status
  loadList()
  nextTick(() => { updateLine(); scrollToActive() })
}

function scrollToActive() {
  const el = tabRefs[activeStatus.value]
  if (el && tabsScroll.value) {
    tabsScroll.value.scrollTo({ left: Math.max(0, el.offsetLeft - 60), behavior: 'smooth' })
  }
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

onMounted(async () => {
  await loadList()
  nextTick(updateLine)
  window.addEventListener('scroll', onWinScroll, { passive: true })
})
onUnmounted(() => {
  window.removeEventListener('scroll', onWinScroll)
})
</script>

<style scoped>
.order-page {
  min-height: 100vh;
  background-color: #f7f7f7;
}
.order-head {
  width: 100%;
  box-sizing: border-box;
  padding-top: 36.2px;
}
.nav-row {
  width: 100%;
  height: 87px;
  padding: 0 34.4px;
  box-sizing: border-box;
  display: flex;
  align-items: center;
  justify-content: space-between;
}
.nav-back {
  width: 23.6px;
  height: 23.6px;
  transform: rotate(180deg);
}
.nav-title {
  font-size: 30.8px;
  color: #111;
  font-family: var(--font-bold);
}
.nav-spacer {
  width: 23.6px;
  height: 23.6px;
}

/* tabs 行 */
.tabs-row {
  width: 100%;
  padding: 0 23.6px 0 7.2px;
  box-sizing: border-box;
  display: flex;
  align-items: center;
}
.tabs-scroll {
  flex: 1;
  min-width: 0;
  position: relative;
  display: flex;
  align-items: center;
  overflow-x: auto;
  scrollbar-width: none;
}
.tabs-scroll::-webkit-scrollbar {
  display: none;
}
.tab-item {
  flex-shrink: 0;
  padding: 0 27.2px;
  height: 61.6px;
  display: flex;
  align-items: center;
  position: relative;
  z-index: 1;
}
.tab-text {
  font-size: 25.4px;
  color: #8c8c8c;
  font-family: var(--font-regular);
  white-space: nowrap;
}
.tab-text.on {
  color: #111;
}
.tab-line {
  position: absolute;
  bottom: 7.2px;
  left: 0;
  width: 32.6px;
  height: 3.6px;
  border-radius: 1.8px;
  background: #0047ff;
  transition: transform 0.3s;
}
.tabs-search {
  width: 25.4px;
  height: 25.4px;
  flex-shrink: 0;
}

/* 搜索行 */
.search-row {
  width: 100%;
  margin-top: 12.7px;
  padding: 7.2px 34.4px 19.9px;
  box-sizing: border-box;
}
.search-bar {
  width: 100%;
  height: 70.7px;
  background: #eee;
  border-radius: 34.4px;
  padding: 0 27.2px;
  box-sizing: border-box;
  display: flex;
  align-items: center;
}
.search-input {
  flex: 1;
  min-width: 0;
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

/* 列表 */
.list-area {
  position: relative;
  z-index: 1;
  display: flex;
  flex-direction: column;
  margin-top: 30.8px;
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
.order-list {
  padding: 0 34.4px;
}
.order-item {
  background: #fff;
  border-radius: 25.4px;
  padding: 27.2px;
  margin-bottom: 21.7px;
  font-size: 25.4px;
  color: #111;
  font-family: var(--font-medium);
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
