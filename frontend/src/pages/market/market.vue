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
            <span class="series-value" :class="{ on: s.series_id === selectedSeriesId }">{{ s.floor || '--' }}</span>
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
        <img class="search-sort" src="/static/qbox/market-sort-icon.png" alt="排序" />
      </div>

      <!-- 列表表头 -->
      <MarketListHeader />

      <!-- 列表：藏品行（源站实测 1:1 重建，样式见 MarketGoodsRow 组件） -->
      <div class="list-body">
        <MarketGoodsRow
          v-for="g in goods"
          :key="g.id"
          :goods="g"
          @select="goGoods"
        />

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
    <MarketDatePicker
      v-if="showDatePicker"
      :year="calYear"
      :month="calMonth"
      :lead="calLead"
      :days="calDays"
      :today="todayDate"
      :picked="pickedDate"
      :month-text="calMonth2Text"
      @pick="pickedDate = $event"
      @confirm="confirmDate"
      @close="showDatePicker = false"
    />

    <!-- 全部份数筛选弹窗（原站实测样式与选项） -->
    <MarketCountFilter
      v-if="showCountFilter"
      :options="countOptions"
      :picked="pickedCount"
      @pick="pickedCount = $event"
      @reset="pickedCount = '全部份数'"
      @close="showCountFilter = false"
    />

    <TabBar active="market" />
  </div>
</template>

<script setup>
import { useDatePicker } from '../../composables/useDatePicker'
import MarketDatePicker from '../../components/market/MarketDatePicker.vue'
import MarketCountFilter from '../../components/market/MarketCountFilter.vue'
import MarketListHeader from '../../components/market/MarketListHeader.vue'
import MarketGoodsRow from '../../components/market/MarketGoodsRow.vue'
import { ref, onMounted, onUnmounted } from 'vue'
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
    // 源站实测（2026-10-25 15:15 登录态）：板块集 = 我的关注(-2) / 推荐(-1) / 全部(0) /
    // 破茧(11) / 经典文化(2)。板块卡只渲染「推荐 / 破茧 / 经典文化」三项。
    activitySeries.value = res.data.filter(
      (s) => s.series_id !== -2 && s.series_id !== 0
    )
    // 源站实测默认选中「推荐」(series_id=-1) —— 该板块当前无数据，列表为空
    selectedSeriesId.value =
      (activitySeries.value.find((s) => s.series_id === -1) || {}).series_id ??
      (activitySeries.value.find((s) => s.is_def === 1) || {}).series_id ??
      (activitySeries.value[0] || {}).series_id
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
  const raw = res.code === 1 && Array.isArray(res.data?.data)
    ? res.data.data
    : Array.isArray(res.data)
      ? res.data
      : []
  // 字段映射：沿用原站接口字段名（name / listimg / total_num / flux / deal_num / min_price / market_fluctuate_new）
  goods.value = raw.map((g) => ({
    id: g.id,
    goods_name: g.name,
    cover: g.listimg,
    floor_price: g.min_price,
    issued: g.total_num,
    circ: g.flux,
    deal_num: g.deal_num,
    price_change: g.market_fluctuate_new,
    is_out: g.is_out,
    goods_type: g.goods_type ? Number(g.goods_type) : 2
  }))
  // 板块卡价格 = 该板块藏品地板价均值（源站实测：破茧 50.00 / 经典文化 258.71 / 推荐 --）
  syncSeriesFloor(seriesId)
  loading.value = false
}

// 板块卡价格同步：按源站规则取该板块藏品地板价均值，无数据时显示 --
// 源站实测（2026-10-25 15:15）：切到「破茧」后其卡片显示 50.00，「经典文化」仍为 --；
// 再切到「经典文化」显示其均价，破茧仍保留 50.00 —— 故按板块缓存，不整体重置。
function syncSeriesFloor(seriesId) {
  const rows = goods.value
  if (!rows.length) {
    const cur = activitySeries.value.find((s) => s.series_id === seriesId)
    if (cur) cur.floor = '--'
    return
  }
  const avg = rows.reduce((sum, g) => sum + Number(g.floor_price || 0), 0) / rows.length
  // 源站实测（2026-10-25 23:4x）：推荐板块卡显示「9,999.00」—— 千分位 + 2 位小数
  const cur = activitySeries.value.find((s) => s.series_id === seriesId)
  if (cur) {
    cur.floor = avg.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })
  }
}

function goGoods(g) {
  // 源站实测（2026-10-25 h5.xiyanwl.asia）：点击市场藏品行进入「挂单列表」页
  // URL = marketGoodsList?id={藏品id}&goodsType={类型2}&minPrice={地板价}
  const q = 'id=' + g.id + '&goodsType=' + (g.goods_type || 2) + '&minPrice=' + (g.floor_price ?? '')
  router.push('/pages/market/marketGoodsList?' + q)
}

function goSearch() {
  router.push('/pages/market/search')
}

// ---- 今日：日期选择（原站实测弹窗） ----
const showDatePicker = ref(false)
const {
  calYear,
  calMonth,
  todayDate,
  pickedDate,
  calLead,
  calDays,
  calMonth2Text
} = useDatePicker()
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

<style scoped src="../../styles/pages/market-market-p0.css"></style>
<style scoped src="../../styles/pages/market-market-p1.css"></style>
