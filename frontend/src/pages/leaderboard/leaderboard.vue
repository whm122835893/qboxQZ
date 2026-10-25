<template>
  <div class="lb-page">
    <!-- 头部：rank_top_bg 338rpx 高（源站实测 style height:338px）+ 标题图 mt 208rpx，384×86rpx -->
    <div class="top-bg">
      <div class="nav-back-circle" @click="$router.back()">
        <img class="back-icon" src="/static/cw/back_btn.png" alt="返回" />
      </div>
      <img class="rank-title" src="/static/good/rank_title.png" alt="GOOD 今日热榜" />
    </div>

    <!-- 榜单列表区（源站实测：卡片 686×164rpx，padding 16/30，radius 8，mb 24，左右外边距 32） -->
    <div class="rank-area">
      <div v-for="(item, i) in list" :key="item.id" class="rank-card">
        <!-- 名次奖牌图：源站 rank1~rank6.png，前三名可见 -->
        <img class="rank-medal" :src="`/static/good/rank${i + 1}.png`" :alt="`第${i + 1}名`" />

        <!-- 藏品图 100×132rpx radius 10 -->
        <div class="cover-box">
          <img class="cover" :src="item.listimg" :alt="item.name" />
        </div>

        <!-- 名称 + 发行/流通 + 价格 -->
        <div class="info-col">
          <div class="info-top">
            <span class="goods-name">{{ item.name }}</span>
            <span class="goods-sub">发行{{ item.total_num }}/流通{{ item.flux }}</span>
          </div>
          <div class="price-row">
            <span class="price-symbol">¥</span><span class="price-value">{{ item.min_price }}</span>
          </div>
        </div>

        <!-- 右侧点赞图标 36×35rpx radius 10 -->
        <div class="like-col">
          <img class="like-icon" :src="item.is_like ? '/static/cw/rank_like.png' : '/static/cw/rank_unlike.png'" alt="" />
        </div>
      </div>

      <!-- 无数据空态（源站实测 search-empty「暂无内容」） -->
      <div v-if="!list.length" class="empty">
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
 *  - 头部 rank_top_bg.png 338rpx 高（含 GOOD/今日热榜/奖杯），rank_title.png 384×86rpx mt 208rpx
 *  - 返回钮：w-navbar rank 模式（67rpx 半透明白圆 + back_btn 26rpx）
 *  - 榜单卡：686×164rpx，padding 16/30，radius 8，mb 24；名次图 rank1~6.png 57×76rpx
 *  - 藏品图 100×132rpx radius 10；名称 SourceHanSansCN-Medium 14 号；
 *    副文 SourceHanSansCN-Regular 10 号；价格 ¥ 8 号 + 数值 17 号 Bold
 *  - 接口：GET /api/market/market/marketrebang（源站实测返回 6 条真实藏品）
 */
const list = ref([])

onMounted(async () => {
  const res = await request('/api/market/market/marketrebang', { page: 1 })
  if (res.code === 1 && Array.isArray(res.data)) {
    list.value = res.data
  }
})
</script>

<style scoped>
.lb-page {
  min-height: 100vh;
  background: #f2f2f2;
}
.top-bg {
  width: 100%;
  height: 338px;
  background: url('/static/good/rank_top_bg.png') 0% 0% / 100% 100% no-repeat;
  position: relative;
}
/* 源站实测：navbar__content__left 内 67×67rpx 半透明白圆，左上对齐（超出 44rpx 导航条高度） */
.nav-back-circle {
  position: absolute;
  left: 0;
  top: -11.5px;
  width: 67px;
  height: 67px;
  background: rgba(244, 244, 244, 0.5);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 2;
}
.back-icon {
  height: 26px;
  width: 14px;
}
/* 源站实测：rank_title.png 384×86rpx，mt 208rpx，左 32rpx */
.rank-title {
  position: absolute;
  left: 32px;
  top: 208px;
  width: 384px;
  height: 86px;
  object-fit: fill;
}
/* 源站实测：列表区 mt 24rpx（mt-12），左右 padding 32rpx（plr-16） */
.rank-area {
  padding: 24px 32px 40px;
}
/* 榜单卡：686×164rpx，padding 16/30，radius 8，mb 24 */
.rank-card {
  width: 100%;
  height: 164px;
  box-sizing: border-box;
  background: #fff;
  border-radius: 8px;
  padding: 16px 30px;
  margin-bottom: 24px;
  display: flex;
  align-items: center;
}
/* 名次奖牌 57×76rpx；源站与封面间距 24rpx（mr-12 = 24rpx） */
.rank-medal {
  width: 57px;
  height: 76px;
  object-fit: contain;
  margin-right: 24px;
  flex-shrink: 0;
}
.cover-box {
  width: 100px;
  height: 132px;
  border-radius: 10px;
  overflow: hidden;
  background: transparent;
  flex-shrink: 0;
}
.cover {
  width: 100px;
  height: 132px;
  object-fit: cover;
  display: block;
}
/* 源站实测：封面右边缘 243 → 名称 x 259，间距 16rpx */
.info-col {
  flex: 1 1 0%;
  min-width: 0;
  margin-left: 16px;
  padding-top: 7px;
  display: flex;
  flex-direction: column;
  align-self: stretch;
}
.info-top {
  display: flex;
  flex-direction: column;
}
/* 源站实测名称 28rpx（SourceHanSansCN-Medium，font-weight 700） */
.goods-name {
  max-width: 150px;
  font-size: 28px;
  font-weight: 700;
  color: #020202;
  font-family: var(--font-medium);
  line-height: 28px;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}
/* 源站实测副文 20rpx（SourceHanSansCN-Regular） */
.goods-sub {
  font-size: 20px;
  color: #030106;
  font-family: var(--font-regular);
  line-height: 20px;
  margin-top: 12px;
}
.price-row {
  display: flex;
  align-items: baseline;
  margin-top: 22px;
}
/* 源站实测 ¥ 符号 16rpx Bold */
.price-symbol {
  font-size: 16px;
  color: #030106;
  font-family: var(--font-bold);
}
/* 源站实测价格数值 34rpx（SourceHanSansCN-Bold 字族，font-weight 400），ml-4（8rpx） */
.price-value {
  font-size: 34px;
  font-weight: 400;
  color: #030106;
  font-family: var(--font-bold);
  margin-left: 8px;
  line-height: 34px;
}
.like-col {
  flex: 1 1 0%;
  display: flex;
  align-items: flex-end;
  justify-content: flex-end;
}
.like-icon {
  width: 36px;
  height: 35px;
  border-radius: 10px;
  object-fit: contain;
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
