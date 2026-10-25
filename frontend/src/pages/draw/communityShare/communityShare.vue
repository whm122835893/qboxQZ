<template>
  <div class="cs-page">
    <!-- 导航栏 -->
    <div class="navbar">
      <img class="navbar-back" src="/static/cw/back_btn.png" alt="返回" @click="$router.back()" />
      <span class="nav-title">分享</span>
    </div>

    <div class="body">
      <!-- 助力次数深胶囊（深棕 #302920） -->
      <div class="dark-capsule">
        <span class="capsule-text">您已成功获得助力 <span class="capsule-num">0</span> 次</span>
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

      <!-- 空态（文件 icon + 暂无分享记录） -->
      <div class="empty">
        <svg class="empty-icon" viewBox="0 0 48 48" fill="none" stroke="#c3c9d4" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M28 4H12a4 4 0 0 0-4 4v32a4 4 0 0 0 4 4h24a4 4 0 0 0 4-4V16z"/><path d="M28 4v12h12"/><path d="M16 24h12"/><path d="M16 31h12"/><circle cx="30" cy="33" r="7" fill="#c3c9d4" stroke="none"/><path d="M27 33h6" stroke="#f5f5f5"/></svg>
        <span class="empty-text">暂无分享记录</span>
      </div>

      <!-- 分享社群规则按钮（深棕大按钮，文字暗灰） -->
      <div class="rule-btn"><span class="rule-text">分享社群规则</span></div>
    </div>

    <!-- 底部提交（fixed 橙色） -->
    <div class="footer">
      <div class="submit-btn"><span class="submit-text">提交</span></div>
    </div>
  </div>
</template>

<script setup>
/**
 * 原站 chunk 反解（pages-draw-communityShare-communityShare）：
 *  - 社群分享助力页（抽签活动配套）：上传截图（≤5M、3张）、分享记录列表（日期/上传图片数量/审核结果）
 *  - 直接访问无活动数据 → 深棕胶囊「您已成功获得助力 0 次」+ 空记录（实测渲染）
 *  - 提交/分享图片接口未抓包（避免向原站账号写入），静态壳复刻
 */
</script>

<style scoped>
.cs-page {
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
.empty {
  margin-top: 122px;
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
  margin-top: 50px;
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
.footer {
  position: fixed;
  left: 0;
  bottom: 0;
  width: 100%;
  padding: 0 30px 33px;
  box-sizing: border-box;
  background: #f5f5f5;
}
.submit-btn {
  width: 100%;
  height: 88px;
  background: #ff7800;
  border-radius: 44px;
  display: flex;
  align-items: center;
  justify-content: center;
}
.submit-text {
  font-size: 30px;
  color: #fff;
  font-family: var(--font-bold);
}
</style>
