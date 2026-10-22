<template>
  <!-- 汇付钱包内嵌页的本地复刻（原站为 webview 加载第三方汇付 H5；规则9 独立实现，余额打码/静态数据） -->
  <div class="huifu-page">
    <!-- 顶部：标题 + 铃铛 -->
    <div class="hf-head">
      <span class="hf-title">我的钱包</span>
      <svg class="hf-bell" viewBox="0 0 24 24" width="22" height="22" fill="#fff"><path d="M12 22a2.5 2.5 0 0 0 2.45-2h-4.9A2.5 2.5 0 0 0 12 22zm8-6v-5c0-3.07-1.63-5.64-4.5-6.32V4a1.5 1.5 0 0 0-3 0v.68C9.64 5.36 8 7.92 8 11v5l-2 2v1h16v-1l-2-2z"/></svg>
    </div>

    <!-- 金色银行卡 -->
    <div class="gold-card">
      <span class="gc-label">钱包余额(元)</span>
      <span class="gc-amount">*****</span>
      <span class="gc-limit">查看限额</span>
      <span class="gc-no">NO.</span>
    </div>
    <div class="gold-notch"></div>

    <!-- 提现 / 充值 -->
    <div class="ops-row">
      <span class="ops-item">提现</span>
      <span class="ops-divider"></span>
      <span class="ops-item">充值</span>
    </div>

    <!-- 权益 Tab -->
    <div class="benefit-row">
      <span class="benefit-item">权益卡</span>
      <span class="benefit-divider"></span>
      <span class="benefit-item">优惠券</span>
      <span class="benefit-divider"></span>
      <span class="benefit-item">津贴</span>
    </div>

    <!-- 功能列表 -->
    <div class="menu-list">
      <div v-for="m in menus" :key="m.label" class="menu-item">
        <span class="menu-icon" v-html="m.icon"></span>
        <span class="menu-label">{{ m.label }}</span>
        <span v-if="m.extra" class="menu-extra">{{ m.extra }}</span>
        <span class="menu-arrow">›</span>
      </div>
    </div>

    <span class="hf-footer">- 本服务由汇付支付提供 -</span>
  </div>
</template>

<script setup>
import { useRouter } from 'vue-router'
const router = useRouter()

// 图标：深色圆形底 + 白色描边 SVG（P0-1 图标规范；原页面图标为汇付侧资源，独立项目以 SVG 等效绘制）
const ic = (path) =>
  `<svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="#e8d9a0" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">${path}</svg>`

const menus = [
  { label: '账单管理', icon: ic('<rect x="4" y="3" width="16" height="18" rx="2"/><path d="M8 8h8M8 12h8M8 16h5"/>') },
  { label: '银行卡管理', extra: '去绑卡', icon: ic('<rect x="2" y="6" width="20" height="13" rx="2"/><path d="M2 10h20"/>') },
  { label: '支付管理', icon: ic('<circle cx="12" cy="12" r="9"/><path d="M9 12l2 2 4-4"/>') },
  { label: '账户管理', icon: ic('<circle cx="12" cy="8" r="4"/><path d="M4 21c0-4 3.6-6 8-6s8 2 8 6"/>') },
  { label: '更多', icon: ic('<circle cx="5" cy="12" r="1.6"/><circle cx="12" cy="12" r="1.6"/><circle cx="19" cy="12" r="1.6"/>') }
]

function goBack() {
  history.length > 1 ? router.back() : router.replace('/pages/wallet/wallet')
}
</script>

<style scoped>
.huifu-page {
  min-height: 100vh;
  background-color: #2e2c29;
  padding-bottom: 40px;
}
/* 顶部 */
.hf-head {
  position: relative;
  height: 79.7px;
  display: flex;
  align-items: center;
  justify-content: center;
}
.hf-title {
  font-size: 32.6px;
  font-weight: 500;
  color: #fff;
  font-family: var(--font-medium);
}
.hf-bell {
  position: absolute;
  right: 30.8px;
  top: 50%;
  transform: translateY(-50%);
}

