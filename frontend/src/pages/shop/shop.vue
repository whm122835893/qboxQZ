<template>
  <div class="shop-page">
    <!-- 导航栏 -->
    <div class="shop-navbar">
      <img class="nav-back" src="/static/cw/back_btn.png" alt="返回" @click="goBack" />
      <span class="nav-title">积分兑换</span>
    </div>

    <div class="shop-container">
      <!-- 可用积分 -->
      <div class="score-col">
        <span class="score-label">可用积分</span>
        <div class="score-row">
          <span class="score-num">{{ score }}</span>
          <div class="score-view">
            <span class="score-view-text">查看</span>
            <img class="score-view-arrow" src="/static/qbox/common-arrow-right-black.png" alt="" />
          </div>
        </div>
      </div>

      <!-- 积分兑换藏品卡 -->
      <div class="collection-card">
        <div class="card-head">
          <div class="card-side">
            <span class="card-side-text">Q</span>
            <span class="card-side-text">BOX</span>
          </div>
          <img class="card-title-img" src="/static/qbox/points-title.png" alt="积分兑换藏品" />
          <span class="card-side-right">QBOX</span>
        </div>
        <div class="card-body">
          <!-- 原站实测：列表为空时保持"加载中"状态（u-loading-icon），如实复刻 -->
          <div v-if="goods.length === 0 && loading" class="loading-row">
            <span class="loading-spinner"></span>
            <span class="loading-text">加载中</span>
          </div>
          <template v-else>
            <div v-for="(g, i) in goods" :key="i" class="goods-item">{{ g.name }}</div>
          </template>
        </div>
      </div>

      <!-- 积分兑换实物 -->
      <div class="physical-section">
        <img class="physical-title-img" src="/static/qbox/points-physical-title.png" alt="积分兑换实物" />
        <div class="physical-empty">
          <img class="physical-empty-img" src="/static/qbox/common-empty.png" alt="" />
        </div>
      </div>
    </div>

    <!-- 回到顶部 -->
    <img
      v-show="showTotop"
      class="totop"
      src="/static/mescroll-totop.png"
      alt="回到顶部"
      @click="scrollTop"
    />
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import { useRouter } from 'vue-router'
import { request } from '../../api/request'

const router = useRouter()
const goods = ref([])
// 可用积分：原站实测显示 0（getUserInfo.score=1 但页面显示 0，说明积分另有数据源未在抓包中出现，默认 0）
const score = ref(0)
const loading = ref(true)
const showTotop = ref(false)

function goBack() {
  history.length > 1 ? router.back() : router.replace('/pages/index/index')
}
function onWinScroll() {
  showTotop.value = window.scrollY > 200
}
function scrollTop() {
  window.scrollTo({ top: 0, behavior: 'smooth' })
}

onMounted(async () => {
  // 实测请求：{"type":"30","page":1,"crystal_type":""}
  const res = await request('/api/crystal_goods/list', { type: '30', page: 1, crystal_type: '' })
  if (res.code === 1 && Array.isArray(res.data?.data)) {
    goods.value = res.data.data
  }
  loading.value = true // 原站行为：列表为空时保持加载中状态
  window.addEventListener('scroll', onWinScroll, { passive: true })
})
onUnmounted(() => {
  window.removeEventListener('scroll', onWinScroll)
})
</script>

<style scoped>
.shop-page {
  min-height: 100vh;
  background-color: #f7f7f7;
  background-image: url('/static/qbox/points-bg.png');
  background-repeat: no-repeat;
  background-size: 100% auto;
  background-position: center top;
}
.shop-navbar {
  position: relative;
  width: 100%;
  height: 79.7px;
  display: flex;
  align-items: center;
  z-index: 5;
}
.nav-back {
  width: 16.3px;
  height: 29px;
  margin-left: 27.2px;
}
.nav-title {
  position: absolute;
  left: 50%;
  top: 50%;
  transform: translate(-50%, -50%);
  font-size: 32.6px;
  font-weight: 700;
  color: #000;
  font-family: var(--font-bold);
}
.shop-container {
  width: 100%;
  display: flex;
  flex-direction: column;
  align-items: center;
  padding: 188.7px 34.4px 79.7px;
  box-sizing: border-box;
}

