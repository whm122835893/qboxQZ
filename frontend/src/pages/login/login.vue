<template>
  <div class="login-page">
    <!-- 背景层：原图取自原站 /static/qbox/login-bg.png，"Wellcome QBox" 标题艺术字包含在背景图中 -->
    <div class="login-bg"></div>

    <!-- 导航栏：返回箭头（原站为 9x16px 图片箭头） -->
    <div class="login-navbar" @click="goBack">
      <img class="navbar-back" src="/static/cw/back_btn.png" alt="返回" />
    </div>

    <div class="login-content">
      <!-- 密码登录模式（原站 viewMode=password） -->
      <template v-if="viewMode === 'password'">
        <div class="form-block">
          <div class="ipt-row">
            <span class="ipt-label">账号</span>
            <span class="ipt-divider"></span>
            <input
              v-model="form.account"
              class="ipt-input"
              type="number"
              maxlength="11"
              placeholder="请输入手机号"
            />
          </div>
          <div class="ipt-row">
            <span class="ipt-label">密码</span>
            <span class="ipt-divider divider-ml"></span>
            <input
              v-model="form.password"
              class="ipt-input"
              type="password"
              placeholder="输入你的密码"
            />
            <span class="forgot-pwd" @click="goForgetPwd"> 忘记密码? </span>
          </div>
        </div>

        <div class="btn-block">
          <div class="btn-primary" @click="handleLogin">
            <span class="btn-primary-text">立即登录</span>
          </div>
          <div class="btn-outline" @click="viewMode = 'sms'">
            <span class="btn-outline-text">验证码登录</span>
          </div>
        </div>

        <div class="agreement-row">
          <div class="agreement-check" :class="{ checked: checked }" @click="checked = !checked">
            <svg
              v-if="checked"
              viewBox="0 0 24 24"
              width="12"
              height="12"
              fill="none"
              stroke="#fff"
              stroke-width="3.5"
              stroke-linecap="round"
              stroke-linejoin="round"
            >
              <polyline points="20 6 9 17 4 12"></polyline>
            </svg>
          </div>
          <span class="agreement-text">登录即同意</span>
          <span class="agreement-link" @click.stop="toAgreement('usageterms', '用户协议')"> 《用户协议》 </span>
          <span class="agreement-text">、</span>
          <span class="agreement-link" @click.stop="toAgreement('agreement', '隐私政策')"> 《隐私政策》 </span>
        </div>
      </template>

      <!-- 验证码登录模式：输入手机号（原站 viewMode=sms） -->
      <template v-else-if="viewMode === 'sms'">
        <div class="form-block">
          <div class="ipt-row">
            <span class="ipt-label">账号</span>
            <span class="ipt-divider"></span>
            <input
              v-model="form2.mobile"
              class="ipt-input"
              type="number"
              maxlength="11"
              placeholder="请输入手机号"
            />
          </div>
        </div>
        <div class="btn-block btn-block-sms">
          <div class="btn-primary" @click="handleSendCode">
            <span class="btn-primary-text">{{ codeTips || '获取验证码' }}</span>
          </div>
          <div class="btn-outline" @click="viewMode = 'password'">
            <span class="btn-outline-text">账号密码登录</span>
          </div>
          <div class="sms-tip-row">
            <span class="sms-tip">未注册账号将自动注册</span>
          </div>
        </div>
        <div class="agreement-row">
          <div class="agreement-check" :class="{ checked: checked }" @click="checked = !checked">
            <svg
              v-if="checked"
              viewBox="0 0 24 24"
              width="12"
              height="12"
              fill="none"
              stroke="#fff"
              stroke-width="3.5"
              stroke-linecap="round"
              stroke-linejoin="round"
            >
              <polyline points="20 6 9 17 4 12"></polyline>
            </svg>
          </div>
          <span class="agreement-text">登录即同意</span>
          <span class="agreement-link" @click.stop="toAgreement('usageterms', '用户协议')"> 《用户协议》 </span>
          <span class="agreement-text">、</span>
          <span class="agreement-link" @click.stop="toAgreement('agreement', '隐私政策')"> 《隐私政策》 </span>
        </div>
      </template>

      <!-- 验证码输入模式（原站 viewMode=smsCode）：6 格验证码 -->
      <template v-else>
        <div class="smscode-head">
          <span class="smscode-title">输入短信验证码</span>
          <span class="smscode-sub">请输入11位手机号{{ maskMobile(form2.mobile) }}</span>
        </div>
        <div class="smscode-boxs" @click="focusCode">
          <div v-for="i in 6" :key="i" class="smscode-box">
            <span class="smscode-char">{{ (form2.captcha || '')[i - 1] || '' }}</span>
          </div>
          <input
            ref="codeInput"
            v-model="form2.captcha"
            class="smscode-hidden-input"
            type="number"
            maxlength="6"
          />
        </div>
        <div class="smscode-resend" @click="handleSendCode">
          <span class="smscode-resend-text">{{ codeTips || '重新获取' }}</span>
        </div>
        <div class="btn-block">
          <div class="btn-primary" @click="handleSmsLogin">
            <span class="btn-primary-text">立即登录</span>
          </div>
          <div class="btn-outline" @click="viewMode = 'password'">
            <span class="btn-outline-text">账号密码登录</span>
          </div>
          <div class="sms-tip-row">
            <span class="sms-tip">未注册账号将自动注册</span>
          </div>
        </div>
      </template>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, nextTick, watch } from 'vue'
