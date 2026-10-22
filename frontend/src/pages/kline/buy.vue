<template>
  <div class="kb-page">
    <!-- 导航栏（透明底：购买 18px 700 白 + 右上 切换行情 13px #7a7a7a） -->
    <div class="navbar">
      <img class="navbar-back" src="/static/cw/back_back_white.png" alt="返回" @click="$router.back()" />
      <span class="nav-title">购买</span>
      <span class="nav-switch" @click="$router.replace('/pages/kline/info')">切换行情</span>
    </div>

    <!-- banner 图（Welcome to QBOX 机器人） -->
    <div class="banner"></div>

    <!-- 行情头部（凤吹晚霞 19px 600 白 / qbox 13px #7a7a7a / -0.53% 13px / 112.34CNY 22px） -->
    <div class="quote-head">
      <div class="quote-row">
        <span class="q-name">凤吹晚霞</span>
        <span class="q-code">qbox</span>
        <span class="q-change">-0.53%</span>
        <span class="q-price">112.34CNY</span>
      </div>
      <!-- tabs（流通 白选中 / 相关公告 / 成交额 #7a7a7a 16px） -->
      <div class="q-tabs">
        <span v-for="(t, i) in qTabs" :key="t" class="q-tab" :class="{ 'q-tab-active': current === i }" @click="current = i">{{ t }}</span>
      </div>
    </div>

    <!-- K 线区域（依赖行情数据，无数据时为黑色区域，实测） -->
    <div class="chart-area"></div>
  </div>
</template>

<script setup>
import { ref } from 'vue'

/**
 * 原站 DOM 实测（2026-10-23，pages/kline/buy）：
 *  - 黑底页；标题「购买」18px 700 #fff；右上「切换行情」13px #7a7a7a；
 *  - banner：img.xiyanwl.asia/uploads/20261012/be317f845530f581d20c38c14a41b8f2.jpg（已下载本地）；
 *  - 行情行：名称 19px 600 #fff / qbox 13px #7a7a7a / -0.53% 13px #333 / 112.34CNY 22px #333；
 *  - tabs：流通(选中 白)/相关公告/成交额 16px；K 线区依赖行情数据（无数据黑区，实测）
 */
const qTabs = ['流通', '相关公告', '成交额']
const current = ref(0)
</script>

<style scoped>
.kb-page {
  min-height: 100vh;
  background: #0b0d12;
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
  color: #7a7a7a;
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
  font-size: 34.4px;
  font-weight: 600;
  color: #fff;
}
.q-code {
  margin-left: 12px;
  font-size: 23.6px;
  color: #7a7a7a;
}
.q-change {
  margin-left: 12px;
  font-size: 23.6px;
  color: #2eaf63;
}
.q-price {
  margin-left: auto;
  font-size: 39.9px;
  color: #333;
}
.q-tabs {
  margin-top: 32px;
  display: flex;
  gap: 43px;
}
.q-tab {
  font-size: 29px;
  color: #7a7a7a;
}
.q-tab-active {
  color: #fff;
  font-weight: 600;
}
.chart-area {
  margin-top: 25px;
  width: 100%;
  height: 700px;
  background: #0b0d12;
}
</style>
