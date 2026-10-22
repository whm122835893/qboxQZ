<template>
  <div class="pwd-set-page">
    <!-- 导航栏（原站 u-navbar fixed h79.7：返回 16.3×29 + 标题 32.6 居中） -->
    <div class="navbar">
      <img class="navbar-back" src="/static/cw/back_btn.png" alt="" @click="$router.back()" />
      <span class="nav-title">设置操作密码</span>
    </div>

    <div class="body">
      <!-- 表单（原站实测：mt637.7，行 h87 bg#f5f5f5 r34.4 padding 0 38.1，标签 w119.6 27.2px，分隔竖线 1×27.2 #d9d9d9，输入 25.4px） -->
      <div class="form">
        <div class="ipt-row">
          <span class="ipt-label">操作密码</span>
          <span class="ipt-div"></span>
          <input v-model="form.password" class="ipt-input" type="password" maxlength="6" placeholder="请输入6位数字" />
        </div>
        <div class="ipt-row">
          <span class="ipt-label">确认密码</span>
          <span class="ipt-div"></span>
          <input v-model="form.confirm" class="ipt-input" type="password" maxlength="6" placeholder="请输入6位数字" />
        </div>
        <div class="ipt-row">
          <span class="ipt-label">手机号码</span>
          <span class="ipt-div"></span>
          <input v-model="form.mobile" class="ipt-input" type="tel" maxlength="11" placeholder="请输入手机号码" />
        </div>
        <div class="ipt-row">
          <span class="ipt-label">验证码</span>
          <span class="ipt-div"></span>
          <input v-model="form.captcha" class="ipt-input" type="number" maxlength="6" placeholder="请输入验证码" />
          <span class="get-code" @click="sendCode">{{ countDown > 0 ? `(重新发送 ${countDown}s)` : '获取验证码' }}</span>
        </div>
      </div>
      <!-- 提示（原站实测 y666 13px #8c8c8c） -->
      <div class="tip">请输入6位数字操作密码</div>
    </div>

    <!-- 确认按钮（原站实测 (26.5,822,361×48) r24 bg#0047ff，底部悬浮 26@414） -->
    <div class="btn-ok" @click="submit">确认</div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted, onUnmounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { request } from '../../../api/request'
import { toast } from '../../../utils/toast'
import { useUserStore } from '../../../stores/user'

const route = useRoute()
const router = useRouter()
const userStore = useUserStore()

const form = reactive({
  password: '',
  confirm: '',
  mobile: '',
  captcha: String(route.query.code || '') // 原站实测：验证手机号页确认后带 code 跳入本页
})
const countDown = ref(0)
let timer = null

function startCountDown() {
  countDown.value = 60
  timer = setInterval(() => {
    countDown.value--
    if (countDown.value <= 0) clearInterval(timer)
  }, 1000)
}

// 短信验证码（原站 chunk 实测：/sms/send event=resetpwd，顶象验证码开关关闭时直接发送）
async function sendCode() {
  if (countDown.value > 0) return toast('倒计时结束后再发送')
  const mobile = String(form.mobile ?? '').trim()
  if (!mobile) return toast('请输入手机号')
  if (mobile.length !== 11) return toast('请输入11位手机号')
  await request('/api/sms/send', { mobile, event: 'resetpwd' })
  toast('发送成功')
  startCountDown()
}

// 提交（原站 chunk 实测：form 校验 → setActionPwd → toast → navigateBack 2）
async function submit() {
  const pwd = String(form.password ?? '')
  if (!pwd || pwd.length !== 6) return toast('请输入6位数字操作密码')
  if (pwd !== String(form.confirm ?? '')) return toast('两次密码不一致')
  const mobile = String(form.mobile ?? '').trim()
  if (!mobile) return toast('请输入手机号')
  if (mobile.length !== 11) return toast('请输入11位手机号')
  if (!String(form.captcha ?? '')) return toast('请输入验证码')
  const res = await request('/api/user/setPayPassword', {
    password: pwd,
    mobile,
    captcha: String(form.captcha)
  })
  if (res.code === 1) {
    toast(res.msg || '设置成功')
    await userStore.getUserInfo()
    setTimeout(() => router.go(-2), 1500)
  }
}

onMounted(async () => {
  // 原站 chunk 实测：直接进入本页且无验证码时，重定向到验证手机号页
  //（action 取值：pay_password 存在 → 'set' 旧密码校验模式；不存在 → 'reset' 短信模式）
  if (!form.captcha) {
    const res = await userStore.getUserInfo()
    const action = res.data?.pay_password ? 'set' : 'reset'
    router.replace('/pages/my/actionPwd/getCode?action=' + action)
    return
  }
  // 手机号默认带出当前账号（原站同款逻辑：表单 mobile 预填）
  if (!form.mobile && userStore.userinfo?.mobile) form.mobile = userStore.userinfo.mobile
})
onUnmounted(() => timer && clearInterval(timer))
</script>

<style scoped>
.pwd-set-page {
  min-height: 100vh;
  background-color: #fff;
  background-image: url('/static/qbox/login-bg.png');
  background-repeat: no-repeat;
  background-size: 100% auto;
  background-position: center top;
}
/* 导航栏（与登录页同款悬浮导航） */
.navbar {
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
/* 表单区（原站实测 mt637.7，左右 47.1；用 padding-top 防止 margin 塌陷把背景图推下去） */
.body {
  position: relative;
  z-index: 2;
  padding: 637.7px 47.1px 0;
}
.form {
  margin-top: 0;
  display: flex;
  flex-direction: column;
  gap: 34.4px;
}
.ipt-row {
  height: 87px;
  background: #f5f5f5;
  border-radius: 34.4px;
  padding: 0 31px 0 31px;
  box-sizing: border-box;
  display: flex;
  align-items: center;
}
.ipt-label {
  width: 119.6px;
  flex-shrink: 0;
  font-size: 27.2px;
  color: #111;
  font-family: var(--font-regular);
  white-space: nowrap;
}
.ipt-div {
  width: 1.8px;
  height: 27.2px;
  background: #d9d9d9;
  margin-right: 21.7px;
  flex-shrink: 0;
}
.ipt-input {
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
.ipt-input::placeholder {
  color: #c0c4cc;
}
.get-code {
  flex-shrink: 0;
  font-size: 25.4px;
  color: #0047ff;
}
/* 提示（原站实测 y666 → 距表单底 117.8 设计） */
.tip {
  margin-top: 117.8px;
  font-size: 23.6px;
  color: #8c8c8c;
  font-family: var(--font-regular);
}
/* 确认按钮（原站实测 (26.5,822,361×48) r24 #0047ff 底部悬浮） */
.btn-ok {
  position: fixed;
  left: 47.1px;
  right: 47.1px;
  bottom: 47.1px;
  height: 87px;
  background: #0047ff;
  border-radius: 43.5px;
  color: #fff;
  font-size: 29px;
  font-weight: 600;
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 2;
}
</style>
