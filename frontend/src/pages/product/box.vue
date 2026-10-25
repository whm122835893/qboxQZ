<template>
  <!-- 盲盒详情（pages/product/box）
       源站 DOM 实测（750 视口，2026-10-25，登录态，无参数直接访问）：
       - 根容器 bg #f7f7f7 + /static/qbox/market-goods-bg.png（no-repeat / size 100% 994px / center top）
       - 导航栏 44px，返回图 17×30（/static/cw/back_btn.png），
         标题「盲盒名称」32px/500 #2d2d2d AlimamaShuHeiTi-Bold，右侧收藏图 40×40（detail-unfavorited.png）
       - 内容 align center，padding 0 33px，padding-bottom 220px
       - 封面 436×436 mt 139，bg #000，shadow 6px 1px 33px rgba(0,0,0,.13)，radius 64，border 6px #fff
       - 价格卡 684px mt 65，bg rgba(255,255,255,.83)，radius 32，padding 32 36：
         左列名称 32px Bold #111；标签行「限量0份」蓝底 #0018ff r12 px16 h40 / 「流通0份」黑底 #111
         右列价格「¥」19px + 「0.00」38px，AlimamaFangYuanTiVF-Thin #2d2d2d
       - 三个信息卡 684px mt 24，bg #fff，radius 32，padding 32 28：
         「创作者」/「藏品介绍」/「购买须知」，标题 32px AlimamaShuHeiTi-Bold #2d2d2d（底部 10px 蓝条）
         创作者卡含头像 122×122 r19 + 名称 22px
         购买须知正文 22px/1.7 #696969 AlimamaFangYuanTiVF-Thin
       - 底部固定条 bg #f7f7f7 padding 16 36，按钮 h88 r44 bg #cacdd3，文案「立即购买」28px #fff -->
  <div class="pb-page">
    <div class="navbar">
      <img class="nav-back" src="/static/cw/back_btn.png" alt="返回" @click="goBack" />
      <span class="nav-title">盲盒名称</span>
      <img class="nav-fav" src="/static/qbox/detail-unfavorited.png" alt="收藏" />
    </div>

    <div class="content">
      <!-- 封面 -->
      <div class="cover-box">
        <img v-if="goods.listimg" class="cover-img" :src="goods.listimg" :alt="goods.name" />
      </div>

      <!-- 价格卡 -->
      <div class="price-card">
        <div class="pc-left">
          <span class="pc-name">{{ goods.name }}</span>
          <div class="pc-tags">
            <span class="tag tag-limit">限量{{ goods.total_num }}份</span>
            <span class="tag tag-flux">流通{{ goods.flux }}份</span>
          </div>
        </div>
        <div class="pc-right">
          <span class="pc-symbol">¥</span>
          <span class="pc-value">{{ goods.price }}</span>
        </div>
      </div>

      <!-- 创作者 -->
      <div class="info-card">
        <span class="watermark">QBOX</span>
        <div class="ic-title">
          <span class="ic-bar"></span>
          <span class="ic-text">创作者</span>
        </div>
        <div class="creator-row">
          <img class="cr-avatar" src="/static/qbox/detail-creator-default.png" alt="创作者" />
          <span class="cr-name">{{ goods.creator }}</span>
        </div>
      </div>

      <!-- 藏品介绍 -->
      <div class="info-card">
        <span class="watermark">QBOX</span>
        <div class="ic-title">
          <span class="ic-bar"></span>
          <span class="ic-text">藏品介绍</span>
        </div>
      </div>

      <!-- 购买须知 -->
      <div class="info-card">
        <span class="watermark">QBOX</span>
        <div class="ic-title">
          <span class="ic-bar"></span>
          <span class="ic-text">购买须知</span>
        </div>
        <p class="notice-text">
          数字藏品为虚拟数字商品，而非实物，仅限实名认证为年满18周岁，并小于60周岁的中国大陆用户购买。数字藏品的版权由发行方或原创者拥有，除另行取得版权拥有者书面同意外，用户不得将数字藏品用于任何商业用途。本商品一经售出，不支持退换请勿对数字藏品进行炒作、场外交易、欺诈，或以任何其他非法方式进行使用。
        </p>
      </div>
    </div>

    <!-- 底部固定条 -->
    <div class="bottom-bar">
      <div class="buy-btn">
        <span class="buy-text">立即购买</span>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'

/**
 * 源站 DOM 实测（2026-10-25，750 视口，登录态，无参数直接访问）：
 * 根容器 .page-container：min-height 100%，bg rgb(247,247,247)，
 *   background-image url(/static/qbox/market-goods-bg.png)，no-repeat / size 100% 994px / center top。
 * u-navbar 高 44px：返回 uni-image 17×30（/static/cw/back_btn.png），
 *   标题 uni-text width 400px / 32px / 500 / #2d2d2d / AlimamaShuHeiTi-Bold；
 *   右侧 uni-image 40×40（/static/qbox/detail-unfavorited.png）。
 * 内容 wrapper：width 100%，flex column，align-items center，padding 0 33px，padding-bottom 220px。
 * 封面：436×436，margin-top 139，bg #000，box-shadow 6px 1px 33px rgba(0,0,0,.13)，
 *   radius 64，border 6px solid #fff，overflow hidden。
 * 价格卡：width 684，margin-top 65，bg rgba(255,255,255,.83)，同 shadow，radius 32，padding 32 36，
 *   flex space-between align-center。
 *   左列：名称 32px SourceHanSansCN-Bold #111；标签行 gap 12：
 *     「限量N份」h40 padding 0 16 bg #0018ff radius 12，22px AlimamaFangYuanTiVF-Thin #fff；
 *     「流通N份」bg #111 同规格。
 *   右列：align-items flex-end；「¥」19px line-height 1 + 「0.00」38px，
 *     AlimamaFangYuanTiVF-Thin #2d2d2d。
 * 信息卡：width 684，margin-top 24，bg #fff，radius 32，padding 32 28，position relative overflow hidden。
 *   水印：absolute left 42 top 34，51px / line-height 26px / rgba(104,104,104,.08) / NanoByongGyeHei-Regular。
 *   标题行：relative z-index 1，align-items flex-end；底部 10px 高 #0018ff 蓝条 absolute；
 *     标题 32px AlimamaShuHeiTi-Bold #2d2d2d。
 *   创作者行：mt 24，头像 122×122 r19，名称 22px #2d2d2d。
 *   购买须知正文：22px / line-height 1.7 / #696969 / AlimamaFangYuanTiVF-Thin。
 * 底部固定条：fixed bottom 0，width 750，padding 16px 36px，bg rgb(247,247,247)；
 *   按钮 flex1 h88 radius 44 bg rgb(202,205,211)；文案 28px SourceHanSansCN-Medium #fff。
 */
const router = useRouter()

// 源站在无商品参数时返回空字段（名称/份数/价格均为 0），如实复刻
const goods = ref({
  name: '',
  listimg: '',
  total_num: 0,
  flux: 0,
  price: '0.00',
  creator: 'QBOX'
})

function goBack() {
  if (window.history.length > 1) router.back()
  else router.replace('/pages/index/index')
}
</script>

<style scoped src="../../styles/pages/product-box.css"></style>
