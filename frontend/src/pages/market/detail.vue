<template>
  <!-- 市场藏品详情（pages/market/detail）：需携带市场商品 id 参数进入；
       原站直接访问（无参数）MarketDetail 返回「已售出」后重定向回首页
       （2026-10-23 抓包：最终 URL 变为 /pages/index/index），复刻该行为 -->
</template>

<script setup>
import { onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { request } from '../../api/request'

const router = useRouter()
onMounted(async () => {
  await request('/api/market/market/MarketDetail/ids/undefined', {})
  // 原站行为：详情不存在（已售出）→ 重定向首页
  router.replace('/pages/index/index')
})
</script>
