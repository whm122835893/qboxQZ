<template>
  <div class="setting-page">
    <!-- 导航栏 -->
    <div class="setting-navbar">
      <img class="nav-back" src="/static/cw/back_btn.png" alt="返回" @click="goBack" />
      <span class="nav-title">设置</span>
    </div>

    <div class="setting-container">
      <div v-for="row in rows" :key="row.label" class="setting-row" @click="onRow(row)">
        <span class="row-label">{{ row.label }}</span>
        <!-- 头像行 -->
        <img v-if="row.label === '头像'" class="row-avatar" :src="avatarUrl" alt="头像" />
        <!-- 昵称/手机号值 -->
        <span v-if="row.value !== undefined" class="row-value">{{ row.value }}</span>
        <img v-if="row.arrow" class="row-arrow" src="/static/qbox/arrow_right_circle.png" alt="" />
      </div>
    </div>

    <!-- 退出登录（固定底部，原站 378×45 r22 #0018ff） -->
    <div class="logout-wrap">
      <div class="logout-btn" @click="logoutConfirm = true">
        <span class="logout-text">退出登录</span>
      </div>
    </div>

    <!-- 退出登录确认弹窗（原站点击行为未观测到 UI，按 uni-modal 通用样式实现，标注为推断） -->
    <div v-if="logoutConfirm" class="mask">
      <div class="modal">
        <span class="modal-title">提示</span>
        <span class="modal-body">确定退出登录吗？</span>
        <div class="modal-ft">
          <span class="modal-btn" @click="logoutConfirm = false">取消</span>
          <span class="modal-btn modal-btn-primary" @click="doLogout">确定</span>
        </div>
      </div>
    </div>
  </div>
  <NickModal :visible="showNick" :nickname="userinfo?.nickname || '--'" @close="showNick = false" />
  <AvatarCropper :visible="showCropper" :avatar="avatarUrl" @close="showCropper = false" />
</template>

<script setup>
import NickModal from '../../components/NickModal.vue'
import AvatarCropper from '../../components/AvatarCropper.vue'

import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useUserStore } from '../../stores/user'

const router = useRouter()
const userStore = useUserStore()
const logoutConfirm = ref(false)

const userinfo = computed(() => userStore.userinfo)
// 原站实测手机号打码格式：175****1293
const maskedMobile = computed(() => {
  const m = userinfo.value?.mobile || ''
  return m.length === 11 ? m.slice(0, 3) + '****' + m.slice(7) : m
})
// 源站实测（2026-10-25 登录态）：设置页昵称/手机号显示为「--」（源站该页未回填用户资料）
const avatarUrl = computed(() => {
  const a = userinfo.value?.avatar
  return !a || a === '/defautAvatar.png' ? '/static/logo/logo.png' : a
})

// 跳转路径全部来自原站实测
const rows = computed(() => [
  { label: '头像', avatar: true, arrow: true }, // 头像点击 → 头像裁剪（原站为内置裁剪弹层，独立项目待后端上传接口后接入）
  { label: '昵称', value: '--', arrow: true }, // 昵称点击 → 昵称编辑弹层（同上）
  { label: '手机号', value: '--' },
  { label: '我的认证', path: '/pages/certification/certification', arrow: true },
  { label: '我的地址', path: '/pages/my/address/list', arrow: true },
  { label: '操作密码', path: '/pages/my/actionPwd/setPwd', arrow: true },
  { label: '注销账号', path: '/pages/my/cancellation', arrow: true }
])

function go(path) {
  router.push(path)
}
function onRow(row) {
  // 原站实测：点头像 → 头像裁剪弹层；点昵称 → 修改昵称弹窗（均在设置页内以弹层呈现，不跳转）
  if (row.label === '头像') { showCropper.value = true; return }
  if (row.label === '昵称') { showNick.value = true; return }
  if (row.path) go(row.path)
}
const showNick = ref(false)
const showCropper = ref(false)
function doLogout() {
  userStore.logout()
  logoutConfirm.value = false
  router.replace('/pages/login/login')
}
function goBack() {
  history.length > 1 ? router.back() : router.replace('/pages/my/my')
}

onMounted(async () => {
  if (userStore.token) await userStore.getUserInfo()
})
</script>

<style scoped>
.setting-page {
  min-height: 100vh;
  background-color: #f8f8f9;
  padding-bottom: 179.1px;
  box-sizing: border-box;
}
.setting-navbar {
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
  font-weight: 700;
  color: #000;
  font-family: var(--font-bold);
}
.setting-container {
  width: 677.5px;
  margin-top: 23.6px;
  margin-left: 34.4px;
  display: flex;
  flex-direction: column;
  gap: 16px;
}
.setting-row {
  height: 94.2px;
  padding: 0 39.9px;
  box-sizing: border-box;
  background: #fff;
  border-radius: 25.4px;
  display: flex;
  align-items: center;
}
.row-label {
  flex: 1;
  font-weight: bold;
  font-size: 25.4px;
  color: #1f1f1f;
  font-family: var(--font-bold);
}
.row-avatar {
  width: 59.8px;
  height: 59.8px;
  border-radius: 14.5px;
}
.row-value {
  font-size: 23.6px;
  color: #939393;
  font-family: var(--font-regular);
  margin-right: 12.7px;
  max-width: 400px;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}
.row-arrow {
  width: 30.8px;
  height: 30.8px;
  margin-left: 12.7px;
}

/* 退出登录 */
.logout-wrap {
  position: fixed;
  left: 0;
  right: 0;
  bottom: 0;
  z-index: 10;
  padding-bottom: 47.1px;
  display: flex;
  justify-content: center;
}
.logout-btn {
  width: 684.9px;
  height: 81.5px;
  background: #0018ff;
  border-radius: 39.9px;
  display: flex;
  align-items: center;
  justify-content: center;
}
.logout-text {
  font-size: 27.2px;
  color: #fff;
  font-family: var(--font-medium);
}

/* 确认弹窗（uni-modal 通用样式） */
.mask {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.4);
  z-index: 9998;
  display: flex;
  align-items: center;
  justify-content: center;
}
.modal {
  width: 477.3px;
  background: #fff;
  border-radius: 25.4px;
  padding: 36.2px 29px 0;
  box-sizing: border-box;
  display: flex;
  flex-direction: column;
  align-items: center;
}
.modal-title {
  font-weight: bold;
  font-size: 31.7px;
  color: #333;
  font-family: var(--font-bold);
}
.modal-body {
  margin-top: 21.7px;
  font-size: 27.2px;
  color: #333;
  font-family: var(--font-regular);
}
.modal-ft {
  width: 100%;
  margin-top: 36.2px;
  border-top: 1.8px solid #ebebeb;
  display: flex;
}
.modal-btn {
  flex: 1;
  height: 79.7px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 29px;
  color: #000;
  font-family: var(--font-regular);
}
.modal-btn-primary {
  color: #007aff;
  border-left: 1.8px solid #ebebeb;
}
</style>
