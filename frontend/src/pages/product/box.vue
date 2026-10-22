<template>
  <!-- 产品详情（pages/product/box）：需携带商品参数进入；
       原站直接访问（无参数）请求详情接口失败后重定向回首页
       （2026-10-23 抓包：BoxDetail/ProductDetail 返回「找不到该盲盒/商品」，最终 URL 变为 /pages/index/index），
       有效参数下的详情 UI 不可观测（原站商品数据无法获取），按规则7不臆造，仅复刻重定向行为 -->
</template>

<script setup>
import { onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { request } from '../../api/request'

const router = useRouter()
onMounted(async () => {
  const api = 'box' === 'box'
    ? '/api/box/blind_box/BoxDetail/ids/undefined?origin=undefined&crystal_goods_id=undefined'
    : '/api/box/collection/ProductDetail/ids/undefined?batch_id=undefined'
  await request(api, {})
  // 原站行为：详情不存在 → 重定向首页
  router.replace('/pages/index/index')
})
</script>
