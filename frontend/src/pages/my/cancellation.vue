<template>
  <div class="cx-page">
    <div class="navbar">
      <img class="navbar-back" src="/static/cw/back_btn.png" alt="返回" @click="$router.back()" />
      <span class="nav-title">注销账号申请</span>
    </div>

    <div class="body">
      <span class="page-title">注销账号</span>
      <span class="center-title">您正在注销账号</span>
      <span class="center-warn">请谨慎操作</span>

      <div class="safe-card">
        <span class="safe-title">帐号安全状态：</span>
        <span class="safe-text">您的账号没有被盗风险及违规行为</span>
        <span class="safe-title">帐号安全状态：</span>
        <span class="safe-text">1、无法登录； 2、所有信息将用于删除和清理（包括加入社区、账户余额、发布动态、聊天记录等）； 3、绑定手机/微信/AppleID/人脸认证等将会解绑； 4、其他不限于以上所列举的你的账号的资产和权益。</span>
      </div>
    </div>

    <!-- 底部确认注销（fixed 蓝色大按钮） -->
    <div class="footer">
      <div class="confirm-btn" @click="showAgreement = true"><span class="confirm-text">确认注销</span></div>
    </div>

    <!-- 注销注意事项弹窗（原站 chunk：365天冷静期 + 注销协议勾选） -->
    <div v-if="showAgreement" class="mask" @click.self="showAgreement = false">
      <div class="agree-modal">
        <span class="agree-title">QBOX注销注意事项</span>
        <div class="agree-scroll">
          <p class="agree-text">为了保障您的合法权益，我们设置了365天的冷静期。如果您在成功申请注销账号后的365天内再次登录的。即视为您撤回注销申请；若365天内未登录，则该账号将完成注销。</p>
          <p class="agree-text">具体内容请见 <span class="agree-link">《QBOX软件注销协议》</span></p>
        </div>
        <div class="agree-check" @click="checked = !checked">
          <div class="checkbox" :class="{ 'checkbox-active': checked }">
            <svg v-if="checked" class="check-icon" viewBox="0 0 24 24" fill="none"><path d="M5 12.5L10 17.5L19 7" stroke="#fff" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
          </div>
          <span class="check-label">我已阅读并同意以上协议</span>
        </div>
        <div class="agree-ft">
          <span class="agree-btn" @click="showAgreement = false">取消</span>
          <span class="agree-btn agree-btn-primary" @click="onConfirm">确定</span>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { toast } from '../../utils/toast'

/**
 * 原站 chunk 反解（pages-my-cancellation）：
 *  - 注销账号申请页：页面标题区 + 帐号安全状态白卡（文案硬编码）+ 底部「确认注销」
 *  - 确认 → 「QBOX注销注意事项」弹窗（365天冷静期 + 《QBOX软件注销协议》 + 勾选协议）；
 *    未勾选 → toast「请先勾选注销协议」；提交接口未抓包（避免真实注销），标注推断
 */
const showAgreement = ref(false)
const checked = ref(false)

function onConfirm() {
  if (!checked.value) {
    toast('请先勾选注销协议')
    return
  }
  showAgreement.value = false
  toast('注销申请已提交（接口未接入）')
}
</script>

<style scoped>
.cx-page {
  min-height: 100vh;
  background: #f7f7f7;
  padding-bottom: 160px;
  box-sizing: border-box;
}
.navbar {
  position: relative;
  width: 100%;
  height: 79.7px;
  display: flex;
  align-items: center;
  justify-content: center;
}
.navbar-back {
  position: absolute;
  left: 23.6px;
  top: 50%;
  transform: translateY(-50%);
  width: 17px;
  height: auto;
}
.nav-title {
  font-size: 32.6px;
  font-weight: 500;
  color: #000;
  font-family: var(--font-medium);
}
.body {
  padding: 0 30px;
}
.page-title {
  display: block;
  margin-top: 12px;
  font-size: 44px;
  color: #111;
  font-family: var(--font-bold);
}
.center-title {
  display: block;
  margin-top: 36px;
  text-align: center;
  font-size: 32px;
  color: #111;
  font-family: var(--font-bold);
}
.center-warn {
  display: block;
  margin-top: 14px;
  text-align: center;
  font-size: 28px;
  color: #f5222d;
}
.safe-card {
  margin-top: 36px;
  background: #fff;
  border-radius: 16px;
  padding: 24px;
  display: flex;
  flex-direction: column;
  gap: 14px;
}
.safe-title {
  font-size: 28px;
  color: #111;
  font-family: var(--font-bold);
}
.safe-text {
  font-size: 26px;
  line-height: 1.6;
  color: #666;
}
/* ---- 底部 ---- */
.footer {
  position: fixed;
  left: 0;
  bottom: 0;
  width: 100%;
  padding: 16px 30px calc(16px + env(safe-area-inset-bottom));
  box-sizing: border-box;
  background: #f7f7f7;
}
.confirm-btn {
  width: 100%;
  height: 88px;
  background: #0018ff;
  border-radius: 44px;
  display: flex;
  align-items: center;
  justify-content: center;
}
.confirm-text {
  font-size: 30px;
  color: #fff;
  font-family: var(--font-medium);
}
/* ---- 弹窗 ---- */
.mask {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.6);
  z-index: 999;
  display: flex;
  align-items: center;
  justify-content: center;
}
.agree-modal {
  width: 652px;
  background: #fff;
  border-radius: 24px;
  padding: 44px 40px 0;
  box-sizing: border-box;
  display: flex;
  flex-direction: column;
}
.agree-title {
  text-align: center;
  font-size: 32px;
  font-weight: 600;
  color: #000;
  font-family: var(--font-medium);
}
.agree-scroll {
  max-height: 40vh;
  overflow-y: auto;
  margin-top: 24px;
}
.agree-text {
  font-size: 26px;
  line-height: 1.7;
  color: #333;
  margin: 0.75em 0;
}
.agree-link {
  color: #0018ff;
}
.agree-check {
  margin-top: 20px;
  display: flex;
  align-items: center;
}
.checkbox {
  width: 32.6px;
  height: 32.6px;
  border-radius: 50%;
  border: 1px solid #d8d8d8;
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
  margin-left: 14px;
  font-size: 24px;
  color: #333;
}
.agree-ft {
  margin-top: 30px;
  display: flex;
  border-top: 0.5px solid #ebebeb;
}
.agree-btn {
  flex: 1;
  padding: 25px 0;
  text-align: center;
  font-size: 32px;
  color: #000;
}
.agree-btn-primary {
  color: #007aff;
  border-left: 0.5px solid #ebebeb;
}
</style>
