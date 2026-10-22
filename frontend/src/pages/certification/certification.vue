<template>
  <div class="cert-page">
    <!-- 导航栏 -->
    <div class="cert-navbar">
      <img class="nav-back" src="/static/cw/back_btn.png" alt="返回" @click="goBack" />
      <span class="nav-title">实名认证</span>
    </div>

    <!-- 已认证态（原站实测：pt379 艺术区 + 您已实名认证 + 信息卡 + 安全提示） -->
    <div v-if="isAuthed" class="cert-container">
      <div class="authed-col">
        <span class="authed-title">您已实名认证</span>
        <div class="id-card">
          <img class="id-card-bg" src="/static/qbox/certification-card-bg.png" alt="" />
          <div class="id-card-info">
            <span class="ic-label">姓名</span>
            <span class="ic-name">{{ realName }}</span>
            <span class="ic-label ic-mt">身份证号</span>
            <span class="ic-idcard">{{ idCardMasked }}</span>
          </div>
        </div>
        <div class="security-row">
          <img class="security-icon" src="/static/qbox/certification-security.png" alt="" />
          <span class="security-text">信息安全保障中</span>
        </div>
      </div>
    </div>

    <!-- 未认证态（表单结构提取自原站编译产物：w678 白卡 r26 + 真实姓名/身份证号 + 联系方式 + 底部认证按钮） -->
    <div v-else class="cert-container cert-form-wrap">
      <div class="form-card">
        <div class="form-field">
          <span class="field-label">真实姓名</span>
          <input
            v-model="form.realName"
            class="field-input"
            type="text"
            placeholder="请输入真实姓名"
            placeholder-class="field-placeholder"
          />
        </div>
        <div class="field-divider"></div>
        <div class="form-field">
          <span class="field-label">身份证号</span>
          <input
            v-model="form.idCard"
            class="field-input"
            type="text"
            placeholder="请输入身份证号"
            placeholder-class="field-placeholder"
          />
        </div>
      </div>

      <div class="contact-row">
        <span class="contact-label">联系方式</span>
        <span class="contact-value">{{ maskedMobile }}</span>
      </div>

      <div class="tip-row">
        <span class="tip-icon">i</span>
        <span class="tip-text">*实名认证仅限年满18周岁（不含）到65周岁（含）之间的中国大陆用户，您填写的实名认证信息，须和您的注册手机号所绑定的，身份信息一致未经您的授权，您的身份信息不会用于其他用途</span>
      </div>

      <!-- 提交确认弹层（原站 w-toast 实测文案） -->
      <div v-if="showConfirm" class="mask">
        <div class="toast-card">
          <span class="toast-title">确认提交</span>
          <span class="toast-content">实名信息认证后不可修改,请核验信息无误后再提交</span>
          <div class="toast-btns">
            <span class="toast-cancel" @click="showConfirm = false">取消</span>
            <span class="toast-ok" @click="submitCert">认证</span>
          </div>
        </div>
      </div>
    </div>

    <!-- 未认证底部按钮 -->
    <div v-if="!isAuthed" class="submit-wrap">
      <div class="submit-btn" :class="{ able }" @click="trySubmit">
        <span class="submit-text">认证</span>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useUserStore } from '../../stores/user'
import { request } from '../../api/request'

const router = useRouter()
const userStore = useUserStore()

const form = ref({ realName: '', idCard: '' })
const showConfirm = ref(false)

const isAuthed = computed(() => Number(userStore.userinfo?.real_name_authentication) === 1)
const realName = computed(() => userStore.userinfo?.real_name || '--')
// 原站实测显示：520******838（脱敏规则 前3+******+后3）
const idCardMasked = computed(() => {
  const id = userStore.userinfo?.id_card || '520******838'
  return id
})
const maskedMobile = computed(() => {
  const m = userStore.userinfo?.mobile || ''
  return m.length === 11 ? m.slice(0, 3) + '****' + m.slice(7) : m
})
const able = computed(() => form.value.realName.trim().length > 0 && form.value.idCard.trim().length > 0)

function trySubmit() {
  if (!able.value) return
  showConfirm.value = true
}
async function submitCert() {
  showConfirm.value = false
  const res = await request('/api/user/realName', {
    real_name: form.value.realName.trim(),
    id_card: form.value.idCard.trim()
  })
  // 实测流程：提交后跳认证结果页（成功→已认证态；失败→失败态）
  router.replace(res.code === 1 ? '/pages/certification/certification' : '/pages/certification/certificationStatus')
}
function goBack() {
  history.length > 1 ? router.back() : router.replace('/pages/my/my')
}

onMounted(async () => {
  if (userStore.token) await userStore.getUserInfo()
})
</script>

<style scoped>
.cert-page {
  min-height: 100vh;
  background-color: #f5f7fb;
}
.cert-navbar {
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
  font-size: 32.6px;
  font-weight: 500;
  color: #000;
  font-family: var(--font-medium);
}
.cert-container {
  width: 100%;
  display: flex;
  flex-direction: column;
  align-items: center;
  background-color: #f5f7fb;
  background-image: url('/static/qbox/certification-bg.png');
  background-repeat: no-repeat;
  background-size: 100% auto;
  background-position: center top;
}

