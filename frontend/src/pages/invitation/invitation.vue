<template>
  <div class="invite-page">
    <!-- 导航栏 -->
    <div class="invite-navbar">
      <img class="nav-back" src="/static/cw/back_btn.png" alt="返回" @click="goBack" />
      <span class="nav-title">邀请好友</span>
    </div>

    <div class="invite-container">
      <!-- 邀请卡（原站 363×461 r8） -->
      <div class="invite-card">
        <span class="card-brand">QBOX</span>
        <span class="card-sub">邀请你加入QBOX，扫码注册加入我们</span>
        <div class="qr-wrap">
          <img v-if="qrDataUrl" class="qr-img" :src="qrDataUrl" alt="邀请二维码" />
        </div>
        <div class="code-box" @click="copyCode">
          <span class="code-tip">点击复制邀请码</span>
          <span class="code-value">{{ inviteCode }}</span>
        </div>
        <div class="btn-row">
          <div class="btn-save" @click="saveQr">保存图片</div>
          <div class="btn-copy" @click="copyLink">复制邀请链接</div>
        </div>
      </div>

      <!-- 邀请记录 -->
      <div class="record-section">
        <span class="record-title">邀请记录</span>
        <div class="record-empty">
          <img class="record-empty-img" src="/static/qbox/common-empty.png" alt="" />
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import QRCode from 'qrcode'
import { useUserStore } from '../../stores/user'
import { request } from '../../api/request'

const router = useRouter()
const userStore = useUserStore()

const inviteCode = computed(() => userStore.userinfo?.inviter_code || '')
const qrDataUrl = ref('')

// 独立项目：二维码本地生成（原站为 /addons/qrcode 接口生成，规则9 不依赖）
const inviteLink = computed(() => {
  return `${location.origin}/#/pages/login/login?code=${inviteCode.value}`
})

async function genQr() {
  if (!inviteCode.value) return
  qrDataUrl.value = await QRCode.toDataURL(inviteLink.value, {
    width: 308,
    margin: 1,
    color: { dark: '#000000', light: '#ffffff' }
  })
}

function toast(msg) {
  const el = document.createElement('div')
  el.textContent = msg
  el.style.cssText =
    'position:fixed;top:18%;left:50%;transform:translateX(-50%);background:rgba(0,0,0,0.75);color:#fff;font-size:14px;padding:9px 18px;border-radius:8px;z-index:99999;white-space:nowrap;'
  document.body.appendChild(el)
  setTimeout(() => el.remove(), 1800)
}

async function copyText(text) {
  try {
    await navigator.clipboard.writeText(text)
  } catch {
    const ta = document.createElement('textarea')
    ta.value = text
    document.body.appendChild(ta)
    ta.select()
    document.execCommand('copy')
    ta.remove()
  }
}

function copyCode() {
  if (!inviteCode.value) return
  copyText(inviteCode.value)
  toast('邀请码已复制')
}
function copyLink() {
  copyText(inviteLink.value)
  toast('邀请链接已复制')
}
function saveQr() {
  if (!qrDataUrl.value) return
  const a = document.createElement('a')
  a.href = qrDataUrl.value
  a.download = `qbox-invite-${inviteCode.value}.png`
  a.click()
  toast('二维码已保存')
}

function goBack() {
  history.length > 1 ? router.back() : router.replace('/pages/my/my')
}

onMounted(async () => {
  if (userStore.token) await userStore.getUserInfo()
  await genQr()
  // 邀请统计/记录（实测 my_list 为空 → 空态黑猫）
  await request('/api/user/ranking', { page: 1, types: 2 })
})
</script>

<style scoped>
.invite-page {
  min-height: 100vh;
  background-color: #fff;
}
.invite-navbar {
  position: relative;
  width: 100%;
  height: 79.7px;
  display: flex;
  align-items: center;
  z-index: 5;
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
  font-size: 30.8px;
  font-weight: 500;
  color: #111;
  font-family: var(--font-medium);
}
.invite-container {
  width: 100%;
  display: flex;
  flex-direction: column;
  align-items: center;
  padding: 0 23.6px;
  box-sizing: border-box;
  background-color: #fff;
  background-image: url('/static/qbox/invitation-bg.png');
  background-repeat: no-repeat;
  background-size: 100% auto;
  background-position: center top;
  padding-top: 449.3px;
}

/* ---- 邀请卡 ---- */
.invite-card {
  width: 657.6px;
  height: 835.1px;
  background: #fff;
  border-radius: 14.5px;
  box-sizing: border-box;
  padding: 47.1px 39.9px 39.9px;
  display: flex;
  flex-direction: column;
  align-items: center;
  box-shadow: rgba(0, 0, 0, 0.05) 0 3.6px 18.1px;
}
.card-brand {
  font-size: 34.4px;
  color: #111;
  font-family: 'NanoByongGyeHei-Regular', var(--font-regular);
}
.card-sub {
  margin-top: 14.5px;
  font-size: 23.6px;
  color: #8c8c8c;
  font-family: var(--font-regular);
}
.qr-wrap {
  flex: 1;
  display: flex;
  align-items: center;
  justify-content: center;
}
.qr-img {
  width: 279px;
  height: 279px;
  border-radius: 14.5px;
}
.code-box {
  width: 100%;
  border: 1.8px solid #ececec;
  border-radius: 14.5px;
  padding: 16.3px 0;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 7.2px;
}
.code-tip {
  font-size: 21.7px;
  color: #999;
  font-family: var(--font-regular);
}
.code-value {
  font-size: 36.2px;
  font-weight: bold;
  color: #111;
  font-family: var(--font-bold);
  letter-spacing: 1px;
}
.btn-row {
  width: 100%;
  margin-top: 21.7px;
  display: flex;
  align-items: center;
}
.btn-save {
  width: 208.4px;
  height: 70.7px;
  border: 1.8px solid #0047ff;
  border-radius: 18.1px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 23.6px;
  color: #0047ff;
  font-family: var(--font-regular);
}
.btn-copy {
  flex: 1;
  height: 70.7px;
  margin-left: 14.5px;
  background: #111;
  border-radius: 18.1px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 23.6px;
  color: #fff;
  font-family: var(--font-regular);
}

/* ---- 邀请记录 ---- */
.record-section {
  width: 657.6px;
  margin-top: 39.9px;
  display: flex;
  flex-direction: column;
}
.record-title {
  font-weight: 600;
  font-size: 30.8px;
  color: #111;
  font-family: var(--font-bold);
}
.record-empty {
  width: 100%;
  min-height: 300px;
  display: flex;
  align-items: center;
  justify-content: center;
}
.record-empty-img {
  width: 282.6px;
  height: 282.6px;
  /* 原站 uni-image 实测：156×156 容器 + background-size:contain → 图等比缩放为 106.7×156 居中显示；
     object-fit:contain 与其完全等价（此前 fill 压扁 / height:auto 放大 均有偏差，已按实测修正） */
  object-fit: contain;
}
</style>
