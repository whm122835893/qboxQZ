<template>
  <div class="vip-page">
    <!-- 导航栏（原站 w-navbar showWhite：白色返回 + 白色标题，透明底浮于头部大图上） -->
    <div class="navbar">
      <img class="navbar-back" src="/static/cw/back_back_white.png" alt="返回" @click="$router.back()" />
      <span class="nav-title">会员中心</span>
    </div>

    <div class="vip-body">
      <!-- 卡片一：专属特权（原站 678 宽 vip-card-bg 圆角24，padding 32/28/36） -->
      <div class="card card-privilege">
        <div class="card-head">
          <div class="head-left">
            <div class="vip-badge"><span class="vip-badge-text">VIP</span></div>
            <span class="head-title">专属特权</span>
          </div>
          <div class="head-more" @click="toast('暂未开放')">
            <span class="more-text">了解更多</span>
            <img class="more-arrow" src="/static/qbox/common-arrow-right-gray.png" alt="" />
          </div>
        </div>
        <div class="privilege-list">
          <div v-for="item in privilegeList" :key="item.title" class="privilege-item">
            <img class="privilege-icon" :src="item.icon" alt="" />
            <span class="privilege-title">{{ item.title }}</span>
            <span class="privilege-desc">{{ item.desc }}</span>
          </div>
        </div>
      </div>

      <!-- 卡片二：开通/续费（原站 678×200，margin-top 24，padding 32/28） -->
      <div class="card card-open">
        <!-- 非会员态 -->
        <div v-if="!isVip" class="open-left">
          <span class="open-title">成为VIP会员</span>
          <span class="open-sub">开启你的数字藏品之旅</span>
          <div class="price-row">
            <span class="price-symbol">¥</span>
            <span class="price-num">{{ vipPrice }}</span>
            <span class="price-unit">/月</span>
          </div>
        </div>
        <!-- 会员态（原站实测：头像+昵称+会员标+有效期） -->
        <div v-else class="open-left-vip">
          <img class="vip-avatar" :src="avatarUrl" alt="头像" />
          <div class="vip-info">
            <div class="vip-name-row">
              <span class="vip-name">{{ userinfo?.nickname || 'Qbox 用户' }}</span>
              <img class="vip-crown" src="/static/qbox/mine-member.png" alt="" />
              <div class="vip-badge-sm"><span class="vip-badge-sm-text">VIP</span></div>
            </div>
            <span class="vip-expire">会员有效期：{{ vipExpireText }}</span>
          </div>
        </div>
        <div class="open-btn" @click="onOpen">
          <span class="open-btn-text">{{ isVip ? '立即续费' : '立即开通' }}</span>
        </div>
      </div>

      <!-- 会员购买说明（原站 678 宽 margin-top 40，标题 22 #dbbfa5，内容 19 #fff 行高1.8） -->
      <div class="notes">
        <span class="notes-title">会员购买说明：</span>
        <!-- 内容来自 /api/index/getConfig vip_agreement（原站抓包实录） -->
        <div class="notes-content agreement" v-html="agreement"></div>
      </div>
    </div>

    <!-- 开通VIP协议弹窗（原站 vip-modal：白底圆角，600 高协议滚动区 + 圆形勾选 + 取消/确认） -->
    <div v-if="show" class="mask" @click.self="show = false">
      <div class="modal">
        <span class="modal-title">开通VIP协议</span>
        <div class="modal-scroll">
          <div class="agreement modal-agreement" v-html="agreement"></div>
        </div>
        <div class="check-row" @click="toggleCheck">
          <div class="checkbox" :class="{ 'checkbox-active': agreed }">
            <svg v-if="agreed" class="check-icon" viewBox="0 0 24 24" fill="none">
              <path d="M5 12.5L10 17.5L19 7" stroke="#fff" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" />
            </svg>
          </div>
          <span class="check-label">我已阅读并同意以上协议</span>
        </div>
        <div class="modal-ft">
          <div class="ft-btn ft-cancel" @click="show = false"><span class="ft-cancel-text">取消</span></div>
          <div class="ft-btn ft-confirm" @click="onAction"><span class="ft-confirm-text">确认</span></div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { request } from '../../api/request'
