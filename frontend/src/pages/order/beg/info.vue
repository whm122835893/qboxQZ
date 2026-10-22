<template>
  <div class="bi-page">
    <!-- 导航栏（17px Bold #111 渲染） -->
    <div class="navbar">
      <img class="navbar-back" src="/static/cw/back_btn.png" alt="返回" @click="$router.back()" />
      <span class="nav-title">{{ isEntrust ? '委托订单详情' : '求购订单详情' }}</span>
    </div>

    <div class="body">
      <!-- 商品卡（黑图 + 两行价格说明 12px #8c8c8c y110/133） -->
      <div class="goods-card">
        <div class="goods-img"></div>
        <div class="goods-info">
          <span class="goods-line">当前藏品最高价：¥ 0</span>
          <span class="goods-line">当前藏品求购价：¥ 0</span>
        </div>
      </div>

      <!-- 字段组一（求购单价/应付金额/支付钱包） -->
      <div class="field-card">
        <div class="field-row">
          <span class="field-label">{{ isEntrust ? '委托单价' : '求购单价' }}</span>
          <span class="field-price">¥ 0</span>
        </div>
        <div class="field-row">
          <span class="field-label">应付金额</span>
          <span class="field-price">¥ 0</span>
        </div>
        <div class="field-row">
          <span class="field-label">支付钱包</span>
          <span class="field-value">--</span>
        </div>
      </div>

      <!-- 字段组二（求购/委托订单） -->
      <span class="group-title">{{ isEntrust ? '委托订单' : '求购订单' }}</span>
      <div class="field-card">
        <div class="field-row">
          <span class="field-label">订单编号</span>
          <span class="field-value">--</span>
        </div>
        <div class="field-row">
          <span class="field-label">支付方式</span>
          <span class="field-value">--</span>
        </div>
        <div class="field-row">
          <span class="field-label">支付时间</span>
          <span class="field-value">Invalid Date</span>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { useRoute } from 'vue-router'
import { request } from '../../../api/request'

/**
 * 原站 DOM 实测（2026-10-23，pages/order/beg/info 与 entrust/info 同构）：
 *  - 标题 17px Bold #111；商品卡黑图 + 说明 12px #8c8c8c（y110/133）；
 *  - 字段：label 15px Bold #111（求购单价/应付金额 15px Bold），价格 15px AlimamaShuHeiTi；
 *    值 14px #111 / -- ；订单编号等 14px #8c8c8c；「支付时间 Invalid Date」为原站
 *    无数据时的原样输出（bug 级细节，如实复刻）
 */
const route = useRoute()
const isEntrust = computed(() => route.path.includes('entrust'))

request(isEntrust.value ? '/api/order/order/entrustOrderInfo' : '/api/order/order/begOrderInfo', {})
</script>

<style scoped>
.bi-page {
  min-height: 100vh;
  background: #f7f7f7;
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
  font-size: 30.8px;
  font-weight: 700;
  color: #111;
}
.body {
  padding: 0 17.7px;
}
.goods-card {
  margin-top: 12px;
  background: #fff;
  border-radius: 16px;
  padding: 20px;
  display: flex;
  align-items: center;
}
.goods-img {
  width: 158px;
  height: 158px;
  border-radius: 12px;
  background: #0e0f12;
  flex-shrink: 0;
}
.goods-info {
  flex: 1;
  margin-left: 22px;
  display: flex;
  flex-direction: column;
  gap: 20px;
}
.goods-line {
  font-size: 21.7px;
  color: #8c8c8c;
}
.field-card {
  margin-top: 22px;
  background: #fff;
  border-radius: 16px;
  padding: 6px 22px;
}
.field-row {
  padding: 30px 0;
  display: flex;
  align-items: center;
  justify-content: space-between;
  border-bottom: 1px solid #f5f5f5;
}
.field-row:last-child {
  border-bottom: none;
}
.field-label {
  font-size: 27.2px;
  font-weight: 700;
  color: #111;
}
.field-price {
  font-size: 27.2px;
  font-family: 'AlimamaShuHeiTi-Bold', var(--font-medium);
  color: #111;
}
.field-value {
  font-size: 25.4px;
  color: #111;
}
.group-title {
  display: block;
  margin: 40px 4px 0;
  font-size: 27.2px;
  font-weight: 700;
  color: #111;
}
.field-card + .group-title {
  margin-top: 40px;
}
</style>
