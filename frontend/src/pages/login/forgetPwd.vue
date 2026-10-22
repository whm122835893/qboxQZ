<template>
  <div class="login-page">
    <div class="login-navbar">
      <img class="navbar-back" src="/static/cw/back_btn.png" alt="" @click="$router.back()" />
    </div>

    <div class="form-wrap">
      <div class="ipt-row">
        <span class="ipt-label">手机号</span>
        <span class="ipt-div"></span>
        <input v-model="form.mobile" class="ipt-input" type="tel" maxlength="11" placeholder="请输入手机号" />
      </div>
      <div class="ipt-row">
        <span class="ipt-label">验证码</span>
        <span class="ipt-div"></span>
        <input v-model="form.code" class="ipt-input" type="number" maxlength="6" placeholder="请输入验证码" />
        <span class="get-code" @click="getCode">{{ countDown > 0 ? `重新发送 ${countDown}s` : '获取验证码' }}</span>
      </div>
      <div class="ipt-row">
        <span class="ipt-label">新密码</span>
        <span class="ipt-div"></span>
        <input v-model="form.password" class="ipt-input" type="password" placeholder="请输入登录密码" />
      </div>
      <div class="ipt-row">
        <span class="ipt-label">确认密码</span>
        <span class="ipt-div"></span>
        <input v-model="form.confirm" class="ipt-input" type="password" placeholder="请确认登录密码" />
      </div>

      <div class="btn-primary" @click="submit">确认</div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import { useRouter } from 'vue-router'
import { request } from '../../api/request'
import { toast } from '../../utils/toast'

/**
 * 原站 chunk 反解（pages-login-forgetPwd）：
 *  - 短信 event:"resetpwd"；提交 POST /api/user/resetpwd {mobile, captcha, password}（字段按接口语义，chunk 无显式字段名，标注推断）
 *  - 成功跳 /pages/login/login?loginType=1
 */
const router = useRouter()
const form = ref({ mobile: '', code: '', password: '', confirm: '' })
const countDown = ref(0)
let timer = null

function startCountDown() {
  countDown.value = 60
  timer = setInterval(() => {
    countDown.value--
    if (countDown.value <= 0) clearInterval(timer)
  }, 1000)
}

async function getCode() {
  if (countDown.value > 0) return
  const mobile = String(form.value.mobile ?? '').trim()
  if (!mobile) return toast('请输入手机号')
  if (mobile.length !== 11) return toast('请输入11位手机号')
  await request('/api/sms/send', { mobile, event: 'resetpwd' })
  toast('发送成功')
  startCountDown()
}

async function submit() {
  const mobile = String(form.value.mobile ?? '').trim()
  if (!mobile) return toast('请输入手机号')
  if (!String(form.value.code ?? '')) return toast('请输入验证码')
  const pwd = String(form.value.password ?? '')
  if (!pwd) return toast('请输入登录密码')
  if (pwd !== String(form.value.confirm ?? '')) return toast('请确认登录密码')
  const res = await request('/api/user/resetpwd', {
    mobile,
    captcha: String(form.value.code),
    password: pwd
  })
  if (res.code === 1) {
    toast(res.msg || '重置成功')
    setTimeout(() => router.push('/pages/login/login?loginType=1'), 1200)
  }
}

onUnmounted(() => timer && clearInterval(timer))
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
.login-navbar {
  position: fixed;
  left: 0;
  top: 0;
  z-index: 2;
  width: 100%;
  height: 79.7px;
  display: flex;
  align-items: center;
  padding: 0 23.6px;
  background: transparent;
}
.navbar-back {
  width: 16.3px;
  height: 29px;
  object-fit: contain;
}
.form-wrap {
  position: relative;
  z-index: 2;
  display: flex;
  flex-direction: column;
  padding: 0 47.1px 99px;
  margin-top: 619.8px;
}
.ipt-row {
  display: flex;
  align-items: center;
  height: 83px;
  padding: 0 32px;
  background: #f5f5f5;
  border-radius: 19px;
  margin-bottom: 23.6px;
  box-sizing: border-box;
}
.ipt-label {
  font-size: 29px;
  color: #111;
  font-family: var(--font-regular);
  flex-shrink: 0;
}
.ipt-div {
  width: 1.8px;
  height: 27.2px;
  background: #d9d9d9;
  margin: 0 21.7px;
  flex-shrink: 0;
}
.ipt-input {
  flex: 1;
  min-width: 0;
  border: none;
  outline: none;
  background: transparent;
  font-size: 25.4px;
  color: #111;
  font-family: var(--font-regular);
}
.ipt-input::placeholder {
  color: #c0c4cc;
}
.get-code {
  flex-shrink: 0;
  font-size: 25.4px;
  color: #0047ff;
}
.btn-primary {
  width: 100%;
  height: 99px;
  background: var(--qbox-primary);
  border-radius: 50px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 32px;
  color: #fff;
  font-family: var(--font-medium);
  margin-top: 23.6px;
  box-sizing: border-box;
}
</style>
