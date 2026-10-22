<template>
  <!-- 寄售提交页（pages/consignments/consignments）：需携带 collection 参数进入；
       原站直接访问（无参数）渲染纯白空白页（2026-10-23 实测截图），如实复刻空白壳 -->
  <div class="cs-page"></div>
</template>

<script setup>
/**
 * 原站 chunk 反解（pages-consignments-consignments）：
 *  - 寄售提交页完整逻辑（商品卡/藏品编号/寄售单价/总售价/综合服务费/预计收入/收款方式汇付·易宝/
 *    发起寄售→再次确认→最终确认→操作密码校验），但全部依赖 collection 数据渲染；
 *  - 直接访问（无参数）时原站渲染纯白空白（实测），有效 UI 不可观测，按规则7不臆造，仅复刻空白壳；
 *  - 反解的表单结构与文案已登记 API 文档 6h 节，供后端联调阶段实现参考
 */
</script>

<style scoped>
.cs-page {
  min-height: 100vh;
  background: #fff;
}
</style>
