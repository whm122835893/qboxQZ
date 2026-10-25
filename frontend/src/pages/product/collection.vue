<template>
  <!-- 藏品发售详情（pages/product/collection）：
       样式 1:1 实测自源站 2026-10-24（#/pages/product/collection?id=7&batch_id=3，「创世蛇凤里希」页）：
       浅灰页底 #f7f7f7 / 居中方形主图白框阴影 / 白色名称卡(名称 Bold + 蓝色"限量N份"胶囊 + 右侧价格) /
       黑色胶囊 Tabs(藏品信息/相关公告) / 内容白卡(QBOX 水印 + 蓝色下划线小节标题：创作者/藏品介绍/购买须知) /
       底部固定蓝色按钮（源站未开售为"设置购买提醒"+倒计时，发售中状态为"立即购买"，取源站盲盒详情实测按钮文案） -->
  <div class="pd-page">
    <div class="pd-navbar">
      <img class="nav-back" src="/static/cw/back_btn.png" alt="返回" @click="goBack" />
      <span class="nav-title">{{ goods.goods_name }}</span>
      <img class="nav-fav" src="/static/qbox/detail-unfavorited.png" alt="收藏" />
    </div>

    <!-- 主图（居中方卡，白框 + 阴影） -->
    <div class="pd-main-wrap">
      <img class="pd-main-img" :src="goods.cover" :alt="goods.goods_name" />
    </div>

    <!-- 名称卡 -->
    <div class="pd-name-card">
      <div class="pd-left">
        <span class="pd-name">{{ goods.goods_name }}</span>
        <span class="pd-capsule">限量{{ goods.limit_num }}份</span>
      </div>
      <div class="pd-price">
        <span class="pd-cny">¥</span>
        <span class="pd-num">{{ goods.price }}</span>
      </div>
    </div>

    <!-- Tabs（黑色胶囊） -->
    <div class="pd-tabs">
      <span class="pd-tab" :class="{ on: tab === 0 }" @click="tab = 0">藏品信息</span>
      <span class="pd-tab" :class="{ on: tab === 1 }" @click="tab = 1">相关公告</span>
    </div>

    <!-- 藏品信息 -->
    <template v-if="tab === 0">
      <div class="pd-card">
        <span class="pd-sec-title">创作者</span>
        <span class="pd-creator">{{ goods.creator }}</span>
      </div>
      <div class="pd-card pd-card-wide">
        <span class="pd-sec-title">藏品介绍</span>
        <p class="pd-text">{{ goods.intro }}</p>
      </div>
      <div class="pd-card">
        <span class="pd-sec-title">购买须知</span>
        <p class="pd-text">{{ goods.notice }}</p>
      </div>
    </template>

    <!-- 相关公告（源站该 Tab 未观测到内容，按空态处理） -->
    <div v-else class="pd-card pd-empty">
      <img class="pd-empty-img" src="/static/qbox/common-empty.png" alt="" />
    </div>

    <!-- 底部固定按钮（发售中 → 立即购买） -->
    <div class="pd-bottom">
      <div class="pd-buy-btn" @click="onBuy">立即购买</div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter, useRoute } from 'vue-router'

const router = useRouter()
const route = useRoute()

/**
 * 演示数据：后端未就绪（ProductDetail 接口源站需有效商品 id，无法抓包），
 * 字段结构对应 docs/03-API接口文档 的 ProductDetail；id/batch_id 由首页发售卡传入。
 */
const goods = ref({
  id: route.query.id || null,
  batch_id: route.query.batch_id || null,
  goods_name: 'KUUKIE 怪物口袋·黑熊',
  cover: '/uploads/20261013/891305447d1a31e997fd7a0e078488a5.png',
  limit_num: 5000,
  price: '39.90',
  creator: 'qbox',
  intro: 'KUUKIE 怪物口袋系列数字藏品，以街头潮流文化为灵感，每一只怪物都有独一无二的性格与故事。',
  // 购买须知文案取自源站详情页实测原文
  notice:
    '数字藏品为虚拟数字商品，而非实物，仅限实名认证为年满18周岁，并小于60周岁的中国大陆用户购买。' +
    '数字藏品的版权由发行方或原创者拥有，除另行取得版权权利者书面同意外，用户不得将数字藏品用于任何商业用途。' +
    '本商品一经售出，不支持退换。请勿对数字藏品进行炒作、场外交易、欺诈，或以任何其他非法方式进行使用。'
})

const tab = ref(0)

function goBack() {
  history.length > 1 ? router.back() : router.replace('/pages/index/index')
}
function onBuy() {
  // 支付为 mock（规则9：不对接真实资金通道），跳转订单确认占位
  router.push('/pages/order/orderSubmit?id=' + (goods.value.id || ''))
}
</script>

<style scoped src="../../styles/pages/product-collection.css"></style>
