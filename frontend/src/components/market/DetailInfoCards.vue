<template>
  <!--
    寄售详情信息卡组（源站 pages/market/detail）
    源站实测 2026-10-25 11:56：
      信息卡 白底 radius 32 padding 16/32，行高 78
        label 26px fw600 #181717（左）/ value 26px #000（右对齐）
      创作者卡 / 购买须知卡 白底 radius 32 padding 32/28
        标题 38px #111；水印 QBOX 51px rgba(104,104,104,.08)
        创作者头像 122×122 radius 19；名称 30px #111
        须知正文 22px fw300 #696969，行高 30
  -->
  <div class="info-card">
    <div class="info-row">
      <span class="info-label">藏品编号</span>
      <span class="info-value">#{{ collectionCode }}</span>
    </div>
    <div class="info-row">
      <span class="info-label">所属专辑</span>
      <span class="info-value">{{ seriesName }}</span>
    </div>
    <div class="info-row">
      <span class="info-label">合约地址</span>
      <span class="info-value">{{ contract }}</span>
    </div>
    <div class="info-row">
      <span class="info-label">认证标识</span>
      <span class="info-value">{{ certId }}</span>
    </div>
  </div>

  <div class="creator-card">
    <span class="wm-text">QBOX</span>
    <div class="card-title">创作者</div>
    <div class="creator-row">
      <!-- 源站实测 2026-10-25：该头像 src 为空，渲染 122×122r 灰底空块；加载失败时隐藏 img 露出底色 -->
      <img class="creator-logo" :src="publisherLogo" alt="" @error="onLogoError" />
      <span class="creator-name">{{ publisherName }}</span>
    </div>
  </div>

  <div class="notice-card">
    <span class="wm-text">QBOX</span>
    <div class="card-title">购买须知</div>
    <p class="notice-text">{{ noticeText }}</p>
  </div>
</template>

<script setup>
/**
 * 寄售详情信息卡组（单一职责：纯展示，不含业务请求）。
 * 几何与配色严格对齐源站实测值（见模板注释）。
 */
/** 头像加载失败时隐藏 img，露出灰底占位（与源站空 src 表现一致） */
function onLogoError(e) {
  e.target.style.visibility = 'hidden'
}

defineProps({
  collectionCode: { type: [String, Number], default: '' },
  seriesName: { type: String, default: '' },
  contract: { type: String, default: '--' },
  certId: { type: [String, Number], default: '--' },
  publisherLogo: { type: String, default: '' },
  publisherName: { type: String, default: '' },
  noticeText: { type: String, default: '' }
})
</script>

<style scoped>
/* 信息卡：白底 radius 32 padding 16/32 */
.info-card {
  margin-top: 16px;
  background: #fff;
  border-radius: 32px;
  padding: 16px 32px;
}
.info-row {
  display: flex;
  align-items: center;
  justify-content: space-between;
  height: 78px;
}
.info-label {
  font-size: 26px;
  font-weight: 600;
  color: #181717;
  font-family: var(--font-medium);
}
.info-value {
  font-size: 26px;
  color: #000;
  font-family: var(--font-regular);
  text-align: right;
}
/* 创作者卡 / 购买须知卡：白底 radius 32 padding 32/28 */
.creator-card,
.notice-card {
  position: relative;
  margin-top: 16px;
  background: #fff;
  border-radius: 32px;
  padding: 32px 28px;
  overflow: hidden;
}
/* 水印 QBOX（源站实测 2026-10-26，market/detail 创作者卡/购买须知卡）：
   span 182×72rpx，fs 50rpx，行高 26rpx（小于字号，字形贴顶，与标题同层重叠），
   left 44rpx top 10rpx（相对卡片），颜色 rgba(104,104,104,.08) */
.wm-text {
  position: absolute;
  left: 44px;
  top: 10px;
  width: 182px;
  height: 72px;
  font-size: 50px;
  color: rgba(104, 104, 104, 0.08);
  font-family: var(--font-medium);
  line-height: 26px;
  pointer-events: none;
}
.card-title {
  position: relative;
  font-size: 38px;
  color: #111;
  font-family: var(--font-medium);
  line-height: 38px;
}
.creator-row {
  position: relative;
  margin-top: 24px;
  display: flex;
  align-items: flex-start;
  gap: 20px;
}
.creator-logo {
  width: 122px;
  height: 122px;
  border-radius: 19px;
  object-fit: cover;
  background: #f2f2f2;
}
.creator-name {
  font-size: 30px;
  color: #111;
  font-family: var(--font-regular);
  line-height: 26px;
  padding-top: 2px;
}
.notice-text {
  position: relative;
  margin: 24px 0 0;
  font-size: 22px;
  font-weight: 300;
  color: #696969;
  font-family: var(--font-regular);
  line-height: 30px;
}
</style>