import { useUserStore } from '../../stores/user'
import { toast } from '../../utils/toast'

const userStore = useUserStore()
const userinfo = computed(() => userStore.userinfo)

// 特权列表（原站 chunk 反解实录：4 项图标 88，标题 19 600 #f6f7f6，描述 17 #6b6c6a）
const privilegeList = [
  { icon: '/static/qbox/vip-exclusive-collectible.png', title: '专属藏品', desc: '独家空投' },
  { icon: '/static/qbox/vip-priority-purchase.png', title: '优先购买', desc: '抢先拥有' },
  { icon: '/static/qbox/vip-exclusive-rights.png', title: '专属权益', desc: '总量购买' },
  { icon: '/static/qbox/vip-exclusive-service.png', title: '专属客服', desc: '优先响应' }
]

const show = ref(false)
const agreement = ref('')
const checked = ref(false)
const configVipTime = ref(0)
const vipServiceCharge = ref('')
const havetime = ref(0)

const agreed = computed(() => checked.value)
const isVip = computed(() => !!(userinfo.value?.vip_time && havetime.value > 0))
const vipPrice = computed(() => {
  const v = vipServiceCharge.value
  return v === undefined || v === null || v === '' ? '--' : v
})
const vipExpireText = computed(() => {
  const t = userinfo.value?.vip_time
  if (!t) return '--'
  const ms = t > 1e12 ? t : t * 1000
  const d = new Date(ms)
  const p = (n) => String(n).padStart(2, '0')
  return `${d.getFullYear()}-${p(d.getMonth() + 1)}-${p(d.getDate())}`
})
const avatarUrl = computed(() => {
  const a = userinfo.value?.avatar
  if (!a || a === '/defautAvatar.png') return '/static/logo/logo.png'
  return a
})

async function init() {
  const res = await request('/api/index/getConfig', { key: 'vip_agreement,vip_time,vip_service_charge' })
  if (res.code === 1 && res.data) {
    agreement.value = res.data.vip_agreement || ''
    configVipTime.value = Number(res.data.vip_time || 0)
    vipServiceCharge.value = res.data.vip_service_charge
  }
  const u = await userStore.getUserInfo()
  if (u.code === 1 && u.data) havetime.value = Number(u.data.vip_time || 0) - Number(u.data.time || 0)
}

function toggleCheck() {
  checked.value = !checked.value
}

// 原站 onOpen：清空勾选；vip_time 配置>0 弹协议，否则 toast「暂未开放VIP开通」
function onOpen() {
  checked.value = false
  if (configVipTime.value > 0) {
    show.value = true
  } else {
    toast('暂未开放VIP开通')
  }
}

// 原站 onAction：未勾选 toast「请先勾选是否同意协议」；通过则 createVIPOrder 拉起支付
async function onAction() {
  if (!agreed.value) {
    toast('请先勾选是否同意协议')
    return
  }
  show.value = false
  const res = await request('/api/order/createVIPOrder', {})
  if (res.code === 1) {
    // 原站此处拉起支付（order_type 21）；独立项目支付通道未接入，toast 提示（标注为推断）
    toast('下单成功，支付通道暂未接入')
  }
}

onMounted(init)
</script>

<style scoped>
.vip-page {
  min-height: 100vh;
  background-color: #0a0a0a;
  background-image: url('/static/qbox/vip-bg.png');
  background-repeat: no-repeat;
  background-size: 100% auto;
  background-position: top center;
}

/* ---- 导航栏（白色版，浮于头部大图上） ---- */
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
  color: #fff;
  font-family: var(--font-medium);
  white-space: nowrap;
}

