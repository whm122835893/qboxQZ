<template>
  <div class="market-page">
    <div class="market-container">
      <!-- 标题（原站为真实文字 NanoByongGyeHei 25px） -->
      <div class="market-title-row">
        <span class="market-title">欢迎来到QBOX集市</span>
      </div>

      <!-- Tab 行：活动市场 / 自由市场(NEW) / 我的关注 + 今日 -->
      <div class="tab-row">
        <div class="tab-group">
          <div class="tab-item" @click="switchTab('activity')">
            <span class="tab-text" :class="{ on: tab === 'activity' }"> 活动市场 </span>
            <span class="tab-line" :class="{ on: tab === 'activity' }"></span>
          </div>
          <div class="tab-item tab-item-start" @click="switchTab('free')">
            <div class="tab-free-wrap">
              <div class="tab-item-col">
                <span class="tab-text" :class="{ on: tab === 'free' }">自由市场</span>
                <span class="tab-line" :class="{ on: tab === 'free' }"></span>
              </div>
              <span class="new-badge">NEW</span>
            </div>
          </div>
          <div class="tab-item" @click="switchTab('follow')">
            <div class="tab-item-col">
              <span class="tab-text" :class="{ on: tab === 'follow' }">我的关注</span>
              <span class="tab-line" :class="{ on: tab === 'follow' }"></span>
            </div>
          </div>
        </div>
        <div class="today-pill" @click="showDatePicker = true">
          <span class="today-text">今日</span>
          <img class="today-arrow" src="/static/qbox/common-triangle-right.png" alt="" />
        </div>
      </div>

      <!-- 活动市场：板块横滑区 -->
      <template v-if="tab === 'activity'">
        <div class="series-scroll">
          <div
            v-for="s in activitySeries"
            :key="s.series_id"
            class="series-card"
            :class="{ selected: s.series_id === selectedSeriesId }"
            @click="selectSeries(s)"
          >
            <div class="series-head">
              <template v-if="s.series_id === selectedSeriesId">
                <img class="series-badge" src="/static/qbox/market-board-badge.png" alt="板块" />
                <span class="series-name on">{{ s.series_name }}</span>
              </template>
              <template v-else>
                <span class="series-name">{{ s.series_name }}</span>
              </template>
            </div>
            <span class="series-value" :class="{ on: s.series_id === selectedSeriesId }"> -- </span>
          </div>
        </div>

        <!-- 筛选行 -->
        <div class="filter-row">
          <div class="filter-pill" @click="showCountFilter = true">
            <span class="filter-text">全部份数</span>
            <img class="pill-arrow" src="/static/qbox/common-triangle-right.png" alt="" />
          </div>
          <div class="filter-pill">
            <span class="filter-text">全部价格</span>
            <img class="pill-arrow" src="/static/qbox/common-triangle-right.png" alt="" />
          </div>
        </div>
      </template>

      <!-- 自由市场 / 我的关注：小标题 -->
      <div v-if="tab === 'free'" class="sub-title">个人集市</div>
      <div v-if="tab === 'follow'" class="sub-title">我的关注</div>

      <!-- 搜索行（仅活动市场，原站实测自由市场/我的关注不显示） -->
      <div v-if="tab === 'activity'" class="search-row">
        <div class="search-box" @click="goSearch">
          <img class="search-icon" src="/static/qbox/common-search-gray.png" alt="" />
          <span class="search-placeholder">输入藏品关键词</span>
        </div>
        <img class="search-sort" src="/static/qbox/market-sort.png" alt="排序" />
      </div>

      <!-- 列表表头 -->
      <div class="list-header">
        <span class="lh-name">藏品名称</span>
        <span class="lh-col">
          地板价
          <img class="lh-sort" src="/static/qbox/market-sort-icon.png" alt="" />
        </span>
        <span class="lh-col">
          成交量
          <img class="lh-sort" src="/static/qbox/market-sort-icon.png" alt="" />
        </span>
        <span class="lh-col lh-last">
          涨跌幅
          <img class="lh-sort" src="/static/qbox/market-sort-icon.png" alt="" />
        </span>
      </div>

      <!-- 列表：空态（当前账号与板块下无在售藏品，原站同款空态） -->
      <div class="list-body">
        <div v-if="!loading && goods.length === 0" class="list-empty">
          <img class="list-empty-img" src="/static/qbox/common-empty.png" alt="" />
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

    <!-- 今日：选择日期弹窗（原站实测样式） -->
    <div v-if="showDatePicker" class="mask" @click.self="showDatePicker = false">
      <div class="sheet">
        <div class="sheet-head">
          <span class="sheet-title">选择日期</span>
          <span class="sheet-close" @click="showDatePicker = false">
            <svg viewBox="0 0 24 24" width="16" height="16" stroke="#333" stroke-width="2.5" stroke-linecap="round" fill="none">
              <line x1="5" y1="5" x2="19" y2="19"></line><line x1="19" y1="5" x2="5" y2="19"></line>
            </svg>
          </span>
        </div>
        <div class="cal-month">{{ calYear }}年{{ calMonth }}月</div>
        <div class="cal-week">
          <span v-for="w in ['一','二','三','四','五','六','日']" :key="w" class="cal-week-item">{{ w }}</span>
        </div>
        <div class="cal-month2">{{ calMonth2Text }}</div>
        <div class="cal-grid">
          <span class="cal-watermark">{{ calMonth }}</span>
          <span v-for="i in calLead" :key="'b'+i" class="cal-cell"></span>
          <span
            v-for="d in calDays"
            :key="d"
            class="cal-cell"
            :class="{ disabled: d > todayDate, picked: d === pickedDate }"
            @click="d <= todayDate && (pickedDate = d)"
          >{{ d }}</span>
        </div>
        <div class="sheet-btns">
          <div class="btn-cancel" @click="showDatePicker = false">取消</div>
          <div class="btn-ok" @click="confirmDate">确定</div>
        </div>
      </div>
    </div>

    <!-- 全部份数筛选弹窗（原站实测样式与选项） -->
    <div v-if="showCountFilter" class="mask" @click.self="showCountFilter = false">
      <div class="sheet">
        <div class="option-grid">
          <div
            v-for="(o, i) in countOptions"
            :key="i"
            class="option-pill"
            :class="{ picked: o.label === pickedCount }"
            @click="pickedCount = o.label"
          >{{ o.label }}</div>
        </div>
        <div class="sheet-btns option-btns">
          <div class="btn-reset" @click="pickedCount = '全部份数'">重置</div>
          <div class="btn-dark" @click="showCountFilter = false">确定</div>
        </div>
      </div>
    </div>

    <TabBar active="market" />
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { useRouter } from 'vue-router'
import { request } from '../../api/request'
import TabBar from '../../components/TabBar.vue'

