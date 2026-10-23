<template>
  <div class="det-page">
    <!-- 导航栏（透明底，无标题，仅返回键 17rpx） -->
    <div class="navbar">
      <img class="navbar-back" src="/static/cw/back_btn.png" alt="返回" @click="$router.back()" />
    </div>

    <!-- 商品区（pt 60rpx：436 盒 + 422 主图 r45 / 深色信息卡） -->
    <div class="product-wrap">
      <div class="img-box">
        <img v-if="product.main_image" class="main-img" :src="resolveImg(product.main_image)" alt="" />
      </div>
      <div class="info-card">
        <div class="name-row">
          <span class="product-name">{{ product.name }}</span>
        </div>
        <div class="num-row">
          <span class="total-num">发行数&nbsp;&nbsp;{{ product.total_num }}</span>
        </div>
      </div>
    </div>

    <!-- 卡片区（藏品介绍 / 购买须知） -->
    <div class="cards">
      <div class="pf-card">
        <span class="pf-watermark"> QBOX </span>
        <div class="pf-title-row">
          <div class="pf-title-box">
            <div class="pf-underline"></div>
            <span class="pf-title"> 藏品介绍 </span>
          </div>
        </div>
        <!-- 藏品介绍内容（introduce_image 富文本；详情接口失败时原站不渲染） -->
        <div v-if="product.introduce_image" class="pf-content intro-content" v-html="product.introduce_image"></div>
      </div>

      <div class="pf-card">
        <span class="pf-watermark"> QBOX </span>
        <div class="pf-title-row">
          <div class="pf-title-box">
            <div class="pf-underline"></div>
            <span class="pf-title"> 购买须知 </span>
          </div>
        </div>
        <span class="pf-content"> 数字藏品为虚拟数字商品，而非实物，仅限实名认证为年满18周岁，并小于60周岁的中国大陆用户购买。数字藏品的版权由发行方或原创者拥有，除另行取得版权拥有者书面同意外，用户不得将数字藏品用于任何商业用途。本商品一经售出，不支持退换请勿对数字藏品进行炒作、场外交易、欺诈，或以任何其他非法方式进行使用。 </span>
      </div>
    </div>

    <!-- 底部操作栏（详情加载成功后按状态渲染按钮；原站 direct 访问 detail 失败 → 空栏） -->
    <div class="bottom-bar">
      <div class="bottom-btns">
        <div
          v-if="info.product && info.product.weither_resale === 0 && info.status === 1"
          class="action-btn"
          @click="toast('请先完成实名认证')"
        >
          <span class="action-btn-text">立即寄售</span>
        </div>
        <div
          v-if="info.product && info.product.weither_regift === 0 && info.status === 1"
          class="action-btn"
          @click="toast('请先完成实名认证')"
        >
          <span class="action-btn-text">立即转赠</span>
        </div>
        <div v-if="info.status === 2 && info.product && info.product.is_lock === 1" class="action-btn action-btn-lock">
          <span class="action-btn-text">锁定中</span>
        </div>
        <div
          v-if="info.status === 2 && info.product && info.product.is_lock !== 1"
          class="action-btn"
          @click="cancelPop = true"
        >
          <span class="action-btn-text">取消寄售</span>
        </div>
      </div>
    </div>

    <!-- 取消寄售确认（原站 w-toast「确认取消此寄售？」） -->
    <div v-if="cancelPop" class="mask" @click.self="cancelPop = false">
      <div class="confirm-modal">
        <span class="confirm-text">确认取消此寄售？</span>
        <div class="confirm-ft">
          <span class="confirm-btn" @click="cancelPop = false">取消</span>
          <span class="confirm-btn confirm-btn-primary" @click="onCancelConsignment">确定</span>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useRoute } from 'vue-router'
import { request, resolveImg } from '../../api/request'
import { toast } from '../../utils/toast'

/**
 * 原站 chunk 反解（pages-blindBox-blindBoxDetails）：
 *  - onLoad(id) → getUserCollectionDetails(id)（/user_collection/user_collection/detail/ids/{id}）
 *  - 详情失败（code:0 收藏不存在）时原站仍渲染壳：主图/名称/发行数空白 + 藏品介绍/购买须知卡
 *  - 底部按钮（blindBoxDetailButton）：status==1 → 立即寄售(weither_resale==0)/立即转赠(weither_regift==0)
 *    status==2 → 锁定中(is_lock==1)/取消寄售(确认弹窗「确认取消此寄售？」)
 */
const route = useRoute()
const info = ref({})
const product = ref({})
const cancelPop = ref(false)

async function getDetails() {
  const res = await request('/api/user_collection/user_collection/detail/ids/' + (route.query.id || 'undefined'))
  if (res.code === 1 && res.data) {
    info.value = res.data
    product.value = res.data.product || {}
  }
}

function onCancelConsignment() {
  cancelPop.value = false
}

