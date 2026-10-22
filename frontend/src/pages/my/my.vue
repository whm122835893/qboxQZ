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
// 持仓市值：实测该账号显示 ¥0（原站计算源未在接口中暴露，默认 0）
const marketValue = ref(0)

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

<style scoped>
.my-page {
  min-height: 100vh;
  background-color: #f8f8f9;
}
.my-container {
  width: 100%;
  display: flex;
  flex-direction: column;
  align-items: center;
  padding: 181.2px 34.4px 23.6px;
  box-sizing: border-box;
  background-color: #f8f8f9;
  background-image: url('/static/qbox/my-bg.png');
  background-repeat: no-repeat;
  background-size: 100% 100%;
  background-position: center top;
}

/* ---- 头部 ---- */
.my-header {
  width: 677.5px;
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
}
.my-header-left {
  flex: 1;
  display: flex;
  flex-direction: column;
  gap: 16px;
  padding-top: 16.3px;
  min-width: 0;
}
.name-row {
  display: flex;
  align-items: center;
  gap: 27.2px;
}
.nickname {
  font-weight: bold;
  font-size: 41.7px;
  color: #000;
  line-height: 1.2;
  font-family: var(--font-bold);
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}
.name-badges {
  height: 39.9px;
  display: flex;
  align-items: stretch;
  background: #c5c5c5;
  border-radius: 0 19.9px 19.9px 0;
  overflow: hidden;
  flex-shrink: 0;
}
.badge-real {
  padding: 0 16.3px 0 14.5px;
  display: flex;
  align-items: center;
  font-size: 19.9px;
  color: #111;
  font-family: var(--font-medium);
}
.badge-cert {
  padding: 0 14.5px 0 21.7px;
  display: flex;
  align-items: center;
  background: #3f7cff;
  font-size: 19.9px;
  color: #fff;
  font-family: var(--font-medium);
}
.nft-addr {
  font-weight: 400;
  font-size: 18.1px;
  color: #939393;
  line-height: 1.4;
  font-family: var(--font-regular);
}
.vip-row {
  align-self: flex-start;
  height: 43.5px;
  display: flex;
  align-items: stretch;
}
.vip-left {
  height: 43.5px;
  padding: 0 12.7px;
  background: #010101;
  border-radius: 12.7px 0 0 12.7px;
  display: flex;
  align-items: center;
}
.vip-icon {
  width: 30.8px;
  height: 30.8px;
}
.vip-right {
  width: 72.5px;
  height: 43.5px;
  background: #e8e8e8;
  border-radius: 0 12.7px 12.7px 0;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 16.3px;
  color: #111;
  font-family: var(--font-medium);
}
.avatar {
  width: 163px;
  height: 163px;
  border-radius: 83.3px;
  background: #fff;
  box-shadow: rgba(0, 0, 0, 0.08) 0 5.4px 14.5px;
  flex-shrink: 0;
  object-fit: cover;
}

/* ---- 资产两卡 ---- */
.asset-row {
  width: 677.5px;
  margin-top: 53.6px;
  display: flex;
  gap: 21.7px;
}
.asset-card {
  width: 327.9px;
  height: 121.4px;
  background: #fff;
  border-radius: 21.7px;
  box-shadow: rgba(0, 0, 0, 0.03) 0 7.2px 27.2px;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 16px;
}
.asset-label {
  height: 29px;
  line-height: 29px;
  font-weight: 400;
  font-size: 21.7px;
  color: #1f1f1f;
  font-family: var(--font-regular);
}
.asset-value-row {
  height: 47.1px;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 3px;
}
.asset-value-row-left {
  justify-content: flex-start;
  width: 99.6px;
}
.asset-yen {
  font-weight: normal;
  font-size: 23.6px;
  color: #111;
  font-family: 'AlimamaFangYuanTiVF-Thin', var(--font-regular);
}
.asset-num {
  font-weight: normal;
  font-size: 36.2px;
  color: #111;
  line-height: 47.1px;
  font-family: var(--font-regular);
}
.asset-arrow {
  width: 34.4px;
  height: 23.6px;
}

/* ---- VIP 横幅 ---- */
.vip-banner {
  width: 677.5px;
  margin-top: 36.2px;
  height: 166.7px;
}

/* ---- 钱包 / 收藏 ---- */
.quick-row {
  width: 677.5px;
  margin-top: 30.8px;
  display: flex;
  gap: 18.1px;
}
.quick-card {
  width: 329.7px;
  height: 152.2px;
  background: #fff;
  border-radius: 23.6px;
  box-shadow: rgba(17, 24, 39, 0.04) 0 7.2px 27.2px;
  padding: 27.2px 23.6px;
  box-sizing: border-box;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
}
.quick-head {
  display: flex;
  align-items: center;
  gap: 21.7px;
}
.quick-icon-wallet {
  width: 36.2px;
  height: 27.2px;
}
.quick-icon-coll {
  width: 39.9px;
  height: 43.5px;
}
.quick-title {
  font-weight: bold;
  font-size: 27.2px;
  color: #1f1f1f;
  font-family: var(--font-bold);
}
.quick-sub {
  display: flex;
  align-items: center;
  gap: 7.2px;
}
.quick-sub-text {
  font-weight: 500;
  font-size: 18.1px;
  color: #b5b4b4;
  font-family: var(--font-regular);
}
.quick-sub-arrow {
  width: 14.5px;
  height: 14.5px;
}

/* ---- 功能菜单 ---- */
.menu-card {
  width: 677.5px;
  margin-top: 43.5px;
  background: #fff;
  border-radius: 30.8px;
  box-shadow: rgba(0, 0, 0, 0.03) 0 7.2px 27.2px;
  overflow: hidden;
}
.menu-row {
  padding: 0 47.1px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  height: 94.2px;
}
.menu-label {
  flex: 1;
  font-weight: bold;
  font-size: 25.4px;
  color: #1f1f1f;
  font-family: var(--font-bold);
}
.menu-arrow {
  width: 30.8px;
  height: 30.8px;
}

/* ---- 回到顶部 ---- */
.totop {
  position: fixed;
  right: 19.9px;
  bottom: 119.6px;
  width: 70.7px;
  height: 72.5px;
  border-radius: 50%;
  z-index: 9990;
}
</style>
