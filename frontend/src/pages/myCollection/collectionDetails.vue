<template>
  <div class="cd-page">
    <!-- 导航栏（藏品名称 17px Bold #111） -->
    <div class="navbar">
      <img class="navbar-back" src="/static/cw/back_btn.png" alt="返回" @click="$router.back()" />
      <span class="nav-title">藏品名称</span>
    </div>

    <div class="body">
      <!-- 主图（数据空时黑色占位，333px 渲染） -->
      <div class="main-img"></div>

      <!-- 数量胶囊 + 价格（限量0份/流通0份 11px 白；¥0.00 19px AlimamaShuHeiTi） -->
      <div class="num-row">
        <span class="num-capsule">限量0份</span>
        <span class="num-capsule">流通0份</span>
        <span class="price">¥0.00</span>
      </div>

      <!-- 持有者卡（藏品持有者 15 Bold + 值 14；认证时间 15 Bold + 值 14） -->
      <div class="info-card">
        <div class="info-row">
          <span class="info-label">藏品持有者</span>
          <span class="info-value">Q9GSWCuI2</span>
        </div>
        <div class="info-row">
          <span class="info-label">认证时间</span>
          <span class="info-value">2026.10.22</span>
        </div>
      </div>

      <!-- 创作者卡（QBOX 水印 28px rgba(104,104,104,.08) NanoByongGyeHei；创作者 17px #2d2d2d；头像 67 + QBOX 12px） -->
      <div class="creator-card">
        <span class="watermark">QBOX</span>
        <span class="creator-title">创作者</span>
        <div class="creator-row">
          <img class="creator-avatar" src="/static/qbox/detail-creator-default.png" alt="" />
          <span class="creator-name">QBOX</span>
        </div>
      </div>

      <!-- 藏品介绍卡（ALLCOMEQBOX 水印 28px；藏品介绍 17px #2d2d2d） -->
      <div class="intro-card intro-pad">
        <span class="watermark watermark-wide">ALLCOMEQBOX ALLCOMEQBOX</span>
        <span class="creator-title">藏品介绍</span>
      </div>

      <!-- 购买须知卡（购买须知 17px + 固定文案 12px #696969） -->
      <div class="notice-card">
        <span class="watermark watermark-wide">QBOX</span>
        <span class="creator-title">购买须知</span>
        <p class="notice-text">数字藏品为虚拟数字商品，而非实物，仅限实名认证为年满18周岁，并小于60周岁的中国大陆用户购买。数字藏品的版权由发行方或原创者拥有，除另行取得版权拥有者书面同意外，用户不得将数字藏品用于任何商业用途。本商品一经售出，不支持退换请勿对数字藏品进行炒作、场外交易、欺诈，或以任何其他非法方式进行使用。</p>
      </div>
    </div>

    <!-- 底部按钮（上链中 16px 白字 #cacdd3，y838） -->
    <div class="bottom-btn">上链中</div>
  </div>
</template>

<script setup>
/**
 * 原站 DOM 实测（2026-10-23，pages/myCollection/collectionDetails）：
 *  - 标题 17px Bold #111；主图黑占位（数据空）；限量/流通胶囊 11px 白（黑底）；
 *    ¥0.00 19px AlimamaShuHeiTi #111；
 *  - 持有者卡：15 Bold + 14 Regular；创作者卡：QBOX 水印 28px rgba(104,104,104,.08)
 *    NanoByongGyeHei + 创作者 17px #2d2d2d + 默认头像 67px + QBOX 12px；
 *  - 介绍/须知卡同构（水印 + 标题），须知固定文案 12px #696969；
 *  - 底部「上链中」16px #fff（灰禁用态按钮 376×50 渲染）
 */
</script>

<style scoped>
.cd-page {
  min-height: 100vh;
  background: #f2f3f5;
  padding-bottom: 160px;
  box-sizing: border-box;
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
  font-size: 30.8px;
  font-weight: 700;
  color: #111;
}
.body {
  padding: 0 22px;
}
.main-img {
  margin-top: 8px;
  width: 100%;
  height: 560px;
  border-radius: 28px;
  background: #0e0f12;
}
.num-row {
  margin-top: 22px;
  width: 100%;
  background: #fff;
  border-radius: 16px;
  padding: 20px 22px;
  box-sizing: border-box;
  display: flex;
  align-items: center;
}
.num-capsule {
  padding: 4px 12px;
  background: #0018ff;
  border-radius: 6px;
  font-size: 19.9px;
  color: #fff;
  margin-right: 12px;
}
.price {
  margin-left: auto;
  font-size: 34.4px;
  font-family: 'AlimamaShuHeiTi-Bold', var(--font-medium);
  color: #111;
}
.info-card {
  margin-top: 32px;
  background: #fff;
  border-radius: 16px;
  padding: 8px 22px;
}
.info-row {
  padding: 22px 0;
  display: flex;
  align-items: center;
  justify-content: space-between;
  border-bottom: 1px solid #f5f5f5;
}
.info-row:last-child {
  border-bottom: none;
}
.info-label {
  font-size: 27.2px;
  font-weight: 700;
  color: #111;
}
.info-value {
  font-size: 25.4px;
  color: #111;
}
.creator-card,
.intro-card,
.notice-card {
  position: relative;
  margin-top: 24px;
  background: #fff;
  border-radius: 16px;
  padding: 31px 28px 34px;
  overflow: hidden;
}
.watermark {
  position: absolute;
  left: 24px;
  top: 9px;
  font-size: 50.7px;
  line-height: 26px;
  color: rgba(104, 104, 104, 0.08);
  font-family: 'NanoByongGyeHei-Regular', var(--font-regular);
  white-space: nowrap;
  pointer-events: none;
}
.watermark-wide {
  left: 12px;
}
.creator-title {
  position: relative;
  z-index: 1;
  margin-top: 25px;
  font-size: 30.8px;
  color: #2d2d2d;
  font-family: 'AlimamaShuHeiTi-Bold', var(--font-medium);
}
.creator-row {
  position: relative;
  z-index: 1;
  margin-top: 20px;
  display: flex;
  flex-direction: column;
  align-items: center;
}
.creator-avatar {
  width: 103px;
  height: 103px;
  object-fit: contain;
}
.creator-name {
  margin-top: 2px;
  font-size: 21.7px;
  color: #2d2d2d;
  font-family: 'AlimamaFangYuanTiVF-Thin', var(--font-regular);
}
.intro-pad {
  padding-bottom: 35px;
}
.notice-text {
  position: relative;
  z-index: 1;
  margin: 27px 0 0;
  font-size: 21.7px;
  line-height: 1.7;
  color: #696969;
}
/* ---- 底部按钮（376×50 渲染，灰禁用态） ---- */
.bottom-btn {
  position: fixed;
  left: 50%;
  transform: translateX(-50%);
  bottom: calc(20px + env(safe-area-inset-bottom));
  width: 681px;
  height: 90px;
  border-radius: 45px;
  background: #cacdd3;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 29px;
  color: #fff;
}
</style>
