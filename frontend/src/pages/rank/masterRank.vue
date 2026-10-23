<template>
  <div class="mr-page">
    <!-- 导航栏（透明底白字，深棕黑页面） -->
    <div class="navbar">
      <img class="navbar-back" src="/static/cw/back_back_white.png" alt="返回" @click="$router.back()" />
      <span class="nav-title">大师动态</span>
    </div>

    <!-- 动态列表（依赖 goods 参数；直接访问无数据 → 空列表，实测全黑） -->
  </div>
</template>

<script setup>
/**
 * 原站 chunk 反解（pages-rank-masterRank）：
 *  - 大师动态页（深棕黑 #1a1002 背景，白字标题）；依赖藏品参数拉取动态；
 *    直接访问无参数 → 空列表（2026-10-23 实测截图：全黑页仅标题）
 */
</script>

<style scoped>
.mr-page {
  min-height: 100vh;
  background: #1a1002;
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
  color: #fff;
  font-family: var(--font-bold);
}
</style>
