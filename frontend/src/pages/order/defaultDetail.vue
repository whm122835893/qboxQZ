<template>
  <div class="od-page">
    <!-- 导航栏（源站实测：高 79.7px，返回图 /static/cw/back_btn.png，居中标题）
         注：源站在加载分支下不渲染页面标题（uni 原生导航标题位于 body 之外，
         加载态正文仅有「加载中...」），故此处按 order 是否就绪条件渲染 -->
    <div v-if="order" class="navbar">
      <img class="navbar-back" src="/static/cw/back_btn.png" alt="返回" @click="goBack" />
      <span class="nav-title">订单详情</span>
    </div>

    <div v-if="order" class="body">
      <!-- 商品卡 -->
      <section class="card">
        <div class="card-head">
          <h2 class="card-title">商品信息</h2>
          <span class="card-status">{{ order.statusText }}</span>
        </div>
        <div class="goods-row">
          <div class="goods-cover-wrap">
            <img
              v-if="order.cover"
              class="goods-cover"
              :src="order.cover"
              :alt="order.name"
              @error="onImgError"
            />
          </div>
          <div class="goods-info">
            <span class="goods-name">{{ order.name }}</span>
            <span class="goods-sub">数量 ×{{ order.num }}</span>
          </div>
          <span class="goods-amount">¥{{ order.amount }}</span>
        </div>
      </section>

      <!-- 订单信息卡 -->
      <section class="card">
        <div class="card-head"><h2 class="card-title">订单信息</h2></div>
        <div class="info-row">
          <span class="k">订单号</span>
          <span class="v mono">{{ order.orderNo }}</span>
          <button class="icon-btn" type="button" aria-label="复制订单号" @click="copyOrderNo">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <rect x="9" y="9" width="12" height="12" rx="2" />
              <path d="M5 15V5a2 2 0 0 1 2-2h10" />
            </svg>
          </button>
        </div>
        <div class="info-row"><span class="k">购买数量</span><span class="v">{{ order.num }}</span></div>
        <div class="info-row"><span class="k">订单金额</span><span class="v strong">¥{{ order.amount }}</span></div>
        <div class="info-row">
          <span class="k">
            <svg class="row-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M3 7h18v12H3z" /><path d="M3 11h18" /><path d="M16 15h2" />
            </svg>
            支付方式
          </span>
          <span class="v">{{ order.payType }}</span>
        </div>
        <div class="info-row"><span class="k">订单时间</span><span class="v">{{ order.createTime }}</span></div>
        <div class="info-row"><span class="k">订单状态</span><span class="v">{{ order.statusText }}</span></div>
      </section>
    </div>

    <!-- 加载态（数据未就绪时居中「加载中...」，与 market/detail.vue 写法一致） -->
    <div v-else class="loading-mask">
      <div class="loading-wrap">
        <div class="loading-icon"><span class="spinner"></span></div>
        <span class="loading-text">加载中...</span>
      </div>
    </div>

    <!-- 底部固定操作条：待支付「去支付」/ 已支付「查看藏品」 -->
    <div v-if="order" class="bottom-bar">
      <button v-if="order.unpaid" class="primary-btn" type="button" @click="onPay">去支付</button>
      <button v-else class="secondary-btn" type="button" @click="onViewCollection">查看藏品</button>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { request } from '../../api/request'
import { toast } from '../../utils/toast'

/**
 * 默认（商城/盲盒）订单详情 —— 源站 pages/order/defaultDetail
 *  入口：订单列表进入 /pages/order/defaultDetail?id={订单id}
 *  接口：POST /api/order/order/orderDetail/ids/{id}
 *  实测（无有效 id）：{ code: 0, msg: '找不到该订单或已被删除', data: null }
 *    → 页面渲染「加载中...」并弹 toast「找不到该订单或已被删除」
 *  降级：code !== 1 时保持加载态 + 用接口真实 msg 弹 toast，不编造数据。
 */
const route = useRoute()
const router = useRouter()
const order = ref(null)

/** 支付方式展示文案（源站返回 pay_way/pay_type） */
const PAY_TYPE_TEXT = { huifu: '汇付', huifu_pay: '汇付', weixin: '微信支付', wx: '微信支付', alipay: '支付宝', balance: '余额支付' }
/**
 * 订单状态文案。
 * 源站实测（2026-10-25 11:58 我的订单接口）：order_status 1=待支付 2=转入成功 3=已取消。
 * 同时兼容 status / status_text 形态。
 */
