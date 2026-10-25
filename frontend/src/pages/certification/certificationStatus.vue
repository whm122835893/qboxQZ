<template>
  <!-- 认证结果页（原站实测：标题 认证结果；本账号实测展示失败态） -->
  <div class="status-page">
    <div class="status-navbar">
      <img class="nav-back" src="/static/cw/back_btn.png" alt="返回" @click="goBack" />
      <span class="nav-title">认证结果</span>
    </div>

    <div class="status-body">
      <span class="status-title">{{ passed ? '实名认证成功' : '实名认证失败' }}</span>
      <span class="status-sub">{{ passed ? '您已完成实名认证' : '该身份信息认证失败，请重试' }}</span>
      <div class="retry-btn" @click="retry">
        <span class="retry-text">重新认证</span>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useUserStore } from '../../stores/user'

const router = useRouter()
const userStore = useUserStore()
// 原站实测：本账号展示失败态；成功态文案按同布局实现
const passed = ref(false)

function retry() {
  router.replace('/pages/certification/certification')
}
function goBack() {
  history.length > 1 ? router.back() : router.replace('/pages/my/my')
}

onMounted(async () => {
  if (userStore.token) await userStore.getUserInfo()
})
</script>

<style scoped>
.status-page {
  min-height: 100vh;
  background-color: #fff;
}
.status-navbar {
  position: relative;
  width: 100%;
  height: 44px;
  display: flex;
  align-items: center;
  justify-content: center;
}
.nav-back {
  position: absolute;
  left: 5px;
  top: 50%;
  transform: translateY(-50%);
  width: 27px;
  height: 27px;
}
.nav-title {
  position: absolute;
  left: 50%;
  top: 50%;
  transform: translate(-50%, -50%);
  font-size: 16px;
  font-weight: 700;
  color: #000;
  font-family: var(--font-bold);
}
.status-body {
  width: 100%;
  display: flex;
  flex-direction: column;
  align-items: center;
  /* 源站实测：uni-page-body 顶部 y=44，内容区 pt-40 → 标题基线区起点 y=168 */
  padding-top: 124px;
}
.status-title {
  margin-top: 0;
  /* 源站实测 fs=48px fw=500 c=#333，r=[231,168,288,70] */
  font-size: 48px;
  font-weight: 500;
  line-height: 70px;
  color: #333;
  font-family: var(--font-regular);
}
.status-sub {
  /* 源站实测 fs=28px c=#7a7a7a，r=[193,254,364,40]，与标题间距 16px */
  margin-top: 16px;
  font-size: 28px;
  line-height: 40px;
  color: #7a7a7a;
  font-family: var(--font-regular);
}
.retry-btn {
  /* 源站实测 r=[201,738,348,92] bg=#ff7800 br=46px fs=30px fw=600 */
  margin-top: 444px;
  width: 348px;
  height: 92px;
  background: #ff7800;
  border-radius: 46px;
  display: flex;
  align-items: center;
  justify-content: center;
}
.retry-text {
  font-weight: 600;
  font-size: 30px;
  color: #fff;
  font-family: var(--font-bold);
}
</style>
