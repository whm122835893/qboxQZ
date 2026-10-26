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

    <!-- 列表：订单卡（源站实测 2026-10-25 23:5x 登录态，相对卡 678×282rpx：
         行1 订单号：xxx 22r #8c8c8c（左）+ 状态 24r #8c8c8c（右，待支付红 #e53e3e）
         行2 封面 120×120r + 名称 28r #111 + 名称下「购买数量 N」22r 灰
             右列「购买金额」22r 灰 / ¥金额 28r #8c8c8c 数字体 + 右箭头 16×24r
         行3 订单时间:xxx 20r #8c8c8c
         待支付单右侧「去支付」132×66r 白字 22r 蓝底 r16） -->
    <div class="list-area">
      <div v-if="list.length" class="order-list">
        <div v-for="(o, i) in list" :key="i" class="order-item" @click="goOrderDetail(o)">
          <div class="oi-head">
            <span class="oi-no">订单号：{{ o.order_sn }}</span>
            <span class="oi-status" :class="{ 'oi-status-pay': o.order_status === 1 }">{{ statusText(o.order_status) }}</span>
          </div>
          <div class="oi-body">
            <img v-if="o.goods_cover_image" class="oi-cover" :src="o.goods_cover_image" alt="" />
            <div class="oi-info">
              <span class="oi-name">{{ o.goods_name }}</span>
              <span class="oi-sub">购买数量 {{ o.num }}</span>
            </div>
            <div class="oi-right">
              <span class="oi-amt-label">购买金额</span>
              <span class="oi-amount">¥ {{ o.total_price }}</span>
            </div>
            <img class="oi-arrow" src="/static/qbox/common-arrow-right-gray.png" alt="" />
          </div>
          <div class="oi-foot">
            <span class="oi-time">订单时间:{{ formatTime(o.createtime) }}</span>
            <span
              v-if="o.order_status === 1"
              class="oi-pay-btn"
              @click.stop="goOrderDetail(o)"
            >去支付</span>
          </div>
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

/**
 * 源站订单状态 → 文案（源站实测 2026-10-25 11:58）：
 *   order_status 1 = 待支付（红色 #e53e3e + 「去支付」按钮）
 *   order_status 2 = 转入成功（灰色 #8c8c8c）
 *   order_status 3 = 已取消（灰色 #8c8c8c）
 */
const ORDER_STATUS_TEXT = { 1: '待支付', 2: '转入成功', 3: '已取消' }

function statusText(s) {
  return ORDER_STATUS_TEXT[s] || ''
}

/** 源站订单时间格式：YYYY-MM-DD HH:mm:ss（源站实测 "2026-10-25 10:55:44"） */
function formatTime(ts) {
  const d = new Date(Number(ts || 0) * 1000)
  const p = (n) => String(n).padStart(2, '0')
  return (
    d.getFullYear() + '-' + p(d.getMonth() + 1) + '-' + p(d.getDate()) +
    ' ' + p(d.getHours()) + ':' + p(d.getMinutes()) + ':' + p(d.getSeconds())
  )
}

/**
 * 点击订单卡 → 订单详情
 * 源站实测（2026-10-25 23:5x）：市场单(order_type=2)与商城单(order_type=3)均进入
 * /pages/order/defaultDetail?id={id}&orderType={order_type}，由 orderType 区分详情接口
 */
function goOrderDetail(o) {
  router.push('/pages/order/defaultDetail?id=' + o.id + '&orderType=' + (o.order_type || '3'))
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

<style scoped src="../../styles/pages/order-list.css"></style>
