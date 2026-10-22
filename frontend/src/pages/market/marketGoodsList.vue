<template>
  <div class="mg-page">
    <!-- 导航栏（藏品名称 17px Bold #111 + 右上收藏心形） -->
    <div class="navbar">
      <img class="navbar-back" src="/static/cw/back_btn.png" alt="返回" @click="$router.back()" />
      <span class="nav-title">藏品名称</span>
      <img class="nav-fav" src="/static/qbox/detail-unfavorited.png" alt="收藏" />
    </div>

    <div class="body">
      <!-- 主图（数据空时黑色占位 333px 渲染） -->
      <div class="main-img"></div>

      <!-- 名称卡（藏品名称 17px Bold + 限量/流通胶囊 + 地板价 ￥--） -->
      <div class="name-card">
        <div class="name-row">
          <span class="g-name">藏品名称</span>
          <div class="floor-col">
            <span class="floor-label">地板价</span>
            <span class="floor-price">￥--</span>
          </div>
        </div>
        <div class="num-row">
          <span class="num-capsule">售罄0份</span>
          <span class="num-capsule">流通0份</span>
        </div>
      </div>

      <!-- Tabs（市场价格 #111 / 相关公告 / 成交记录 / 成交趋势 #8c8c8c，15px） -->
      <div class="m-tabs">
        <span
          v-for="(t, i) in mTabs"
          :key="t"
          class="m-tab"
          :class="{ 'm-tab-active': current === i }"
          @click="current = i"
        >{{ t }}</span>
      </div>

      <!-- 筛选行（售罄/求购/委托 小 tab + 价格/编号 排序箭头 8×13） -->
      <div class="filter-row">
        <div class="mini-tabs">
          <span class="mini-tab mini-tab-active">售罄</span>
          <span class="mini-tab">求购</span>
          <span class="mini-tab">委托</span>
        </div>
        <div class="sort-col">
          <span class="sort-label">价格</span>
          <img class="sort-icon" src="/static/qbox/market-sort.png" alt="" />
        </div>
        <div class="sort-col">
          <span class="sort-label">编号</span>
          <img class="sort-icon" src="/static/qbox/market-sort-asc.png" alt="" />
        </div>
      </div>

      <!-- 空态（common-empty 156px 渲染 y586） -->
      <div class="empty">
        <img class="empty-img" src="/static/qbox/common-empty.png" alt="" />
      </div>
    </div>

    <!-- 底部快捷下单（15px 白字 #0018ff，y857） -->
    <div class="quick-btn">快捷下单</div>
  </div>
</template>

<script setup>
import { ref } from 'vue'

/**
 * 原站 DOM 实测（2026-10-23，pages/market/marketGoodsList）：
 *  - 标题 17px Bold #111 + 右上收藏心形；主图黑占位；
 *  - 名称卡：藏品名称 17px Bold + 地板价 10px（￥--）+ 售罄/流通胶囊；
 *  - tabs 15px：市场价格(#111 Bold)/相关公告/成交记录/成交趋势(#8c8c8c)；
 *  - 筛选：售罄/求购/委托 + 价格/编号排序箭头（market-sort 8×13）；
 *  - 空态 common-empty 156px（y586）；底部「快捷下单」15px 白字 #0018ff（y857）
 */
const mTabs = ['市场价格', '相关公告', '成交记录', '成交趋势']
const current = ref(0)
</script>

<style scoped>
.mg-page {
  min-height: 100vh;
  background: #f2f3f5;
  padding-bottom: 160px;
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
  font-size: 30.8px;
  font-weight: 700;
  color: #111;
}
.nav-fav {
  position: absolute;
  right: 23.6px;
  top: 50%;
  transform: translateY(-50%);
  width: 43.5px;
  height: 43.5px;
  object-fit: contain;
}
.body {
  padding: 0 22px;
}
.main-img {
  margin-top: 8px;
  width: 100%;
  height: 617px;
  border-radius: 28px;
  background: #0e0f12;
}
.name-card {
  margin-top: 25px;
  background: #fff;
  border-radius: 16px;
  padding: 24px 22px;
}
.name-row {
  display: flex;
  align-items: center;
}
.g-name {
  font-size: 30.8px;
  font-weight: 700;
  color: #111;
}
.floor-col {
  margin-left: auto;
  display: flex;
  flex-direction: column;
  align-items: flex-end;
}
.floor-label {
  font-size: 18.1px;
  color: #000;
}
.floor-price {
  font-size: 25.4px;
  font-weight: 700;
  color: #111;
}
.num-row {
  margin-top: 18px;
  display: flex;
}
.num-capsule {
  padding: 4px 12px;
  background: #0018ff;
  border-radius: 6px;
  font-size: 19.9px;
  color: #fff;
  margin-right: 12px;
}
.m-tabs {
  margin-top: 40px;
  display: flex;
  justify-content: space-between;
  padding: 0 8px;
}
.m-tab {
  font-size: 27.2px;
  color: #8c8c8c;
}
.m-tab-active {
  color: #111;
  font-weight: 700;
}
.filter-row {
  margin-top: 30px;
  display: flex;
  align-items: center;
}
.mini-tabs {
  display: flex;
  gap: 30px;
}
.mini-tab {
  font-size: 23.6px;
  color: #999;
}
.mini-tab-active {
  color: #0018ff;
  font-weight: 600;
}
.sort-col {
  margin-left: auto;
  display: flex;
  align-items: center;
  gap: 6px;
}
.sort-col + .sort-col {
  margin-left: 30px;
}
.sort-label {
  font-size: 23.6px;
  color: #666;
}
.sort-icon {
  width: 14.5px;
  height: 23.6px;
  object-fit: contain;
}
.empty {
  margin-top: 90px;
  display: flex;
  flex-direction: column;
  align-items: center;
}
.empty-img {
  width: 282.6px;
  height: 282.6px;
  object-fit: contain;
}
/* ---- 底部快捷下单 ---- */
.quick-btn {
  position: fixed;
  left: 50%;
  transform: translateX(-50%);
  bottom: calc(20px + env(safe-area-inset-bottom));
  width: 681px;
  height: 90px;
  border-radius: 45px;
  background: #0018ff;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 27.2px;
  color: #fff;
}
</style>
