<template>
  <div class="beg-page">
    <!-- 导航行（原站实测：pt36.2 h87 padding 0 34.4，返回 13px 黑箭头旋转180°，标题居中 17px Bold，右占位 13px） -->
    <div class="nav-row">
      <img class="nav-back" src="/static/qbox/common-arrow-right-black.png" alt="" @click="$router.back()" />
      <span class="nav-title">我的求购</span>
      <div class="nav-ph"></div>
    </div>

    <!-- Tab 行（原站 u-tabs：4 项等分 flex1 h61.6 padding 0 27.2，选中黑字 + 蓝 32.6×3.6 下划线；右侧搜索图标 25.4） -->
    <div class="tab-row">
      <div class="tab-group">
        <div
          v-for="t in tabs"
          :key="String(t.status)"
          :ref="(el) => setTabRef(el, String(t.status))"
          class="tab-item"
          @click="switchTab(t.status)"
        >
          <span class="tab-text" :class="{ on: activeStatus === t.status }">{{ t.label }}</span>
        </div>
        <div class="tab-line" :style="lineStyle"></div>
      </div>
      <img class="tab-search-icon" src="/static/qbox/common-search-black.png" alt="" @click="focusSearch" />
    </div>

    <!-- 搜索框（原站实测：mt12.7 外层 padding 7.2 34.4 19.9，盒子 376×39@414=681.6×70.7 r34.4 bg#eee padding 0 27.2，输入 13px，右灰放大镜 34.4） -->
    <div class="search-wrap">
      <div class="search-box">
        <input
          ref="searchInput"
          v-model="keywords"
          class="search-input"
          type="text"
          placeholder="输入藏品名称或订单号搜索"
          @keyup.enter="loadList"
        />
        <img class="search-icon" src="/static/qbox/common-search-gray.png" alt="" @click="loadList" />
      </div>
    </div>

    <!-- 列表区：空态（原站实测 mt30.8 min-h478.2 黑猫 282.6 图片自带文案） -->
    <div class="list-wrap">
      <div v-if="!loading && list.length === 0" class="list-empty">
        <img class="list-empty-img" src="/static/qbox/common-empty.png" alt="" />
      </div>
      <!-- 求购卡片（原站账号无求购单，卡片结构不可观测，按接口字段语义实现并标注推断） -->
      <div v-for="(o, i) in list" :key="i" class="beg-card">
        <div class="beg-main">
          <span class="beg-name">{{ o.goods_name || o.name }}</span>
          <span class="beg-price">¥{{ o.price }}</span>
        </div>
        <span class="beg-status">{{ statusText(o.status) }}</span>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, nextTick } from 'vue'
import { request } from '../../../api/request'

const tabs = [
  { label: '全部', status: null },
  { label: '求购中', status: 2 },
  { label: '已成交', status: 3 },
  { label: '已取消', status: 4 }
]

const activeStatus = ref(null)
const keywords = ref('')
const loading = ref(false)
const list = ref([])
const searchInput = ref(null)
const tabRefs = {}
const lineStyle = ref({ transform: 'translateX(38.9px)' })

// 求购中=2 已成交=3 已取消=4（原站逐 Tab 抓包实测）；全部传 null
const statusText = (s) => ({ 2: '求购中', 3: '已成交', 4: '已取消' })[s] || ''

function setTabRef(el, status) {
  if (el) tabRefs[status] = el
}

function updateLine() {
  // 下划线为行内样式（不经 vw 转换），统一用渲染像素计算：居中 = 元素中心 - 线宽一半
  const el = tabRefs[String(activeStatus.value)]
  const line = document.querySelector('.tab-line')
  if (el && line) {
    const cx = el.offsetLeft + el.offsetWidth / 2
    lineStyle.value = { transform: `translateX(${cx - line.offsetWidth / 2}px)` }
  }
}

function switchTab(status) {
  if (activeStatus.value === status) return
  activeStatus.value = status
  loadList()
  nextTick(updateLine)
}

function focusSearch() {
  searchInput.value && searchInput.value.focus()
}

async function loadList() {
  loading.value = true
  const res = await request('/api/market/trade/begBuyOrderList', {
    page: 1,
    status: activeStatus.value,
    keywords: keywords.value
  })
  if (res.code === 1) list.value = res.data?.data || []
  loading.value = false
}

onMounted(async () => {
  await loadList()
  nextTick(updateLine)
})
</script>

<style scoped>
.beg-page {
  min-height: 100vh;
  background: #f7f7f7;
}
/* 导航行（原站：外层 pt36.2 + 内层 h87 padding 0 34.4） */
.nav-row {
  padding-top: 36.2px;
  height: 123.2px;
  box-sizing: border-box;
  padding-left: 34.4px;
  padding-right: 34.4px;
  display: flex;
  align-items: flex-end;
  justify-content: space-between;
}
/* 行内容高度 87（48@414） */
.nav-row :deep(.nav-back),
.nav-back {
  margin-bottom: 31.7px;
}
.nav-title {
  margin-bottom: 27.2px;
}
.nav-ph {
  margin-bottom: 31.7px;
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
.nav-ph {
  width: 23.6px;
  height: 23.6px;
}
/* Tab 行 */
.tab-row {
  width: 100%;
  padding: 0 23.6px 0 7.2px;
  box-sizing: border-box;
  display: flex;
  align-items: center;
}
.tab-group {
  flex: 1;
  min-width: 0;
  position: relative;
  display: flex;
  height: 79.7px;
}
.tab-item {
  flex: 1 1 0%;
  padding: 0 27.2px;
  height: 79.7px;
  display: flex;
  align-items: center;
  justify-content: center;
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
  bottom: 0;
  left: 0;
  width: 32.6px;
  height: 3.6px;
  background: #0018ff;
  transition: transform 0.3s;
}
.tab-search-icon {
  width: 25.4px;
  height: 25.4px;
  flex-shrink: 0;
}
/* 搜索框 */
.search-wrap {
  width: 100%;
  margin-top: 12.7px;
  padding: 7.2px 34.4px 19.9px;
  box-sizing: border-box;
}
.search-box {
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
  height: 100%;
  border: none;
  outline: none;
  background: transparent;
  font-size: 23.6px;
  color: #111;
  font-family: var(--font-regular);
}
.search-input::placeholder {
  color: #a6a6a8;
}
.search-icon {
  width: 34.4px;
  height: 34.4px;
  flex-shrink: 0;
}
/* 列表区 */
.list-wrap {
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
/* 求购卡片（推断结构，原站不可观测） */
.beg-card {
  background: #fff;
  border-radius: 23.6px;
  margin: 0 30.8px 21.7px;
  padding: 30.8px;
  display: flex;
  align-items: center;
  justify-content: space-between;
}
.beg-main {
  display: flex;
  flex-direction: column;
  gap: 10.9px;
}
.beg-name {
  font-size: 27.2px;
  color: #111;
  font-family: var(--font-medium);
}
.beg-price {
  font-size: 25.4px;
  color: #0018ff;
  font-family: var(--font-bold);
}
.beg-status {
  font-size: 23.6px;
  color: #8c8c8c;
}
</style>
