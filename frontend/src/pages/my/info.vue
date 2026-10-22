<template>
  <div class="ui-page">
    <!-- 导航栏（白底，标题 18px 居中，与其他白底子页同款） -->
    <div class="navbar">
      <img class="navbar-back" src="/static/cw/back_btn.png" alt="" @click="$router.back()" />
      <span class="nav-title">个人信息</span>
    </div>

    <!-- 三行资料（原站实测：行1 头像 h100、行2 昵称 h55、行3 手机号 h55，bg#fff 行间 1px，label fs15，值 fs13，底部 #f8f8f9） -->
    <div class="rows">
      <div class="row row-avatar" @click="showCropper = true">
        <span class="row-label">头像</span>
        <img class="row-avatar-img" :src="avatarSrc" alt="" />
      </div>
      <div class="row" @click="openNick">
        <span class="row-label">昵称</span>
        <span class="row-value">{{ nickname }}</span>
        <img class="row-arrow" src="/static/qbox/arrow_right_circle.png" alt="" />
      </div>
      <div class="row">
        <span class="row-label">手机号</span>
        <span class="row-value">{{ mobileMasked }}</span>
        <img class="row-arrow" src="/static/qbox/arrow_right_circle.png" alt="" />
      </div>
    </div>

    <NickModal :visible="showNick" :nickname="nickname" @close="showNick = false" />
    <AvatarCropper :visible="showCropper" :avatar="avatarSrc" @close="showCropper = false" />
  </div>

</template>
<script setup>
import { ref, computed, onMounted } from 'vue'
import { useUserStore } from '../../stores/user'
import NickModal from '../../components/NickModal.vue'
import AvatarCropper from '../../components/AvatarCropper.vue'

const userStore = useUserStore()
const showNick = ref(false)
const nickInput = ref('')
const showCropper = ref(false)
const showPreview = ref(false)
const cropSrc = ref('')

// 原站实测：avatar=/defautAvatar.png 时页面实际显示 static/good/user_default_icon.png（默认图标兜底）
const avatarSrc = computed(() => {
  const a = userStore.userinfo?.avatar
  if (!a || a.includes('defautAvatar')) return '/static/good/user_default_icon.png'
  return a
})
const nickname = computed(() => userStore.userinfo?.nickname || '--')
// 原站同款打码：175****1293（前3后4）
const mobileMasked = computed(() => {
  const m = userStore.userinfo?.mobile || ''
  return m.length === 11 ? m.slice(0, 3) + '****' + m.slice(7) : m
})

function openNick() {
  showNick.value = true
}

onMounted(async () => {
  if (userStore.token) await userStore.getUserInfo()
})
</script>

<style scoped>
.ui-page {
  min-height: 100vh;
  background: #f8f8f9;
}
/* 导航栏（白底，标题 18px 居中） */
.navbar {
  position: relative;
  height: 79.7px;
  background: #fff;
  display: flex;
  align-items: center;
  padding: 0 23.6px;
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
/* 行（原站实测：行1 h100、行2/3 h55，bg#fff padding 30.8，行间 1px 灰，label fs27.2 #000，值 fs23.6 #000） */
.rows {
  margin-top: 30.8px; /* 原站实测：行区与导航间隔 17px@414 */
}
.row {
  background: #fff;
  padding: 0 30.8px;
  display: flex;
  align-items: center;
  margin-bottom: 1.8px;
}
.row-avatar {
  height: 181.1px;
}
.row:not(.row-avatar) {
  height: 99.6px;
}
.row-label {
  font-size: 27.2px;
  color: #000;
  font-family: var(--font-regular);
}
.row-avatar-img {
  margin-left: auto;
  width: 119.6px;
  height: 119.6px;
  border-radius: 50%;
  object-fit: cover;
}
.row-value {
  margin-left: auto;
  font-size: 23.6px;
  color: #000;
  font-family: var(--font-regular);
}
.row-arrow {
  width: 29px;
  height: 29px;
  margin-left: 9px;
  flex-shrink: 0;
}
</style>
