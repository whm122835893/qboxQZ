<template>
  <div class="ifa-page">
    <!-- 导航栏 -->
    <div class="navbar">
      <img class="navbar-back" src="/static/cw/back_btn.png" alt="返回" @click="$router.back()" />
      <span class="nav-title">邀请好友助力</span>
    </div>

    <div class="body">
      <!-- 邀请按钮（橙色胶囊） -->
      <div class="invite-btn"><span class="invite-text">邀请</span></div>

      <!-- 助力次数深胶囊 -->
      <div class="dark-capsule">
        <span class="capsule-text">您已成功获得助力<span class="capsule-num">0</span>次</span>
      </div>

      <!-- 表头 -->
      <div class="table-head">
        <span class="th">日期</span>
        <span class="th">用户类型</span>
        <span class="th">为您助力用户</span>
        <span class="th">结果</span>
      </div>

      <!-- 空态 -->
      <div class="empty">
        <svg class="empty-icon" viewBox="0 0 48 48" fill="none" stroke="#c3c9d4" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M28 4H12a4 4 0 0 0-4 4v32a4 4 0 0 0 4 4h24a4 4 0 0 0 4-4V16z"/><path d="M28 4v12h12"/><path d="M16 24h12"/><path d="M16 31h12"/><circle cx="30" cy="33" r="7" fill="#c3c9d4" stroke="none"/><path d="M27 33h6" stroke="#f5f5f5"/></svg>
        <span class="empty-text">数据为空</span>
      </div>

      <!-- 助力规则按钮 -->
      <div class="rule-btn"><span class="rule-text">助力规则</span></div>
    </div>
  </div>
</template>

<script setup>
/**
 * 原站 chunk 反解（pages-draw-InvitationFriendAssist-InvitationFriendAssist）：
 *  - 抽签活动邀请助力页：邀请（橙 88rpx 胶囊）+ 深棕胶囊「您已成功获得助力 0 次」
 *    + 记录表（日期/用户类型/为您助力用户/结果，空态「数据为空」）+ 助力规则深棕按钮
 *  - 分享海报相关接口未抓包，静态壳复刻（直接访问实测渲染）
 */
</script>

<style scoped>
.ifa-page {
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
  padding: 50px 30px 0;
}
.invite-btn {
  width: 687px;
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
.dark-capsule {
  margin-top: 30px;
  width: 687px;
  height: 100px;
  background: #302920;
  border-radius: 50px;
  display: flex;
  align-items: center;
  justify-content: center;
}
.capsule-text {
  font-size: 27.2px;
  color: #b8b2a8;
}
.capsule-num {
  color: #fff;
  font-weight: 600;
  margin: 0 4px;
}
.table-head {
  margin-top: 36px;
  display: flex;
}
.th {
  flex: 1;
  text-align: center;
  font-size: 24px;
  color: #999;
}
.empty {
  margin-top: 78px;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 16px;
}
.empty-icon {
  width: 110px;
  height: 110px;
}
.empty-text {
  font-size: 25.4px;
  color: #999;
}
.rule-btn {
  margin-top: 40px;
  width: 687px;
  height: 100px;
  background: #302920;
  border-radius: 50px;
  display: flex;
  align-items: center;
  padding: 0 40px;
  box-sizing: border-box;
}
.rule-text {
  font-size: 30.8px;
  color: #6b6455;
}
</style>