import { useRouter } from 'vue-router'
import { useUserStore } from '../../stores/user'
import { request } from '../../api/request'

const router = useRouter()
const userStore = useUserStore()

// 与原站一致的登录状态，报错文案均来自原站实测
const viewMode = ref('password') // password | sms | smsCode
const checked = ref(true) // 原站默认勾选协议
const codeTips = ref('')
let codeTimer = null
const codeInput = ref(null)

const form = reactive({ account: '', password: '' })
const form2 = reactive({ mobile: '', captcha: '' })

function toast(msg) {
  // 轻提示：复刻原站 u-toast 顶部黑色圆角样式
  const el = document.createElement('div')
  el.textContent = msg
  el.style.cssText =
    'position:fixed;top:18%;left:50%;transform:translateX(-50%);background:rgba(0,0,0,0.75);color:#fff;font-size:14px;padding:9px 18px;border-radius:8px;z-index:99999;font-family:var(--font-regular);white-space:nowrap;'
  document.body.appendChild(el)
  setTimeout(() => el.remove(), 2000)
}

function goBack() {
  history.length > 1 ? router.back() : router.replace('/pages/index/index')
}
function goForgetPwd() {
  router.push('/pages/login/forgetPwd')
}
function toAgreement(path, title) {
  // 协议页在原站为 webview 外链，本项目独立实现，待开发阶段先落占位页
  router.push('/pages/webview/webview')
}
function maskMobile(m) {
  if (!m || m.length !== 11) return ''
  return m.slice(0, 3) + '****' + m.slice(7)
}

async function handleLogin() {
  const account = String(form.account ?? '').trim()
  const password = String(form.password ?? '')
  if (!account) return toast('请输入手机号')
  if (account.length !== 11) return toast('请输入11位手机号')
  if (!password) return toast('请输入密码')
  const res = await userStore.login(account, password)
  if (res.code === 1) {
    toast('登录成功')
    setTimeout(() => router.replace('/pages/index/index'), 600)
  } else {
    toast(res.msg || '登录失败')
  }
}

function startCodeCountdown() {
  let seconds = 60
  codeTips.value = `重新发送 ${seconds}s`
  clearInterval(codeTimer)
  codeTimer = setInterval(() => {
    seconds -= 1
    if (seconds <= 0) {
      clearInterval(codeTimer)
      codeTips.value = ''
    } else {
      codeTips.value = `重新发送 ${seconds}s`
    }
  }, 1000)
}

async function handleSendCode() {
  const mobile = String(form2.mobile ?? '').trim()
  if (!mobile) return toast('请输入手机号')
  if (mobile.length !== 11) return toast('请输入11位手机号')
  if (!checked.value) return toast('请先同意用户协议')
  // 独立项目：短信验证码由本项目后端发送；mock 阶段验证码固定 123456（见 API 文档）
  const res = await request('/api/user/sendsms', { mobile, event: 'mobilelogin' })
  if (res.code === 1) {
    toast('验证码已发送')
    viewMode.value = 'smsCode'
    startCodeCountdown()
  } else {
    toast(res.msg || '发送失败')
  }
}

function focusCode() {
  codeInput.value && codeInput.value.focus()
}

watch(viewMode, (v) => {
  if (v === 'smsCode') nextTick(focusCode)
})

async function handleSmsLogin() {
  const captcha = String(form2.captcha ?? '')
  if (!captcha || captcha.length !== 6) return toast('请输入验证码')
  const res = await request('/api/user/mobilelogin', { mobile: String(form2.mobile), captcha })
  if (res.code === 1) {
    toast('登录成功')
    setTimeout(() => router.replace('/pages/index/index'), 600)
  } else {
    toast(res.msg || '登录失败')
  }
}
</script>

