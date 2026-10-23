<template>
  <div class="bb-page">
    <!-- 导航栏（原站 w-navbar 默认白底黑字） -->
    <div class="navbar">
      <img class="navbar-back" src="/static/cw/back_btn.png" alt="返回" @click="$router.back()" />
      <span class="nav-title">我的盲盒</span>
    </div>

    <!-- 内容区（原站 .content 相对定位，.con 绝对居中） -->
    <div class="content">
      <div class="con">
        <div class="listimg">
          <img v-if="info.listimg" class="l-img" :src="info.listimg" alt="" />
        </div>
        <div class="num-view"><span>共有{{ info.num }}个</span></div>
        <div class="open-btn" @click="onOpenAll"><span class="open-btn-text">一键开启</span></div>
        <span class="open-hint">一次最多打开10个盲盒</span>
      </div>
    </div>

    <!-- 拆盒全屏黑幕（原站 pop_con：100vw×100vh 纯黑，5 秒后跳结果页） -->
    <div v-if="showPop" class="pop-con"></div>

    <!-- 底部按钮栏（原站 bg-btns ptb-10 plr-20，u-safe-bottom） -->
    <div class="bg-btns">
      <div class="btns-row">
        <div class="bb-btn bb-btn-plain" @click="goConsign"><span class="bb-btn-text">立即寄售</span></div>
        <div class="bb-btn bb-btn-primary" @click="onTakeApart"><span class="bb-btn-text bb-btn-text-white">逐个拆开</span></div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useRouter } from 'vue-router'
import { request } from '../../api/request'
import { toast } from '../../utils/toast'

/**
 * 原站 chunk 反解（pages-blindBox-index 模块 965c）：
 *  - info = uni.getStorageSync('blindBoxInfo')（由收藏页盲盒入口写入：blindBoxId/listimg/num/id）
 *  - open(): openBoxs(info.blindBoxId) → storage('blindBoxResultList') → redirectTo result
 *  - takeApart(): openBox(info.blindBoxId) → 全屏黑幕 5s → storage('blindBoxResult') → redirectTo result2
 *  - 立即寄售 → /pages/consignments/consignments {id: info.id, pageType:1}
 * 直接访问无 storage 时即原站观测空态（图片空 + 「共有个」+ 按钮）。
 */
const router = useRouter()
const showPop = ref(false)

const info = computed(() => {
  try {
    return JSON.parse(localStorage.getItem('blindBoxInfo') || '{}')
  } catch (e) {
    return {}
  }
})

async function onOpenAll() {
  await request('/api/user_collection/user_collection/openBoxs', { openid: info.value.blindBoxId })
  localStorage.setItem('blindBoxResultList', JSON.stringify(null))
  router.replace('/pages/blindBox/result')
}

async function onTakeApart() {
  await request('/api/user_collection/user_collection/openbox', { openid: info.value.blindBoxId })
  localStorage.setItem('blindBoxResult', JSON.stringify(null))
  showPop.value = true
  setTimeout(() => {
    showPop.value = false
    router.replace('/pages/blindBox/result2')
  }, 5000)
}

function goConsign() {
  router.push('/pages/consignments/consignments')
}
</script>

<style scoped>
.bb-page {
  min-height: 100vh;
  background: #fff;
  display: flex;
  flex-direction: column;
}
/* ---- 导航栏 ---- */
.navbar {
  position: relative;
  width: 100%;
  height: 79.7px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}
.navbar-back {
  position: absolute;
  left: 23.6px;
  top: 50%;
  transform: translateY(-50%);
  width: 17px;
  height: auto;
}
.nav-title {
  font-size: 32.6px;
  font-weight: 700;
  color: #000;
  font-family: var(--font-bold);
}

/* ---- 内容区（.con 绝对居中，与原站一致） ---- */
.content {
  position: relative;
  flex: 1;
}
.con {
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  display: flex;
  flex-direction: column;
  align-items: center;
}
.listimg {
  width: 600px;
  height: 372px;
  margin-top: 500px;
  display: flex;
  justify-content: center;
  align-items: center;
}
.l-img {
  position: relative;
  top: -400px;
  width: 200px;
  height: 200px;
  border-radius: 10px;
  object-fit: cover;
}
.num-view {
  width: 330px;
  height: 54px;
  display: flex;
  justify-content: center;
  align-items: center;
  font-size: 24px;
  font-weight: 500;
  color: #333;
  font-family: var(--font-medium);
}
.open-btn {
  margin-top: 32px;
  width: 500px;
  height: 88px;
  background: #ff7800;
  border-radius: 44px;
  display: flex;
  align-items: center;
  justify-content: center;
}
.open-btn-text {
  font-size: 30px;
  color: #fff;
  font-family: var(--font-medium);
}
.open-hint {
  margin-top: 16px;
  font-size: 24px;
  color: #000;
  font-family: var(--font-regular);
}

/* ---- 拆盒黑幕 ---- */
.pop-con {
  position: fixed;
  inset: 0;
  background: #000;
  z-index: 999;
}

/* ---- 底部按钮 ---- */
.bg-btns {
  padding: 20px 0;
  background: #fff;
}
.btns-row {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 0 40px;
}
.bb-btn {
  width: 320px;
  height: 92px;
  border-radius: 50px;
  display: flex;
  align-items: center;
  justify-content: center;
}
.bb-btn-plain {
  background: #f1f0f0;
}
.bb-btn-primary {
  background: #ff7800;
}
.bb-btn-text {
  font-size: 30px;
  color: #000;
  font-family: var(--font-medium);
}
.bb-btn-text-white {
  color: #fff;
}
</style>
