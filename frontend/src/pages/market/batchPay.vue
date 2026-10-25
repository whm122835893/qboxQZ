<template>
  <!-- 批量支付（源站 pages/market/batchPay）：
       源站直接访问（无订单参数）渲染纯白空白页 —— 此处如实复刻为空壳 -->
  <div v-if="!hasOrderParam" class="bp-page"></div>

  <div v-else class="bp-page bp-page--filled">
    <!-- 导航栏 -->
    <div class="navbar">
      <img class="navbar-back" src="/static/cw/back_btn.png" alt="返回" @click="goBack" />
      <span class="nav-title">批量支付</span>
    </div>

    <!-- 订单清单 -->
    <div class="body">
      <div v-if="list.length" class="pay-list">
        <div v-for="o in list" :key="o.id" class="pay-item">
          <div class="pi-cover-wrap">
            <img
              v-if="o.cover"
              class="pi-cover"
              :src="o.cover"
              :alt="o.goods_name"
              @error="onImgError"
            />
          </div>
          <div class="pi-info">
            <span class="pi-name">{{ o.goods_name }}</span>
            <span class="pi-no">编号：{{ o.order_no }}</span>
          </div>
          <span class="pi-amount">¥{{ o.amount }}</span>
        </div>
      </div>

      <div v-else class="list-empty">
        <svg class="empty-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
          <path d="M3 7h18v12H3z" />
          <path d="M3 11h18" />
          <path d="M8 15h4" />
        </svg>
        <span class="empty-text">暂无可批量支付的订单</span>
      </div>
    </div>

    <!-- 底部固定条：合计金额 + 立即支付（280×88 radius 44） -->
    <div class="bottom-bar">
      <div class="bottom-inner">
        <div class="total">
          <span class="total-label">合计金额</span>
          <span class="total-num">¥{{ totalAmount }}</span>
        </div>
        <button class="pay-btn" type="button" :disabled="!list.length" @click="onPay">立即支付</button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { request } from '../../api/request'
import { toast } from '../../utils/toast'

/**
 * 批量支付页（我方自定义路径，源站直接访问实测为纯白空白页 → 无订单参数时保留空壳行为）
 *  数据源：POST /api/order/order/orderList { page: 1, status: 1, keywords: '' }
 *          status 1 = 批量付 tab，订单取自返回的 data.data
 *  支付：POST /api/market/Prepayment/createOrder，随后用项目 toast 提示接口真实 msg
 */
const route = useRoute()
const router = useRouter()

const list = ref([])

/** 订单参数（id / ids / order_ids 任一存在即视为带参进入） */
const hasOrderParam = computed(() => {
  const q = route.query || {}
  return Boolean(q.id || q.ids || q.order_ids || q.order_id)
})

const totalAmount = computed(() => {
  const sum = list.value.reduce((acc, o) => acc + Number(o.amount || 0), 0)
  return sum.toFixed(2)
})

function normalize(o) {
  const amount = Number(o.price ?? o.total_price ?? o.amount)
  return {
    id: o.id ?? o.order_no,
    order_no: o.order_no || o.order_sn || o.id || '',
    goods_name: o.goods_name || o.name || '商品',
    cover: o.cover || o.listimg || '',
    amount: Number.isFinite(amount) ? amount.toFixed(2) : '0.00'
  }
}

async function loadList() {
  const res = await request('/api/order/order/orderList', {
    page: 1,
    status: 1,
    keywords: ''
  })
  const rows = res && res.code === 1 && Array.isArray(res.data?.data) ? res.data.data : []
  list.value = rows.map(normalize)
}

async function onPay() {
  const ids = list.value.map(o => o.id).filter(v => v !== undefined && v !== null)
  const res = await request('/api/market/Prepayment/createOrder', {
    order_ids: ids,
    order_id: ids[0],
    pay_type: route.query.pay_type || ''
  })
  toast(res && res.msg ? res.msg : '支付通道暂未接入')
}

function goBack() {
  history.length > 1 ? router.back() : router.replace('/pages/order/list')
}
/** 封面图加载失败时隐藏 img，露出占位底色 */
function onImgError(e) {
  e.target.style.visibility = 'hidden'
}

onMounted(() => {
  if (hasOrderParam.value) loadList()
})
</script>

<style scoped>
.bp-page {
  min-height: 100vh;
  background: #fff;
}
.bp-page--filled {
  background: var(--qbox-page-bg);
  padding-bottom: 220px;
  box-sizing: border-box;
  /* 本页局部色板（其余颜色一律继承全局 token） */
  --bp-brand: #0018ff;
}
.navbar {
  position: relative;
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
  font-weight: 700;
  color: #000;
  font-family: var(--font-medium);
}
.body {
  padding: 0 17.7px;
}
.pay-list {
  padding-top: 17.7px;
}
.pay-item {
  background: #fff;
  border-radius: 11px;
  padding: 22.6px;
  margin-bottom: 17.7px;
  display: flex;
  align-items: center;
}
.pi-cover-wrap {
  position: relative;
  width: 96px;
  height: 96px;
  border-radius: 8px;
  overflow: hidden;
  flex-shrink: 0;
  background: var(--qbox-input-bg);
}
.pi-cover {
  width: 96px;
  height: 96px;
  object-fit: cover;
  display: block;
}
.pi-info {
  flex: 1;
  min-width: 0;
  margin-left: 17.7px;
  display: flex;
  flex-direction: column;
  gap: 12px;
}
.pi-name {
  font-size: 27.2px;
  color: #000;
  font-family: var(--font-medium);
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}
.pi-no {
  font-size: 23.6px;
  color: var(--qbox-gray-text);
  font-family: var(--font-regular);
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}
.pi-amount {
  flex-shrink: 0;
  margin-left: 17.7px;
  font-size: 28px;
  color: #000;
  font-family: var(--font-medium);
}
.list-empty {
  min-height: 400px;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 17.7px;
}
.empty-icon {
  width: 24px;
  height: 24px;
  color: var(--qbox-placeholder);
}
.empty-text {
  font-size: 25.4px;
  color: var(--qbox-gray-text);
  font-family: var(--font-regular);
}
/* 底部固定条 */
.bottom-bar {
  position: fixed;
  left: 0;
  right: 0;
  bottom: 0;
  background: var(--qbox-page-bg);
  padding: 16px 36px calc(16px + env(safe-area-inset-bottom));
  box-sizing: border-box;
}
.bottom-inner {
  display: flex;
  align-items: center;
  justify-content: space-between;
}
.total {
  display: flex;
  flex-direction: column;
  gap: 8px;
  min-width: 0;
}
.total-label {
  font-size: 23.6px;
  color: var(--qbox-gray-text);
  font-family: var(--font-regular);
}
.total-num {
  font-size: 39.9px;
  color: #000;
  font-family: var(--font-medium);
}
.pay-btn {
  width: 280px;
  height: 88px;
  border-radius: 44px;
  border: none;
  background: var(--bp-brand);
  color: #fff;
  font-size: 29px;
  font-family: var(--font-medium);
  flex-shrink: 0;
  cursor: pointer;
  transition: opacity 0.15s ease;
}
.pay-btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}
</style>