/* ---- 可用积分 ---- */
.score-col {
  width: 677.5px;
  display: flex;
  flex-direction: column;
  gap: 14.5px;
}
.score-label {
  font-weight: bold;
  font-size: 25.4px;
  color: #686868;
  font-family: var(--font-bold);
}
.score-row {
  display: flex;
  align-items: flex-end;
  gap: 7.2px;
}
.score-num {
  font-weight: normal;
  font-size: 58px;
  color: #000;
  line-height: 1;
  font-family: 'AlimamaFangYuanTiVF-Thin', var(--font-regular);
}
.score-view {
  display: flex;
  align-items: center;
  gap: 4px;
  padding-bottom: 5.4px;
}
.score-view-text {
  font-size: 23.6px;
  color: #8c8c8c;
  font-family: var(--font-regular);
}
.score-view-arrow {
  width: 19.9px;
  height: 19.9px;
}

/* ---- 积分兑换藏品卡 ---- */
.collection-card {
  width: 677.5px;
  background: #fff;
  border-radius: 14.5px;
  padding: 45.3px 27.2px 32.6px;
  box-sizing: border-box;
  margin-top: 14.5px;
}
.card-head {
  position: relative;
  width: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
}
.card-side {
  position: absolute;
  left: 0;
  top: 50%;
  transform: translateY(-50%);
  display: flex;
  flex-direction: column;
  gap: 3.6px;
}
.card-side-text {
  font-weight: bold;
  font-size: 18.1px;
  color: #686868;
  line-height: 1.1;
  font-family: var(--font-bold);
}
.card-title-img {
  height: 39.9px;
  position: relative;
  z-index: 1;
  width: 271.7px;
}
.card-side-right {
  position: absolute;
  right: 0;
  top: 50%;
  transform: translateY(-50%);
  font-family: 'NanoByongGyeHei-Regular', var(--font-regular);
  font-size: 72.5px;
  color: #686868;
}
.card-body {
  padding-top: 57.6px;
  display: flex;
  flex-direction: column;
  align-items: center;
  min-height: 90px;
}
.loading-row {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 9.1px;
}
.loading-spinner {
  width: 25.4px;
  height: 25.4px;
  border-radius: 50%;
  border: 3.6px solid #d6d6d6;
  border-top-color: #333;
  animation: spin 0.8s linear infinite;
}
@keyframes spin {
  to { transform: rotate(360deg); }
}
.loading-text {
  font-size: 25.4px;
  color: #333;
  font-family: var(--font-regular);
}
.goods-item {
  width: 100%;
  margin-bottom: 21.7px;
  background: #f8f8f8;
  border-radius: 14.5px;
  padding: 21.7px;
  font-size: 25.4px;
  color: #111;
  font-family: var(--font-medium);
}

/* ---- 积分兑换实物 ---- */
.physical-section {
  width: 677.5px;
  display: flex;
  flex-direction: column;
}
.physical-title-img {
  height: 39.9px;
  margin-top: 39.9px;
  width: 275.4px;
}
.physical-empty {
  width: 100%;
  min-height: 478.2px;
  display: flex;
  align-items: center;
  justify-content: center;
}
.physical-empty-img {
  width: 282.6px;
  height: 282.6px;
  /* 原站 uni-image 实测：156×156 容器 + background-size:contain → 图等比缩放为 106.7×156 居中显示；
     object-fit:contain 与其完全等价（此前 fill 压扁 / height:auto 放大 均有偏差，已按实测修正） */
  object-fit: contain;
}

/* ---- 回到顶部 ---- */
.totop {
  position: fixed;
  right: 19.9px;
  bottom: 119.6px;
  width: 70.7px;
  height: 72.5px;
  border-radius: 50%;
  z-index: 9990;
}
</style>
