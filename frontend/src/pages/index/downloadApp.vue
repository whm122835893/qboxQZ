<template>
  <div class="da-page">
    <!-- 导航栏（18px 700 #000） -->
    <div class="navbar">
      <img class="navbar-back" src="/static/cw/back_btn.png" alt="返回" @click="$router.back()" />
      <span class="nav-title">APP下载</span>
    </div>

    <!-- 大图（download-bg.png 878×1899） -->
    <div class="bg-img"></div>

    <!-- 底部下载按钮（apple 白底蓝字 / android 黑底白字，图标 24px，y734/799） -->
    <div class="btn-group">
      <div class="dl-btn dl-btn-light" @click="onDownload('ios')">
        <img class="dl-icon" src="/static/qbox/download-ios.png" alt="" />
        <span class="dl-text dl-text-blue">立即下载</span>
      </div>
      <div class="dl-btn dl-btn-dark" @click="onDownload('android')">
        <img class="dl-icon" src="/static/qbox/download-android.png" alt="" />
        <span class="dl-text dl-text-white">立即下载</span>
      </div>
    </div>
  </div>
</template>

<script setup>
import { toast } from '../../utils/toast'

/**
 * 原站 DOM 实测（2026-10-23）：标题 18px 700 #000；download-bg.png 大图；
 * 两按钮（y734/799 渲染）：白底描边 + ios 图标 24px + 立即下载 16px #0018ff /
 * 黑底 + android 图标 + 立即下载 16px #fff
 */
function onDownload(platform) {
  toast(`${platform === 'ios' ? 'iOS' : 'Android'} 下载通道暂未接入`)
}
</script>

<style scoped>
.da-page {
  min-height: 100vh;
  background: #fff;
  display: flex;
  flex-direction: column;
  align-items: center;
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
.bg-img {
  margin-top: 27px;
  width: 100%;
  height: 1223px;
  background: url('/static/qbox/download-bg.png') center top / 100% auto no-repeat;
}
.btn-group {
  width: 677.7px;
  display: flex;
  flex-direction: column;
  gap: 24px;
  margin-top: -80px;
}
.dl-btn {
  height: 88px;
  border-radius: 44px;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 16px;
}
.dl-btn-light {
  background: #fff;
  border: 1px solid #e5e5e5;
}
.dl-btn-dark {
  background: #141416;
}
.dl-icon {
  width: 43.5px;
  height: 43.5px;
  object-fit: contain;
}
.dl-text {
  font-size: 29px;
}
.dl-text-blue {
  color: #0018ff;
}
.dl-text-white {
  color: #fff;
}
</style>
