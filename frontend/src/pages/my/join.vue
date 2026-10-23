<template>
  <!-- 加入社群（pages/my/join）：原站直接访问渲染空白（config 群内容为空，实测截图），如实复刻 -->
  <div class="jn-page">
    <div class="navbar">
      <img class="navbar-back" src="/static/cw/back_btn.png" alt="返回" @click="$router.back()" />
      <span class="nav-title">加入社群</span>
    </div>
  </div>
</template>

<script setup>
/**
 * 原站 chunk 反解（pages-my-join）：加入社群页，内容依赖 config；
 * 直接访问实测渲染空白（仅有导航），如实复刻
 */
</script>

<style scoped>
.jn-page {
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
  font-family: var(--font-bold);
}
</style>
