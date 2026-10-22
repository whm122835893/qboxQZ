<template>
  <div class="pv-page">
    <div class="navbar">
      <img class="navbar-back" src="/static/cw/back_btn.png" alt="返回" @click="$router.back()" />
      <span class="nav-title">隐私设置</span>
    </div>

    <!-- 隐藏开关卡（u-switch 黑色开启态，实测） -->
    <div class="switch-card">
      <span class="switch-label">对外隐藏所有数字藏品</span>
      <div class="switch" :class="{ 'switch-on': on }" @click="on = !on">
        <div class="switch-dot"></div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'

/**
 * 原站 chunk 反解（pages-my-privacy）：
 *  - 隐私设置页：「对外隐藏所有数字藏品」+ u-switch（实测开启态，黑色轨道白圆在右）；
 *    切换接口未抓包（避免向原站账号写入），本地状态切换（标注推断）
 */
const on = ref(true)
</script>

<style scoped>
.pv-page {
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
.switch-card {
  margin: 0 17.7px;
  background: #f5f6fa;
  border-radius: 16px;
  padding: 15px 24px;
  display: flex;
  align-items: center;
  justify-content: space-between;
}
.switch-label {
  font-size: 28px;
  color: #111;
}
.switch {
  width: 51px;
  height: 31px;
  border-radius: 16px;
  background: #e5e5e5;
  position: relative;
  transition: background 0.2s;
}
.switch-on {
  background: #1a1a1a;
}
.switch-dot {
  position: absolute;
  top: 2px;
  left: 2px;
  width: 27px;
  height: 27px;
  border-radius: 50%;
  background: #fff;
  transition: left 0.2s;
}
.switch-on .switch-dot {
  left: 22px;
}
</style>
