<template>
  <div class="lb-page">
    <!-- 导航栏 -->
    <div class="navbar">
      <img class="navbar-back" src="/static/cw/back_btn.png" alt="返回" @click="$router.back()" />
      <span class="nav-title">参与人数</span>
    </div>

    <div class="body">
      <!-- 参与人数深胶囊（近全宽，左右 15rpx 边距） -->
      <div class="dark-capsule">
        <span class="capsule-text">参与人数 <span class="capsule-num">0</span> 人</span>
      </div>
    </div>

    <!-- 底部邀请好友助力（fixed 橙色） -->
    <div class="footer">
      <div class="invite-btn"><span class="invite-text">邀请好友助力</span></div>
    </div>
  </div>
</template>

<script setup>
/**
 * 原站 chunk 反解（pages-draw-Leaderboard-Leaderboard）：
 *  - 参与人数页：深棕胶囊「参与人数 0 人」（近全宽）+ 底部 fixed 橙色「邀请好友助力」
 *  - 直接访问实测渲染（无接口调用，静态壳）
 */
</script>

<style scoped>
.lb-page {
  min-height: 100vh;
  background: #f5f5f5;
}
.navbar {
  position: relative;
  width: 100%;
  height: 79.7px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: #fff;
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
  padding: 30px 0 0;
}
.dark-capsule {
  width: 100%;
  height: 100px;
  background: #302920;
  border-radius: 50px;
  display: flex;
  align-items: center;
  justify-content: center;
}
.capsule-text {
  font-size: 26px;
  color: #b8b2a8;
}
.capsule-num {
  color: #fff;
  font-weight: 600;
  margin: 0 4px;
}
.footer {
  position: fixed;
  left: 0;
  bottom: 0;
  width: 100%;
  padding: 16px 15px calc(16px + env(safe-area-inset-bottom));
  box-sizing: border-box;
  background: #f5f5f5;
}
.invite-btn {
  width: 100%;
  height: 88px;
  background: #ff7800;
  border-radius: 44px;
  display: flex;
  align-items: center;
  justify-content: center;
}
.invite-text {
  font-size: 30px;
  color: #fff;
  font-family: var(--font-bold);
}
</style>
