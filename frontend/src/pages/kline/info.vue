<template>
  <div class="ki-page">
    <!-- 导航栏（详情 18px 700 白 + 右上 切换购买 13px 白） -->
    <div class="navbar">
      <img class="navbar-back" src="/static/cw/back_back_white.png" alt="返回" @click="$router.back()" />
      <span class="nav-title">详情</span>
      <span class="nav-switch" @click="$router.replace('/pages/kline/buy')">切换购买</span>
    </div>

    <!-- banner 图 -->
    <div class="banner"></div>

    <!-- 行情头部（藏品名称 17px 白 / 比太 13px #7a7a7a / 112.34CNY 17px / -0.53% 13px） -->
    <div class="quote-head">
      <div class="quote-row">
        <span class="q-name">藏品名称</span>
        <span class="q-code">比太</span>
        <span class="q-price">112.34CNY</span>
        <span class="q-change">-0.53%</span>
      </div>

      <!-- 数据网格（label 13px #7a7a7a + 值 13px #fff，两列） -->
      <div class="data-grid">
        <div v-for="g in grid" :key="g[0]" class="data-row">
          <span class="d-label">{{ g[0] }}</span>
          <span class="d-value">30000</span>
        </div>
      </div>
    </div>

    <!-- tabs（行情 白选中 / 历史委托 / 历史成交 / holder榜 13px） -->
    <div class="k-tabs">
      <span v-for="(t, i) in kTabs" :key="t" class="k-tab" :class="{ 'k-tab-active': current === i }" @click="current = i">{{ t }}</span>
    </div>

    <!-- K 线区域 -->
    <div class="chart-area"></div>

    <!-- 底部操作条（开启行情提醒 粉 / 批量买卖 橙 / 关注图标 24 + 关注 11px） -->
    <div class="bottom-bar">
      <div class="bb-btn bb-btn-pink">开启行情提醒</div>
      <div class="bb-btn bb-btn-orange">批量买卖</div>
      <div class="fav-col">
        <img class="fav-icon" src="/static/qbox/detail-unfavorited.png" alt="" />
        <span class="fav-text">关注</span>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'

/**
 * 原站 DOM 实测（2026-10-23，pages/kline/info）：
 *  - 标题「详情」18px 700 #fff；右上「切换购买」13px #fff；
 *  - 行情行：藏品名称 17px #fff / 比太 13px #7a7a7a / 112.34CNY 17px / -0.53% 13px；
 *  - 数据网格（label #7a7a7a + 值 #fff 13px，两列）：流通/最近5min成交量/24h最高/
 *    最近1h成交量/24h最低/今日成交量；tabs 13px；底部双按钮 158×41 渲染（粉/橙）
 *    + 关注图标 24px + 关注 11px 白字
 */
const grid = [['流通：', 30000], ['最近5min成交量：', 30000], ['24h最高：', 30000], ['最近1h成交量：', 30000], ['24h最低：', 30000], ['今日成交量：', 30000]]
const kTabs = ['行情', '历史委托', '历史成交', 'holder榜']
const current = ref(0)
</script>

<style scoped>
.ki-page {
  min-height: 100vh;
  background: #0b0d12;
  padding-bottom: 140px;
  box-sizing: border-box;
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
  color: #fff;
}
.nav-switch {
  position: absolute;
  right: 23.6px;
  top: 50%;
  transform: translateY(-50%);
  font-size: 23.6px;
  color: #fff;
}
.banner {
  margin: 10px 17.7px 0;
  height: 165px;
  border-radius: 16px;
  background: url('/static/banner/kline-banner.jpg') center / cover no-repeat;
}
.quote-head {
  padding: 0 17.7px;
}
.quote-row {
  margin-top: 32px;
  display: flex;
  align-items: baseline;
}
.q-name {
  font-size: 30.8px;
  color: #fff;
}
.q-code {
  margin-left: 12px;
  font-size: 23.6px;
  color: #7a7a7a;
}
.q-price {
  margin-left: 21px;
  font-size: 30.8px;
  color: #333;
}
.q-change {
  margin-left: 12px;
  font-size: 23.6px;
  color: #2eaf63;
}
.data-grid {
  margin-top: 32px;
  display: flex;
  flex-wrap: wrap;
}
.data-row {
  width: 50%;
  padding: 5px 0;
  display: flex;
  align-items: baseline;
}
.d-label {
  font-size: 23.6px;
  color: #7a7a7a;
}
.d-value {
  margin-left: 8px;
  font-size: 23.6px;
  color: #fff;
}
.k-tabs {
  margin-top: 40px;
  display: flex;
  gap: 36px;
  padding: 0 17.7px;
}
.k-tab {
  font-size: 23.6px;
  color: #7a7a7a;
}
.k-tab-active {
  color: #fff;
  font-weight: 600;
}
.chart-area {
  margin: 25px 17.7px 0;
  height: 600px;
  border-radius: 12px;
  background: #0b0d12;
}
/* ---- 底部操作条（按钮 158×41 渲染） ---- */
.bottom-bar {
  position: fixed;
  left: 0;
  bottom: 0;
  width: 100%;
  padding: 16px 24px calc(16px + env(safe-area-inset-bottom));
  box-sizing: border-box;
  background: #0b0d12;
  display: flex;
  align-items: center;
}
.bb-btn {
  width: 286px;
  height: 74px;
  border-radius: 37px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 29px;
  color: #fff;
}
.bb-btn-pink {
  background: #f6739c;
  margin-right: 18px;
}
.bb-btn-orange {
  background: #ff7800;
}
.fav-col {
  margin-left: auto;
  display: flex;
  flex-direction: column;
  align-items: center;
}
.fav-icon {
  width: 43.5px;
  height: 43.5px;
  object-fit: contain;
}
.fav-text {
  margin-top: 4px;
  font-size: 19.9px;
  color: #fff;
}
</style>
