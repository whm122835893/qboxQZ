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
import { ref, reactive, nextTick, watch, computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useUserStore } from '../../stores/user'
import { request } from '../../api/request'

const router = useRouter()
const route = useRoute()
// 登录成功后回跳到被拦截的原页面（路由守卫写入 ?redirect=）
const redirectTarget = computed(() => (route.query.redirect ? String(route.query.redirect) : '/pages/index/index'))
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
    setTimeout(() => router.replace(redirectTarget.value), 600)
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
    setTimeout(() => router.replace(redirectTarget.value), 600)
  } else {
    toast(res.msg || '登录失败')
  }
}
</script>

<style scoped src="../../styles/pages/login-login.css"></style>
