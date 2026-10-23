<template>
  <div class="login-page">
    <div class="login-navbar">
      <img class="navbar-back" src="/static/cw/back_btn.png" alt="" @click="$router.back()" />
    </div>

    <div class="form-wrap">
      <div class="ipt-row">
        <span class="ipt-label">新密码</span>
        <span class="ipt-div"></span>
        <input v-model="form.password" class="ipt-input" :type="show1 ? 'text' : 'password'" placeholder="请输入新密码" />
        <span class="toggle-show" @click="show1 = !show1">{{ show1 ? '隐藏' : '显示' }}</span>
      </div>
      <div class="ipt-row">
        <span class="ipt-label">确认密码</span>
        <span class="ipt-div"></span>
        <input v-model="form.confirm" class="ipt-input" :type="show2 ? 'text' : 'password'" placeholder="请再次输入新密码" />
        <span class="toggle-show" @click="show2 = !show2">{{ show2 ? '隐藏' : '显示' }}</span>
      </div>

      <div class="pwd-tip">密码内需同时包含数字、字母，长度在6-12之间</div>

      <div class="btn-primary" @click="submit"><span>确认</span></div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { request } from '../../api/request'
import { toast } from '../../utils/toast'

/**
 * 原站截图实测（pages/login/setPwd）：新密码/确认密码 2 行（带 显示 切换）+
 * 提示「密码内需同时包含数字、字母，长度在6-12之间」+ 蓝色确认按钮。
 * 提交接口 chunk 未含显式 url（共享模块），按重置密码语义调用 /api/user/resetpwd（标注推断）。
 */
const form = ref({ password: '', confirm: '' })
const show1 = ref(false)
const show2 = ref(false)

function checkPwd(pwd) {
  return /^(?=.*\d)(?=.*[a-zA-Z]).{6,12}$/.test(pwd)
}

async function submit() {
  const pwd = String(form.value.password ?? '')
  if (!pwd) return toast('请输入新密码')
  if (!checkPwd(pwd)) return toast('密码内需同时包含数字、字母，长度在6-12之间')
  if (pwd !== String(form.value.confirm ?? '')) return toast('两次密码不一致')
  const res = await request('/api/user/resetpwd', { password: pwd })
  if (res.code === 1) {
    toast(res.msg || '设置成功')
    setTimeout(() => history.back(), 1200)
  }
}
</script>

<style scoped>
.login-page {
  position: relative;
  width: 100%;
  min-height: 100vh;
  background-color: #fff;
  background-image: url('/static/qbox/login-bg.png');
  background-repeat: no-repeat;
  background-size: 100% auto;
  background-position: center top;
  display: flex;
  flex-direction: column;
  overflow: hidden;
}
.login-navbar {
  position: fixed;
  left: 0;
  top: 0;
  z-index: 2;
  width: 100%;
  height: 79.7px;
  display: flex;
  align-items: center;
  padding: 0 23.6px;
  background: transparent;
}
.navbar-back {
  width: 16.3px;
  height: 29px;
  object-fit: contain;
}
.form-wrap {
  position: relative;
  z-index: 2;
  display: flex;
  flex-direction: column;
  padding: 0 47.1px 99px;
  margin-top: 619.8px;
}
.ipt-row {
  display: flex;
  align-items: center;
  height: 83px;
  padding: 0 32px;
  background: #f5f5f5;
  border-radius: 19px;
  margin-bottom: 23.6px;
  box-sizing: border-box;
}
.ipt-label {
  font-size: 25.4px;
  color: #111;
  font-family: var(--font-regular);
  flex-shrink: 0;
}
.ipt-div {
  width: 1.8px;
  height: 27.2px;
  background: #d9d9d9;
  margin: 0 21.7px;
  flex-shrink: 0;
}
.ipt-input {
  flex: 1;
  min-width: 0;
  border: none;
  outline: none;
  background: transparent;
  font-size: 25.4px;
  color: #111;
  font-family: var(--font-regular);
}
.ipt-input::placeholder {
  color: #c0c4cc;
}
.toggle-show {
  flex-shrink: 0;
  margin-left: 16.3px;
  font-size: 23.6px;
  color: #939393;
}
.pwd-tip {
  font-size: 23.6px;
  color: #8c8c8c;
  font-family: var(--font-regular);
}
.btn-primary {
  width: 100%;
  height: 99px;
  background: var(--qbox-primary);
  border-radius: 50px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 30.8px;
  color: #fff;
  font-family: var(--font-medium);
  margin-top: 47.1px; /* 原站实测：提示行到按钮间隔 26px@414 */
  box-sizing: border-box;
}
</style>
