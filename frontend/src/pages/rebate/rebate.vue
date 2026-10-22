<template>
  <!-- 返利中心（pages/rebate/rebate）：依赖 commision_info 数据；
       原站直接访问 /api/user/commision_info 返回 404（实测）→ 页面渲染纯白空白，如实复刻 -->
  <div class="rb-page"></div>
</template>

<script setup>
import { onMounted } from 'vue'
import { request } from '../../api/request'

/**
 * 原站实测（2026-10-23）：commision_info → {code:404, msg:An error occurred} → 白屏
 */
onMounted(async () => {
  await request('/api/user/commision_info', {})
})
</script>

<style scoped>
.rb-page {
  min-height: 100vh;
  background: #fff;
}
</style>
