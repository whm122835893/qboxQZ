<template>
  <div class="lottery-page">
    <!-- 导航行：返回 + 居中标题 + 右侧抽奖记录胶囊（原站实测 70×26） -->
    <div class="lottery-head">
      <img class="head-back" src="/static/qbox/common-arrow-right-black.png" alt="返回" @click="goBack" />
      <span class="head-title">抽奖活动</span>
      <span class="record-pill" @click="goRecord">抽奖记录</span>
    </div>

    <div class="lottery-body">
      <!-- 本期开池：lottery-bg.png 蓝色卡（原站 377×124 r17） -->
      <div class="pool-card">
        <!-- 本期开池/积分文案烙在 lottery-bg.png 背景图中（原站 DOM 无这些文字节点） -->
        <div class="pool-left">
          <div class="pool-slots">
            <div v-for="i in 4" :key="i" class="pool-slot">
              <img v-if="slots[i - 1]" class="pool-slot-img" :src="slots[i - 1]" alt="" />
            </div>
          </div>
        </div>
        <span class="pool-all-btn">
          全部奖品
          <img class="pool-all-arrow" src="/static/qbox/common-arrow-right-black.png" alt="" />
        </span>
      </div>

      <!-- 当前奖池白卡（377×173 r17 padding 15px 13px） -->
      <div class="cur-card">
        <div class="cur-head">
          <span class="cur-title">当前奖池</span>
          <span class="cur-all">
            全部奖品
            <img class="cur-all-arrow" src="/static/qbox/common-arrow-right-black.png" alt="" />
          </span>
        </div>
        <div class="cur-prizes">
          <div v-for="(p, i) in prizes" :key="i" class="cur-prize">
            <img class="cur-prize-img" :src="p.image" alt="" />
            <span class="cur-prize-name">{{ p.name }}</span>
          </div>
          <!-- 源站实测（2026-10-25）：奖池为空时展示 common-empty.png 怪物图 284×284，
               图中自带「这里空空的!」文字；上方叠一层居中深色 tooltip「暂无抽奖活动」 -->
          <div v-if="!prizes.length" class="cur-empty">
            <img class="cur-empty-img" src="/static/qbox/common-empty.png" alt="" />
          </div>
        </div>
      </div>

      <!-- 剩余次数 -->
      <div class="remain-row">
        <span class="remain-text">剩余抽奖次数 {{ remainCount }}</span>
      </div>

      <!-- 抽奖按钮行 -->
      <div class="btn-row">
        <div class="btn-five" @click="onDraw">
          <span class="btn-five-title">抽奖五次</span>
          <span class="btn-five-sub">(消耗五次抽奖机会)</span>
        </div>
        <div class="btn-one" @click="onDraw">
          <span class="btn-one-title">抽奖一次</span>
          <span class="btn-one-sub">(消耗一次抽奖机会)</span>
        </div>
        <!-- 源站实测（2026-10-25 登录态）：info 失败时页面不展示「暂无抽奖活动」气泡，
             仅保留剩余次数 0 与抽奖按钮，故此处不渲染气泡 -->
      </div>

      <!-- 抽奖规则卡 -->
      <div class="rule-card">
        <span class="rule-title">抽奖规则</span>
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
import { useRoute, useRouter } from 'vue-router'
import { request } from '../../api/request'
import { centerToast } from '../../utils/toast'

const route = useRoute()
const router = useRouter()

const prizes = ref([])
const slots = ref([])
const remainCount = ref(0)
const showTotop = ref(false)

function goBack() {
  history.length > 1 ? router.back() : router.replace('/pages/index/index')
}
function goRecord() {
  router.push('/pages/lottery/record')
}
function onDraw() {
  // 原站实测：无抽奖活动时点击未观察到行为，保持无操作
}
function onWinScroll() {
  showTotop.value = window.scrollY > 200
}
function scrollTop() {
  window.scrollTo({ top: 0, behavior: 'smooth' })
}

onMounted(async () => {
  const id = route.query.id || '1'
  // 源站实测（2026-10-25）：info 失败时弹出居中 uni-toast「暂无抽奖活动」，剩余次数归 0
  const info = await request('/api/lottery/info', { id })
  if (info.code !== 1) {
    remainCount.value = 0
    centerToast('暂无抽奖活动')
  }
  // 实测：statistics 返回奖池奖品列表
  const stat = await request('/api/lottery/statistics', { lottery_id: id })
  if (stat.code === 1 && Array.isArray(stat.data)) {
    prizes.value = stat.data.map(p => ({ name: p.name, image: p.image }))
    // 本期开池 4 个奖槽：取前 4 个奖品的 lottery_image（实测只有 1 个奖品，其余为黑色占位）
    slots.value = stat.data.slice(0, 4).map(p => p.lottery_image).filter(Boolean)
  }
  window.addEventListener('scroll', onWinScroll, { passive: true })
})
onUnmounted(() => {
  window.removeEventListener('scroll', onWinScroll)
})
</script>

<style scoped src="../../styles/pages/lottery-lottery-p0.css"></style>
<style scoped src="../../styles/pages/lottery-lottery-p1.css"></style>
