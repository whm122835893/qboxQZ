<template>
  <!-- 启动页（pages/index/lanuch）：首页启动引导层；1 秒后自动进入首页（原站「跳过 1s」倒计时） -->
  <div class="lc-page" @click="goHome">
    <!-- 跳过胶囊（13px 白字，右上） -->
    <div class="skip-capsule" @click.stop="goHome">跳过 {{ seconds }}s</div>

    <!-- 中央 logo（QBOX 蓝盒） -->
    <img class="lc-logo" src="/static/logo/logo.png" alt="QBOX" />

    <!-- 底部标语 -->
    <span class="lc-slogan">属GBOX｜懂数字</span>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import { useRouter } from 'vue-router'

/**
 * 原站 DOM 实测（2026-10-23）：「跳过 1s」13px #fff；背景蓝紫渐变（home-bg 系）；
 * 中央 QBOX 蓝盒 logo；底部「属GBOX｜懂数字」；倒计时结束/点击 → 进入首页
 */
const router = useRouter()
const seconds = ref(1)
let timer = null

function goHome() {
  if (timer) clearInterval(timer)
  router.replace('/pages/index/index')
}

onMounted(() => {
  timer = setInterval(() => {
    seconds.value -= 1
    if (seconds.value <= 0) goHome()
  }, 1000)
})
onUnmounted(() => { if (timer) clearInterval(timer) })
</script>

<style scoped>
.lc-page {
  position: relative;
  min-height: 100vh;
  background: linear-gradient(180deg, #eef1fc 0%, #dbe3f8 45%, #c8d4f2 100%);
  display: flex;
  flex-direction: column;
  align-items: center;
  overflow: hidden;
}
.skip-capsule {
  position: absolute;
  right: 19px;
  top: 60px;
  padding: 7px 16px;
  background: rgba(0, 0, 0, 0.25);
  border-radius: 22px;
  font-size: 23.6px;
  color: #fff;
  z-index: 2;
}
.lc-logo {
  margin-top: 425px;
  width: 290px;
  height: 290px;
  object-fit: contain;
}
.lc-slogan {
  position: absolute;
  bottom: 22px;
  font-size: 23.6px;
  color: #9aa7d0;
}
</style>
