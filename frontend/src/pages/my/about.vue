<template>
  <div class="ab-page">
    <div class="navbar">
      <img class="navbar-back" src="/static/cw/back_btn.png" alt="返回" @click="$router.back()" />
      <span class="nav-title">关于我们</span>
    </div>

    <div class="body">
      <!-- App 图标（原站 w-image 320rpx，logo.png 蓝盒+2026） -->
      <img class="app-logo" src="/static/logo/logo.png" alt="logo" />
      <!-- 「关于我们：」内容来自 config，实测为空 -->
      <span class="about-label">关于我们：</span>
    </div>

    <!-- 协议链接 -->
    <div class="agreements">
      <span class="agreement-link" @click="$router.push('/pages/my/privacy')">《用户服务协议》</span>
      <span class="agreement-link" @click="$router.push('/pages/my/privacy')">《隐私政策》</span>
    </div>
  </div>
</template>

<script setup>
/**
 * 原站 chunk 反解（pages-my-about）：
 *  - App 图标（/static/logo/logo.png）+ 「关于我们：」（config 内容实测为空）
 *    + 底部《用户服务协议》《隐私政策》链接；含版本检查（您已经是最新版本了 toast）
 */
</script>

<style scoped>
.ab-page {
  min-height: 100vh;
  background: #f5f5f7;
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
  display: flex;
  flex-direction: column;
  align-items: center;
}
.app-logo {
  margin-top: 54px;
  width: 318px;
  height: 318px;
  object-fit: contain;
  background: #fff;
}
.about-label {
  align-self: flex-start;
  margin: 175px 0 0 33px;
  font-size: 32px;
  color: #111;
  font-family: var(--font-bold);
}
.agreements {
  margin-top: 250px;
  display: flex;
  justify-content: center;
  gap: 4px;
}
.agreement-link {
  font-size: 28px;
  color: #98a2b3;
}
</style>
