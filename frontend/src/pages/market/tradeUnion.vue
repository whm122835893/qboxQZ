<template>
  <div class="tu-page">
    <!-- 导航栏（u-navbar 标准 44px：fixed + placeholder，标题 18px 500 #000 黑色——原站如此） -->
    <div class="navbar">
      <img class="navbar-back" src="/static/cw/back_btn.png" alt="返回" @click="$router.back()" />
      <span class="nav-title">GM工会</span>
    </div>

    <div class="body">
      <!-- 锁图标 60×60 渲染 -->
      <img class="lock-icon" src="/static/icon/gh/lock.png" alt="" />
      <span class="lock-title"> 您的公会未解锁 </span>
      <span class="lock-sub"> 赶快购买公会藏品解锁您的公会吧~ </span>

      <!-- Tabs（个人权益/我的工会/申请入会，三等分；字 17px；active #E3A86A bold；线 20×3 bottom 20rpx） -->
      <div class="tabbar">
        <div
          v-for="(t, i) in pageList"
          :key="t.name"
          class="tab-item"
          :class="{ 'tab-item-active': current === i }"
          @click="current = i"
        >
          <span class="tab-text">{{ t.name }}</span>
          <div v-if="current === i" class="tab-line"></div>
        </div>
      </div>

      <!-- 个人权益卡（card-view：686×470rpx #513724 r20 padding 50rpx） -->
      <div v-if="current === 0" class="card-view">
        <div class="benefit-row">
          <img class="benefit-icon" src="/static/icon/gh/fee.png" alt="" />
          <div class="benefit-body">
            <span class="benefit-title"> 手续费减免 </span>
            <span class="benefit-text"> 持有人可以享受消费返利1.88%，被邀请人 可以享受消费返利1%手续费;持有人还可以 额外享受所有被邀请人消费返利的0.88%。 </span>
          </div>
        </div>
        <img class="benefit-divider" src="/static/icon/gh/divider.png" alt="" />
        <div class="benefit-row">
          <img class="benefit-icon benefit-icon-sm" src="/static/icon/gh/d-msg.png" alt="" />
          <div class="benefit-body">
            <span class="benefit-title"> 公会成员人数 </span>
            <span class="benefit-text"> 每位公会会长最多可绑定邀请30人。 </span>
          </div>
        </div>
      </div>

      <!-- 我的工会 / 申请入会 → search 空态（原站如此） -->
      <div v-else class="empty">
        <img class="empty-img" src="/static/empty/search-empty.png" alt="" />
        <span class="empty-text">暂无内容</span>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'

/**
 * 原站 chunk 反解 + DOM/截图实测（pages-market-tradeUnion，2026-10-23）：
 *  - container 背景 = /static/icon/gh/d-bg.png（750×656 棕色纹理图，computed style 实测）
 *    100% auto + 兜底色 #3d281c；padding-top 55px（渲染）
 *  - navbar：u-navbar 标准 44px（fixed+placeholder），标题 18px 500 **#000**（黑字，原站如此）；
 *    back 9×16px（back_btn.png）
 *  - lock 60×60；「您的公会未解锁」16px bold 黑 #26292d（font-color-dark），mt-24 mb-16；
 *    「赶快购买…」12px #FCD8B1
 *  - tabbar mt-40 全宽；u-tabs：item flex1，text 82×66px 居中 17px，active #E3A86A bold /
 *    #CAB6A1，线 20×3 #E3A86A（line bottom 20rpx）
 *  - card-view：686×470rpx #513724 r20 padding 50rpx；icon 32×28 margin-right 32px；
 *    标题 17px bold #E3A86A；正文 12px #CAB6A1 line-height 19px；分隔线 337×1px margin 22px 0
 *  - 源码值 = 渲染值 × 1.812；图标 fee/divider 为 chunk 内嵌 base64 已提取本地
 */
const pageList = [{ name: '个人权益' }, { name: '我的工会' }, { name: '申请入会' }]
const current = ref(0)
</script>

<style scoped>
.tu-page {
  min-height: 100vh;
  background: url('/static/icon/gh/d-bg.png') no-repeat;
  background-size: 100% auto;
  background-color: #3d281c;
}
/* ---- 导航栏（u-navbar 标准 44px 渲染） ---- */
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
  left: 29px;
  top: 50%;
  transform: translateY(-50%);
  width: 16.3px;
  height: 29px;
  object-fit: contain;
}
.nav-title {
  width: 398.6px;
  font-size: 32.6px;
  font-weight: 700;
  color: #000;
  text-align: center;
  font-family: var(--font-bold);
}
/* ---- 主体（container padding-top 55px 渲染） ---- */
.body {
  display: flex;
  flex-direction: column;
  align-items: center;
  padding-top: 99.7px;
}
.lock-icon {
  width: 108.7px;
  height: 108.7px;
  object-fit: contain;
}
.lock-title {
  margin-top: 43.5px;
  margin-bottom: 38px;
  font-size: 30.8px;
  font-weight: 700;
  color: #26292d;
}
.lock-sub {
  font-size: 23.6px;
  color: #fcd8b1;
}
/* ---- Tabs（mt-40 渲染；text 82×66px；line 20×3 bottom 20rpx） ---- */
.tabbar {
  margin-top: 87px;
  width: 100%;
  display: flex;
}
.tab-item {
  flex: 1;
  height: 119.6px;
  display: flex;
  align-items: center;
  justify-content: center;
  position: relative;
}
.tab-text {
  width: 148.6px;
  height: 119.6px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 30.8px;
  color: #cab6a1;
}
.tab-item-active .tab-text {
  color: #e3a86a;
  font-weight: bold;
}
.tab-line {
  position: absolute;
  bottom: 19.4px;
  left: 50%;
  margin-left: -18.1px;
  width: 36.2px;
  height: 5.4px;
  background: #e3a86a;
}
/* ---- 个人权益卡（686×470rpx #513724 r20 padding 50rpx；mt-24 渲染） ---- */
.card-view {
  margin-top: 43.5px;
  width: 686px;
  height: 470px;
  background: #513724;
  border-radius: 20px;
  padding: 25px 50px 50px;
  box-sizing: border-box;
  display: flex;
  flex-direction: column;
  justify-content: center;
}
.benefit-row {
  display: flex;
  align-items: flex-start;
}
.benefit-icon {
  width: 58px;
  height: 50.7px;
  flex-shrink: 0;
  margin-right: 58px;
}
.benefit-icon-sm {
  width: 60.1px;
  height: 49.1px;
}
.benefit-body {
  flex: 1;
  min-width: 0;
  display: flex;
  flex-direction: column;
}
.benefit-title {
  font-size: 30.8px;
  font-weight: bold;
  color: #e3a86a;
}
.benefit-text {
  margin-top: 27px;
  font-size: 23.6px;
  color: #cab6a1;
  line-height: 34.4px;
}
.benefit-divider {
  width: 610.6px;
  height: 1.8px;
  margin: 39.9px 0;
  object-fit: contain;
}
/* ---- 空态 ---- */
.empty {
  margin-top: 108.7px;
  display: flex;
  flex-direction: column;
  align-items: center;
}
.empty-img {
  width: 265px;
  object-fit: contain;
}
.empty-text {
  margin-top: 6px;
  font-size: 18.1px;
  color: #a9a7ac;
}
</style>
