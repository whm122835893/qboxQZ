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
        <!-- 暂无抽奖活动气泡（lottery/info 失败时原站展示的深色 tooltip） -->
        <div v-if="showNoActivity" class="no-activity-tip">
          <span class="no-activity-text">暂无抽奖活动</span>
          <span class="no-activity-arrow"></span>
        </div>
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

const route = useRoute()
const router = useRouter()

const prizes = ref([])
const slots = ref([])
const remainCount = ref(0)
const showNoActivity = ref(false)
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
  // 实测：info 失败（暂无抽奖活动）→ 深色气泡提示 + 剩余次数 0
  const info = await request('/api/lottery/info', { id })
  if (info.code !== 1) {
    showNoActivity.value = true
    remainCount.value = 0
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

<style scoped>
.lottery-page {
  min-height: 100vh;
  background-color: #f7f7f7;
}
.lottery-head {
  position: relative;
  width: 100%;
  box-sizing: border-box;
  padding: 36.2px 34.4px 0;
  height: 123.4px;
  display: flex;
  align-items: center;
  justify-content: space-between;
}
.head-back {
  width: 23.6px;
  height: 23.6px;
  transform: rotate(180deg);
}
.head-title {
  position: absolute;
  left: 50%;
  top: 50%;
  margin-top: 18.1px;
  transform: translate(-50%, -50%);
  font-size: 30.8px;
  font-weight: 500;
  color: #000;
  font-family: var(--font-medium);
}
.record-pill {
  width: 126.8px;
  height: 47.1px;
  background: #fff;
  border-radius: 23.6px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 21.7px;
  color: #333;
  font-family: var(--font-regular);
  box-shadow: rgba(0, 0, 0, 0.04) 0 3.6px 10.9px;
}
.lottery-body {
  width: 100%;
  padding: 59.8px 33.5px 47.1px;
  box-sizing: border-box;
  display: flex;
  flex-direction: column;
  align-items: center;
}

/* ---- 本期开池蓝卡 ---- */
.pool-card {
  position: relative;
  width: 683px;
  height: 224.6px;
  background-image: url('/static/qbox/lottery-bg.png');
  background-repeat: no-repeat;
  background-size: 683px 224.6px;
  background-position: center center;
  border-radius: 30.8px;
  overflow: hidden;
}
.pool-left {
  padding: 92.4px 29.9px 0;
  display: flex;
  flex-direction: column;
}
.pool-slots {
  margin-top: 14.5px;
  display: flex;
  gap: 7.2px;
  border: 3.6px solid rgba(255, 255, 255, 0.85);
  border-radius: 12.7px;
  padding: 5.4px;
  width: fit-content;
}
.pool-slot {
  width: 77.9px;
  height: 77.9px;
  background: #0e0f12;
  border-radius: 5.4px;
  flex-shrink: 0;
  overflow: hidden;
  display: flex;
  align-items: center;
  justify-content: center;
}
.pool-slot-img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}
.pool-all-btn {
  position: absolute;
  right: 21.7px;
  bottom: 16.3px;
  height: 43.5px;
  padding: 0 16.3px;
  background: #fff;
  border-radius: 21.7px;
  display: flex;
  align-items: center;
  gap: 5.4px;
  font-size: 21.7px;
  color: #111;
  font-family: var(--font-medium);
}
.pool-all-arrow {
  width: 12.7px;
  height: 12.7px;
}

/* ---- 当前奖池白卡 ---- */
.cur-card {
  width: 683px;
  margin-top: 34px;
  background: #fff;
  border-radius: 30.8px;
  padding: 27.2px 23.6px;
  box-sizing: border-box;
  display: flex;
  flex-direction: column;
}
.cur-head {
  display: flex;
  align-items: center;
  justify-content: space-between;
}
.cur-title {
  font-weight: bold;
  font-size: 30.8px;
  color: #111;
  font-family: var(--font-bold);
}
.cur-all {
  display: flex;
  align-items: center;
  gap: 5.4px;
  font-size: 21.7px;
  color: #333;
  font-family: var(--font-regular);
}
.cur-all-arrow {
  width: 12.7px;
  height: 12.7px;
  opacity: 0.4;
}
.cur-prizes {
  margin-top: 14.5px;
  display: flex;
  flex-direction: column;
}
.cur-prize {
  display: flex;
  flex-direction: column;
  align-items: center;
}
.cur-prize-img {
  width: 160px;
  height: 160px;
  border-radius: 14.5px;
  background: #f8f8f8;
  object-fit: cover;
}
.cur-prize-name {
  margin-top: 13px;
  font-size: 23.6px;
  color: #111;
  font-family: var(--font-regular);
}

/* ---- 剩余次数 ---- */
.remain-row {
  width: 683px;
  margin-top: 110px;
}
.remain-text {
  font-size: 21.7px;
  color: #8c8c8c;
  font-family: var(--font-regular);
}

/* ---- 抽奖按钮行 ---- */
.btn-row {
  position: relative;
  width: 683px;
  margin-top: 14px;
  display: flex;
  gap: 21.7px;
}
.btn-five {
  flex: 1.15;
  height: 79.7px;
  background: #0047ff;
  border-radius: 14.5px;
  display: flex;
  flex-direction: row;
  align-items: center;
  justify-content: center;
  gap: 8px;
}
.btn-five-title {
  font-weight: bold;
  font-size: 27.2px;
  color: #fff;
  font-family: var(--font-bold);
}
.btn-five-sub {
  margin-top: 0;
  font-size: 19.9px;
  color: rgba(255, 255, 255, 0.8);
  font-family: var(--font-regular);
}
.btn-one {
  flex: 1;
  height: 79.7px;
  background: #fff;
  border: 1.8px solid #0047ff;
  border-radius: 14.5px;
  display: flex;
  flex-direction: row;
  align-items: center;
  justify-content: center;
  gap: 8px;
}
.btn-one-title {
  font-weight: bold;
  font-size: 27.2px;
  color: #0047ff;
  font-family: var(--font-bold);
}
.btn-one-sub {
  margin-top: 0;
  font-size: 19.9px;
  color: #0047ff;
  opacity: 0.8;
  font-family: var(--font-regular);
}
/* 暂无抽奖活动气泡（深色 tooltip + 下箭头） */
.no-activity-tip {
  position: absolute;
  left: 50%;
  top: -50px;
  transform: translateX(-50%);
  display: flex;
  flex-direction: column;
  align-items: center;
  z-index: 5;
}
.no-activity-text {
  background: rgba(31, 31, 31, 0.9);
  color: #fff;
  font-size: 23.6px;
  font-family: var(--font-regular);
  padding: 12.7px 21.7px;
  border-radius: 10.9px;
  white-space: nowrap;
}
.no-activity-arrow {
  width: 0;
  height: 0;
  border-left: 7.2px solid transparent;
  border-right: 7.2px solid transparent;
  border-top: 9.1px solid rgba(31, 31, 31, 0.9);
}

/* ---- 抽奖规则卡 ---- */
.rule-card {
  width: 683px;
  margin-top: 39px;
  background: #fff;
  border-radius: 30.8px;
  padding: 32.6px 23.6px;
  box-sizing: border-box;
}
.rule-title {
  font-weight: bold;
  font-size: 30.8px;
  color: #111;
  font-family: var(--font-bold);
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
