<template>
  <div class="compound-page">
    <!-- 头部导航（原站实测：pt20 + h48 行，返回箭头为 common-arrow-right-black 旋转180°） -->
    <div class="compound-head">
      <img class="head-back" src="/static/qbox/common-arrow-right-black.png" alt="返回" @click="goBack" />
      <span class="head-title">合成活动</span>
      <span class="head-placeholder"></span>
    </div>

    <!-- 内容区 -->
    <div class="compound-body">
      <div class="compound-inner">
        <!-- 主题活动列表（themeinfo play 数组）；实测当前主题 play=[] 渲染黑猫空态 -->
        <template v-if="schemes.length > 0">
          <div v-for="(p, i) in schemes" :key="i" class="scheme-card">
            <span class="scheme-name">{{ p.name || '合成方案' }}</span>
          </div>
        </template>
        <div v-else class="empty-box">
          <img class="empty-img" src="/static/qbox/common-empty.png" alt="" />
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

    <!-- 原站实测：themeinfo 返回 code=0 时 uni.showToast 弹出「未开启」（uni-simple-toast，居中） -->
    <div v-if="showNotOpen" class="uni-simple-toast">
      <p class="uni-simple-toast__text">未开启</p>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { request } from '../../api/request'

const route = useRoute()
const router = useRouter()
const schemes = ref([])
const showTotop = ref(false)
const showNotOpen = ref(false)

function goBack() {
  history.length > 1 ? router.back() : router.replace('/pages/compound/task')
}

function onWinScroll() {
  showTotop.value = window.scrollY > 200
}
function scrollTop() {
  window.scrollTo({ top: 0, behavior: 'smooth' })
}

onMounted(async () => {
  const thm = route.query.thm || 0
  const res = await request('/api/synthesis/themeinfo', { thm })
  // 原站实测：接口返回 {code:0, msg:'未开启'} → 弹出 uni.showToast「未开启」，
  // 页面仍渲染黑猫空态（play 为空）
  if (res.code === 1 && Array.isArray(res.data?.play)) {
    schemes.value = res.data.play
  } else {
    showNotOpen.value = true
    setTimeout(() => { showNotOpen.value = false }, 2000)
  }
  window.addEventListener('scroll', onWinScroll, { passive: true })
})
onUnmounted(() => {
  window.removeEventListener('scroll', onWinScroll)
})
</script>

<style scoped>
.compound-page {
  min-height: 100vh;
  background-color: #f7f7f7;
}
/* 导航行：pt20px + h48px，返回箭头 13px 旋转180°，标题居中（原站实测） */
.compound-head {
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
  color: #111;
  font-family: var(--font-bold);
  white-space: nowrap;
}
.head-placeholder {
  width: 23.6px;
  height: 23.6px;
}
.compound-body {
  width: 100%;
  padding: 7.2px 32.6px 47.1px;
  box-sizing: border-box;
  display: flex;
  flex-direction: column;
  align-items: center;
}
.compound-inner {
  width: 683px;
  margin-top: 30.8px;
  display: flex;
  flex-direction: column;
}
.empty-box {
  width: 100%;
  min-height: 478.2px;
  display: flex;
  align-items: center;
  justify-content: center;
}
.empty-img {
  width: 282.6px;
  height: 282.6px;
  /* 原站 uni-image 实测：156×156 容器 + background-size:contain → 图等比缩放为 106.7×156 居中显示；
     object-fit:contain 与其完全等价（此前 fill 压扁 / height:auto 放大 均有偏差，已按实测修正） */
  object-fit: contain;
}
.scheme-card {
  background: #fff;
  border-radius: 25.4px;
  padding: 27.2px;
  margin-bottom: 21.7px;
  font-size: 30.8px;
  color: #111;
  font-family: var(--font-medium);
}
.totop {
  position: fixed;
  right: 19.9px;
  bottom: 119.6px;
  width: 70.7px;
  height: 72.5px;
  border-radius: 50%;
  z-index: 9990;
}
/* 原站 uni-simple-toast：居中深色胶囊，白字 14px，1.6s 后自动消失 */
.uni-simple-toast {
  position: fixed;
  top: 45%;
  left: 50%;
  transform: translateX(-50%);
  z-index: 9999;
  background: rgba(0, 0, 0, 0.7);
  border-radius: 6px;
  padding: 10px 16px;
  max-width: 70%;
}
.uni-simple-toast__text {
  margin: 0;
  font-size: 14px;
  line-height: 20px;
  color: #fff;
  font-family: var(--font-regular);
  text-align: center;
  white-space: nowrap;
}
</style>

