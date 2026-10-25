<template>
  <div class="gr-page">
    <!-- 导航栏（源站实测：navbar 44rpx 高，返回图 17×30rpx，标题 34rpx Medium #000） -->
    <div class="navbar">
      <img class="navbar-back" src="/static/cw/back_btn.png" alt="返回" @click="$router.back()" />
      <span class="nav-title">赠送记录</span>
    </div>

    <!-- 记录列表容器（源站实测：bg #f4f6fa，padding 24rpx，radius 16rpx，左右外边距 32rpx） -->
    <div class="list-box">
      <!-- 单条记录（源站实测：ptb-8 = padding 16rpx 上下，两栏布局） -->
      <div v-for="(it, i) in list" :key="i" class="record-item">
        <div class="left-col">
          <div class="code-row">
            <span class="code">#{{ it.collection_code }}</span>
            <span class="slash">/</span>
          </div>
          <span class="time">{{ it.time }}</span>
        </div>
        <div class="price-wrap">
          <span class="price-symbol">￥</span>
          <span class="price">{{ it.price }}</span>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { request } from '../../api/request'

/**
 * 原站 chunk 反解 + DOM 实测（pages-market-giftRecord，2026-10-24 22:30 登录态）：
 *  - 接口 GET /api/market/trade/tradingInformation {status:4, page:1}
 *    → data.list 20 条真实流水（collection_code 藏品编号 / price 赠送价 / time 相对时间）
 *  - 容器：class "list bg-color-item pa-12 br-8"（bg #f4f6fa、padding 24rpx、radius 16rpx）
 *  - 行：class "ptb-8 flex ... item"（padding 16rpx 上下）
 *    左列：编号 28rpx Bold + "/" 24rpx，下方时间 24rpx #999
 *    右列：价格 32rpx Bold，￥ 符号 24rpx
 */
const list = ref([])

onMounted(async () => {
  const res = await request('/api/market/trade/tradingInformation', { status: 4, page: 1 })
  if (res.code === 1 && res.data) list.value = res.data.list || []
})
</script>

<style scoped>
.gr-page {
  min-height: 100vh;
  background: #fff;
}
.navbar {
  position: relative;
  width: 100%;
  height: 44px;
  display: flex;
  align-items: center;
  justify-content: center;
}
.navbar-back {
  position: absolute;
  left: 0;
  top: 50%;
  transform: translateY(-50%);
  width: 17px;
  height: 30px;
}
.nav-title {
  font-size: 34px;
  font-weight: 500;
  color: #000;
  font-family: var(--font-medium);
}
.list-box {
  margin: 0 32px;
  background: #f4f6fa;
  border-radius: 16px;
  padding: 24px;
}
/* 源站实测：行高 119rpx（padding 16rpx 上下 + 内容 87rpx） */
.record-item {
  padding: 16px 0;
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
}
.left-col {
  display: flex;
  flex-direction: column;
}
.code-row {
  display: flex;
  align-items: center;
  margin-bottom: 10px;
}
.code {
  font-size: 28px;
  font-weight: 700;
  color: #000;
  font-family: var(--font-bold);
}
.slash {
  font-size: 24px;
  color: #000;
  font-family: var(--font-regular);
}
.time {
  font-size: 24px;
  color: #999;
  font-family: var(--font-regular);
}
.price-wrap {
  display: flex;
  align-items: flex-end;
}
.price-symbol {
  font-size: 24px;
  color: #000;
  font-family: var(--font-regular);
}
.price {
  font-size: 32px;
  font-weight: 700;
  color: #000;
  font-family: var(--font-bold);
}
</style>
