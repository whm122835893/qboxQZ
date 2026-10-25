<template>
  <!--
    确认订单页（源站 pages/order/orderSubmit）
    源站实测 2026-10-25 12:05（无订单参数直接访问）：
      导航：uni 原生 page-head（高 44px），标题「确认订单」34px fw700 居中（容器宽 400）
      区块链地址: label 28px #999 @[48,172,148,40]；值 28px #000 @[196,176,478,32]
      订单金额:   label 28px #999 @[48,247,120,40]；值 32px fw500 #000 右对齐 @[610,244,92,46]
      支付方式卡：徽标「汇」22px #fff 圆底 @[69,374,22,33]；名称「汇付」26px #111 @[120,371,338,39]；
                  「未支持该支付通道收款」22px #8c8c8c @[474,374,220,33]
      底部条 [0,1214,750,120]：合计「￥」24px fw500 @[32,1267,24,26] + 金额 40px fw500 @[56,1252]；
                  「立即支付」480×92 @[238,1228]，30px fw600 #fff
      无订单参数时源站金额显示 NaN（源站自身 bug，如实复刻）
  -->
  <div class="os-page">
    <div class="navbar">
      <img class="navbar-back" src="/static/cw/back_btn.png" alt="返回" @click="goBack" />
      <span class="nav-title">确认订单</span>
    </div>

    <div class="body">
      <!-- 订单信息卡（商品信息为空时源站不渲染卡片） -->
      <div v-if="product.name" class="goods-card">
        <img v-if="product.cover" class="goods-img" :src="product.cover" alt="" />
        <span class="goods-name">{{ product.name }}</span>
      </div>

      <!-- 区块链地址 -->
      <div class="info-row">
        <span class="k">区块链地址:</span>
        <span class="v addr">{{ hideUrl(userinfo?.ntf_url || '') }}</span>
        <svg class="copy-icon" viewBox="0 0 24 24" fill="none" stroke="#333" stroke-width="2">
          <rect x="9" y="9" width="12" height="12" rx="2" />
          <path d="M5 15V5a2 2 0 0 1 2-2h10" />
        </svg>
      </div>

      <!-- 订单金额 -->
      <div class="info-row">
        <span class="k">订单金额:</span>
        <span class="v amount">￥{{ totalPrice }}</span>
      </div>

      <!-- 支付方式（源站实测：汇付通道未开通 → 显示「未支持该支付通道收款」） -->
      <div class="pay-card">
        <div class="pay-row">
          <span class="pay-badge">汇</span>
          <span class="pay-name">汇付</span>
          <span class="pay-unsupport">未支持该支付通道收款</span>
        </div>
      </div>
    </div>

    <!-- 底部：合计 + 立即支付 -->
    <div class="footer">
      <span class="total-symbol">￥</span>
      <span class="total-num">{{ totalPrice }}</span>
      <div class="pay-btn" :class="{ disabled: disable }" @click="onPay">立即支付</div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useUserStore } from '../../stores/user'
import { toast } from '../../utils/toast'

/**
 * 源站实测（2026-10-25 12:05 登录态，pages/order/orderSubmit）：
 *  - 确认订单支付页：区块链地址（hideUrl 打码 0x590****b783d + 复制）/ 订单金额 ￥totalPrice
 *    + PayType 支付方式（汇付通道 auth_key 未开通 → 「未支持该支付通道收款」）
 *    + 底部合计 ￥totalPrice + 立即支付 480×92
 *  - 直接访问无订单参数：源站金额显示 NaN（源站自身 bug，如实复刻）
 *  - 源站 market 链路（寄售详情「立即购买」）实际不走本页，而是弹底部订单确认弹窗后拉起汇付收银台；
 *    本页对应商城 / 盲盒等确认订单入口。
 */
const router = useRouter()
const userStore = useUserStore()
const product = ref({})
const userinfo = computed(() => userStore.userinfo)

const totalPrice = computed(() => {
  // 源站：totalPrice 依赖商品价格，无数据时为 NaN（源站即显示 ￥NaN，如实复刻）
  const price = Number(product.value.price)
  return Number.isFinite(price) ? price.toFixed(2) : 'NaN'
})

