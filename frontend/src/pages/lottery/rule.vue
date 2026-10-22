<template>
  <div class="lr-page">
    <div class="navbar">
      <img class="navbar-back" src="/static/cw/back_btn.png" alt="返回" @click="$router.back()" />
      <span class="nav-title">抽奖规则</span>
    </div>

    <!-- 规则内容卡（config 提供，实测为空 → 灰色圆角卡） -->
    <div class="rule-card"></div>
  </div>
</template>

<script setup>
/**
 * 原站 DOM 实测：u-navbar 标题 18px 700 #000；内容卡灰底圆角、文本为空（实测截图）
 */
</script>

<style scoped>
.lr-page {
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
  font-weight: 700;
  color: #000;
}
.rule-card {
  margin: 0 17.7px;
  height: 800px;
  background: #f5f6fa;
  border-radius: 16px;
}
</style>
