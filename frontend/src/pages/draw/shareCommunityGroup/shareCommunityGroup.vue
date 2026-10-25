<template>
  <div class="sg-page">
    <!-- 导航栏 -->
    <div class="navbar">
      <img class="navbar-back" src="/static/cw/back_btn.png" alt="返回" @click="$router.back()" />
      <span class="nav-title">分享</span>
    </div>

    <div class="body">
      <!-- 积分深胶囊 -->
      <div class="dark-capsule">
        <span class="capsule-text">分享社群，已获得 <span class="capsule-num">0</span> 积分</span>
        <span class="capsule-action">分享图片</span>
      </div>

      <!-- 上传图片 -->
      <span class="upload-title">上传图片:</span>
      <div class="upload-box"></div>
      <span class="upload-tip">上传截图，图片大小限制5M内，数量3张</span>

      <!-- 表头 -->
      <div class="table-head">
        <span class="th">日期</span>
        <span class="th">上传图片数量</span>
        <span class="th">审核结果</span>
      </div>

      <!-- 分享社群规则按钮 -->
      <div class="rule-btn"><span class="rule-text">分享社群规则</span></div>

      <!-- 提交按钮（原站在内容流内 y446，非 fixed） -->
      <div class="submit-btn"><span class="submit-text">提交</span></div>
    </div>
  </div>
</template>

<script setup>
/**
 * 原站 chunk 反解（pages-draw-shareCommunityGroup-shareCommunityGroup）：
 *  - 分享社群页（抽签活动配套）：深棕胶囊「分享社群，已获得 0 积分」+ 上传截图 + 记录表头
 *    + 分享社群规则深棕按钮 + 底部提交按钮为黑色（与社群分享页橙色不同，实测截图）
 */
</script>

<style scoped>
.sg-page {
  min-height: 100vh;
  background: #f5f5f5;
  padding-bottom: 180px;
  box-sizing: border-box;
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
  padding: 20px 30px 0;
}
.dark-capsule {
  width: 687px;
  height: 100px;
  background: #302920;
  border-radius: 50px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 0 36px;
  box-sizing: border-box;
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
.capsule-action {
  font-size: 27.2px;
  color: #ff7800;
}
.upload-title {
  display: block;
  margin-top: 30px;
  font-size: 28px;
  color: #111;
  font-family: var(--font-bold);
}
.upload-box {
  margin-top: 20px;
  width: 160px;
  height: 160px;
  background: #e5e5e5;
  border-radius: 24px;
}
.upload-tip {
  display: block;
  margin-top: 56px;
  font-size: 27.2px;
  color: #999;
}
.table-head {
  margin-top: 27px;
  display: flex;
}
.th {
  flex: 1;
  text-align: center;
  font-size: 23.6px;
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
  font-size: 28px;
  color: #6b6455;
}
.footer {
  position: fixed;
  left: 0;
  bottom: 0;
  width: 100%;
  padding: 30px 30px calc(30px + env(safe-area-inset-bottom));
  box-sizing: border-box;
  background: #f5f5f5;
}
.submit-btn {
  margin-top: 54px;
  width: 100%;
  height: 88px;
  background: #141416;
  border-radius: 44px;
  display: flex;
  align-items: center;
  justify-content: center;
}
.submit-text {
  align-self: stretch;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 30px;
  color: #fff;
  font-family: var(--font-bold);
}
</style>
