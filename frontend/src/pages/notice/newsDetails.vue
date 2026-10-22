<template>
  <!-- 公告富文本详情（pages/notice/newsDetails）：依赖公告 id 参数（newsInfo 接口）；
       原站直接访问（无参数）正文为空，仅渲染导航「潮玩有话说」，如实复刻壳 -->
  <div class="nd-page">
    <div class="navbar">
      <img class="navbar-back" src="/static/cw/back_btn.png" alt="返回" @click="$router.back()" />
      <span class="nav-title">潮玩有话说</span>
    </div>
    <!-- 富文本正文（无参数时为空，原站如此） -->
  </div>
</template>

<script setup>
/**
 * 原站 DOM 实测（2026-10-23）：u-navbar 标题 18px 700 #000（渲染），正文依赖 newsInfo 数据
 */
</script>

<style scoped>
.nd-page {
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
</style>