/* 金色卡 */
.gold-card {
  position: relative;
  margin: 14.5px 21.7px 0;
  height: 260px;
  border-radius: 14.5px 14.5px 0 0;
  background: linear-gradient(135deg, #e8d9a0 0%, #d9be7a 45%, #cfae62 100%);
  padding: 30px 27.2px;
  box-sizing: border-box;
  display: flex;
  flex-direction: column;
}
.gold-card::after {
  content: '';
  position: absolute;
  right: -30px;
  top: 40px;
  width: 180px;
  height: 180px;
  background: rgba(255, 255, 255, 0.14);
  transform: rotate(30deg);
}
.gc-label {
  font-size: 23.6px;
  color: #6b5b2a;
  font-family: var(--font-regular);
}
.gc-amount {
  margin-top: 12.7px;
  font-size: 47.1px;
  font-weight: bold;
  color: #4a3d1a;
  letter-spacing: 4px;
  font-family: var(--font-bold);
}
.gc-limit {
  position: absolute;
  right: 27.2px;
  top: 60px;
  padding: 7.2px 18.1px;
  border: 1.8px solid #6b5b2a;
  border-radius: 29px;
  font-size: 21.7px;
  color: #4a3d1a;
  font-family: var(--font-regular);
}
.gc-no {
  position: absolute;
  left: 27.2px;
  bottom: 25.4px;
  font-size: 25.4px;
  letter-spacing: 3px;
  color: #7a682f;
  font-family: var(--font-regular);
}
.gold-notch {
  height: 60px;
  background: #232120;
  margin: 0 21.7px;
  clip-path: polygon(0 0, 46% 0, 50% 60%, 54% 0, 100% 0, 100% 100%, 0 100%);
}

/* 提现 / 充值 */
.ops-row {
  display: flex;
  background: #232120;
  margin: -18.1px 21.7px 0;
  border-radius: 0 0 14.5px 14.5px;
  padding-bottom: 25.4px;
}
.ops-item {
  flex: 1;
  text-align: center;
  font-size: 29px;
  color: #fff;
  font-family: var(--font-regular);
}
.ops-divider {
  width: 1.8px;
  height: 32.6px;
  background: rgba(255, 255, 255, 0.2);
  margin-top: 5px;
}

/* 权益 Tab */
.benefit-row {
  display: flex;
  margin-top: 25.4px;
  padding: 0 40px;
}
.benefit-item {
  flex: 1;
  text-align: center;
  font-size: 27.2px;
  color: #fff;
  font-family: var(--font-regular);
}
.benefit-divider {
  width: 1.8px;
  height: 27.2px;
  background: rgba(255, 255, 255, 0.25);
}

/* 功能列表 */
.menu-list {
  margin-top: 25.4px;
  background: #fff;
}
.menu-item {
  height: 103px;
  margin-left: 21.7px;
  padding-right: 21.7px;
  border-bottom: 1.8px solid #f2f2f2;
  display: flex;
  align-items: center;
}
.menu-item:last-child {
  border-bottom: none;
}
.menu-icon {
  width: 50.7px;
  height: 50.7px;
  border-radius: 50%;
  background: #2e2c29;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}
.menu-label {
  flex: 1;
  margin-left: 21.7px;
  font-size: 27.2px;
  color: #111;
  font-family: var(--font-regular);
}
.menu-extra {
  font-size: 23.6px;
  color: #999;
  font-family: var(--font-regular);
  margin-right: 7.2px;
}
.menu-arrow {
  font-size: 36.2px;
  color: #ccc;
  font-family: var(--font-regular);
}

.hf-footer {
  display: block;
  text-align: center;
  margin-top: 43.5px;
  font-size: 23.6px;
  color: #999;
  font-family: var(--font-regular);
}
</style>
