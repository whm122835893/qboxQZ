<template>
  <!-- 以求购价出售（pages/market/sellBegBuy）
       源站 DOM 实测（750 视口，2026-10-25，登录态）：
       - 根容器 bg #f7f7f7，padding-bottom calc(160px + safe-area)
       - 导航栏 44px，返回图 17×30（/static/cw/back_btn.png），标题「以求购价出售」34px/500 #000
       - 内容区 align center，padding 0 36px
       - 藏品卡容器 678px（无参数时为空）
       - 说明卡 678px 白底 r22 padding 28；标题「以求购价出售说明」28px SourceHanSansCN-Bold #111；
         三条说明 24px/1.6 #686868
       - 底部固定条白底 padding 16 36，按钮 h88 r44 bg #0018ff，文案「确认出售」30px #fff -->
  <div class="sbb-page">
    <div class="navbar">
      <img class="nav-back" src="/static/cw/back_btn.png" alt="返回" @click="goBack" />
      <span class="nav-title">以求购价出售</span>
    </div>

    <div class="content">
      <!-- 藏品卡（需携带藏品参数时才有内容；无参数时源站为空） -->
      <div v-if="goods" class="goods-card">
        <img class="g-cover" :src="goods.listimg" :alt="goods.name" />
        <div class="g-info">
          <span class="g-name">{{ goods.name }}</span>
          <span class="g-sub">当前藏品最高价：¥{{ goods.max_price }}</span>
          <span class="g-sub">当前藏品求购价：¥{{ goods.beg_price }}</span>
        </div>
      </div>

      <!-- 说明卡 -->
      <div class="desc-card">
        <span class="desc-title">以求购价出售说明</span>
        <div class="desc-list">
          <span class="desc-item">1.成交顺序是以买方发起求购订单的金额由高至低的顺序成交 。</span>
          <span class="desc-item">2.买方求购订单的付款钱包和卖方收款账户钱包需要一致才可成交，如果不一致会导致无法成交。</span>
          <span class="desc-item">3.订单一旦成交，不可撤销。</span>
        </div>
      </div>
    </div>

    <!-- 底部固定条 -->
    <div class="bottom-bar">
      <div class="submit-btn">
        <span class="submit-text">确认出售</span>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'

/**
 * 源站 DOM 实测（2026-10-25，750 视口，登录态）：
 * 根容器 .page-container background-color rgb(247,247,247)，padding-bottom calc(160px + env(safe-area-inset-bottom))。
 * u-navbar 高 44px，返回 uni-image 17×30（/static/cw/back_btn.png），
 * 标题 uni-text width 400px / font-size 34px / font-weight 500 / color rgb(0,0,0)。
 * 内容 wrapper：width 100%，flex column，align-items center，padding 0 36px。
 * 藏品卡容器 678px（无藏品参数时为空，无子节点）。
 * 说明卡：678px，bg #fff，border-radius 22px，padding 28px，flex column。
 *   标题 28px / SourceHanSansCN-Bold / #111；说明列表 gap 12；条目 24px / line-height 1.6 / #686868。
 * 底部固定条：position fixed bottom 0，width 750，padding 16px 36px，bg #fff。
 *   按钮 flex1 h88 bg #0018ff radius 44；文案 30px / SourceHanSansCN-Medium / #fff。
 */
const router = useRouter()

// 源站无参数访问时藏品卡为空；有 id 参数时按接口填充（当前 mock 无该接口数据 → 保持空）
const goods = ref(null)

function goBack() {
  if (window.history.length > 1) router.back()
  else router.replace('/pages/index/index')
}
</script>

<style scoped>
.sbb-page {
  min-height: 100vh;
  background: #f7f7f7;
  padding-bottom: calc(160px + env(safe-area-inset-bottom));
  box-sizing: border-box;
}
/* ---- 导航栏（44px，返回 17×30，标题 34px/500 #000） ---- */
.navbar {
  position: relative;
  height: 44px;
  display: flex;
  align-items: center;
  padding: 0 36px;
  background: transparent;
}
.nav-back {
  width: 17px;
  height: 30px;
  object-fit: contain;
}
.nav-title {
  position: absolute;
  left: 50%;
  transform: translateX(-50%);
  width: 400px;
  font-size: 34px;
  font-weight: 500;
  color: #000;
  font-family: var(--font-regular);
  text-align: center;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}
/* ---- 内容区 ---- */
.content {
  width: 100%;
  display: flex;
  flex-direction: column;
  align-items: center;
  padding: 0 36px;
  box-sizing: border-box;
}
.goods-card {
  width: 678px;
  display: flex;
  align-items: center;
  gap: 20px;
  background: #fff;
  border-radius: 22px;
  padding: 24px;
  box-sizing: border-box;
  margin-bottom: 24px;
}
.g-cover {
  width: 120px;
  height: 120px;
  border-radius: 19px;
  object-fit: cover;
  flex-shrink: 0;
}
.g-info {
  flex: 1;
  min-width: 0;
  display: flex;
  flex-direction: column;
  gap: 8px;
}
.g-name {
  font-size: 30px;
  color: #111;
  font-family: var(--font-bold);
}
.g-sub {
  font-size: 24px;
  color: #686868;
  font-family: var(--font-regular);
}
/* ---- 说明卡 ---- */
.desc-card {
  width: 678px;
  background: #fff;
  border-radius: 22px;
  padding: 28px;
  box-sizing: border-box;
  display: flex;
  flex-direction: column;
}
.desc-title {
  font-size: 28px;
  color: #111;
  font-family: var(--font-bold);
}
.desc-list {
  margin-top: 12px;
  display: flex;
  flex-direction: column;
  gap: 12px;
}
.desc-item {
  font-size: 24px;
  line-height: 1.6;
  color: #686868;
  font-family: var(--font-regular);
}
/* ---- 底部固定条 ---- */
.bottom-bar {
  position: fixed;
  left: 0;
  bottom: 0;
  width: 750px;
  padding: 16px 36px calc(16px + env(safe-area-inset-bottom));
  box-sizing: border-box;
  background: #fff;
  display: flex;
  align-items: center;
}
.submit-btn {
  flex: 1;
  height: 88px;
  background: #0018ff;
  border-radius: 44px;
  display: flex;
  align-items: center;
  justify-content: center;
}
.submit-text {
  font-size: 30px;
  color: #fff;
  font-family: var(--font-medium);
}
</style>