/* ---- 已认证态（实测 pt379） ---- */
.cert-container {
  padding: 686.6px 34.4px 19.9px;
  box-sizing: border-box;
}
.cert-form-wrap {
  padding: 686.6px 34.4px 179.1px;
}
.authed-col {
  width: 677.5px;
  display: flex;
  flex-direction: column;
  align-items: center;
}
.authed-title {
  font-size: 30.8px;
  color: #111;
  font-family: var(--font-bold);
}
.id-card {
  width: 677.5px;
  margin-top: 27.2px;
  position: relative;
}
.id-card-bg {
  width: 677.5px;
  display: block;
  height: 286.2px;
}
.id-card-info {
  position: absolute;
  left: 39.9px;
  top: 0;
  bottom: 0;
  display: flex;
  flex-direction: column;
  justify-content: center;
  gap: 8px;
}
.ic-label {
  font-size: 21.7px;
  color: rgba(255, 255, 255, 0.8);
  font-family: var(--font-regular);
}
.ic-mt {
  margin-top: 7.2px;
}
.ic-name {
  font-size: 30.8px;
  color: #fff;
  font-family: var(--font-bold);
}
.ic-idcard {
  font-size: 27.2px;
  color: #fff;
  font-family: var(--font-medium);
}
.security-row {
  margin-top: 23.6px;
  display: flex;
  align-items: center;
  gap: 8px;
}
.security-icon {
  width: 27.2px;
  height: 27.2px;
}
.security-text {
  font-size: 21.7px;
  color: #8c8c8c;
  font-family: var(--font-regular);
}

/* ---- 未认证表单（结构/样式取自原站编译产物） ---- */
.form-card {
  width: 678px;
  background: #fff;
  border-radius: 47.1px;
  box-shadow: 0 7.2px 7.2px 0 rgba(0, 0, 0, 0.03);
  padding: 14.5px 58px;
  box-sizing: border-box;
  display: flex;
  flex-direction: column;
}
.form-field {
  padding: 43.5px 0;
  display: flex;
  flex-direction: column;
  gap: 14.5px;
}
.field-label {
  font-size: 47.1px;
  color: #1f1f1f;
  font-family: var(--font-medium);
}
.field-input {
  border: none;
  outline: none;
  background: transparent;
  font-size: 25.4px;
  color: #1f1f1f;
  font-family: var(--font-regular);
}
.field-input::placeholder {
  color: #b5b4b4;
}
.field-divider {
  height: 1.8px;
  background: #eee;
}
.contact-row {
  width: 678px;
  height: 174px;
  margin-top: 36.2px;
  background: #fff;
  border-radius: 47.1px;
  box-shadow: 0 7.2px 7.2px 0 rgba(0, 0, 0, 0.03);
  padding: 0 58px;
  box-sizing: border-box;
  display: flex;
  align-items: center;
}
.contact-label {
  flex: 1;
  font-size: 47.1px;
  color: #1f1f1f;
  font-family: var(--font-medium);
}
.contact-value {
  font-size: 47.1px;
  color: #1f1f1f;
  font-family: var(--font-regular);
}
.tip-row {
  width: 678px;
  margin-top: 18.1px;
  display: flex;
  align-items: flex-start;
  gap: 18.1px;
}
.tip-icon {
  width: 50.7px;
  height: 50.7px;
  border-radius: 25.4px;
  background: #0018ff;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 36.2px;
  color: #fff;
  font-family: var(--font-bold);
  line-height: 1;
  flex-shrink: 0;
}
.tip-text {
  flex: 1;
  font-size: 39.9px;
  color: #8c8c8c;
  line-height: 1.5;
  font-family: var(--font-regular);
}

/* 提交确认弹层（原站 w-toast 文案实测） */
.mask {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.5);
  z-index: 9998;
  display: flex;
  align-items: center;
  justify-content: center;
}
.toast-card {
  width: 577.7px;
  background: #fff;
  border-radius: 25.4px;
  padding: 36.2px 29px 0;
  box-sizing: border-box;
  display: flex;
  flex-direction: column;
  align-items: center;
}
.toast-title {
  font-weight: bold;
  font-size: 31.7px;
  color: #333;
  font-family: var(--font-bold);
}
.toast-content {
  margin-top: 21.7px;
  font-size: 25.4px;
  color: #666;
  line-height: 1.5;
  text-align: center;
  font-family: var(--font-regular);
}
.toast-btns {
  width: 100%;
  margin-top: 36.2px;
  border-top: 1.8px solid #ebebeb;
  display: flex;
}
.toast-cancel {
  flex: 1;
  height: 79.7px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 29px;
  color: #000;
  font-family: var(--font-regular);
}
.toast-ok {
  flex: 1;
  height: 79.7px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 29px;
  color: #0018ff;
  font-weight: bold;
  font-family: var(--font-medium);
  border-left: 1.8px solid #ebebeb;
}

/* 底部认证按钮（686×83rpx #0018ff r41，未填满 opacity .5） */
.submit-wrap {
  position: fixed;
  left: 0;
  right: 0;
  bottom: 0;
  z-index: 10;
  padding-bottom: 87px;
  display: flex;
  justify-content: center;
}
.submit-btn {
  width: 686px;
  height: 150.3px;
  background: #0018ff;
  border-radius: 74.3px;
  display: flex;
  align-items: center;
  justify-content: center;
  opacity: 0.5;
}
.submit-btn.able {
  opacity: 1;
}
.submit-text {
  font-size: 29px;
  color: #fff;
  font-family: var(--font-medium);
}
</style>
