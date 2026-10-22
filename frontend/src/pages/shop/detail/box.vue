<template>
  <!-- 积分商品详情（pages/shop/detail/box）：需携带商品参数进入；
       原站直接访问（无参数）实测重定向回首页（2026-10-23 抓包：URL 变为 /pages/index/index），
       有效参数下的详情 UI 未观测到（积分商城商品列表为空），按规则7不臆造，仅复刻重定向行为 -->
</template>

<script setup>
import { onMounted } from 'vue'
import { useRouter } from 'vue-router'

const router = useRouter()
onMounted(() => {
  // 原站行为：无商品参数直接访问 → 重定向首页
  router.replace('/pages/index/index')
})
</script>
