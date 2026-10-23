<template>
  <div class="rules-page">
    <!-- 导航栏（原站 w-navbar 默认白底黑字） -->
    <div class="navbar">
      <img class="navbar-back" src="/static/cw/back_btn.png" alt="返回" @click="$router.back()" />
      <span class="nav-title"></span>
    </div>

    <div class="mobile-rules-container">
      <!-- 信息卡（原站硬编码 demo 文案；emoji 按项目 P0 规则以统一 SVG 描边图标等义替换，已在 API 文档记录差异） -->
      <div class="info-card">
        <div class="info-item">
          <span class="info-label">
            <svg class="lb-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2v1a2 2 0 0 0 0 4v1a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-1a2 2 0 0 0 0-4z"/><path d="M13 7v10" stroke-dasharray="2 2"/></svg>
            藏品名称
          </span>
          <span class="info-value">Good 夺宝卡</span>
        </div>
        <div class="info-item">
          <span class="info-label">
            <svg class="lb-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="M14.5 9.5c-.5-.9-1.4-1.5-2.5-1.5-1.5 0-2.5.9-2.5 2s1 1.7 2.5 2 2.5.9 2.5 2-1 2-2.5 2c-1.1 0-2-.6-2.5-1.5"/><path d="M12 6.5v11"/></svg>
            申购价格
          </span>
          <span class="info-value">99元/份</span>
        </div>
        <div class="info-item">
          <span class="info-label">
            <svg class="lb-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 8l-9-5-9 5v8l9 5 9-5z"/><path d="M3 8l9 5 9-5"/><path d="M12 13v8"/></svg>
            发行总量
          </span>
          <span class="info-value">100,000份</span>
        </div>
        <div class="info-item">
          <span class="info-label">
            <svg class="lb-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="13" r="8"/><path d="M12 9v4l2.5 2.5"/><path d="M9 2h6"/></svg>
            申购时间
          </span>
          <span class="info-value" style="font-size: 14px;">2026.01.01 14:00 - 2026.01.02 14:00</span>
        </div>
      </div>

      <!-- 申购规则 -->
      <div class="rule-section">
        <h3 class="section-title">
          <svg class="tt-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 20V10"/><path d="M10 20V4"/><path d="M16 20v-7"/><path d="M22 20H2"/></svg>
          申购规则
        </h3>
        <div class="rule-item highlight">
          <span class="rule-icon">
            <svg class="ri-svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="8" r="4"/><path d="M4 21c0-4 3.6-6 8-6s8 2 8 6"/></svg>
          </span>
          <p> 单个用户最大申购数量： <strong class="emphasize">10份</strong><br /><small>(每个账户最多申购10份Good 夺宝卡)</small></p>
        </div>
        <div class="rule-item">
          <span class="rule-icon">
            <svg class="ri-svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 17l6-6 4 4 8-8"/><path d="M15 7h6v6"/></svg>
          </span>
          <p> 发行总量： <strong class="emphasize">100,000份</strong><br /><small>Good 夺宝卡总发行数量为10万份</small></p>
        </div>
      </div>

      <!-- 申购流程 -->
      <div class="rule-section">
        <h3 class="section-title">
          <svg class="tt-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12a9 9 0 1 1-3-6.7"/><path d="M21 3v6h-6"/></svg>
          申购流程
        </h3>
        <div class="rule-item">
          <span class="rule-icon"><span class="num-badge">1</span></span>
          <p> 用户可在申购时间内进行申购 <br /><small>2026年1月1日14:00至1月2日14:00</small></p>
        </div>
        <div class="rule-item">
          <span class="rule-icon"><span class="num-badge">2</span></span>
          <p> 每个用户最多申购 <strong>10份</strong> Good 夺宝卡 <br /><small>按账户实名信息计算</small></p>
        </div>
        <div class="rule-item">
          <span class="rule-icon"><span class="num-badge">3</span></span>
          <p> 申购价格为 <strong class="emphasize">99元/份</strong><br /><small>申购时需确保账户余额充足</small></p>
        </div>
      </div>

      <!-- 结果与退款 -->
      <div class="rule-section">
        <h3 class="section-title">
          <svg class="tt-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="5" y="3" width="14" height="18" rx="2"/><path d="M9 8h6"/><path d="M9 12h6"/><path d="M9 16h4"/></svg>
          结果与退款
        </h3>
        <div class="rule-item">
          <span class="rule-icon">
            <svg class="ri-svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="M8 12.5l3 3 5-6"/></svg>
          </span>
          <p> 申购结束后公布中签结果 <br /><small>成功中签的用户将获得Good 夺宝卡</small></p>
        </div>
        <div class="rule-item highlight">
          <span class="rule-icon">
            <svg class="ri-svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 12a8 8 0 1 1 3 6.2"/><path d="M4 12V7"/><path d="M4 12h5"/><path d="M12 8v4l3 2"/></svg>
          </span>
          <p> 未中签的申购金额将 <strong class="refund">全额原路退回</strong><br /><small>申购失败的资金将自动退回至原支付账户</small></p>
        </div>
        <div class="rule-item">
          <span class="rule-icon">
            <svg class="ri-svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="13" r="8"/><path d="M12 9v4l2.5 2.5"/><path d="M9 2h6"/></svg>
          </span>
          <p> 退款将在申购结束后 <strong>1-3个工作日内</strong> 完成 <br /><small>具体到账时间以银行/支付机构为准</small></p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
