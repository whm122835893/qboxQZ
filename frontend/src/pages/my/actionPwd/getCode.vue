<template>
  <div class="code-page">
    <!-- 导航栏 -->
    <div class="navbar">
      <img class="navbar-back" src="/static/cw/back_btn.png" alt="" @click="$router.back()" />
      <span class="nav-title">验证手机号</span>
    </div>

    <!-- 已有密码（action=set）：输入当前操作密码 + 忘记密码?（原站 chunk 实测分支） -->
    <div v-if="mode === 'oldpwd'" class="wrap">
      <div class="box">
        <input v-model="oldPwd" class="box-input" type="password" maxlength="6" placeholder="输入当前操作密码" />
      </div>
      <span class="forget" @click="goReset">忘记密码？</span>
    </div>

    <!-- 未设密码/忘记密码（action=reset，原站实测截图）：手机号 + 验证码 + 发送验证码，进入自动发码 -->
    <div v-else class="wrap">
      <div class="box">
        <input v-model="mobile" class="box-input" type="tel" maxlength="11" placeholder="请输入手机号码" />
      </div>
      <div class="box cap-box">
        <input v-model="captcha" class="box-input" type="number" maxlength="6" placeholder="请输入验证码" />
        <span class="send-btn" @click="sendCode">{{ countDown > 0 ? `(重新发送 ${countDown}s)` : '发送验证码' }}</span>
      </div>
    </div>

    <!-- 确认按钮（原站实测橙色 #ff7800 (13,820,388×49) r24.5 底部悬浮） -->
    <div class="btn-ok" @click="submit">确认</div>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { request } from '../../../api/request'
import { toast } from '../../../utils/toast'
import { useUserStore } from '../../../stores/user'

/**
 * 原站 chunk 实测流程（pages-my-actionPwd-getCode，action 语义按编译产物反解）：
 *  - action=reset（未设密码/忘记密码）：短信验证模式——手机号+验证码，进入页面自动 sendCode()
 *    （/sms/send event=resetpwd）→ 确认 → /pages/my/actionPwd/setPwd?code=<验证码>
 *  - action=set（已有密码）：旧密码校验模式——输入当前操作密码 → /user/checkPaypass
 *    → 确认 → 携返回 code 跳 setPwd；「忘记密码?」→ 跳 action=reset
 */
const route = useRoute()
const router = useRouter()
const userStore = useUserStore()

const mode = ref('set')
const mobile = ref('')
const captcha = ref('')
const oldPwd = ref('')
const countDown = ref(0)
let timer = null

function startCountDown() {
  countDown.value = 60
  timer = setInterval(() => {
    countDown.value--
    if (countDown.value <= 0) clearInterval(timer)
  }, 1000)
}

async function sendCode() {
  if (countDown.value > 0) return toast('倒计时结束后再发送')
  const m = String(mobile.value ?? '').trim()
  if (!m) return toast('请输入手机号')
  if (m.length !== 11) return toast('请输入11位手机号')
  await request('/api/sms/send', { mobile: m, event: 'resetpwd' })
  toast('发送成功')
  startCountDown()
}

async function submit() {
  if (mode.value === 'oldpwd') {
    if (!String(oldPwd.value ?? '')) return toast('请输入当前操作密码')
    const res = await request('/api/user/checkPaypass', { pay_password: oldPwd.value })
    if (res.code === 1) {
      router.push('/pages/my/actionPwd/setPwd?code=' + (res.data?.code || ''))
    }
    return
  }
  // set 模式：确认 → 携验证码跳设置页（原站 toNext(this.code)）
  const c = String(captcha.value ?? '').trim()
  if (!c) return toast('请输入验证码')
  router.push('/pages/my/actionPwd/setPwd?code=' + c)
}

function goReset() {
  // 忘记密码：走短信验证重置（原站 reset() → getCode?action=reset）
  router.replace('/pages/my/actionPwd/getCode?action=reset')
}

// 短信模式自动发码（原站 chunk 实测：checkVerifyStatus → sendCode）
async function autoSend() {
  if (!mobile.value && userStore.userinfo?.mobile) mobile.value = userStore.userinfo.mobile
  if (!String(mobile.value ?? '').trim()) return
  await request('/api/sms/send', { mobile: mobile.value, event: 'resetpwd' })
  startCountDown()
}

onMounted(async () => {
  await userStore.getUserInfo()
  // 原站 chunk 实测：action=reset → 短信模式；其余（set）→ 旧密码模式
  const action = String(route.query.action || 'reset')
  mode.value = action === 'reset' ? 'sms' : 'oldpwd'
  if (mode.value === 'sms') {
    if (!mobile.value && userStore.userinfo?.mobile) mobile.value = userStore.userinfo.mobile
    autoSend()
  }
})
onUnmounted(() => timer && clearInterval(timer))
</script>

<style scoped>
.code-page {
  min-height: 100vh;
  background: #fff;
}
.navbar {
  position: relative;
  height: 79.7px;
  display: flex;
  align-items: center;
  padding: 0 23.6px;
}
.navbar-back {
  width: 16.3px;
  height: 29px;
  object-fit: contain;
}
.nav-title {
  position: absolute;
  left: 50%;
  top: 50%;
  transform: translate(-50%, -50%);
  width: 398.6px;
  text-align: center;
  font-size: 32.6px;
  font-weight: 500;
  color: #000;
  font-family: var(--font-medium);
  white-space: nowrap;
}
/* 内容区（原站实测 mt378.6，左右 74.3） */
.wrap {
  margin-top: 378.6px;
  padding: 0 74.3px;
  display: flex;
  flex-direction: column;
}
.box {
  height: 97.9px;
  border: 1.8px solid #000;
  border-radius: 48.9px;
  padding: 0 29px;
  box-sizing: border-box;
  display: flex;
  align-items: center;
}
.cap-box {
  margin-top: 41.7px;
  justify-content: space-between;
}
.box-input {
  flex: 1;
  min-width: 0;
  height: 100%;
  border: none;
  outline: none;
  background: transparent;
  font-size: 25.4px;
  color: #111;
  font-family: var(--font-regular);
}
.box-input::placeholder {
  color: #c0c4cc;
}
.send-btn {
  flex-shrink: 0;
  font-size: 25.4px;
  color: #555;
}
.forget {
  margin-top: 43.5px;
  align-self: flex-start;
  font-size: 23.6px;
  color: #0047ff;
}
/* 确认按钮（原站实测橙 #ff7800 r44.4 底部悬浮） */
.btn-ok {
  position: fixed;
  left: 23.6px;
  right: 23.6px;
  bottom: 48.9px;
  height: 88.8px;
  background: #ff7800;
  border-radius: 44.4px;
  color: #fff;
  font-size: 29px;
  font-weight: 600;
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 2;
}
</style>
