<template>
  <div class="lb-page">
    <!-- 头部（rank_top_bg 336rpx 高；rank_title 图 mt 206rpx；白半透明圆返回钮 rank navType） -->
    <div class="top-bg">
      <div class="nav-back-circle" @click="$router.back()">
        <img class="back-icon" src="/static/cw/back_btn.png" alt="返回" />
      </div>
      <img class="rank-title" src="/static/good/rank_title.png" alt="GOOD 今日热榜" />
    </div>

    <!-- 榜单区（购买榜/发行/流通；数据空 → search 空态，user/ranking 实测返回空） -->
    <div class="rank-area">
      <div class="empty">
        <img class="empty-img" src="/static/empty/search-empty.png" alt="" />
        <span class="empty-text">暂无内容</span>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { request } from '../../api/request'

/**
 * 原站 chunk 反解（pages-leaderboard-leaderboard）：
 *  - 头部 rank_top_bg.png（含 GOOD/今日热榜/奖杯，100% 100%）高 336rpx；rank_title.png 380×85rpx mt 206rpx
 *  - 返回钮：w-navbar rank 模式（67rpx 半透明白圆 + back_btn 26rpx）
 *  - 榜单类型：购买榜(￥ buy_collection_total_price)/邀请榜(yao_count)；发行/流通字段
 *  - user/ranking 实测返回空 → search-empty 空态「暂无内容」（mt 160rpx）
 */
const ranking = ref({})

onMounted(async () => {
  const res = await request('/api/user/ranking', { page: 1, types: 2 })
  if (res.code === 1 && res.data) ranking.value = res.data
})
</script>

<style scoped>
.lb-page {
  min-height: 100vh;
  background: #f2f2f2;
}
.top-bg {
  width: 100%;
  height: 336.9px;
  background: url('/static/good/rank_top_bg.png') 0% 0% / 100% 100% no-repeat;
  position: relative;
}
.nav-back-circle {
  position: absolute;
  left: 12.2px;
  top: 9.4px;
  width: 37px;
  height: 37px;
  background: rgba(244, 244, 244, 0.5);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 2;
}
.back-icon {
  height: 14.4px;
  width: auto;
}
.rank-title {
  position: absolute;
  left: 30px;
  top: 114px;
  width: 210px;
  height: 47px;
  object-fit: contain;
}
.rank-area {
  padding: 0 30px;
}
.empty {
  display: flex;
  flex-direction: column;
  align-items: center;
  margin-top: 185px;
}
.empty-img {
  width: 265px;
  height: 258.7px;
  object-fit: contain;
}
.empty-text {
  margin-top: 4px;
  font-size: 25.4px;
  color: #999;
}
</style>