<style scoped>
/* ===== 布局与数值均取自原站登录页源码（rpx 数值直接书写为 px，经 postcss 转 vw） ===== */
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
  height: 44px;
  display: flex;
  align-items: center;
  padding: 0 13px;
  background: transparent;
}
.navbar-back {
  width: 9px;
  height: 16px;
  object-fit: contain;
}
.login-content {
  position: relative;
  z-index: 2;
  flex: 1;
  display: flex;
  flex-direction: column;
  padding: 0 48px 80px;
}

/* ---- 输入框 ---- */
.form-block {
  margin-top: 620px;
  display: flex;
  flex-direction: column;
  gap: 24px;
}
.ipt-row {
  display: flex;
  align-items: center;
  height: 83px;
  padding: 0 32px;
  background: #f5f5f5;
  border-radius: 19px;
}
.ipt-label {
  font-size: 26px;
  color: #000000;
  font-family: var(--font-regular);
  white-space: nowrap;
}
.ipt-divider {
  width: 1px;
  height: 28px;
  background: #d9d9d9;
  margin: 0 16px;
  flex-shrink: 0;
}
.ipt-input {
  flex: 1;
  border: none;
  outline: none;
  background: transparent;
  font-size: 32px;
  color: #000000;
  font-family: 'AlimamaFangYuanTiVF-Thin', var(--font-regular);
  min-width: 0;
}
.ipt-input::placeholder {
  font-weight: 400;
  font-size: 22px;
  color: #8b8b8b;
}
.forgot-pwd {
  font-size: 24px;
  color: #8c8c8c;
  font-family: var(--font-regular);
  white-space: nowrap;
}

/* ---- 按钮 ---- */
.btn-block {
  margin-top: 48px;
  display: flex;
  flex-direction: column;
  gap: 32px;
}
.btn-block-sms {
  margin-top: 64px;
}
.btn-primary {
  width: 100%;
  height: 99px;
  background: var(--qbox-primary);
  border-radius: 50px;
  display: flex;
  align-items: center;
  justify-content: center;
}
.btn-primary:active {
  opacity: 0.85;
}
.btn-primary-text {
  color: #fff;
  font-size: 32px;
  font-family: var(--font-medium);
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
}
.btn-outline:active {
  background: #f7f7f7;
}
.btn-outline-text {
  color: #1a1a1a;
  font-size: 32px;
  font-family: var(--font-medium);
}

/* ---- 协议 ---- */
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
  border: 2px solid #8c8c8c;
  background: #fff;
  display: flex;
  align-items: center;
  justify-content: center;
  margin-right: 8px;
  flex-shrink: 0;
}
.agreement-check.checked {
  background: #000000;
  border-color: #000000;
}
.agreement-text {
  font-size: 24px;
  color: #8c8c8c;
  font-family: var(--font-regular);
}
.agreement-link {
  font-size: 24px;
  color: var(--qbox-primary);
  font-family: var(--font-regular);
}

/* ---- 短信验证码 ---- */
.sms-tip-row {
  display: flex;
  align-items: center;
  justify-content: center;
}
.sms-tip {
  font-size: 24px;
  color: #8c8c8c;
  font-family: var(--font-regular);
}
.smscode-head {
  margin-top: 620px;
  display: flex;
  flex-direction: column;
  gap: 16px;
}
.smscode-title {
  font-size: 32px;
  color: #111;
  font-family: var(--font-medium);
}
.smscode-sub {
  font-size: 24px;
  color: #8c8c8c;
  font-family: var(--font-regular);
}
.smscode-boxs {
  margin-top: 48px;
  position: relative;
  display: flex;
  gap: 16px;
}
.smscode-box {
  flex: 1;
  height: 83px;
  border-radius: 16px;
  background: #f5f5f5;
  display: flex;
  align-items: center;
  justify-content: center;
}
.smscode-char {
  font-size: 40px;
  color: #111;
  font-family: 'NanoByongGyeHei-Regular', var(--font-regular);
}
.smscode-hidden-input {
  position: absolute;
  left: 0;
  top: 0;
  width: 100%;
  height: 100%;
  opacity: 0;
  border: none;
  outline: none;
  background: transparent;
}
.smscode-resend {
  margin-top: 24px;
}
.smscode-resend-text {
  font-size: 24px;
  color: #8c8c8c;
  font-family: var(--font-regular);
}
</style>
