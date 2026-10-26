<template>
  <!-- 寄售提交页（pages/consignments/consignments）：
       样式 1:1 实测自源站 2026-10-24（持仓弹窗去寄售 → ?pageType=2&id=180409，真实表单页）：
       导航"发起寄售"；藏品白卡(封面 + 名称 + 最高价/求购价两行 + 蓝色"返回市场"按钮)；
       寄售单价卡(灰底输入框 ¥ + 元/份 后缀)；收款方式卡(灰底行：F 图标 + 汇付钱包 + 蓝色对勾)；
       费用明细三行(总售价/综合服务费5%/预计收入，随单价实时联动)；寄售说明卡(源站原文两条)；
       底部"发起寄售"按钮（未填写浅色禁用态，填写后 #0018ff） -->
  <div v-if="!hasParams" class="cs-blank"></div>
  <div v-else class="cs-page">
    <div class="navbar">
      <img class="nav-back" src="/static/cw/back_btn.png" alt="返回" @click="goBack" />
      <span class="nav-title">发起寄售</span>
    </div>

    <!-- 藏品卡 -->
    <div class="goods-card">
      <img class="g-cover" :src="goods.cover" :alt="goods.goods_name" />
      <div class="g-info">
        <span class="g-name">{{ goods.goods_name }}</span>
        <span class="g-line">当前藏品最高价：¥--</span>
        <span class="g-line">当前藏品求购价：¥--</span>
      </div>
      <span class="g-market-btn" @click="goBack">返回市场</span>
    </div>

    <!-- 寄售单价 -->
    <div class="form-card">
      <span class="form-title">寄售单价</span>
      <div class="price-box">
        <span class="price-cny">¥</span>
        <input v-model="price" class="price-input" type="text" placeholder="请输入寄售单价" />
        <span class="price-unit">元/份</span>
      </div>

      <!-- 收款方式 -->
      <span class="form-title sec-gap">收款方式</span>
      <div class="pay-row">
        <span class="pay-icon">F</span>
        <div class="pay-info">
          <span class="pay-name">汇付钱包</span>
          <span class="pay-sub">安全便捷 交易更高效</span>
        </div>
        <span class="pay-check">
          <svg viewBox="0 0 12 10" fill="none"><path d="M1 5.2 4.4 8.6 11 1.4" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
        </span>
      </div>
    </div>

    <!-- 费用明细（随单价联动） -->
    <div class="fee-area">
      <div class="fee-row">
        <span class="fee-label">总售价</span>
        <span class="fee-value">¥{{ feeTotal }}</span>
      </div>
      <div class="fee-row">
        <span class="fee-label">综合服务费(5%)</span>
        <span class="fee-value">- ¥{{ feeService }}</span>
      </div>
      <div class="fee-row">
        <span class="fee-label">预计收入</span>
        <span class="fee-value">¥{{ feeIncome }}</span>
      </div>
    </div>

    <!-- 寄售说明（源站原文） -->
    <div class="notice-card">
      <span class="notice-title">寄售说明</span>
      <p class="notice-text">1.商品未经购买前，可取消上架，取消挂单后三分钟禁止挂单，一经购买，无法取消上架。</p>
      <p class="notice-text">2你将获得除综合服务费 (5%) 之外的所有收入.</p>
    </div>

    <!-- 底部固定按钮 -->
    <div class="cs-bottom">
      <div class="cs-btn" :class="{ disabled: !canSubmit }" @click="onSubmit">发起寄售</div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { request } from '../../api/request'
import { toast } from '../../utils/toast'
import { useRoute, useRouter } from 'vue-router'

const route = useRoute()
const router = useRouter()

/**
 * 源站登录态实测（750 视口，2026-10-25）：直接访问（无 id / pageType 参数）时
 * 页面为完全空白（uni-page-body 内无任何节点，innerText 长度 0）。
 * 携带参数（持仓弹窗 → ?pageType=2&id=xxx）时才渲染真实表单。
 * 故：无参数 → 空白页；有参数 → 下方完整表单。
 */
const hasParams = computed(() => !!(route.query.id || route.query.pageType))

/**
 * 寄售上架页 —— 唯一数据源约束：
 *   持仓 id 经 URL 参数传入，藏品信息由持仓详情接口
 *   （/api/user_collection/user_collection/detail/ids/{id}）查询渲染，禁止写死数据。
 *   提交调用 POST /api/market/market/consign（真实上架，服务费率由后端配置下发）。
 */
const goods = ref({
  id: route.query.id || null,
  goods_name: '',
  cover: '',
  collection_no: route.query.no ? decodeURIComponent(route.query.no) : ''
})

async function loadGoods() {
  if (!goods.value.id) return
  const res = await request('/api/user_collection/user_collection/detail/ids/' + goods.value.id, {})
  if (!res || res.code !== 1 || !res.data) {
    toast(res && res.msg ? res.msg : '找不到该持仓')
    return
  }
  const d = res.data
  goods.value.goods_name = (d.goods && d.goods.name) || d.goods_name || ''
  goods.value.cover = (d.goods && d.goods.main_image) || d.main_image || ''
  if (!goods.value.collection_no) goods.value.collection_no = '#' + (d.collection_code ?? '')
}

const price = ref('')
const num = computed(() => parseFloat(price.value) || 0)
const feeTotal = computed(() => num.value ? num.value.toFixed(2).replace(/\.00$/, '') : '0')
// 服务费率：由系统配置下发（后端 sysConfig），前端按同值展示；提交以后端计算为准
const feeRate = 0.05
const feeService = computed(() => (num.value * feeRate).toFixed(2))
const feeIncome = computed(() => (num.value * (1 - feeRate)).toFixed(2))
const canSubmit = computed(() => num.value > 0 && goods.value.id)

function goBack() {
  history.length > 1 ? router.back() : router.replace('/pages/myCollection/myCollection')
}
async function onSubmit() {
  if (!canSubmit.value) return
  const res = await request('/api/market/market/consign', {
    user_collectible_id: goods.value.id,
    price: num.value
  })
  if (!res || res.code !== 1) {
    toast(res && res.msg ? res.msg : '上架失败')
    return
  }
  toast('寄售挂单已提交')
  setTimeout(() => router.replace('/pages/consignments/myConsignment'), 1500)
}

onMounted(loadGoods)
</script>

<style scoped src="../../styles/pages/consignments-consignments.css"></style>
