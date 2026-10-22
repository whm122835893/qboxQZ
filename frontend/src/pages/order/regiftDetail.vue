<template>
  <!-- 转赠订单详情（pages/order/regiftDetail）：需携带订单 id 参数进入；
       原站直接访问（无参数）giveOrderDetail 返回「找不到该订单或已被删除」后重定向回首页
       （2026-10-23 抓包：最终 URL 变为 /pages/index/index），复刻该行为 -->
</template>

<script setup>
import { onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { request } from '../../api/request'

const route = useRoute()
const router = useRouter()
onMounted(async () => {
  await request('/api/order/order/giveOrderDetail/ids/' + (route.query.id || 'undefined'), {})
  // 原站行为：订单不存在 → 重定向首页
  router.replace('/pages/index/index')
})
</script>
