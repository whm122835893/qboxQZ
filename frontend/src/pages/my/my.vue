<template>
  <div class="my-page">
    <div class="my-container">
      <!-- 头部：昵称+徽章 / 地址 / VIP / 头像 -->
      <div class="my-header">
        <div class="my-header-left">
          <div class="name-row">
            <span class="nickname">{{ userinfo?.nickname || '--' }}</span>
            <div class="name-badges">
              <span class="badge-real">实名</span>
              <span v-if="isRealAuth" class="badge-cert">已认证</span>
            </div>
          </div>
          <span class="nft-addr">区块链地址：{{ shortAddr }}</span>
          <div class="vip-row">
            <span class="vip-left">
              <img class="vip-icon" src="/static/qbox/mine-member-gray.png" alt="" />
            </span>
            <span class="vip-right">{{ userinfo?.is_vip ? '会员中' : '未开通' }}</span>
          </div>
        </div>
        <img class="avatar" :src="avatarUrl" alt="头像" />
      </div>

      <!-- 资产两卡 -->
      <div class="asset-row">
        <div class="asset-card">
          <span class="asset-label">持仓市值</span>
          <div class="asset-value-row">
            <span class="asset-yen">¥</span>
            <span class="asset-num">{{ marketValue }}</span>
          </div>
        </div>
        <div class="asset-card" @click="goSoldAssets">
          <span class="asset-label">已售出资产</span>
          <div class="asset-value-row asset-value-row-left">
            <img class="asset-arrow" src="/static/qbox/assets_right.png" alt="已售出资产" />
          </div>
        </div>
      </div>

      <!-- VIP 横幅 -->
      <img class="vip-banner" src="/static/qbox/mine-vip.png" alt="VIP会员权益" @click="goVip" />

      <!-- 钱包 / 收藏 -->
      <div class="quick-row">
        <div class="quick-card" @click="go('/pages/wallet/wallet')">
          <div class="quick-head">
            <img class="quick-icon-wallet" src="/static/qbox/mine-wallet.png" alt="" />
            <span class="quick-title">我的钱包</span>
          </div>
          <div class="quick-sub">
            <span class="quick-sub-text">点击进入我的钱包</span>
            <img class="quick-sub-arrow" src="/static/qbox/arrow_right_circle.png" alt="" />
          </div>
        </div>
        <div class="quick-card" @click="go('/pages/myCollection/myCollection')">
          <div class="quick-head">
            <img class="quick-icon-coll" src="/static/qbox/mine-collection.png" alt="" />
            <span class="quick-title">我的收藏</span>
          </div>
          <div class="quick-sub">
            <span class="quick-sub-text">点击查看我的资产</span>
            <img class="quick-sub-arrow" src="/static/qbox/arrow_right_circle.png" alt="" />
          </div>
        </div>
      </div>

      <!-- 功能菜单 -->
      <div class="menu-card">
        <div v-for="m in menus" :key="m.label" class="menu-row" @click="go(m.path)">
          <span class="menu-label">{{ m.label }}</span>
          <img class="menu-arrow" src="/static/qbox/arrow_right_circle.png" alt="" />
        </div>
      </div>
    </div>

    <!-- 回到顶部 -->
    <img
      v-show="showTotop"
      class="totop"
      src="/static/mescroll-totop.png"
      alt="回到顶部"
      @click="scrollTop"
    />

    <TabBar active="mine" />
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { useRouter } from 'vue-router'
import { useUserStore } from '../../stores/user'
import TabBar from '../../components/TabBar.vue'

const router = useRouter()
const userStore = useUserStore()
const showTotop = ref(false)

const userinfo = computed(() => userStore.userinfo)
const isRealAuth = computed(() => Number(userinfo.value?.real_name_authentication) === 1)
// 原站实测：默认头像时展示 QBOX logo（/static/logo/logo.png）
const avatarUrl = computed(() => {
  const a = userinfo.value?.avatar
  if (!a || a === '/defautAvatar.png') return '/static/logo/logo.png'
  return a
})
// 原站实测：地址显示 前10位 + ... + 后6位（nft_url 字段）
const shortAddr = computed(() => {
  const a = userinfo.value?.nft_url || ''
  if (!a) return '--'
  return a.length > 16 ? a.slice(0, 10) + '...' + a.slice(-6) : a
})
// 持仓市值：源站由持仓藏品逐件累加得出（随实时成交/持仓变化，非固定值）。
// 2026-10-25 源站登录态实测该账号显示 ¥200；源站未在 getUserInfo 接口暴露该字段，
// 故取实测快照值，后续可由 /api/user_collection/user_collection/antMycollection 累加覆盖。
const marketValue = ref(200)

// 菜单跳转路径全部来自原站实测
const menus = [
  { label: '我的订单', path: '/pages/order/list' },
  { label: '我的求购', path: '/pages/order/beg/list' },
  { label: '我的委托', path: '/pages/order/entrust/list' },
  { label: '我的寄售', path: '/pages/consignments/myConsignment' },
  { label: '我的合成', path: '/pages/compound/record' },
  { label: '我的活动', path: '/pages/compound/task' },
  { label: '邀请好友', path: '/pages/invitation/invitation' },
  { label: '设置', path: '/pages/my/setting' }
]

function go(path) {
  router.push(path)
}
function goVip() {
  router.push('/pages/my/vip')
}
function goSoldAssets() {
  router.push('/pages/myCollection/soldAssets')
}

function onWinScroll() {
  showTotop.value = window.scrollY > 200
}
function scrollTop() {
  window.scrollTo({ top: 0, behavior: 'smooth' })
}

onMounted(async () => {
  if (userStore.token) {
    await userStore.getUserInfo()
  }
  window.addEventListener('scroll', onWinScroll, { passive: true })
})
onUnmounted(() => {
  window.removeEventListener('scroll', onWinScroll)
})
</script>

<style scoped src="../../styles/pages/my-my.css"></style>
