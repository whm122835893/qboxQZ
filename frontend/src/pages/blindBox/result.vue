<template>
  <div class="res-page">
    <!-- 导航栏 -->
    <div class="navbar">
      <img class="navbar-back" src="/static/cw/back_btn.png" alt="返回" @click="$router.back()" />
      <span class="nav-title">盲盒结果</span>
    </div>

    <!-- 内容（原站 .content 690rpx padding 26/26/200） -->
    <div class="content">
      <!-- 一键开启结果列表（storage blindBoxResultList；结构按 chunk：图 / 名称+编号 / 稀有度图，布局为 uni 默认纵向，标注推断） -->
      <div v-for="(item, idx) in list" :key="idx" class="item">
        <img v-if="item.listimg" class="item-img" :src="resolveImg(item.listimg)" alt="" />
        <div class="info">
          <span class="item-name">{{ item.name }}</span>
          <span class="item-code">藏品编号: <span class="item-code-num">#{{ item.collection_code }}</span></span>
        </div>
        <img v-if="item.rarity_icon" class="item-rarity" :src="resolveImg(item.rarity_icon)" alt="" />
      </div>
      <div class="hint">商品陆续从链上转入个人中心，如有延迟请耐心等待</div>
    </div>

    <!-- 底部按钮（bg-color-item ptb-10，fixed h170rpx） -->
    <div class="footer">
      <div class="btns-row">
        <div class="res-btn res-btn-plain" @click="$router.back()"><span class="res-btn-text">我的数字藏品</span></div>
        <div class="res-btn res-btn-primary" @click="$router.push('/pages/index/index')"><span class="res-btn-text res-btn-text-white">继续购买</span></div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { resolveImg } from '../../api/request'

/**
 * 原站 chunk 反解（pages-blindBox-result 模块 b0f8）：
 *  - list = uni.getStorageSync('blindBoxResultList')（一键开启后由我的盲盒页写入）
 *  - item：listimg 59×59 r4 + name(13 500) + 藏品编号:#code(12) + rarity_icon 59×55
 *  - hint：商品陆续从链上转入个人中心，如有延迟请耐心等待
 *  - 我的数字藏品 → navigateBack；继续购买 → switchTab 首页
 * 无数据时即原站直接访问观测态：仅 hint + 底部按钮。
 */
function loadList() {
  try {
    return JSON.parse(localStorage.getItem('blindBoxResultList') || '[]') || []
  } catch (e) {
    return []
  }
}
const list = ref(loadList())
</script>

<style scoped>
.res-page {
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
  font-weight: 500;
  color: #000;
  font-family: var(--font-medium);
}
/* ---- 内容 ---- */
.content {
  width: 690px;
  margin: 0 auto;
  padding: 26px 26px 200px;
  border-radius: 18px;
  box-sizing: border-box;
}
.item {
  margin-bottom: 24px;
  padding: 16px;
  border-radius: 16px;
  background: #fff;
}
.item-img {
  width: 59px;
  height: 59px;
  border-radius: 4px;
  object-fit: cover;
}
.info {
  display: flex;
  flex-direction: column;
}
.item-name {
  font-size: 26px;
  font-weight: 500;
  color: #000;
  font-family: var(--font-medium);
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}
.item-code {
  margin-top: 12px;
  font-size: 24px;
  color: #000;
}
.item-code-num {
  color: #999;
  font-weight: 500;
}
.item-rarity {
  width: 59px;
  height: 55px;
  object-fit: contain;
}
.hint {
  width: 100%;
  text-align: center;
  font-size: 20px;
  color: #333;
  font-family: var(--font-regular);
}
/* ---- 底部 ---- */
.footer {
  position: fixed;
  left: 0;
  bottom: 0;
  width: 100%;
  height: 170px;
  background: #f4f6fa;
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
