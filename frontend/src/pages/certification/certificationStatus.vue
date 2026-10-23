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
  height: 79.7px;
  display: flex;
  align-items: center;
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
.status-body {
  width: 100%;
  display: flex;
  flex-direction: column;
  align-items: center;
  padding-top: 172px;
}
.status-title {
  margin-top: 0;
  font-size: 47.1px;
  color: #000;
  font-family: var(--font-regular);
}
.status-sub {
  margin-top: 18.1px;
  font-size: 27.2px;
  color: #999;
  font-family: var(--font-regular);
}
.retry-btn {
  margin-top: 515px;
  width: 327.9px;
  height: 79.7px;
  background: #ff7800;
  border-radius: 39.9px;
  display: flex;
  align-items: center;
  justify-content: center;
}
.retry-text {
  font-weight: 600;
  font-size: 29px;
  color: #fff;
  font-family: var(--font-bold);
}
</style>
