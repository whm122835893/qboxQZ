<template>
  <div class="os-page">
    <!-- 导航栏 -->
    <div class="navbar">
      <img class="navbar-back" src="/static/cw/back_btn.png" alt="返回" @click="$router.back()" />
      <span class="nav-title">确认订单</span>
    </div>

    <div class="body">
      <!-- 商品卡（product 无数据 → 高度塌缩为浅灰条，原站实测一致） -->
      <div class="goods-card">
        <div v-if="product.listimg" class="goods-img">{{ product.listimg }}</div>
        <div class="goods-info">
          <span class="goods-name">{{ product.name || '' }}</span>
        </div>
      </div>

      <!-- 信息卡 -->
      <div class="info-card">
        <div v-if="product.publisher?.user?.nickname" class="info-row">
          <span class="k">创作者:</span><span class="v">{{ product.publisher.user.nickname }}</span>
        </div>
        <div v-if="orderBaseInfo.order_type === 1" class="info-row">
          <span class="k">藏品编号:</span><span class="v">随机编号</span>
        </div>
        <div v-if="userinfo?.ntf_url" class="info-row">
          <span class="k">区块链地址:</span>
          <span class="v addr">{{ hideUrl(userinfo.ntf_url) }}</span>
          <svg class="copy-icon" viewBox="0 0 24 24" fill="none" stroke="#333" stroke-width="2"><rect x="9" y="9" width="12" height="12" rx="2"/><path d="M5 15V5a2 2 0 0 1 2-2h10"/></svg>
        </div>
        <div class="info-row">
          <span class="k">订单金额:</span>
          <span class="v amount">￥{{ totalPrice }}</span>
        </div>
      </div>

      <!-- 支付方式卡（PayType） -->
      <div class="pay-card">
        <div v-for="p in payList" :key="p.key" class="pay-row">
          <div class="pay-badge"><span class="pay-badge-text">{{ (p.aname || '').slice(0, 1) }}</span></div>
          <span class="pay-name"> {{ p.name }} </span>
          <span class="pay-unsupport"> 未支持该支付通道收款 </span>
        </div>
      </div>
    </div>

    <!-- 底部（fixed：合计 + 立即支付 480rpx #ff7800） -->
    <div class="footer">
      <div class="footer-inner">
        <span class="total"><span class="total-symbol">￥</span><span class="total-num">{{ totalPrice }}</span></span>
        <div class="pay-btn pay-btn-text" @click="onPay"> 立即支付 </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { request } from '../../api/request'
import { useUserStore } from '../../stores/user'
import { toast } from '../../utils/toast'

/**
 * 原站 chunk 反解（pages-order-orderSubmit）：
 *  - 确认订单支付页：商品卡（order_type==1 时带数量步进器）+ 信息卡（创作者/藏品编号:随机编号/
 *    区块链地址 hideUrl 打码 0x590****b783d + 复制 / 订单金额 ￥totalPrice）
 *    + PayType 支付方式（radio；有 auth_key 不可用 → 「未支持该支付通道收款」）
 *    + 底部 ￥totalPrice + 立即支付 480rpx circle #ff7800（disabled 不可点）
 *  - 直接访问无订单参数：product 空、totalPrice=NaN（原站即显示 ￥NaN，bug 原样复刻，实测）
 *  - createOrder/createDefaultOrder/createMarketOrder → pay() 拉起支付（未抓包，标注推断）
 */
const userStore = useUserStore()
const product = ref({})
const orderBaseInfo = ref({ order_type: 1, count: 1 })
const payInfo = ref({})
const userinfo = computed(() => userStore.userinfo)

const payList = ref([{ name: '汇付', aname: 'A', key: 'huifu_pay_status', auth_key: 'huifu_account', pay_way: 'huifu', able: false }])