getDetails()
</script>

<style scoped>
.det-page {
  min-height: 100vh;
  background-color: #f7f7f7;
  background-image: url('/static/qbox/market-goods-bg.png');
  background-repeat: no-repeat;
  background-size: 100%;
  background-position: center top;
}
/* ---- 导航栏（透明、无标题） ---- */
.navbar {
  position: fixed;
  left: 0;
  top: 0;
  z-index: 2;
  width: 100%;
  height: 79.7px;
  display: flex;
  align-items: center;
  background: transparent;
}
.navbar-back {
  margin-left: 23.6px;
  width: 17px;
  height: auto;
}
/* ---- 商品区 ---- */
.product-wrap {
  padding-top: 60px;
}
.img-box {
  width: 436px;
  height: 436px;
  margin: 0 auto;
  display: flex;
  justify-content: center;
  align-items: center;
}
.main-img {
  width: 422px;
  height: 422px;
  border-radius: 45px;
  object-fit: cover;
}
.info-card {
  background: #1b1d20;
  border-radius: 24px;
  padding: 27px 34px;
  margin: 27px 45px 0;
  display: flex;
  flex-direction: column;
}
.name-row {
  display: flex;
  align-items: center;
  justify-content: space-between;
}
.product-name {
  font-size: 32px;
  color: #fff;
}
.num-row {
  margin-top: 10px;
  display: flex;
  align-items: center;
}
.total-num {
  margin-left: 6px;
  color: #a3a3a3;
  font-size: 21px;
  font-family: var(--font-regular);
}
/* ---- 卡片区 ---- */
.cards {
  margin-top: 51px;
  padding: 0 32px calc(24px + env(safe-area-inset-bottom) + 120px);
  display: flex;
  flex-direction: column;
  gap: 24px;
}
.pf-card {
  width: 100%;
  background: #fff;
  border-radius: 32px;
  padding: 32px 28px 31px;
  box-sizing: border-box;
  display: flex;
  flex-direction: column;
  position: relative;
  overflow: hidden;
}
.pf-watermark {
  position: absolute;
  left: 42px;
  top: 9px;
  font-weight: 400;
  font-size: 51px;
  line-height: 26px;
  color: rgba(104, 104, 104, 0.08);
  font-family: 'NanoByongGyeHei-Regular', var(--font-regular);
  white-space: nowrap;
  pointer-events: none;
  z-index: 0;
}
.pf-title-row {
  width: 100%;
  margin-top: 16px;
  position: relative;
  z-index: 1;
  display: flex;
  align-items: flex-end;
}
.pf-title-box {
  position: relative;
}
.pf-underline {
  position: absolute;
  left: 0;
  right: 0;
  bottom: 6px;
  height: 10px;
  background: #0018ff;
}
.pf-title {
  position: relative;
  z-index: 1;
  font-size: 30.8px;
  color: #2d2d2d;
  font-family: 'AlimamaFangYuanTiVF-Thin', var(--font-regular);
}
.pf-content {
  position: relative;
  z-index: 1;
  font-weight: 300;
  font-size: 22px;
  color: #696969;
  font-family: 'AlimamaFangYuanTiVF-Thin', var(--font-regular);
  line-height: 1.7;
}
.intro-content :deep(p) {
  margin: 1em 0;
  padding: 0;
}
/* ---- 底部操作栏 ---- */
.bottom-bar {
  width: 100%;
  position: fixed;
  left: 0;
  bottom: 0;
  z-index: 10;
  padding: 16px 36px calc(16px + env(safe-area-inset-bottom));
  box-sizing: border-box;
  background: #f7f7f7;
}
.bottom-btns {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 16px;
}
.action-btn {
  flex: 1;
  height: 88px;
  background: #0018ff;
  border-radius: 44px;
  display: flex;
  align-items: center;
  justify-content: center;
}
.action-btn-lock {
  background: #cacdd3;
}
.action-btn-text {
  font-size: 30px;
  color: #fff;
  font-family: var(--font-medium);
}
/* ---- 取消寄售确认 ---- */
.mask {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.6);
  z-index: 999;
  display: flex;
  align-items: center;
  justify-content: center;
}
.confirm-modal {
  width: 622px;
  background: #fff;
  border-radius: 24px;
  padding: 60px 40px 0;
  display: flex;
  flex-direction: column;
  align-items: center;
}
.confirm-text {
  font-size: 30px;
  color: #333;
  font-family: var(--font-regular);
}
.confirm-ft {
  margin-top: 47px;
  width: 100%;
  display: flex;
  border-top: 0.5px solid #ebebeb;
}
.confirm-btn {
  flex: 1;
  padding: 25px 0;
  text-align: center;
  font-size: 32px;
  color: #000;
  font-family: var(--font-regular);
}
.confirm-btn-primary {
  color: #007aff;
  border-left: 0.5px solid #ebebeb;
}
</style>