const router = useRouter()

const tab = ref('activity') // activity | free | follow
const loading = ref(false)
const goods = ref([])
const showTotop = ref(false)

// ---- 系列板块（getSeries?type=10 实测数据结构） ----
const activitySeries = ref([])
const selectedSeriesId = ref(null)

// ---- Tab 切换（实测：自由市场 series_id=-13、我的关注=-2、活动市场=选中板块） ----
async function switchTab(t) {
  tab.value = t
  await loadList()
}

async function loadSeries() {
  const res = await request('/api/index/getSeries?type=10')
  if (res.code === 1 && Array.isArray(res.data)) {
    // 活动市场展示：推荐(-1) + 正式系列；排除「我的关注(-2)」「全部(0)」（原站实测渲染）
    activitySeries.value = res.data.filter(s => s.series_id !== -2 && s.series_id !== 0)
    const def = activitySeries.value.find(s => s.is_def === 1)
    selectedSeriesId.value = def ? def.series_id : (activitySeries.value[0] || {}).series_id
  }
}

async function selectSeries(s) {
  selectedSeriesId.value = s.series_id
  await loadList()
}

async function loadList() {
  loading.value = true
  const seriesId = tab.value === 'free' ? -13 : tab.value === 'follow' ? -2 : selectedSeriesId.value
  const res = await request('/api/market/market/getMarketList', {
    goods_type: 2,
    page: 1,
    list_rows: 10,
    series_id: seriesId,
    add_deal_num: 1
  })
  goods.value = res.code === 1 && Array.isArray(res.data?.data) ? res.data.data : []
  loading.value = false
}

