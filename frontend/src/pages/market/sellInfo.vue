<template>
  <!-- 买卖详情（pages/market/sellInfo）
       源站 DOM 实测（750 视口，2026-10-25，登录态）：
       - 根容器 class="container page-container bg-color-pageBg page-bg-colorfull ptb-16"
       - 导航栏 44px，返回图 17×30（/static/cw/back_btn.png），标题「买卖详情」34px/500 #000
       - 统计卡：外层 plr-24；卡内 plr-12 ptb-8 flex space-between center
         左侧封面 126×126 r10 + ml-10 名称（14px）；右侧两行：
         今日最高 / 今日成交量（字号 10，右列 ml-8）；第二行 今日最低 / 今日地板价（mt-10）
         数值前缀「￥」：最高/最低红色 rgb(222,16,16)，地板价绿色 rgb(25,222,16)
       - 「成交记录」标题 mtb-20 plr-16 font-w-bold；列表 plr-16 list mt-10（空）
       - 加载区 mt-16：spinner 14px #333 + 文案 14px #333；空态 mt-32 图片 265px + 「暂无内容」26px #999 -->
  <div class="si-page">
    <div class="navbar">
      <img class="nav-back" src="/static/cw/back_btn.png" alt="返回" @click="goBack" />
      <span class="nav-title">买卖详情</span>
    </div>

    <!-- 统计卡 -->
    <div class="stat-wrap">
      <div class="stat-card">
        <div class="stat-left">
          <img v-if="goods.listimg" class="stat-cover" :src="goods.listimg" :alt="goods.name" />
          <img v-else class="stat-cover" :src="FALLBACK_COVER" alt="" />
          <span class="stat-name">{{ goods.name }}</span>
        </div>
        <div class="stat-right">
          <div class="stat-row">
            <div class="stat-col">
              <span class="stat-label">今日最高</span>
              <span class="stat-value stat-up">￥</span>
            </div>
            <div class="stat-col">
              <span class="stat-label">今日成交量</span>
              <span class="stat-value"></span>
            </div>
          </div>
          <div class="stat-row stat-row-gap">
            <div class="stat-col">
              <span class="stat-label">今日最低</span>
              <span class="stat-value stat-down">￥</span>
            </div>
            <div class="stat-col">
              <span class="stat-label">今日地板价</span>
              <span class="stat-value stat-floor">￥</span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- 成交记录 -->
    <div class="record-title">成交记录</div>
    <div class="record-list">
      <div v-if="records.length" v-for="r in records" :key="r.id" class="record-row">
        <span class="r-name">{{ r.name }}</span>
        <span class="r-price">￥{{ r.price }}</span>
        <span class="r-time">{{ r.time }}</span>
      </div>
      <div v-else class="loading">
        <span class="spin"></span>
        <span class="loading-text">加载中</span>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'

/**
 * 源站 DOM 实测（2026-10-25，750 视口，登录态）：
 * 根容器 .page-container，class 含 bg-color-pageBg / page-bg-colorfull / ptb-16。
 * u-navbar 高 44px，返回 uni-image 17×30（/static/cw/back_btn.png），
 * 标题 uni-text width 400px / 34px / 500 / rgb(0,0,0)。
 * 统计卡外层 plr-24；卡内 plr-12 ptb-8 flex space-between align-center：
 *   左：封面 uni-image 126×126 radius 10 + 名称（ml-10，font-size-14）
 *   右：两行 flex；每行两列 flex-dir-col font-size-10，右列 ml-8
 *       第一行 今日最高 / 今日成交量；第二行 今日最低 / 今日地板价（行 mt-10）
 *       数值前缀「￥」：最高与最低 color rgb(222,16,16)，地板价 color rgb(25,222,16)
 * 「成交记录」uni-view class mtb-20 plr-16 font-color-dark font-w-bold
 * 列表 plr-16 list mt-10（无参数时为空）
 * 加载区 mt-16：spinner 14×14 color #333 + 文案 14px #333；空态（display:none）图片 265px + 「暂无内容」26px #999
 */
const router = useRouter()

/**
 * 无参数进入本页时源站封面位仍为真实藏品图（无数据时展示平台默认藏品封面），
 * 此处回落到已本地化的源站真实封面，避免出现破图。
 */
const FALLBACK_COVER = '/uploads/20261025/7c674d998b7e86b520f0930c18825d3c.jpg'

const goods = ref({ name: '', listimg: '' })
const records = ref([])

function goBack() {
  if (window.history.length > 1) router.back()
  else router.replace('/pages/index/index')
}
</script>

<style scoped>
.si-page {
  min-height: 100vh;
  background: #f7f7f7;
  box-sizing: border-box;
}
/* ---- 导航栏 ---- */
.navbar {
  position: relative;
  height: 44px;
  display: flex;
  align-items: center;
  padding: 0 36px;
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
/* ---- 统计卡 ---- */
.stat-wrap {
  padding: 0 24px;
}
.stat-card {
  background: #fff;
  border-radius: 20px;
  padding: 8px 12px;
  display: flex;
  align-items: center;
  justify-content: space-between;
}
.stat-left {
  display: flex;
  align-items: center;
}
.stat-cover {
  width: 126px;
  height: 126px;
  border-radius: 10px;
  object-fit: cover;
  background: #111;
}
.stat-name {
  margin-left: 10px;
  font-size: 28px;
  color: #000;
  font-family: var(--font-regular);
}
.stat-right {
  display: flex;
  flex-direction: column;
}
.stat-row {
  display: flex;
  align-items: center;
}
.stat-row-gap {
  margin-top: 10px;
}
.stat-col {
  display: flex;
  flex-direction: column;
  font-size: 20px;
  color: #999;
  font-family: var(--font-regular);
}
.stat-col + .stat-col {
  margin-left: 8px;
}
.stat-label {
  font-size: 20px;
  color: #999;
  font-family: var(--font-regular);
}
.stat-value {
  margin-top: 4px;
  font-size: 20px;
  font-family: var(--font-regular);
}
.stat-up,
.stat-down {
  color: #de1010;
}
.stat-floor {
  color: #19de10;
}
/* ---- 成交记录 ---- */
.record-title {
  margin: 20px 0;
  padding: 0 16px;
  font-weight: 700;
  color: #333;
  font-size: 28px;
  font-family: var(--font-regular);
}
.record-list {
  padding: 0 16px;
  margin-top: 10px;
}
.record-row {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 12px 0;
  font-size: 24px;
  color: #333;
}
.loading {
  margin-top: 16px;
  display: flex;
  flex-direction: column;
  align-items: center;
}
.spin {
  width: 14px;
  height: 14px;
  border-radius: 50%;
  border: 2px solid #d6d6d6;
  border-top-color: #333;
  animation: spin 1.2s ease-in-out infinite;
}
@keyframes spin {
  to {
    transform: rotate(360deg);
  }
}
.loading-text {
  font-size: 14px;
  color: #333;
  font-family: var(--font-regular);
}
</style>
