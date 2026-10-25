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

<style scoped src="../../styles/pages/certification-certification.css"></style>