function goSearch() {
  router.push('/pages/market/search')
}

// ---- 今日：日期选择（原站实测弹窗） ----
const showDatePicker = ref(false)
const now = new Date()
const calYear = ref(now.getFullYear())
const calMonth = ref(now.getMonth() + 1)
const todayDate = ref(now.getDate())
const pickedDate = ref(now.getDate())
const calLead = computed(() => {
  const first = new Date(calYear.value, calMonth.value - 1, 1).getDay()
  return (first + 6) % 7 // 周一开头
})
const calDays = computed(() => new Date(calYear.value, calMonth.value, 0).getDate())
const calMonth2Text = computed(() => `${calYear.value}年${calMonth.value}月`)
function confirmDate() {
  showDatePicker.value = false
}

// ---- 全部份数筛选（原站实测弹窗与选项） ----
const showCountFilter = ref(false)
const pickedCount = ref('全部份数')
const countOptions = [
  { label: '全部份数' },
  { label: '大于10000份' },
  { label: '小于10000份' },
  { label: '小于5000份' },
  { label: '小于1000份' },
  { label: '小于100份' }
]

function onWinScroll() {
  showTotop.value = window.scrollY > 200
}
function scrollTop() {
  window.scrollTo({ top: 0, behavior: 'smooth' })
}

onMounted(async () => {
  await loadSeries()
  await loadList()
  window.addEventListener('scroll', onWinScroll, { passive: true })
})
onUnmounted(() => {
  window.removeEventListener('scroll', onWinScroll)
})
</script>

<style scoped>
.market-page {
  min-height: 100vh;
  background-color: #f8f8f9;
}
.market-container {
  width: 100%;
  min-height: 100vh;
  display: flex;
  flex-direction: column;
  align-items: center;
  padding: 79.7px 34.4px 0;
  box-sizing: border-box;
  background-color: #f8f8f9;
  background-image: url('/static/qbox/market-bg.png');
  background-repeat: no-repeat;
  background-size: 100% auto;
  background-position: center top;
}
.market-title-row {
  width: 677.5px;
}
.market-title {
  font-weight: 400;
  font-size: 45.3px;
  color: #000;
  line-height: 1.2;
  font-family: 'NanoByongGyeHei-Regular', var(--font-regular);
}

/* ---- Tab 行 ---- */
.tab-row {
  width: 677.5px;
  margin-top: 21.7px;
  display: flex;
  align-items: center;
  justify-content: space-between;
}
.tab-group {
  display: flex;
  align-items: flex-end;
  gap: 24px;
}
.tab-item-col {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 8px;
}
.tab-text {
  font-size: 29px;
  font-family: 'MiSans-Semibold', sans-serif;
  color: #5b5b5b;
  white-space: nowrap;
}
.tab-text.on {
  font-weight: bold;
  color: #000;
}
.tab-line {
  width: 47.1px;
  height: 5.4px;
  border-radius: 1.8px;
  background: transparent;
}
.tab-line.on {
  background: #0047ff;
}
.tab-item-start {
  align-items: flex-start;
}
.tab-free-wrap {
  display: flex;
  align-items: flex-start;
  gap: 8px;
}
.new-badge {
  padding: 1.8px 9.1px;
  background: #0018ff;
  border-radius: 7.2px;
  display: flex;
  align-items: center;
  font-weight: bold;
  font-size: 12.7px;
  color: #fff;
  font-family: 'MiSans-Semibold', sans-serif;
}
.today-pill {
  padding: 7.2px 19.9px;
  background: #d9ddff;
  border-radius: 18.1px;
  display: flex;
  align-items: center;
  gap: 6px;
}
.today-text {
  font-size: 23.6px;
  color: #0018ff;
  font-family: 'MiSans-Regular', sans-serif;
}
.today-arrow {
  width: 10.9px;
  height: 10.9px;
  transform: rotate(90deg);
  opacity: 0.55;
}