/** 源站支付通道未开通时按钮不可用 */
const disable = computed(() => true)

function hideUrl(a) {
  return a && a.length > 10 ? a.slice(0, 5) + '****' + a.slice(-5) : a || ''
}

function onPay() {
  // 源站：checkCaptcha → createOrder → pay() 拉起汇付收银台；
  // 独立项目未接支付通道，按钮按源站不可用态处理。
  if (disable.value) {
    toast('未支持该支付通道收款')
    return
  }
}

function goBack() {
  history.length > 1 ? router.back() : router.replace('/pages/my/my')
}

onMounted(() => {
  userStore.getUserInfo()
})
</script>

<style scoped>
.os-page {
  min-height: 100vh;
  background: #f4f6fa;
  padding-bottom: 240px;
  box-sizing: border-box;
}
/* 源站用 uni 原生 page-head（高 44px），此处按项目约定用固定导航承载标题 */
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
  font-size: 34px;
  font-weight: 700;
  color: #000;
  font-family: var(--font-medium);
}
/* 源站实测：body 左右留白 48px */
.body {
  padding: 0 48px;
}
/* 商品卡（源站 product 为空时不渲染） */
.goods-card {
  margin-top: 38px;
  background: #fff;
  border-radius: 8px;
  padding: 20px 24px;
  display: flex;
  align-items: center;
  gap: 20px;
}
.goods-img {
  width: 92px;
  height: 92px;
  border-radius: 8px;
  object-fit: cover;
  background: #f4f6fa;
}
.goods-name {
  font-size: 28px;
  color: #000;
  line-height: 38px;
}
/* 信息行：源站实测 行 1 @y172，行 2 @y247（行距 75px） */
.info-row {
  margin-top: 35px;
  display: flex;
  align-items: center;
  font-size: 28px;
  min-height: 40px;
}
.k {
  color: #999;
  flex-shrink: 0;
}
.v {
  color: #000;
  margin-left: 0;
  flex: 1;
  min-width: 0;
  line-height: 32px;
}
.addr {
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}
.copy-icon {
  width: 24px;
  height: 24px;
  margin-left: 12px;
  flex-shrink: 0;
}
/* 源站实测：订单金额 32px fw500 */
.amount {
  font-size: 32px;
  font-weight: 500;
  text-align: right;
  flex: none;
  line-height: 46px;
}
/* 支付方式卡：源站实测 徽标 @[69,374,22,33] 名称 @[120,371] 提示 @[474,374] */
.pay-card {
  margin-top: 72px;
  background: #fff;
  border-radius: 12px;
  padding: 22px 21px;
  box-sizing: border-box;
}
.pay-row {
  display: flex;
  align-items: center;
}
.pay-badge {
  width: 33px;
  height: 33px;
  border-radius: 50%;
  background: #111;
  color: #fff;
  font-size: 22px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}
.pay-name {
  margin-left: 28px;
  font-size: 26px;
  color: #111;
  line-height: 39px;
}
.pay-unsupport {
  margin-left: auto;
  font-size: 22px;
  color: #8c8c8c;
  line-height: 33px;
}
/* 底部条：源站实测 [0,1214,750,120]；立即支付 480×92 @[238,1228] */
.footer {
  position: fixed;
  left: 0;
  right: 0;
  bottom: 0;
  height: 120px;
  background: #f4f6fa;
  display: flex;
  align-items: center;
  padding: 0 32px;
  box-sizing: border-box;
  z-index: 10;
}
.total-symbol {
  font-size: 24px;
  font-weight: 500;
  color: #000;
  align-self: center;
}
.total-num {
  font-size: 40px;
  font-weight: 500;
  color: #000;
  align-self: center;
  margin-left: 0;
}
.pay-btn {
  margin-left: auto;
  width: 480px;
  height: 92px;
  border-radius: 46px;
  background: #ff7800;
  color: #fff;
  font-size: 30px;
  font-weight: 600;
  display: flex;
  align-items: center;
  justify-content: center;
}
.pay-btn.disabled {
  background: #cacdd3;
}
</style>
