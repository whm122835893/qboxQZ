<template>
  <!-- 启动页（pages/index/lanuch）
       源站 DOM 实测（750 视口，2026-10-25）：
       - 根容器 position relative / width 100% / height 100vh，
         background-image url(/static/qbox/splash-bg.png)（no-repeat / size 100% 100% / center center）
         —— logo 与底部标语「潮启QBOX｜藏定不凡」均烘焙在该背景图内
       - 跳过胶囊：absolute top 120 right 40，bg rgba(0,0,0,.3)，radius 30，padding 10 24，
         文案「跳过 Ns」24px SourceHanSansCN-Regular #fff -->
  <div class="lc-page" @click="goHome">
    <div class="skip-capsule" @click.stop="goHome">跳过 {{ seconds }}s</div>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import { useRouter } from 'vue-router'

/**
 * 源站 DOM 实测（2026-10-25，750 视口）：
 * 根容器：position relative；width 100%；height 100vh；
 *   background-image url("/static/qbox/splash-bg.png")，no-repeat / size 100% 100% / center center。
 *   —— QBOX 蓝盒 logo 与底部标语「潮启QBOX｜藏定不凡」均已烘焙在 splash-bg.png 内，无需额外 DOM。
 * 跳过胶囊：position absolute；top 120px；right 40px；background-color rgba(0,0,0,.3)；
 *   border-radius 30px；padding 10px 24px；flex center；
 *   文本 24px / SourceHanSansCN-Regular / #fff，内容「跳过 2s」（倒计时递减）。
 */
const router = useRouter()
const seconds = ref(2)
let timer = null

function goHome() {
  if (timer) clearInterval(timer)
  timer = null
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
  width: 100%;
  height: 100vh;
  overflow: hidden;
  background-image: url('/static/qbox/splash-bg.png');
  background-repeat: no-repeat;
  background-size: 100% 100%;
  background-position: center center;
}
.skip-capsule {
  position: absolute;
  top: 120px;
  right: 40px;
  background-color: rgba(0, 0, 0, 0.3);
  border-radius: 30px;
  padding: 10px 24px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 24px;
  color: #fff;
  font-family: var(--font-regular);
}
</style>