/* ---- 板块横滑 ---- */
.series-scroll {
  width: 677.5px;
  height: 146.8px;
  margin-top: 14.5px;
  display: flex;
  gap: 20px;
  overflow-x: auto;
  scrollbar-width: none;
}
.series-scroll::-webkit-scrollbar {
  display: none;
}
.series-card {
  flex-shrink: 0;
  box-sizing: border-box;
  height: 146.8px;
  min-width: 154px;
  padding: 19.9px 21.7px;
  display: flex;
  flex-direction: column;
  justify-content: flex-start;
  overflow: hidden;
  background: linear-gradient(#fff, #fefefe);
  border-radius: 27.2px;
  border: 3.6px solid transparent;
}
.series-card.selected {
  background: linear-gradient(#eee, #fdfdfd);
  border-radius: 19.9px;
  border: 3.6px solid #fdfdfd;
}
.series-head {
  display: flex;
  align-items: center;
  /* 徽章与名称间距：原站实测 6px 渲染 = 10.9 源码px */
  gap: 10.9px;
}
.series-badge {
  width: 87px;
  height: 29px;
  flex-shrink: 0;
  margin-left: -12.7px;
}
.series-name {
  font-size: 23.6px;
  line-height: 29px;
  color: #98a6bf;
  font-family: 'MiSans-Regular', sans-serif;
  white-space: nowrap;
}
.series-name.on {
  color: #343434;
  font-family: 'MiSans-Semibold', sans-serif;
}
.series-value {
  margin-top: 14.5px;
  line-height: 1.1;
  white-space: nowrap;
  font-family: 'AlimamaFangYuanTiVF-Thin', var(--font-regular);
  color: #98a6bf;
  /* 普通态 14px 渲染 = 25.4 源码px（原站实测） */
  font-size: 25.4px;
}
.series-value.on {
  color: #1a2440;
  /* 选中态放大 18px 渲染 = 32.7 源码px（原站实测：点击哪个板块哪个的价格数字变大） */
  font-size: 32.7px;
}

/* ---- 筛选行 ---- */
.filter-row {
  width: 677.5px;
  margin-top: 27.2px;
  display: flex;
  align-items: center;
  gap: 14px;
}
.filter-pill {
  padding: 14.5px 21.7px;
  display: flex;
  align-items: center;
  background: #f0f0f0;
  border-radius: 25.4px;
  gap: 6px;
}
.filter-text {
  font-size: 23.6px;
  color: #8c8c8c;
  font-family: 'MiSans-Regular', sans-serif;
}
.pill-arrow {
  width: 10.9px;
  height: 10.9px;
  transform: rotate(90deg);
  opacity: 0.35;
}

/* ---- 自由市场/我的关注 小标题 ---- */
.sub-title {
  width: 677.5px;
  margin-top: 27.2px;
  font-weight: bold;
  font-size: 29px;
  color: #000;
  font-family: 'MiSans-Semibold', sans-serif;
}

/* ---- 搜索行 ---- */
.search-row {
  width: 677.5px;
  margin-top: 25.4px;
  display: flex;
  align-items: center;
  gap: 14.5px;
}
.search-box {
  flex: 1;
  height: 70.7px;
  background: #f0f0f0;
  border-radius: 34.4px;
  padding: 0 27.2px;
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
  font-size: 23.6px;
  color: #c8c8c8;
  font-family: 'MiSans-Regular', sans-serif;
}
.search-sort {
  width: 30.8px;
  height: 30.8px;
}

/* ---- 列表表头 ---- */
.list-header {
  width: 677.5px;
  margin-top: 41.7px;
  padding-left: 23.6px;
  padding-right: 19.9px;
  box-sizing: border-box;
  display: flex;
  align-items: center;
}
.lh-name {
  width: 239.1px;
  font-weight: 500;
  font-size: 21.7px;
  line-height: 1.5;
  color: #919191;
  font-family: 'MiSans-Regular', sans-serif;
  flex-shrink: 0;
}
.lh-col {
  width: 117.8px;
  flex-shrink: 0;
  display: flex;
  align-items: center;
  justify-content: flex-end;
  box-sizing: border-box;
  font-weight: 500;
  font-size: 21.7px;
  line-height: 1.5;
  color: #919191;
  font-family: 'MiSans-Regular', sans-serif;
  gap: 2px;
}
.lh-last {
  width: 108.7px;
}
.lh-sort {
  width: 27.2px;
  height: 27.2px;
  flex-shrink: 0;
}

/* ---- 列表空态 ---- */
.list-body {
  width: 677.5px;
  margin-top: 27.2px;
  display: flex;
  flex-direction: column;
}
.list-empty {
  width: 100%;
  min-height: 478.2px;
  display: flex;
  flex-direction: column;
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

/* ---- 底部弹窗（选择日期 / 全部份数） ---- */
.mask {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.5);
  z-index: 9998;
  display: flex;
  align-items: flex-end;
}
.sheet {
  width: 100%;
  background: #fff;
  border-radius: 29px 29px 0 0;
  padding: 32px 25px calc(25px + env(safe-area-inset-bottom));
  box-sizing: border-box;
}
.sheet-head {
  position: relative;
  display: flex;
  align-items: center;
  justify-content: center;
}
.sheet-title {
  font-weight: bold;
  font-size: 29px;
  color: #000;
  font-family: 'MiSans-Semibold', sans-serif;
}
.sheet-close {
  position: absolute;
  right: 5px;
  top: 50%;
  transform: translateY(-50%);
  display: flex;
}
.cal-month {
  margin-top: 21.7px;
  text-align: center;
  font-size: 25.4px;
  color: #333;
  font-family: var(--font-regular);
}
.cal-week {
  margin-top: 21.7px;
  display: flex;
}
.cal-week-item {
  flex: 1;
  text-align: center;
  font-size: 23.6px;
  color: #333;
  font-family: var(--font-regular);
}
.cal-month2 {
  margin-top: 25.4px;
  text-align: center;
  font-size: 25.4px;
  color: #333;
  font-family: var(--font-regular);
  position: relative;
  z-index: 1;
}
.cal-grid {
  margin-top: 14.5px;
  display: flex;
  flex-wrap: wrap;
  position: relative;
}
.cal-watermark {
  position: absolute;
  left: 50%;
  top: 30%;
  transform: translate(-50%, -50%);
  font-size: 163px;
  color: rgba(0, 0, 0, 0.04);
  pointer-events: none;
}
.cal-cell {
  width: 14.2857%;
  height: 74.3px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 25.4px;
  color: #333;
  font-family: var(--font-regular);
  box-sizing: border-box;
}
.cal-cell.disabled {
  color: #ccc;
}
.cal-cell.picked {
  background: #0018ff;
  color: #fff;
  border-radius: 14.5px;
}
.sheet-btns {
  margin-top: 21.7px;
  display: flex;
  gap: 21.7px;
}
.btn-cancel {
  flex: 1;
  height: 79.7px;
  background: #fff;
  border: 1.8px solid #ececec;
  border-radius: 39.9px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 29px;
  color: #333;
  font-family: var(--font-medium);
}
.btn-ok {
  flex: 1;
  height: 79.7px;
  background: #0024ff;
  border-radius: 39.9px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 29px;
  color: #fff;
  font-family: var(--font-medium);
}

/* ---- 全部份数弹窗 ---- */
.option-grid {
  display: flex;
  flex-wrap: wrap;
  gap: 21.7px;
}
.option-pill {
  width: calc((100% - 43.4px) / 3);
  height: 59.8px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: #f0f0f0;
  border: 1.8px solid transparent;
  border-radius: 14.5px;
  box-sizing: border-box;
  font-size: 23.6px;
  color: #333;
  font-family: var(--font-regular);
}
.option-pill.picked {
  background: #fff;
  border: 1.8px solid #1a1a1a;
}
.option-btns {
  margin-top: 32px;
}
.btn-reset {
  width: 154px;
  height: 79.7px;
  background: #fff;
  border: 1.8px solid #ececec;
  border-radius: 39.9px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 29px;
  color: #333;
  font-family: var(--font-medium);
}
.btn-dark {
  flex: 1;
  height: 79.7px;
  background: #1a1a1a;
  border-radius: 39.9px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 29px;
  color: #fff;
  font-family: var(--font-medium);
}
</style>
