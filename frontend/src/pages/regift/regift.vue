<template>
  <!-- 转赠页（pages/regift/regift）：
       样式 1:1 实测自源站 2026-10-24（持仓详情点转赠 → ?id=180409，真实表单页）：
       导航"转赠" 18px Bold；藏品白卡(封面 91 方图圆角12 + 名称 17 Bold + "编号：#xxx/500000")；
       赠送价格卡(标题 15 #8c8c8c + 灰底输入框)；选择用户卡(搜索图标 + 输入邀请码/钱包地址)；
       底部"确认转赠"按钮（未填写为浅色禁用态，填写后 #0018ff）+ 提示"24小时内对方未领取，将退还给你" -->
  <div class="rg-page">
    <div class="navbar">
      <img class="nav-back" src="/static/cw/back_btn.png" alt="返回" @click="goBack" />
      <span class="nav-title">转赠</span>
    </div>

    <!-- 藏品卡 -->
    <div class="goods-card">
      <img class="g-cover" :src="goods.cover" :alt="goods.goods_name" />
      <div class="g-info">
        <span class="g-name">{{ goods.goods_name }}</span>
        <span class="g-no">编号：<span class="g-no-main">{{ goods.collection_no }}</span><span class="g-no-total">/{{ goods.limit_num }}</span></span>
      </div>
    </div>

    <!-- 赠送价格 -->
    <div class="form-card">
      <span class="form-title">赠送价格</span>
      <input v-model="price" class="form-input" type="text" placeholder="输入赠送价格" />
    </div>

    <!-- 选择用户 -->
    <div class="form-card">
      <span class="form-title">选择用户</span>
      <div class="input-wrap">
        <img class="input-icon" src="/static/qbox/common-search-gray.png" alt="" />
        <input v-model="account" class="form-input has-icon" type="text" placeholder="输入用户邀请码或线包地址" />
      </div>
    </div>

    <!-- 确认转赠（源站未填写为浅色禁用态；填写完整后变主蓝可点） -->
    <div class="rg-bottom">
      <div class="rg-btn" :class="{ disabled: !canSubmit }" @click="onSubmit">确认转赠</div>
      <span class="rg-note">24小时内对方未领取，将退还给你</span>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { toast } from '../../utils/toast'

const route = useRoute()
const router = useRouter()

/**
 * 演示数据：转赠表单提交需操作真实持仓资产（规则9：不向原站账号写入），
 * 字段由持仓详情页传入（id/编号），其余取演示值；接口对接后替换。
 */
const goods = ref({
  id: route.query.id || null,
  goods_name: 'KUUKIE 怪物口袋·黑熊',
  cover: '/uploads/20261013/891305447d1a31e997fd7a0e078488a5.png',
  collection_no: route.query.no ? decodeURIComponent(route.query.no) : '#000027',
  limit_num: 5000
})

const price = ref('')
const account = ref('')
const canSubmit = computed(() => price.value !== '' && account.value !== '')

function goBack() {
  history.length > 1 ? router.back() : router.replace('/pages/myCollection/myCollection')
}
function onSubmit() {
  if (!canSubmit.value) return
  // 支付/上链为 mock（规则9），提交后返回
  toast('转赠申请已提交')
  setTimeout(() => router.back(), 1500)
}
</script>

<style scoped>
.rg-page {
  min-height: 100vh;
  background: #f2f3f5;
  box-sizing: border-box;
}
.navbar {
  position: relative;
  width: 100%;
  height: 79.7px;
  display: flex;
  align-items: center;
  justify-content: center;
}
.nav-back {
  position: absolute;
  left: 23.6px;
  top: 50%;
  transform: translateY(-50%);
  width: 17px;
  height: auto;
}
.nav-title {
  font-size: 32.6px;
  font-weight: 700;
  color: #000;
}

/* 藏品卡（681×212 圆角 23.6，内边距 23.6；封面 164.9 圆角 21.7） */
.goods-card {
  margin: 0 34.4px;
  background: #fff;
  border-radius: 23.6px;
  padding: 23.6px;
  box-sizing: border-box;
  display: flex;
  align-items: center;
}
.g-cover {
  width: 164.9px;
  height: 164.9px;
  border-radius: 21.7px;
  object-fit: cover;
  background: #f2f2f2;
  flex-shrink: 0;
}
.g-info {
  flex: 1;
  min-width: 0;
  margin-left: 21.7px;
  display: flex;
  flex-direction: column;
}
.g-name {
  font-size: 30.8px;
  color: #111;
  font-family: 'SourceHanSansCN-Bold', var(--font-bold);
  font-weight: 400;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}
.g-no {
  margin-top: 12.6px;
  font-size: 21.7px;
  color: #111;
}
.g-no-main {
  color: #111;
}
.g-no-total {
  color: #8c8c8c;
}

/* 表单卡（标题 15px #8c8c8c + 灰底输入框） */
.form-card {
  margin: 21.7px 34.4px 0;
  background: #fff;
  border-radius: 23.6px;
  padding: 25.4px 23.6px 23.6px;
  box-sizing: border-box;
}
.form-title {
  display: block;
  font-size: 27.2px;
  color: #8c8c8c;
}
.form-input {
  margin-top: 16.3px;
  width: 100%;
  height: 83px;
  background: #f5f5f5;
  border: none;
  outline: none;
  border-radius: 16.3px;
  padding: 0 23.6px;
  box-sizing: border-box;
  font-size: 25.4px;
  color: #111;
  font-family: var(--font-regular);
}
.form-input::placeholder {
  color: #c0c4cc;
}
.input-wrap {
  position: relative;
  margin-top: 16.3px;
}
.input-icon {
  position: absolute;
  left: 23.6px;
  top: 50%;
  transform: translateY(-50%);
  width: 30.8px;
  height: 30.8px;
}
.input-wrap .form-input {
  margin-top: 0;
  padding-left: 65px;
}

/* 底部按钮（未填写禁用浅色，填写后 #0018ff）+ 提示 */
.rg-bottom {
  margin-top: 58px;
  padding: 0 34.4px;
}
.rg-btn {
  width: 100%;
  height: 85px;
  border-radius: 42.5px;
  background: #0018ff;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 29px;
  color: #fff;
}
.rg-btn.disabled {
  background: #7385f2;
}
.rg-note {
  display: block;
  margin-top: 23.6px;
  text-align: center;
  font-size: 23.6px;
  color: #9a9a9a;
}
</style>
