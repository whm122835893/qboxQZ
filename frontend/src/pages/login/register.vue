<template>
  <div class="login-page">
    <div class="login-navbar">
      <img class="navbar-back" src="/static/cw/back_btn.png" alt="" @click="$router.back()" />
    </div>

    <div class="form-wrap">
      <!-- 行1 手机号 -->
      <div class="ipt-row">
        <span class="ipt-label">手机号</span>
        <span class="ipt-div"></span>
        <input v-model="form.mobile" class="ipt-input" type="tel" maxlength="11" placeholder="请输入手机号" />
      </div>
      <!-- 行2 验证码 -->
      <div class="ipt-row">
        <span class="ipt-label">验证码</span>
        <span class="ipt-div"></span>
        <input v-model="form.code" class="ipt-input" type="number" maxlength="6" placeholder="请输入验证码" />
        <span class="get-code" @click="getCode">{{ countDown > 0 ? `重新发送 ${countDown}s` : '获取验证码' }}</span>
      </div>
      <!-- 行3 密码 -->
      <div class="ipt-row">
        <span class="ipt-label">密码</span>
        <span class="ipt-div"></span>
        <input v-model="form.password" class="ipt-input" type="password" placeholder="请输入密码" />
      </div>
      <!-- 行4 邀请码 -->
      <div class="ipt-row">
        <span class="ipt-label">邀请码</span>
        <span class="ipt-div"></span>
        <input v-model="form.inviter_code" class="ipt-input" type="text" placeholder="请输入邀请码" />
      </div>

      <div class="btn-primary" @click="submit">注册</div>
      <div class="btn-outline" @click="$router.push('/pages/login/login?loginType=1')"><span class="btn-outline-text">去登录</span></div>

      <!-- 协议（与登录页同款） -->
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
import { ref, onMounted, onUnmounted } from 'vue'
import { useRouter } from 'vue-router'
import { request } from '../../api/request'
import { toast } from '../../utils/toast'

/**
 * 原站 chunk 反解（pages-login-register）：
 *  - 字段 {mobile, password, code, inviter_code}；短信 event:"register"
 *  - 校验：请输入手机号/请输入验证码/请输入登录密码/密码内需同时包含数字、字母，长度在6-12之间/请先勾选用户协议
 *  - 提交 POST /api/user/register → 成功跳 /pages/login/login?loginType=1
 */
const router = useRouter()
const form = ref({ mobile: '', password: '', code: '', inviter_code: '' })
const checked = ref(true)
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
  await request('/api/sms/send', { mobile, event: 'register' })
  toast('发送成功')
  startCountDown()
}

function checkPwd(pwd) {
  return /^(?=.*\d)(?=.*[a-zA-Z]).{6,12}$/.test(pwd)
}

async function submit() {
  const mobile = String(form.value.mobile ?? '').trim()
  if (!mobile) return toast('请输入手机号')
  if (!String(form.value.code ?? '')) return toast('请输入验证码')
  const pwd = String(form.value.password ?? '')
  if (!pwd) return toast('请输入登录密码')
  if (!checkPwd(pwd)) return toast('密码内需同时包含数字、字母，长度在6-12之间')
  if (!checked.value) return toast('请先勾选用户协议')
  toast('注册中...')
  const res = await request('/api/user/register', {
    mobile,
    password: pwd,
    code: String(form.value.code),
    inviter_code: String(form.value.inviter_code ?? '')
  })
  if (res.code === 1) {
    toast(res.msg || '注册成功')
    setTimeout(() => router.push('/pages/login/login?loginType=1'), 1200)
  }
}

onMounted(() => {})
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
.btn-outline {
  width: 100%;
  height: 99px;
  background: #fff;
  border: 2px solid #1a1a1a;
  border-radius: 50px;
  display: flex;
  align-items: center;
  justify-content: center;
  margin-top: 23.6px;
  box-sizing: border-box;
}
.btn-outline-text {
  color: #1a1a1a;
  font-size: 32px;
  font-family: var(--font-medium);
}
.agreement-row {
  margin-top: 156px;
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
