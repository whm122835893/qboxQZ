<template>
  <!-- 抽签活动页（pages/activity/draw）：依赖活动数据；
       原站直接访问实测 /api/draw/get 返回「活动不存在」，页面渲染纯白空白（无导航等元素），如实复刻 -->
  <div class="draw-page"></div>
</template>

<script setup>
import { onMounted } from 'vue'
import { request } from '../../api/request'

/**
 * 原站 chunk 反解（pages-activity-draw）：
 *  - onLoad → /api/draw/get → 活动不存在（实测）→ 白屏；
 *    另有 drawRecord（抽奖记录）/ user/ranking 辅助请求
 */
onMounted(async () => {
  await request('/api/draw/get', {})
})
</script>

<style scoped>
.draw-page {
  min-height: 100vh;
  background: #fff;
}
</style>
