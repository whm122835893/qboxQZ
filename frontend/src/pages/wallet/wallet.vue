<template>
  <div class="wallet-page">
    <!-- 导航栏 -->
    <div class="wallet-navbar">
      <img class="nav-back" src="/static/cw/back_btn.png" alt="返回" @click="goBack" />
      <span class="nav-title">我的钱包</span>
    </div>

    <div class="wallet-container">
      <!-- 合规提示条 -->
      <div class="notice-bar">
        <span class="notice-text">* 所有资金均由第三方持牌支付机构进行支付、清算</span>
      </div>

      <!-- 钱包卡片 ×3（实测：A 蓝/已开通，B 青/C 绿/敬请期待） -->
      <div
        v-for="w in wallets"
        :key="w.name"
        class="wallet-card"
        @click="w.open && openWallet(w)"
      >
        <div class="card-head">
          <img class="card-icon" :src="w.icon" alt="" />
          <div class="card-info">
            <span class="card-name">{{ w.name }}</span>
            <span class="card-desc">{{ w.desc }}</span>
          </div>
          <span class="card-pill" :class="{ off: !w.open }">{{ w.open ? '已开通' : '敬请期待对接中' }}</span>
        </div>
        <div class="card-footer" :style="{ background: w.footerColor }">
          <span class="footer-label">余额</span>
          <span class="footer-amount">{{ w.balance }}</span>
          <span class="footer-arrow">›</span>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useUserStore } from '../../stores/user'

const router = useRouter()
const userStore = useUserStore()

const balanceA = computed(() => userStore.userinfo?.money ?? '0.00')

// 三张钱包卡：状态/文案/底色均来自原站实测（A 蓝 #2a51ef 已开通；B 青 #4a8eb0；C 绿 #6b9a4a）
const wallets = ref([
  { name: '钱包A', desc: '安全便捷，交易更高效', open: true, icon: '/static/icon/huifupay.png', footerColor: 'rgb(42, 81, 239)', balance: '0.00' },
  { name: '钱包B', desc: '敬请期待对接中', open: false, icon: '/static/logo/logo.png', footerColor: 'rgb(74, 142, 176)', balance: '0.00' },
  { name: '钱包C', desc: '敬请期待对接中', open: false, icon: '/static/logo/logo.png', footerColor: 'rgb(107, 154, 74)', balance: '0.00' }
])

function openWallet() {
  // 原站实测：钱包A 点击进入汇付钱包内嵌页；独立项目复刻为本地页面（不依赖汇付服务）
  router.push('/pages/wallet/huifuDetail')
}
function goBack() {
  history.length > 1 ? router.back() : router.replace('/pages/my/my')
}

onMounted(async () => {
  if (userStore.token) await userStore.getUserInfo()
  wallets.value[0].balance = balanceA.value
})
</script>

<style scoped>
.wallet-page {
  min-height: 100vh;
  background-color: #fff;
}
.wallet-navbar {
  position: relative;
  width: 100%;
  height: 79.7px;
  display: flex;
  align-items: center;
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
  font-size: 32.6px;
  font-weight: 500;
  color: #000;
  font-family: var(--font-medium);
}
.wallet-container {
  width: 100%;
  display: flex;
  flex-direction: column;
  align-items: center;
  padding: 23.6px 30.8px 79.7px;
  box-sizing: border-box;
  gap: 23.6px;
}

/* 合规提示条 */
.notice-bar {
  width: 684.9px;
  padding: 19.9px 23.6px;
  box-sizing: border-box;
  background: #eef2ff;
  border-radius: 14.5px;
  display: flex;
  align-items: flex-start;
}
.notice-text {
  font-size: 23.6px;
  line-height: 1.5;
  color: #2a51ef;
  font-family: 'MiSans-Regular', sans-serif;
}

/* 钱包卡 */
.wallet-card {
  width: 684.9px;
  border-radius: 23.6px;
  overflow: hidden;
  box-shadow: rgba(0, 0, 0, 0.06) 0 7.2px 23.6px;
}
.card-head {
  width: 100%;
  padding: 27.2px 27.2px 23.6px;
  box-sizing: border-box;
  display: flex;
  align-items: center;
  background: #fff;
}
.card-icon {
  width: 79.7px;
  height: 79.7px;
  flex-shrink: 0;
  border-radius: 14.5px;
}
.card-info {
  flex: 1;
  min-width: 0;
  display: flex;
  flex-direction: column;
  gap: 14.5px;
  margin-left: 14.5px;
}
.card-name {
  font-size: 30.8px;
  line-height: 1.2;
  color: #111;
  font-family: 'MiSans-Semibold', sans-serif;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}
.card-desc {
  font-size: 23.6px;
  line-height: 1.3;
  color: #333;
  font-family: 'MiSans-Regular', sans-serif;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}
.card-pill {
  flex-shrink: 0;
  padding: 7.2px 16.3px;
  border-radius: 23.6px;
  background: rgba(42, 81, 239, 0.12);
  font-size: 21.7px;
  color: #2a51ef;
  font-family: 'MiSans-Regular', sans-serif;
  line-height: 1;
}
.card-pill.off {
  background: #f0f0f0;
  color: #999;
}
.card-footer {
  width: 100%;
  height: 83.3px;
  padding: 0 27.2px;
  box-sizing: border-box;
  display: flex;
  align-items: center;
  gap: 10px;
}
.footer-label {
  font-size: 27.2px;
  color: #fff;
  font-family: 'MiSans-Regular', sans-serif;
}
.footer-amount {
  flex: 1;
  font-size: 34.4px;
  color: #fff;
  font-family: 'MiSans-Regular', sans-serif;
  line-height: 1;
  opacity: 0.95;
}
.footer-arrow {
  font-size: 34.4px;
  color: #fff;
  font-family: var(--font-regular);
}
</style>
