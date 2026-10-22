<template>
  <!-- 转赠页（pages/regift/regift）：需携带 user_collection 参数进入；
       原站直接访问（无参数）重定向回首页（2026-10-23 抓包：最终 URL 变为 /pages/index/index），
       有效参数下的转赠 UI 不可观测（账号无可转赠藏品），按规则7不臆造，仅复刻重定向行为 -->
</template>

<script setup>
import { onMounted } from 'vue'
import { useRouter } from 'vue-router'

const router = useRouter()
onMounted(() => {
  // 原站行为：无转赠参数直接访问 → 重定向首页
  router.replace('/pages/index/index')
})
</script>
