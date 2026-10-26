<template>
  <!--
    订单详情（源站 pages/order/defaultDetail）
    源站实测（2026-10-25 23:5x 登录态，750 视口 / 375px 截图测量）：
    - 导航标题 = 订单状态文案（订单已取消 / 订单空投转入成功 …），非「订单详情」
    - 卡1 藏品卡 [36,88,678,208]r 白底 r22 pad24：封面 160×160r + 名称 28r #111 Bold +
      「当前藏品最高价：¥ 0」「当前藏品求购价：¥ 0」22r #8c8c8c
    - 卡2 金额卡 [36,320,678,280]r pad 8/28：藏品金额 / 应付金额 / 支付钱包 三行，
      label 28r #111 Bold，value 右对齐（¥ 0.00 / 汇付支付 / --）
    - 卡3 购买订单卡 [36,624,678,302]r pad28：标题「购买订单」28r Bold +
      订单编号（26r #8c8c8c label + 右侧值 26r #111 截断 + 复制图标 28×28r）/
      支付方式 / 支付时间
    - 底部按钮：取消订单 260×92r 白底蓝字蓝框 r18 + 继续购买 400×92r 蓝底白字 r18
      （实测：已取消单只显示「继续购买」；未取消单显示两枚按钮）
    - 接口：orderType=3 → POST /api/order/order/orderDetail/ids/{id}
            orderType=2 → POST /api/order/order/MarketOrderDetail/ids/{id}
  -->
  <div class="od-page">
    <!-- 导航栏（标题 = 状态文案，与源站一致） -->
    <div v-if="order" class="navbar">
      <img class="navbar-back" src="/static/cw/back_btn.png" alt="返回" @click="goBack" />
      <span class="nav-title">{{ order.statusText }}</span>
    </div>

    <div v-if="order" class="body">
      <!-- 卡1：藏品卡 -->
      <section class="card card-goods">
        <img v-if="order.cover" class="goods-cover" :src="order.cover" :alt="order.name" @error="onImgError" />
        <div class="goods-info">
          <span class="goods-name">{{ order.name }}</span>
          <span class="goods-sub">当前藏品最高价：¥ {{ order.maxPrice }}</span>
          <span class="goods-sub">当前藏品求购价：¥ {{ order.buyPrice }}</span>
        </div>
      </section>

      <!-- 卡2：金额卡 -->
      <section class="card card-amount">
        <div class="amt-row">
          <span class="amt-label">藏品金额</span>
          <span class="amt-value">¥ {{ order.amount }}</span>
        </div>
        <div class="amt-row">
          <span class="amt-label">应付金额</span>
          <span class="amt-value">¥ {{ order.amount }}</span>
        </div>
        <div class="amt-row">
          <span class="amt-label">支付钱包</span>
          <span class="amt-value">{{ order.walletText }}</span>
        </div>
      </section>

      <!-- 卡3：购买订单卡 -->
      <section class="card card-order">
        <h2 class="card-title">购买订单</h2>
        <div class="info-row">
          <span class="info-label">订单编号</span>
          <span class="info-value">{{ order.orderNo }}</span>
          <svg class="copy-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" @click="copyOrderNo">
            <rect x="9" y="9" width="12" height="12" rx="2" />
            <path d="M5 15V5a2 2 0 0 1 2-2h10" />
          </svg>
        </div>
        <div class="info-row">
          <span class="info-label">支付方式</span>
          <span class="info-value">{{ order.payType }}</span>
        </div>
        <div class="info-row">
          <span class="info-label">支付时间</span>
          <span class="info-value">{{ order.payTime }}</span>
        </div>
      </section>
    </div>

    <!-- 加载态（数据未就绪时居中「加载中...」，与源站一致） -->
    <div v-else class="loading-mask">
      <div class="loading-wrap">
        <div class="loading-icon"><span class="spinner"></span></div>
        <span class="loading-text">加载中...</span>
      </div>
    </div>

    <!-- 底部操作条：未取消单显示「取消订单 + 继续购买」，已取消单只显示「继续购买」
         （源站实测：9538 已取消单仅「继续购买」；8783 转入成功单两枚均显示） -->
    <div v-if="order" class="bottom-bar">
      <div v-if="order.status !== 3" class="btn btn-cancel" @click="onCancel">取消订单</div>
      <div class="btn btn-continue" @click="onContinue">继续购买</div>
    </div>
  </div>