const totalPrice = computed(() => {
  // 原站：totalPrice 依赖商品价格与数量，无数据时为 NaN（实测显示 ￥NaN）
  const price = Number(product.value.price)
  return Number.isFinite(price) ? (price * Number(orderBaseInfo.value.count || 1)).toFixed(2) : 'NaN'
})
const disable = computed(() => !payInfo.value.pay_way)

function hideUrl(a) {
  return a && a.length > 10 ? a.slice(0, 5) + '****' + a.slice(-5) : a
}

function onPay() {
  if (disable.value) return
  // 原站：checkCaptcha → createOrder → pay() 拉起支付；独立项目支付通道未接入（标注推断）
  toast('支付通道暂未接入')
}

async function init() {
  await userStore.getUserInfo()
}
init()
</script>

<style scoped>
.os-page {
  min-height: 100vh;
  background: #f4f6fa;
}
.navbar {
  position: relative;
  width: 100%;
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
.goods-card {
  margin-top: 38px;
  background: #f4f6fa;
  border-radius: 8px;
  padding: 8.8px;
  margin-bottom: 8.8px;
  display: flex;
  min-height: 20px;
}
.goods-img {
  width: 45.8px;
  height: 45.8px;
  border-radius: 4.4px;
  overflow: hidden;
  object-fit: cover;
}
.goods-info {
  flex: 1;
  padding: 0 8.8px;
  display: flex;
  flex-direction: column;
  justify-content: center;
}
.goods-name {
  font-size: 27.2px;
  color: #000;
  display: -webkit-box;
  -webkit-box-orient: vertical;
  -webkit-line-clamp: 2;
  overflow: hidden;
}
.info-card {
  border-radius: 8px;
  background: #f4f6fa;
  padding: 8.8px;
  margin-bottom: 4.4px;
}
.info-row {
  display: flex;
  align-items: center;
  justify-content: space-between;
  font-size: 27.2px;
  margin-bottom: 8.8px;
}
.info-row:last-child {
  margin-bottom: 0;
}
.k {
  color: #999;
}
.v {
  color: #000;
}
.addr {
  flex: 1;
  text-align: right;
}
.copy-icon {
  width: 12px;
  height: 12px;
  margin-left: 4px;
  flex-shrink: 0;
}
.amount {
  font-weight: 500;
  font-size: 30.8px;
  font-family: 'PingFang SC-Medium', 'PingFang SC', var(--font-medium);
}
/* ---- 支付方式 ---- */
.pay-card {
  margin-top: 56.7px;
  background: #f5f5f5;
  border-radius: 11px;
  overflow: hidden;
  display: flex;
  flex-direction: column;
}
.pay-row {
  padding: 27.2px 13.2px;
  display: flex;
  align-items: center;
  box-sizing: border-box;
}
.pay-badge {
  width: 26.5px;
  height: 26.5px;
  border-radius: 50%;
  background: #111;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}
.pay-badge-text {
  font-size: 25.4px;
  color: #fff;
  font-family: var(--font-bold);
}
.pay-name {
  flex: 1;
  min-width: 0;
  margin-left: 8.8px;
  font-size: 25.4px;
  color: #111;
}
.pay-unsupport {
  font-size: 21.7px;
  color: #8c8c8c;
  flex-shrink: 0;
}
/* ---- 底部 ---- */
.footer {
  position: fixed;
  left: 0;
  bottom: 0;
  width: 100%;
  background: #f4f6fa;
  z-index: 10;
}
.footer-inner {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 14.5px 17.7px calc(14.5px + env(safe-area-inset-bottom));
  box-sizing: border-box;
}
.total {
  color: #000;
}
.total-symbol {
  font-size: 23.6px;
}
.total-num {
  font-size: 39.9px;
}
.pay-btn {
  width: 264.9px;
  height: 90.6px;
  border-radius: 44px;
  background: #ff7800;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}
.pay-btn-text {
  font-size: 29px;
  color: #fff;
  font-family: var(--font-medium);
}
</style>
