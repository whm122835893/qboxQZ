<template>
  <div class="res2-page">
    <!-- 导航栏 -->
    <div class="navbar">
      <img class="navbar-back" src="/static/cw/back_btn.png" alt="返回" @click="$router.back()" />
      <span class="nav-title">盲盒结果</span>
    </div>

    <!-- 内容（原站 .content 纵向居中 padding 104/32/228） -->
    <div class="content">
      <img v-if="info.rarity_icon" class="rarity" :src="resolveImg(info.rarity_icon)" alt="" />
      <img v-if="info.main_image" class="main-img" :src="resolveImg(info.main_image)" alt="" />
      <span v-if="info.name" class="name">{{ info.name }}</span>
      <span class="code">藏品编号：<span class="code-num">#{{ info.collection_code }}</span></span>
    </div>

    <!-- 底部按钮（bg #fff fixed h170rpx） -->
    <div class="footer">
      <div class="btns-row">
        <div class="res-btn res-btn-plain" @click="$router.replace('/pages/myCollection/myCollection')"><span class="res-btn-text">我的数字藏品</span></div>
        <div class="res-btn res-btn-primary" @click="$router.push('/pages/index/index')"><span class="res-btn-text res-btn-text-white">继续购买</span></div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { resolveImg } from '../../api/request'

/**
 * 原站 chunk 反解（pages-blindBox-result2 模块 cd37）：
 *  - info = uni.getStorageSync('blindBoxResult')（逐个拆开后由我的盲盒页写入）
 *  - rarity_icon 80×59 + main_image 200×200 aspectFit r10 + name(16 500 #555) + 藏品编号：#code(12 #999)
 *  - 我的数字藏品 → redirectTo myCollection；继续购买 → switchTab 首页
 * 无数据时即原站直接访问观测态：仅「藏品编号：#」居中 + 底部按钮。
 */
function loadInfo() {
  try {
    return JSON.parse(localStorage.getItem('blindBoxResult') || '{}') || {}
  } catch (e) {
    return {}
  }
}
const info = ref(loadInfo())
</script>

<style scoped>
.res2-page {
  min-height: 100vh;
  background: #fff;
}
/* ---- 导航栏 ---- */
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
/* ---- 内容 ---- */
.content {
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
  width: 100%;
  padding: 104px 32px 228px;
  box-sizing: border-box;
}
.rarity {
  width: 80px;
  height: 59px;
  object-fit: contain;
}
.main-img {
  margin-top: 32px;
  margin-bottom: 32px;
  width: 200px;
  height: 200px;
  border-radius: 10px;
  object-fit: contain;
}
.name {
  font-size: 32px;
  font-weight: 500;
  color: #555;
  font-family: var(--font-medium);
}
.code {
  margin-top: 8px;
  font-size: 24px;
  color: #999;
}
.code-num {
  font-weight: 500;
  color: #999;
}
/* ---- 底部 ---- */
.footer {
  position: fixed;
  left: 0;
  bottom: 0;
  width: 100%;
  height: 170px;
  background: #fff;
  padding: 20px 0;
  box-sizing: border-box;
}
.btns-row {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 0 40px;
}
.res-btn {
  width: 320px;
  height: 92px;
  border-radius: 50px;
  display: flex;
  align-items: center;
  justify-content: center;
}
.res-btn-plain {
  background: #f1f0f0;
  margin-right: 20px;
}
.res-btn-primary {
  background: #ff7800;
}
.res-btn-text {
  font-size: 30px;
  color: #000;
  font-family: var(--font-medium);
}
.res-btn-text-white {
  color: #fff;
}
</style>
