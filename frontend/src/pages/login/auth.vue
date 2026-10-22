<template>
  <div class="login-page">
    <div class="form-wrap">
      <!-- 原站实测：无表单，仅两个按钮 + 注册链接 + 协议行；点击跳登录页对应 tab -->
      <div class="btn-primary" @click="goLogin(1)"><span class="btn-text-white">手机号登录</span></div>
      <div class="btn-outline" @click="goLogin(2)"><span class="btn-outline-text">账号密码登录</span></div>

      <div class="register-link">没有账号？<span class="register-link-strong" @click="$router.push('/pages/login/register')">点击注册</span></div>

      <div class="agreement-row">
        <span class="agreement-check" :class="{ on: checked }" @click="checked = !checked">
          <svg v-if="checked" viewBox="0 0 24 24" width="14" height="14" fill="#fff"><path d="M9 16.2L4.8 12l-1.4 1.4L9 19 21 7l-1.4-1.4z"/></svg>
        </span>
        <span class="agreement-text">登录即同意 <span class="agreement-link">《用户协议》</span> 、 <span class="agreement-link">《隐私政策》</span></span>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'

/**
 * 原站截图实测（pages/login/auth 授权页）：无表单，手机号登录（蓝）/账号密码登录（白描边）/
 * 没有账号？点击注册 / 协议勾选行。按钮点击跳登录页对应 tab（chunk 含 loginType 变量；1=验证码 2=密码，按登录页 tab 语义推断）。
 */
const router = useRouter()
const checked = ref(true)

function goLogin(type) {
  if (!checked.value) return
  router.push('/pages/login/login?loginType=' + type)
}
</script>

<style scoped>
.login-page {
  position: relative;
  width: 100%;
  min-height: 100vh;
  background-color: #fff;
  background-image: url('/static/qbox/login-bg.png');
  background-repeat: no-repeat;
  background-size: 100% auto;
  background-position: center top;
  display: flex;
  flex-direction: column;
  overflow: hidden;
}
.form-wrap {
  position: relative;
  z-index: 2;
  display: flex;
  flex-direction: column;
  padding: 0 47.1px 99px;
  margin-top: 885.9px; /* 原站实测：按钮1 y489@414 */
}
.btn-primary {
  width: 100%;
  height: 99px;
  background: var(--qbox-primary);
  border-radius: 43.5px; /* 原站实测 r24@414 */
  display: flex;
  align-items: center;
  justify-content: center;
  box-sizing: border-box;
}
.btn-text-white {
  font-size: 32px;
  color: #fff;
  font-family: var(--font-medium);
}
.btn-outline {
  width: 100%;
  height: 99px;
  background: #fff;
  border: 2px solid #1a1a1a;
  border-radius: 43.5px;
  display: flex;
  align-items: center;
  justify-content: center;
  margin-top: 30.8px; /* 原站实测：按钮2 y560（间隔 17px@414） */
  box-sizing: border-box;
}
.btn-outline-text {
  color: #1a1a1a;
  font-size: 32px;
  font-family: var(--font-medium);
}
.register-link {
  margin-top: 30.8px; /* 原站实测：链接 y631 */
  text-align: center;
  font-size: 23.6px;
  color: #030207;
  font-family: var(--font-regular);
}
.register-link-strong {
  font-weight: 600;
}
.agreement-row {
  margin-top: 135.9px; /* 原站实测：协议行 y~726 */
  display: flex;
  align-items: center;
  justify-content: center;
}
.agreement-check {
  width: 30px;
  height: 30px;
  border-radius: 7px;
  border: 1.8px solid #d9d9d9;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  margin-right: 12.7px;
  background: #fff;
}
.agreement-check.on {
  background: #000;
  border-color: #000;
}
.agreement-text {
  font-size: 25.4px;
  color: #333;
  font-family: var(--font-regular);
}
.agreement-link {
  color: #0047ff;
}
</style>
