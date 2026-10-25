<template>
  <div class="se-page">
    <!-- 导航栏 -->
    <div class="navbar">
      <img class="navbar-back" src="/static/cw/back_btn.png" alt="返回" @click="$router.back()" />
      <span class="nav-title">委托购买</span>
    </div>

    <div class="body">
      <!-- 商品卡（bg#fff r12 padding20） -->
      <div class="goods-card">
        <div v-if="info.listimg" class="goods-img-box">
          <img v-if="info.listimg" class="goods-img" :src="resolveImg(info.listimg)" alt="" />
        </div>
        <div class="goods-name-box">
          <span class="goods-name">{{ info.name }}</span>
        </div>
      </div>

      <!-- 委托价格 -->
      <div class="field-block">
        <span class="field-title">委托价格</span>
        <div class="price-row">
          <input v-model="form.price" class="price-input" type="number" placeholder="请输入价格" @input="priceInput" />
        </div>
      </div>

      <!-- 购买数量 -->
      <div class="field-block field-block-mt">
        <span class="field-title">购买数量</span>
        <div class="stepper">
          <div class="step-btn" @click="minus"><span class="step-symbol"> - </span></div>
          <input v-model="form.number" class="step-input" type="number" @input="changenum" />
          <div class="step-btn" @click="add"><span class="step-symbol"> + </span></div>
        </div>
      </div>

      <!-- 支付方式 -->
      <div class="field-block field-block-mt">
        <span class="field-title">支付方式</span>
        <div v-for="p in payList" :key="p.auth_key" class="pay-card" @click="targetCheck(p)">
          <div class="pay-left">
            <template v-if="p.able">
              <svg v-if="p.check" class="pay-check" viewBox="0 0 24 24" fill="#262626"><path d="M12 2a10 10 0 1 0 0 20 10 10 0 0 0 0-20zm-1.2 13.6l-3.8-3.8 1.4-1.4 2.4 2.4 5-5 1.4 1.4z"/></svg>
              <svg v-else class="pay-check" viewBox="0 0 24 24" fill="none" stroke="#999" stroke-width="1.6"><circle cx="12" cy="12" r="9"/><path d="M8 12.5l2.8 2.8 5.2-5.6"/></svg>
            </template>
            <span class="pay-name">{{ p.aname }}钱包</span>
          </div>
          <span v-if="!p.able" class="pay-open" @click.stop="toOpen">去开通></span>
        </div>
      </div>
    </div>

    <div class="footer-space"></div>

    <!-- 底部（fixed 白底：合计 + 预付 #ff7800） -->
    <div class="footer">
      <div class="footer-inner">
        <span class="total-text">合计：￥{{ totalPrice }}</span>
        <div class="prepay-btn prepay-text" :style="{ opacity: disable ? 0.5 : 1 }" @click="createOrder">预付</div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { request, resolveImg } from '../../api/request'
import { useUserStore } from '../../stores/user'
import { toast } from '../../utils/toast'

/**
 * 原站 chunk 反解（pages-market-submitEntrust）：
 *  - getInfo（商品）→ getEntrustInfo(collection_id)（失败时 chargeInfo 空：无委托说明卡，实测）
 *  - payList 同求购页（basePayList 汇付 A 钱包，单选）
 *  - totalPrice = price*number 2位（否则 --）；底部 w-button 330rpx #ff7800 预付
 *  - createOrder → Prepayment/createOrder → 拉起支付
 */
const route = useRoute()
const router = useRouter()
const userStore = useUserStore()

const info = ref({})
const form = ref({ number: 1, price: null })
const payList = ref([])

const totalPrice = computed(() =>
  form.value.price && form.value.number ? (form.value.price * form.value.number).toFixed(2) : '--'
)
const disable = computed(() => {
  const hasCheck = payList.value.findIndex((p) => p.check) > -1
  return !(form.value.number && form.value.price && hasCheck)
})

async function initPayList() {
  await userStore.getUserInfo()
  const basePayList = [{ name: '汇付', aname: 'A', auth_key: 'huifu_account', pay_type: 4, pay_way: 'huifu', able: true }]
  payList.value = basePayList.map((p) => ({ ...p, able: !!userStore.userinfo?.[p.auth_key], check: false }))
}

async function getInfo() {
  const res = await request('/api/market/Prepayment/collectionDetail', { collection_id: route.query.id || null })
  if (res.code === 1 && res.data) info.value = res.data
}

function minus() {
  if (form.value.number != 1) form.value.number = parseInt(form.value.number) - 1
}
function add() {
  form.value.number = parseInt(form.value.number) + 1
}
function changenum() {
  if (!form.value.number || form.value.number < 1) form.value.number = 1
}
function priceInput() {
  const d = String(form.value.price ?? '')
  if (d.includes('.')) form.value.price = d.split('.')[0]
}
function targetCheck(p) {
  if (!p.able) return
  payList.value = payList.value.map((t) => ({ ...t, check: t.auth_key === p.auth_key && !p.check }))
}
function toOpen() {
  router.push('/pages/wallet/wallet')
}

async function createOrder() {
  if (disable.value) return
  const p = payList.value.find((x) => x.check)
  const res = await request('/api/market/Prepayment/createOrder', { ...form.value, pay_way: p.pay_way, collection_id: route.query.id || null })
  if (res.code === 1) {
    // 原站此处拉起支付；独立项目支付通道未接入（标注推断）
    toast('下单成功，支付通道暂未接入')
  }
}

initPayList()
getInfo()
</script>

<style scoped src="../../styles/pages/market-submitEntrust.css"></style>