const STATUS_TEXT = { 0: '待支付', 1: '待支付', 2: '转入成功', 3: '已取消', 4: '已取消', 7: '待支付' }

function pickNum(...vals) {
  for (const v of vals) { const n = Number(v); if (Number.isFinite(n) && n > 0) return n }
  return 1
}
function pickStr(...vals) {
  for (const v of vals) { if (v !== undefined && v !== null && String(v).trim() !== '') return String(v) }
  return ''
}
function formatAmount(v) {
  const n = Number(v)
  return Number.isFinite(n) ? n.toFixed(2) : '0.00'
}
/** 源站订单时间为 Unix 时间戳（createtime），格式化为 YYYY-MM-DD HH:mm:ss */
function formatTime(ts) {
  const n = Number(ts)
  if (!Number.isFinite(n) || n <= 0) return ''
  const d = new Date(n * 1000)
  const p = (v) => String(v).padStart(2, '0')
  return (
    d.getFullYear() + '-' + p(d.getMonth() + 1) + '-' + p(d.getDate()) +
    ' ' + p(d.getHours()) + ':' + p(d.getMinutes()) + ':' + p(d.getSeconds())
  )
}
function mapOrder(d) {
  const st = d.order_status !== undefined ? d.order_status : d.status
  const statusText = pickStr(d.status_text, d.state_text, STATUS_TEXT[st], '待支付')
  const payRaw = pickStr(d.pay_type, d.pay_way, d.pay_name)
  const unpaid = statusText.indexOf('待支付') > -1 || st === 1 || st === 7 || d.pay_status === 0
  return {
    id: d.id,
    orderNo: pickStr(d.order_sn, d.order_no, d.order_id, d.id, '—'),
    name: pickStr(d.goods_name, d.name, d.title, '商品'),
    cover: pickStr(d.goods_cover_image, d.cover, d.listimg, d.main_image),
    num: pickNum(d.num, d.buy_num, d.count, d.quantity),
    amount: formatAmount(pickStr(d.total_price, d.price, d.amount)),
    payType: payRaw ? PAY_TYPE_TEXT[payRaw.toLowerCase()] || payRaw : '—',
    createTime: pickStr(d.create_time_text, formatTime(d.createtime), d.created_at, d.create_time, '—'),
    statusText,
    unpaid,
    goodsId: pickStr(d.goods_id, d.product_id)
  }
}

async function loadOrder() {
  const id = route.query.id
  const res = await request('/api/order/order/orderDetail/ids/' + (id || 'undefined'), { id })
  if (!res || res.code !== 1 || !res.data) {
    toast(pickMsg(res))
    return
  }
  order.value = mapOrder(res.data)
}
/** 取接口真实 msg（本地 mock 未覆盖该接口时回落到源站实测文案） */
function pickMsg(res) {
  const msg = res && res.msg ? String(res.msg) : ''
  return msg && msg.indexOf('mock 未覆盖') === -1 ? msg : '找不到该订单或已被删除'
}
async function onPay() {
  // 源站：待支付订单点「去支付」拉起汇付收银台（第三方域，独立项目不接入）。
  // 此处按订单支付语义调用 /api/order/order/payOrder，成功后回到我的订单列表。
  const res = await request('/api/order/order/payOrder', { order_id: order.value.id })
  if (!res || res.code !== 1) {
    toast(res && res.msg ? res.msg : '支付失败')
    return
  }
  toast(res.msg || '支付成功')
  router.replace('/pages/order/list')
}
function onViewCollection() {
  if (!order.value.goodsId) { toast('暂无关联藏品信息'); return }
  router.push('/pages/market/detail?id=' + order.value.goodsId)
}
async function copyOrderNo() {
  try {
    await navigator.clipboard.writeText(order.value.orderNo)
    toast('订单号已复制')
  } catch (e) {
    toast('复制失败，请手动选择')
  }
}
function goBack() {
  history.length > 1 ? router.back() : router.replace('/pages/order/list')
}
/** 封面图加载失败时隐藏 img，露出占位底色 */
function onImgError(e) {
  e.target.style.visibility = 'hidden'
}
onMounted(loadOrder)
</script>

