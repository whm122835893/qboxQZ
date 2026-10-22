<template>
  <!-- 市值排行榜（pages/marketCapRanking）：依赖活动/榜单数据；
       原站直接访问实测渲染纯白空白页（含 流通:/市值排行榜规则/知道了 弹窗逻辑，
       数据为空时 UI 不可观测），按规则7不臆造，复刻空白壳 -->
  <div class="mc-page"></div>
</template>

<script setup>
/**
 * 原站 chunk 反解（pages-marketCapRanking-marketCapRanking）：
 *  - 市值排行榜页：流通: 字段 + 「市值排行榜规则」弹窗（知道了）+ user/ranking 数据；
 *    直接访问无数据 → 纯白空白（实测截图）
 */
</script>

<style scoped>
.mc-page {
  min-height: 100vh;
  background: #fff;
}
</style>
