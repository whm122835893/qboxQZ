<template>
  <div class="sh-page">
    <!-- 搜索行（源站 750 视口实测 2026-10-25：容器 padding 44/36/80，行高 72，
         返回图 24×24 旋转180°，搜索框 flex1 h72 #f5f5f5 r36 padding 0 28px，
         图标 32×32，间距 16/12，输入文字 24px #4a4a4a） -->
    <div class="search-row">
      <img class="sh-back" src="/static/qbox/common-arrow-right-black.png" alt="返回" @click="$router.back()" />
      <div class="search-box">
        <img class="search-icon" src="/static/qbox/common-search-gray.png" alt="" />
        <input v-model="keyword" class="search-input" placeholder="请输入内容" @confirm="doSearch" />
      </div>
    </div>

    <!-- Tabs（源站实测：mt 28，字 29px MiSans-Semibold，active #000 / 未选 #5b5b5b，
         下划线 48×6 #0047ff r3，mt 8；兄弟间距 36） -->
    <div class="tabs">
      <div v-for="(t, i) in tabs" :key="t.name" class="tab-item" @click="switchTab(i)">
        <span class="tab-text" :class="{ 'tab-text-active': current === i }">{{ t.name }}</span>
        <div v-if="current === i" class="tab-line"></div>
      </div>
    </div>

    <!-- 表头（源站实测：mt 42，字 23px 500 #919191；
         藏品名称 flex1(398) / 地板价 160 右对齐 / 成交量 120 右对齐） -->
    <div class="table-head">
      <span class="th th-name">藏品名称</span>
      <span class="th th-price">地板价</span>
      <span class="th th-volume">成交量</span>
    </div>

    <!-- 藏品列表（源站实测：容器 mt 28，行间距 24；
         普通行 678×136 padding 20/24 r24 #fff；
         封面 96×96 r16，名称 26px/600 #0c0c0c，副文 21px/400 #9a9a9a，
         收藏图标 32×32，地板价列 120 右对齐 22px/600 #323232，成交量列 72 右对齐；
         已退市行：渐变底 #fdfdfd→#eee + 3px #fefefe 描边 r19 + 阴影 0 1px 12px，高度 142） -->
    <div v-if="list.length" class="goods-list">
      <div
        v-for="g in list"
        :key="g.id"
        class="goods-row"
        :class="{ 'goods-row-delisted': g.is_out === 1 }"
        @click="goGoods(g)"
      >
        <div class="cover-box">
          <img class="cover" :src="g.listimg" :alt="g.name" />
        </div>
        <div class="info-col">
          <span class="goods-name">{{ g.name }}</span>
          <span class="goods-sub">发行{{ g.total_num }}/流通{{ g.flux }}</span>
        </div>
        <img
          class="fav-icon"
          :src="g.is_like ? '/static/qbox/market-favorited.png' : '/static/qbox/market-unfavorited.png'"
          alt=""
        />
        <div class="price-col">
          <span v-if="g.is_out === 1" class="price price-out">已退市</span>
          <span v-else class="price">{{ g.min_price }}</span>
        </div>
        <div class="volume-col">
          <span class="volume">{{ g.deal_num }}</span>
        </div>
      </div>
    </div>

    <!-- 空态（源站实测：列表容器 mt 28 min-height 480，居中，图为 284×284） -->
    <div v-else class="goods-list">
      <div class="empty">
        <img class="empty-img" src="/static/qbox/common-empty.png" alt="" />
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { request } from '../../api/request'

const router = useRouter()

/**
 * 原站 DOM 实测（pages-market-search，750 视口，2026-10-25）：
 *  - 根容器 .page-container：background-color rgb(248,248,249) + background-image
 *    /static/qbox/market-bg.png（no-repeat / size 100% / center top）；
 *    内层 wrapper padding 44px 36px 80px，flex column align center。
 *  - 搜索行 678×72，gap 16；返回图 24×24 rotate(180deg)；
 *    搜索框 flex1 h72 bg #f5f5f5 radius 36 padding 0 28px；图标 32×32；间距 12；输入 24px #4a4a4a。
 *  - tabs mt 28 gap 36，文字 29px MiSans-Semibold（active #000 / 默认 #5b5b5b），
 *    下划线 48×6 #0047ff radius 3 mt 8。
 *  - 表头 mt 42，字 23px/500 #919191；名称 flex1 / 地板价 160 / 成交量 120（右对齐）。
 *  - 列表 mt 28，行间距 24；行 678×136 padding 20/24 radius 24 bg #fff；
 *    行内间距 20；封面 96×96 r16；名称 26px/600 SourceHanSansCN-Bold #0c0c0c；
 *    副文 21px/400 #9a9a9a；收藏图标 32×32；价格与成交量 22px/600 MiSans-Semibold #323232。
 *  - 已退市行（is_out=1）：bg linear-gradient(0deg,#fdfdfd,#eee)，border 3px #fefefe，
 *    radius 19，box-shadow 0 1px 12px rgba(0,0,0,0)，高度 142，价格列文案「已退市」。
 *  - 空态：容器 min-height 480 居中，common-empty 284×284。
 *  - 本项目 CSS 直接书写源站 rpx 数值（1px → 100/750vw），故下列数值即 rpx。
 */
const tabs = [{ name: '数字藏品', goods_type: 2 }, { name: '盲盒', goods_type: 1 }]
const current = ref(0)
const keyword = ref('')
const list = ref([])

async function doSearch() {
  const res = await request('/api/market/market/getMarketList', { goods_type: tabs[current.value].goods_type, keywords: keyword.value || null })
  if (res.code === 1 && res.data) list.value = res.data
}
function switchTab(i) {
  current.value = i
  doSearch()
}

/**
 * 源站实测（2026-10-25 h5.xiyanwl.asia）：搜索页点击藏品行 →
 * marketGoodsList?id={藏品id}&goodsType={类型}&minPrice={地板价}
 */
function goGoods(g) {
  const q = 'id=' + g.id + '&goodsType=' + (g.goods_type ? Number(g.goods_type) : 2) + '&minPrice=' + (g.min_price ?? '')
  router.push('/pages/market/marketGoodsList?' + q)
}
doSearch()
</script>

<style scoped src="../../styles/pages/market-search.css"></style>