<style scoped>
.od-page {
  /* 本页局部色板（其余颜色一律继承全局 token） */
  --od-brand: #0018ff;
  --od-loading-fg: #c8c8c8;
  min-height: 100vh;
  background: var(--qbox-page-bg);
  padding-bottom: 220px;
  box-sizing: border-box;
}
.navbar { position: relative; height: 79.7px; display: flex; align-items: center; justify-content: center; }
.navbar-back { position: absolute; left: 23.6px; top: 50%; transform: translateY(-50%); width: 17px; height: auto; }
.nav-title { font-size: 32.6px; font-weight: 700; color: #000; font-family: var(--font-medium); }
.body { padding: 0 17.7px; }
.card { background: #fff; border-radius: 11px; padding: 27.2px; margin-bottom: 17.7px; }
.card-head { display: flex; align-items: center; justify-content: space-between; }
.card-title { font-size: 30.8px; color: #000; font-family: var(--font-medium); font-weight: 500; }
.card-status { font-size: 25.4px; color: var(--qbox-gray-text); font-family: var(--font-regular); }
.goods-row { margin-top: 27.2px; display: flex; align-items: center; }
.goods-cover-wrap { position: relative; width: 96px; height: 96px; border-radius: 8px; overflow: hidden; flex-shrink: 0; background: var(--qbox-input-bg); }
.goods-cover { width: 96px; height: 96px; object-fit: cover; display: block; }
.goods-info { flex: 1; min-width: 0; margin-left: 17.7px; display: flex; flex-direction: column; gap: 12px; }
.goods-name { font-size: 27.2px; color: #000; font-family: var(--font-medium); overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.goods-sub { font-size: 23.6px; color: var(--qbox-gray-text); font-family: var(--font-regular); }
.goods-amount { flex-shrink: 0; margin-left: 17.7px; font-size: 28px; color: #000; font-family: var(--font-medium); }
.info-row { display: flex; align-items: center; justify-content: space-between; min-height: 78px; border-bottom: 1px solid var(--qbox-divider); }
.info-row:last-child { border-bottom: none; }
.info-row .k { display: flex; align-items: center; gap: 8px; font-size: 25.4px; color: var(--qbox-gray-text); font-family: var(--font-regular); flex-shrink: 0; }
.row-icon { width: 16px; height: 16px; }
.info-row .v { flex: 1; min-width: 0; text-align: right; font-size: 25.4px; color: #000; font-family: var(--font-regular); word-break: break-all; }
.info-row .v.strong { font-size: 28px; font-family: var(--font-medium); }
.info-row .v.mono { font-family: 'JetBrains Mono', 'SFMono-Regular', Menlo, monospace; }
.icon-btn { flex-shrink: 0; margin-left: 8px; width: 44px; height: 44px; display: flex; align-items: center; justify-content: center; border: none; background: transparent; color: var(--qbox-gray-text); cursor: pointer; }
.icon-btn svg { width: 16px; height: 16px; }
/* 底部固定操作条：按钮 678×88 radius 44 */
.bottom-bar { position: fixed; left: 0; right: 0; bottom: 0; background: var(--qbox-page-bg); padding: 16px 36px calc(16px + env(safe-area-inset-bottom)); box-sizing: border-box; }
.primary-btn, .secondary-btn { width: 678px; height: 88px; border-radius: 44px; border: none; font-size: 29px; font-family: var(--font-medium); cursor: pointer; transition: opacity 0.15s ease; }
.primary-btn { background: var(--od-brand); color: #fff; }
.secondary-btn { background: #fff; color: var(--od-brand); border: 1px solid var(--od-brand); }
.primary-btn:active, .secondary-btn:active { opacity: 0.85; }
/* 加载态 */
.loading-mask { position: fixed; inset: 0; background: transparent; display: flex; align-items: center; justify-content: center; }
.loading-wrap { display: flex; flex-direction: column; align-items: center; }
.loading-icon { display: flex; align-items: center; justify-content: center; }
.spinner { display: block; width: 19px; height: 19px; border-radius: 50%; border: 2px solid var(--qbox-input-bg); border-top-color: var(--od-loading-fg); animation: od-spin 1.2s ease-in-out infinite; }
@keyframes od-spin { to { transform: rotate(360deg); } }
.loading-text { font-size: 12px; color: var(--od-loading-fg); font-family: var(--font-regular); }
</style>
