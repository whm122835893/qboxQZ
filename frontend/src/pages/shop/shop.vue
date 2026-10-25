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

<style scoped src="../../styles/pages/shop-shop.css"></style>
