<template>
  <!-- 出售求购单/市场商品详情（pages/market/sellBegBuy）：需携带参数进入；
       原站直接访问（无参数）请求详情失败后重定向回首页
       （2026-10-23 抓包：接口返回「藏品不存在」/500，最终 URL 变为 /pages/index/index），
       有效参数下的 UI 不可观测，按规则7不臆造，仅复刻重定向行为 -->
</template>

<script setup>
import { onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { request } from '../../api/request'

const router = useRouter()
onMounted(async () => {
  await request('/api/market/trade/getSellCollectionInfo', {})
  // 原站行为：详情不存在 → 重定向首页
  router.replace('/pages/index/index')
})
</script>