/**
 * 原站 chunk 反解（pages-product-ruleDetail，纯静态硬编码页 GoodCardRules）：
 *  - 原站该页为固定 px 布局（非 rpx 体系），CSS 原样保留（vite 插件以 @nopx 标记豁免转换，见 style 块首行注释）
 *  - 原站以 emoji 表情字符（票券/钱袋/包裹/时钟/图表/人像/趋势/刷新/序号1-3/剪贴板/对勾/退款/秒表）作图标；
 *    按项目 P0 绝对规则，克隆版以统一 SVG 描边图标等义替换，几何占位一致（差异已在 API 文档登记）
 *  - 信息卡渐变 #667eea→#764ba2 为原站设计原样保留（非 P0-2 所指 Indigo→Pink 组合）
 */
</script>

<style scoped>
/*@nopx*/
.rules-page {
  min-height: 100vh;
  background: #f8f9fa;
}
.navbar {
  position: relative;
  width: 100%;
  height: 44px;
  display: flex;
  align-items: center;
}
.navbar-back {
  margin-left: 13px;
  width: 9px;
  height: 16px;
}
.nav-title {
  position: absolute;
  left: 50%;
  transform: translateX(-50%);
  font-size: 16px;
  color: #303133;
}
.mobile-rules-container {
  padding: 12px;
  font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
  max-width: 100%;
  box-sizing: border-box;
  color: #333;
  line-height: 1.5;
  background-color: #f8f9fa;
}
.info-card {
  background: linear-gradient(135deg, #667eea, #764ba2);
  border-radius: 16px;
  padding: 20px;
  margin-bottom: 20px;
  color: #fff;
  box-shadow: 0 6px 20px rgba(102, 126, 234, 0.2);
}
.info-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 10px 0;
  border-bottom: 1px solid hsla(0, 0%, 100%, 0.1);
}
.info-item:last-child {
  border-bottom: none;
}
.info-label {
  font-size: 14px;
  opacity: 0.9;
  display: inline-flex;
  align-items: center;
  gap: 4px;
}
.lb-icon {
  width: 14px;
  height: 14px;
  flex-shrink: 0;
}
.info-value {
  font-size: 16px;
  font-weight: 600;
  text-align: right;
}
.rule-section {
  background: #fff;
  border-radius: 16px;
  padding: 20px;
  margin-bottom: 16px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
  border: 1px solid #f0f0f0;
}
.section-title {
  font-size: 18px;
  color: #2c3e50;
  margin-top: 0;
  margin-bottom: 16px;
  padding-bottom: 12px;
  border-bottom: 2px solid #f0f0f0;
  font-weight: 700;
  display: flex;
  align-items: center;
  gap: 6px;
}
.tt-icon {
  width: 18px;
  height: 18px;
  flex-shrink: 0;
}
.rule-item {
  display: flex;
  align-items: flex-start;
  padding: 14px 0;
  border-bottom: 1px solid #f5f5f5;
}
.rule-item:last-child {
  border-bottom: none;
}
.rule-icon {
  font-size: 22px;
  margin-right: 16px;
  margin-top: 2px;
  flex-shrink: 0;
  width: 32px;
  height: 32px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: #f8f9fa;
  border-radius: 8px;
}
.ri-svg {
  width: 20px;
  height: 20px;
  color: #333;
}
.num-badge {
  width: 20px;
  height: 20px;
  border-radius: 50%;
  background: #e8edf5;
  color: #1a73e8;
  font-size: 13px;
  font-weight: 700;
  display: flex;
  align-items: center;
  justify-content: center;
}
.highlight {
  background-color: #f0f7ff;
  border-radius: 12px;
  padding: 16px;
  margin: 12px -8px;
  border-left: 4px solid #1a73e8;
}
.emphasize {
  color: #1a73e8;
  font-weight: 700;
}
.refund {
  color: #4caf50;
  font-weight: 700;
}
p {
  margin: 0;
}
strong {
  font-weight: 700;
}
small {
  font-size: 13px;
  color: #666;
  display: block;
  margin-top: 4px;
  line-height: 1.4;
}
</style>