.vip-body {
  position: relative;
  width: 100%;
  display: flex;
  flex-direction: column;
  align-items: center;
  /* 原站 w-navbar 为 fixed+占位：内容 padding-top 364rpx 之外整体再下移一个导航栏高 79.7rpx（背景图不动） */
  padding: 443.7px 36px 80px;
  box-sizing: border-box;
}

/* ---- 卡片通用（vip-card-bg 金边暗底） ---- */
.card {
  width: 678px;
  background-image: url('/static/qbox/vip-card-bg.png');
  background-repeat: no-repeat;
  background-size: 100% 100%;
  border-radius: 24px;
  box-sizing: border-box;
}

/* 卡片一：专属特权 */
.card-privilege {
  padding: 32px 28px 36px;
}
.card-head {
  display: flex;
  align-items: center;
  justify-content: space-between;
}
.head-left {
  display: flex;
  align-items: center;
}
.vip-badge {
  height: 36px;
  padding: 0 12px;
  background: linear-gradient(90deg, #f3d7a0 0%, #c9a46a 100%);
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
}
.vip-badge-text {
  font-size: 20px;
  color: #2b1c08;
  font-family: var(--font-bold);
}
.head-title {
  margin-left: 12px;
  font-size: 28px;
  line-height: 1.4;
  color: #ffe6b5;
  font-family: var(--font-medium);
}
.head-more {
  display: flex;
  align-items: center;
}
.more-text {
  font-weight: 400;
  font-size: 19px;
  color: #7b7b77;
  font-family: var(--font-regular);
}
.more-arrow {
  width: 16px;
  height: 16px;
  margin-left: 4px;
  object-fit: contain;
}
.privilege-list {
  margin-top: 32px;
  display: flex;
  justify-content: space-between;
}
.privilege-item {
  flex: 1;
  display: flex;
  flex-direction: column;
  align-items: center;
}
.privilege-icon {
  width: 88px;
  height: 88px;
  object-fit: contain;
}
.privilege-title {
  margin-top: 10px;
  font-weight: 600;
  font-size: 19px;
  line-height: 1.4;
  color: #f6f7f6;
  font-family: var(--font-medium);
}
.privilege-desc {
  margin-top: 10px;
  font-weight: 400;
  font-size: 17px;
  line-height: 1.4;
  color: #6b6c6a;
  font-family: var(--font-regular);
}

/* 卡片二：开通 */
.card-open {
  height: 200px;
  margin-top: 24px;
  padding: 32px 28px;
  display: flex;
  align-items: center;
  justify-content: space-between;
}
.open-left {
  display: flex;
  flex-direction: column;
}
.open-title {
  font-size: 32px;
  color: #fff4d9;
  font-family: var(--font-bold);
}
.open-sub {
  margin-top: 8px;
  font-weight: 400;
  font-size: 19px;
  color: #dbbfa5;
  font-family: var(--font-regular);
}
.price-row {
  margin-top: 8px;
  display: flex;
  align-items: baseline;
}
.price-symbol {
  font-weight: 600;
  font-size: 28px;
  color: #dbbfa5;
  line-height: 1;
  font-family: 'AlimamaFangYuanTiVF-Thin', var(--font-regular);
}
.price-num {
  font-weight: bold;
  font-size: 32px;
  color: #dbbfa5;
  line-height: 1;
  font-family: 'AlimamaFangYuanTiVF-Thin', var(--font-regular);
}
.price-unit {
  font-weight: 400;
  font-size: 24px;
  color: #dbbfa5;
  line-height: 1;
  font-family: var(--font-regular);
}
/* 会员态 */
.open-left-vip {
  display: flex;
  align-items: center;
  flex: 1;
}
.vip-avatar {
  width: 88px;
  height: 88px;
  border-radius: 44px;
  background: #1a1a1a;
  object-fit: cover;
}
.vip-info {
  margin-left: 20px;
  display: flex;
  flex-direction: column;
}
.vip-name-row {
  display: flex;
  align-items: center;
}
.vip-name {
  font-size: 28px;
  color: #fff4d9;
  font-family: var(--font-medium);
}
.vip-crown {
  width: 28px;
  height: 28px;
  margin-left: 8px;
  object-fit: contain;
}
.vip-badge-sm {
  margin-left: 8px;
  height: 32px;
  padding: 0 10px;
  background: linear-gradient(90deg, #f3d7a0 0%, #c9a46a 100%);
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
}
.vip-badge-sm-text {
  font-size: 18px;
  color: #2b1c08;
  font-family: var(--font-bold);
}
.vip-expire {
  margin-top: 10px;
  font-size: 22px;
  color: #8c8c8c;
  font-family: var(--font-regular);
}
.open-btn {
  height: 64px;
  padding: 0 28px;
  background: linear-gradient(90deg, #f3d7a0 0%, #c9a46a 100%);
  border-radius: 32px;
  display: flex;
  align-items: center;
  justify-content: center;
}
.open-btn-text {
  font-size: 24px;
  color: #2b1c08;
  font-family: var(--font-medium);
}

/* ---- 会员购买说明 ---- */
.notes {
  width: 678px;
  margin-top: 40px;
  display: flex;
  flex-direction: column;
}
.notes-title {
  font-weight: 600;
  font-size: 22px;
  color: #dbbfa5;
  font-family: var(--font-medium);
}
.notes-content {
  margin-top: 16px;
  font-weight: 400;
  font-size: 19px;
  color: #fff;
  line-height: 1.8;
  font-family: var(--font-regular);
}
.agreement :deep(p) {
  margin: 1em 0;
  padding: 0;
}

/* ---- 协议弹窗（原站实测 @414：宽360 高≈517，按钮 138×44 r6，取消#f1f0f0/确认#0018ff） ---- */
.mask {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.6);
  z-index: 999;
  display: flex;
  align-items: center;
  justify-content: center;
}
.modal {
  width: 652px;
  background: #fff;
  border-radius: 20px;
  padding: 44px 50px 54px;
  box-sizing: border-box;
  display: flex;
  flex-direction: column;
}
.modal-title {
  text-align: center;
  font-size: 32px;
  font-weight: 600;
  color: #000;
  font-family: var(--font-medium);
}
.modal-scroll {
  height: 600px;
  margin-top: 20px;
  overflow-y: auto;
}
.modal-agreement {
  margin-top: 24px;
  /* 原站弹窗正文实测 @414：14px/行距21px，4 段 12 行全部落在 600rpx 滚动区内 */
  font-size: 25.4px;
  color: #000;
  line-height: 1.5;
}
.modal-agreement :deep(p) {
  margin: 0.75em 0;
}
.check-row {
  margin-top: 20px;
  display: flex;
  align-items: center;
}
.checkbox {
  width: 32.6px;
  height: 32.6px;
  border-radius: 50%;
  border: 1px solid #d8d8d8;
  background: #fff;
  box-sizing: border-box;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}
.checkbox-active {
  background: #0018ff;
  border-color: #0018ff;
}
.check-icon {
  width: 18px;
  height: 18px;
}
.check-label {
  margin-left: 14.5px;
  font-size: 21.7px;
  color: #333;
  font-family: var(--font-regular);
}
.modal-ft {
  margin-top: 30px;
  display: flex;
  justify-content: space-between;
}
.ft-btn {
  width: 250px;
  height: 80px;
  border-radius: 11px;
  display: flex;
  align-items: center;
  justify-content: center;
}
.ft-cancel {
  background: #f1f0f0;
}
.ft-cancel-text {
  font-size: 28px;
  font-weight: 600;
  color: #000;
  font-family: var(--font-medium);
}
.ft-confirm {
  background: #0018ff;
}
.ft-confirm-text {
  font-size: 28px;
  font-weight: 600;
  color: #fff;
  font-family: var(--font-medium);
}
</style>
