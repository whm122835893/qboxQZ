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

<style scoped src="../../styles/pages/my-vip-p0.css"></style>
<style scoped src="../../styles/pages/my-vip-p1.css"></style>
