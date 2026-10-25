<template>
  <div class="sdg-page"></div>
</template>

<script setup>
/**
 * 源站 DOM 实测（2026-10-25，750 视口，登录态）：
 * uni-page-body 内容为 <!---->（空注释节点），页面 body.innerText 长度为 0，
 * location.hash 保持 /pages/shop/detail/goods（不跳转首页）。
 * 说明：源站积分商城商品列表为空，该详情页无数据可渲染，故为空白。
 */
</script>

<style scoped>
.sdg-page {
  min-height: 100vh;
  background: #f7f7f7;
}
</style>
