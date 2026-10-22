<template>
  <div class="sv-page">
    <div class="navbar">
      <img class="navbar-back" src="/static/cw/back_btn.png" alt="返回" @click="$router.back()" />
      <span class="nav-title">加入社群</span>
    </div>

    <div class="body">
      <span class="sv-title">欢迎加入QBOX官方社群!</span>
      <p class="sv-red"> 以下是QBOX的官方社群，点击二维码图片保存到相册，QQ扫码入群，如果遇到二维码失效或者无法加入请更换其他群或者联系官方客服! </p>
      <span class="sv-gray">点击二维码保存到手机/QQ扫码入群</span>
      <!-- 二维码图片（config 提供，实测未渲染出图片） -->
    </div>
  </div>
</template>

<script setup>
/**
 * 原站 chunk 反解（pages-my-service）：
 *  - 客服/社群页：欢迎语（36rpx Bold 居中）+ 红色说明段落（28rpx）+ 灰色提示（24rpx）；
 *    二维码图片来自 config，实测未渲染出（无图），如实复刻；导航标题为「加入社群」（原站如此）
 */
</script>

<style scoped>
.sv-page {
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
.body {
  padding: 0 30px;
}
.sv-title {
  display: block;
  margin-top: 50px;
  text-align: center;
  font-size: 36px;
  color: #111;
  font-family: var(--font-bold);
}
.sv-red {
  margin-top: 55px;
  font-size: 28px;
  line-height: 1.6;
  color: #f5222d;
}
.sv-gray {
  display: block;
  margin-top: 50px;
  text-align: center;
  font-size: 24px;
  color: #999;
}
</style>
