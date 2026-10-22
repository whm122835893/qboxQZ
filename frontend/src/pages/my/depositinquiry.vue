<template>
  <div class="dq-page">
    <div class="navbar">
      <img class="navbar-back" src="/static/cw/back_btn.png" alt="返回" @click="$router.back()" />
      <span class="nav-title">存证查询</span>
    </div>

    <div class="panel">
      <span class="panel-title">请输入区块链信息</span>
      <textarea v-model="input" class="dq-textarea" placeholder="可输入合约地址"></textarea>
      <span class="panel-title">查询结果</span>
      <div class="dq-result">{{ result }}</div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'

/**
 * 原站 chunk 反解（pages-my-depositinquiry）：
 *  - 存证查询页（静态表单）：请输入区块链信息（textarea 可输入合约地址）+
 *    查询结果（textarea 这里显示查询结果）；查询接口未在直接访问时触发（实测无请求）
 */
const input = ref('')
const result = ref('')
</script>

<style scoped>
.dq-page {
  min-height: 100vh;
  background: #fff;
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
.panel {
  margin: 0 8.8px;
  background: #f5f6fa;
  border-radius: 16px;
  padding: 20px 16px;
}
.panel-title {
  display: block;
  font-size: 28px;
  color: #111;
  font-family: var(--font-bold);
}
.dq-textarea {
  margin-top: 14px;
  width: 100%;
  height: 100px;
  background: #fff;
  border: 1px solid #e5e5e5;
  border-radius: 8px;
  padding: 12px;
  box-sizing: border-box;
  font-size: 26px;
  color: #333;
  resize: none;
  outline: none;
}
.dq-textarea::placeholder {
  color: #c8c8c8;
}
.dq-result {
  margin-top: 14px;
  width: 100%;
  height: 100px;
  background: #fff;
  border: 1px solid #e5e5e5;
  border-radius: 8px;
  padding: 12px;
  box-sizing: border-box;
  font-size: 26px;
  color: #c8c8c8;
}
</style>
