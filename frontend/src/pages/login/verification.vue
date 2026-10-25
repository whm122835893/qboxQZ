<template>
  <div class="vf-page">
    <!-- 顶部返回（源站实测：返回箭头 24×24 #111，左边距 32） -->
    <div class="vf-nav">
      <img class="vf-back" src="/static/cw/back_btn.png" alt="返回" @click="goBack" />
    </div>

    <div class="vf-body">
      <!-- 标题（源站实测 2026-10-25：「输入短信验证码」34px Bold #111，
           副文「已发送至+86」30px #8c8c8c mt 12） -->
      <div class="vf-title">输入短信验证码</div>
      <div class="vf-sub">已发送至{{ mobileMasked }}</div>

      <!-- 验证码输入行（源站实测：下划线输入框 + 右侧「获取验证码」24px #0047ff） -->
      <div class="vf-code-row">
        <input
          v-model="code"
          class="vf-code-input"
          type="tel"
          maxlength="6"
          placeholder="请输入验证码"
          @confirm="onNext"
        />
        <span class="vf-get-code" :class="{ disabled: counting }" @click="onGetCode">
          {{ counting ? countdown + 's' : '获取验证码' }}
        </span>
      </div>

      <!-- 下一步按钮（源站实测：蓝色胶囊通栏 h88 r12 #0018ff，白字 30px） -->
      <button class="vf-next" :disabled="code.length < 6" @click="onNext">下一步</button>
    </div>
  </div>
</template>

<script setup>
/**
 * 源站实测（2026-10-25 登录态，pages/login/verification）：
 *   页面为「输入短信验证码」步骤 —— 标题 + 「已发送至+86」+ 下划线验证码输入框
 *   + 右侧「获取验证码」+ 蓝色胶囊「下一步」按钮。
 *   说明：源站该路由在部分流程下也会承载第三方验证 webview（verificationUrl + postMessage），
 *   但默认渲染的是短信验证码页，本项目按默认形态复刻。
 */
import { ref, computed, onUnmounted } from 'vue'
import { useRouter } from 'vue-router'

const router = useRouter()

const code = ref('')
const counting = ref(false)
const countdown = ref(60)
let timer = null

// 手机号：源站显示「已发送至+86」（按源站实测文案原样）
const mobileMasked = computed(() => '+86')

function goBack() {
  history.length > 1 ? router.back() : router.replace('/pages/login/login')
}

function onGetCode() {
  if (counting.value) return
  counting.value = true
  countdown.value = 60
  timer = setInterval(() => {
    countdown.value -= 1
    if (countdown.value <= 0) {
      clearInterval(timer)
      timer = null
      counting.value = false
    }
  }, 1000)
}

function onNext() {
  if (code.value.length < 6) return
  router.replace('/pages/login/login')
}

onUnmounted(() => {
  if (timer) clearInterval(timer)
})
</script>

<style scoped>
.vf-page {
  min-height: 100vh;
  background: #fff;
}
.vf-nav {
  height: 88px;
  padding-left: 32px;
  display: flex;
  align-items: center;
}
.vf-back {
  width: 24px;
  height: 24px;
}
.vf-body {
  padding: 0 32px;
}
.vf-title {
  font-size: 34px;
  font-weight: 700;
  color: #111;
  font-family: var(--font-medium);
  line-height: 48px;
}
.vf-sub {
  margin-top: 12px;
  font-size: 30px;
  color: #8c8c8c;
  font-family: var(--font-regular);
  line-height: 42px;
}
.vf-code-row {
  margin-top: 60px;
  display: flex;
  align-items: center;
  border-bottom: 2px solid #d9d9d9;
  padding-bottom: 16px;
}
.vf-code-input {
  flex: 1;
  min-width: 0;
  border: none;
  outline: none;
  font-size: 32px;
  color: #111;
  font-family: var(--font-regular);
  background: transparent;
}
.vf-code-input::placeholder {
  color: #c0c4cc;
}
.vf-get-code {
  flex-shrink: 0;
  margin-left: 24px;
  font-size: 24px;
  color: #0047ff;
  font-family: var(--font-regular);
}
.vf-get-code.disabled {
  color: #c0c4cc;
}
.vf-next {
  margin-top: 72px;
  width: 100%;
  height: 88px;
  border: none;
  border-radius: 12px;
  background: #0018ff;
  color: #fff;
  font-size: 30px;
  font-family: var(--font-medium);
  cursor: pointer;
}
.vf-next:disabled {
  background: #d9d9d9;
  cursor: not-allowed;
}
</style>