</template>

<script setup>
/**
 * 订单详情（defaultDetail）—— 源站实测 2026-10-25 23:5x：
 *  入口：我的订单列表点击订单卡 → /pages/order/defaultDetail?id={订单id}&orderType={order_type}
 *        （实测：order_type=2 市场单与 order_type=3 商城单均进入本页，由 orderType 区分接口）
 *  接口：orderType=3 → POST /api/order/order/orderDetail/ids/{id}
 *        orderType=2 → POST /api/order/order/MarketOrderDetail/ids/{id}
 *  实测布局（8783 orderType=3 / 9538 orderType=2）：
 *    导航标题 = 状态文案（订单空投转入成功 / 订单已取消）
 *    状态文案映射（实测样本）：
 *      order_status=3 → 「订单已取消」（9538）
 *      order_status=2 + uc_ids（空投单）→ 「订单空投转入成功」（8783）
 *      其余按语义推导（待支付/普通转入成功），已在 API 文档标注
 *    支付时间：pay_time 为 null 时取 createtime 展示（两样本一致）
 *    支付方式/支付钱包：pay_way=huifu → 「汇付支付」，空 → 「--」
 *    当前藏品最高价/求购价：两样本均显示 ¥ 0（接口未下发该字段，按 0 展示）
 *  无有效 id / code=0 → 页面停留「加载中...」+ 接口真实 msg toast
 */
import { ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { request } from '../../api/request'
import { toast } from '../../utils/toast'

const route = useRoute()
const router = useRouter()
const order = ref(null)

/** 源站订单时间为 Unix 时间戳，格式化 YYYY-MM-DD HH:mm:ss */
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

/** 支付方式文案：pay_way=huifu → 「汇付支付」，其余 → 「--」（源站实测） */
function payWayText(d) {
  const raw = String(d.pay_way || d.pay_type_name || '').toLowerCase()
  return raw === 'huifu' ? '汇付支付' : '--'
}

/** 状态文案（映射规则见文件头注释） */
function statusTextOf(d) {
  const st = Number(d.order_status)
  if (st === 3) return '订单已取消'
  if (st === 2) return d.uc_ids ? '订单空投转入成功' : '订单转入成功'
  return '订单待支付'
}

function mapOrder(d) {
  const st = Number(d.order_status)
  return {
    id: d.id,
    orderNo: d.order_sn || String(d.id || ''),
    name: d.goods_name || '',
    cover: d.goods_cover_image || '',
    amount: (Number(d.total_price) || 0).toFixed(2),
    maxPrice: d.product ? (Number(d.product.price) || 0).toFixed(0) : '0',
    buyPrice: '0',
    walletText: payWayText(d),
    payType: payWayText(d),
    payTime: formatTime(d.pay_time || d.createtime),
    status: st,
    statusText: statusTextOf(d),
    goodsId: d.goods_id,
    goodsType: d.goods_type
  }
}

async function loadOrder() {
  const id = route.query.id
  const orderType = String(route.query.orderType || '3')
  const path = orderType === '2'
    ? '/api/order/order/MarketOrderDetail/ids/' + (id || 'undefined')
    : '/api/order/order/orderDetail/ids/' + (id || 'undefined')
  const res = await request(path, {})
  if (!res || res.code !== 1 || !res.data) {
    toast(res && res.msg ? res.msg : '找不到该订单或已被删除')
    return
  }
  order.value = mapOrder(res.data)
}

/** 取消订单（源站有该按钮；接口未抓包，按语义调用本地 cancelOrder，已在 API 文档标注推断） */
async function onCancel() {
  const res = await request('/api/order/order/cancelOrder', { order_id: order.value.id })
  if (!res || res.code !== 1) {
    toast(res && res.msg ? res.msg : '取消失败')
    return
  }
  toast('订单已取消')
  loadOrder()
}

/** 继续购买 → 回到该藏品的挂单列表（源站语义：继续购买该藏品） */
function onContinue() {
  if (order.value.goodsId) {
    router.replace('/pages/market/marketGoodsList?id=' + order.value.goodsId + '&goodsType=' + (order.value.goodsType || 2))
  } else {
    router.replace('/pages/market/market')
  }
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

<style scoped src="../../styles/pages/order-defaultDetail.css"></style>
