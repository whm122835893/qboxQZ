<template>
  <div class="rr-page">
    <div class="pt-spacer"></div>

    <!-- 记录列表（原站 .list mlr-16 + 阴影；演示卡为原站硬编码数据） -->
    <div class="list">
      <div class="card">
        <div class="card-top">
          <div class="regift-img-box">
            <!-- 原站 rimg=../../static/img/testl.png 该图 404，实际渲染为 u-image 错误占位（灰底圆+信息图标），如实复刻 -->
            <svg class="regift-placeholder" viewBox="0 0 24 24" fill="none" stroke="#c0c4cc" stroke-width="1.6"><circle cx="12" cy="12" r="10" fill="#f3f4f6" stroke="none"/><circle cx="12" cy="7.5" r="1" fill="#909399" stroke="none"/><path d="M12 10.5v6" stroke-linecap="round"/></svg>
          </div>
          <div class="info-bar">
            <span class="regift-name"> 古代中国与外国交通贸易 </span>
            <span class="regift-code">PBCE/2000000000000000</span>
            <div class="regift-status">
              <img class="status-icon" src="/static/icon/rjs.png" alt="" />
              <span class="status-text">待接收</span>
            </div>
          </div>
        </div>
        <div class="divider"></div>
        <div class="info-rows">
          <div class="info-row"><span class="k">赠送者</span><span class="v">数藏爱好者</span></div>
          <div class="info-row"><span class="k">转赠时间</span><span class="v">2018-12-12 09:09</span></div>
          <div class="info-row"><span class="k">退回时间：</span><span class="v">1812061059386431</span></div>
          <div class="info-row"><span class="k">记录单号：</span><span class="v">5871545254154715741</span></div>
        </div>
      </div>
    </div>

    <!-- 空态（w-loading emptyType=search：search-empty.png + 暂无内容） -->
    <div class="empty">
      <img class="empty-img" src="/static/empty/search-empty.png" alt="" />
      <span class="empty-text">暂无内容</span>
    </div>
  </div>
</template>

<script setup>
/**
 * 原站 chunk 反解（pages-regift-record）：
 *  - 页面为转赠记录展示：tabs 数据（我转赠的/我收到的）存在但渲染层未输出（原站如此）
 *  - 顶部演示卡为原站硬编码数据（rimg=../../static/img/testl.png 在原站已 404，渲染为 u-image 错误占位）；
 *    下方 w-loading emptyType=search 空态（search-empty.png + 暂无内容）
 */
</script>

<style scoped>
.rr-page {
  min-height: 100vh;
  background: #fff;
}
.pt-spacer {
  height: 20px;
}
.list {
  margin: 0 32px;
  box-shadow: 0px 1.1px 13.2px 2.2px rgba(11, 42, 113, 0.05);
  border-radius: 12px;
}
.card {
  background: #f4f6fa;
  padding: 6.6px 6.6px 8.8px;
  border-radius: 12px;
  margin-bottom: 6.6px;
}
.card-top {
  display: flex;
  padding: 6.6px;
}
.regift-img-box {
  width: 39.7px;
  height: 39.7px;
  border-radius: 6.6px;
  overflow: hidden;
  background: #f3f4f6;
  flex-shrink: 0;
  display: flex;
  align-items: center;
  justify-content: center;
}
.regift-placeholder {
  width: 22px;
  height: 22px;
}
.info-bar {
  flex: 1;
  margin-left: 8px;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
  min-width: 0;
}
.regift-name {
  font-size: 15.4px;
  font-weight: 500;
  color: #555;
  display: -webkit-box;
  -webkit-box-orient: vertical;
  -webkit-line-clamp: 2;
  overflow: hidden;
}
.regift-code {
  align-self: flex-start;
  font-size: 11px;
  background: #f5f6fa;
  border-radius: 50px;
  padding: 2.2px 4.4px 2.2px 8.8px;
  color: #555;
}
.regift-status {
  display: flex;
  align-items: center;
  padding-top: 3.9px;
}
.status-icon {
  width: 17.7px;
  height: 17.7px;
  object-fit: contain;
}
.status-text {
  margin-left: 2.2px;
  font-size: 15.4px;
  color: #555;
}
.divider {
  padding-top: 11px;
  border-bottom: 0.5px solid #eee;
}
.info-rows {
  font-size: 15.4px;
  padding: 0 6.6px 6.6px;
}
.info-row {
  margin-top: 8.8px;
  display: flex;
  align-items: center;
  justify-content: space-between;
}
.info-row .k {
  color: #999;
}
.info-row .v {
  color: #555;
}
/* ---- 空态 ---- */
.empty {
  display: flex;
  flex-direction: column;
  align-items: center;
  margin-top: 320px;
}
.empty-img {
  width: 264.5px;
  object-fit: contain;
}
.empty-text {
  margin-top: 29px;
  font-size: 25.4px;
  color: #999;
}
</style>
