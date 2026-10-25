<template>
  <div class="sb-page">
    <!-- 导航栏 -->
    <div class="navbar">
      <img class="navbar-back" src="/static/cw/back_btn.png" alt="返回" @click="$router.back()" />
      <span class="nav-title">发起求购</span>
    </div>

    <div class="body">
      <!-- 商品卡（678rpx r22 padding28） -->
      <div class="goods-card">
        <div class="goods-img-box">
          <img v-if="info.listimg" class="goods-img" :src="resolveImg(info.listimg)" alt="" />
        </div>
        <div class="goods-info">
          <span class="goods-name"> {{ info.name || '' }} </span>
          <span class="goods-line"> 当前藏品最高价：¥ {{ highPrice }} </span>
          <span class="goods-line"> 当前藏品求购价：¥ {{ buyPrice }} </span>
        </div>
        <div class="market-btn" @click="goMarket"><span class="market-btn-text"> 返回市场 </span></div>
      </div>

      <!-- 表单卡 -->
      <div class="form-card">
        <span class="form-title"> 求购数量 </span>
        <div class="stepper">
          <div class="step-btn step-left" @click="minus"><span class="step-symbol"> - </span></div>
          <input v-model="form.number" class="step-input" type="number" @input="changenum" />
          <div class="step-btn step-right" @click="add"><span class="step-symbol"> + </span></div>
        </div>
        <span class="form-title form-title-mt"> 求购单价 </span>
        <div class="price-row">
          <span class="price-symbol"> ¥ </span>
          <input v-model="form.price" class="price-input" type="number" placeholder="请输入价格" @input="priceInput" />
          <span class="price-unit">元/份</span>
        </div>
        <span class="form-title form-title-mt"> 支付方式 </span>
        <div v-for="p in payList" :key="p.auth_key" class="pay-row" @click="targetCheck(p)">
          <div class="pay-left">
            <div class="pay-badge"><span class="pay-badge-text"> {{ (p.aname || '').slice(0, 1) }} </span></div>
            <span class="pay-name"> {{ p.aname }}钱包 </span>
          </div>
          <svg v-if="p.able && p.check" class="pay-check" viewBox="0 0 24 24" fill="#0018FF"><path d="M12 2a10 10 0 1 0 0 20 10 10 0 0 0 0-20zm-1.2 13.6l-3.8-3.8 1.4-1.4 2.4 2.4 5-5 1.4 1.4z"/></svg>
          <svg v-else-if="p.able" class="pay-check" viewBox="0 0 24 24" fill="none" stroke="#c8c8c8" stroke-width="1.6"><circle cx="12" cy="12" r="9"/><path d="M8 12.5l2.8 2.8 5.2-5.6"/></svg>
          <span v-else class="pay-open" @click.stop="toOpen"> 去开通 </span>
        </div>
      </div>

      <!-- 求购说明卡 -->
      <div class="note-card">
        <span class="note-title"> 求购说明 </span>
        <span class="note-text"> 1.自发求购订单您可选择求购信息的有效时长，当前默认选择为7 天。在有效期限内，您的求购信息会展示给持有该藏品且有意出售的卖家。 </span>
        <span class="note-text"> 2.若在期限内有卖家接受您的求购条件，系统将自动匹配并推进交易结算，若期限结束仍未成功匹配，已冻结的相应资金会解冻并退回您的账户。 </span>
      </div>
    </div>

    <!-- 底部（fixed：合计 + 预付） -->
    <div class="footer">
      <div class="total-row">
        <span class="total-label"> 合计： </span>
        <span class="total-price"> ¥{{ totalPrice }} </span>
      </div>
      <div class="prepay-btn" :style="{ opacity: disable ? 0.5 : 1 }" @click="createOrder"><span class="prepay-text"> 预付 </span></div>
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
 * 原站 chunk 反解（pages-market-submitBegBuy）：
 *  - getTradInfo(collection_id) → info（失败时默认 {floor_price:{}}：名称空/高价0/求购价0，实测）
 *  - payList = basePayList=[{name:汇付, aname:A, auth_key:huifu_account, pay_type:4, pay_way:huifu}]，able=userInfo[auth_key]，单选
 *  - totalPrice = price*number 保留2位（否则 --）；disable = 无选中钱包||无数量||无单价（按钮 opacity .5）
 *  - createOrder：price<地板价 → toast「当前求购价不得低于地板价的X%」；否则 createBegBuyOrder → 拉起支付
 */
const route = useRoute()
const router = useRouter()
const userStore = useUserStore()

const info = ref({ floor_price: {} })
const form = ref({ number: 1, price: null })
const payList = ref([])

const highPrice = computed(() => info.value.today_highest_price || info.value.max_price || info.value.min_price || 0)
const buyPrice = computed(() => info.value.max_buy_price || info.value.buy_price || 0)
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
  const res = await request('/api/market/trade/collectionDetail', { collection_id: route.query.id || null })
  if (res.code === 1 && res.data) info.value = res.data
}

function goMarket() {
  if (route.query.id) router.push({ path: '/pages/market/marketGoodsList', query: { id: route.query.id, isagreement: 1 } })
}
function minus() {
  if (form.value.number != 1) form.value.number = parseInt(form.value.number) - 1
}
function add() {
  form.value.number = parseInt(form.value.number) + 1
}
function changenum() {
  let e = String(form.value.value ?? form.value.number ?? '')
  if (!e || e < 1) form.value.number = 1
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
  const floor = Number(info.value.floor_price?.minprice || 0)
  if (floor && Number(form.value.price) < floor) {
    toast('当前求购价不得低于地板价的' + (info.value.floor_price.price_price_ratio || '') + '%')
    return
  }
  if (disable.value) return
  const p = payList.value.find((x) => x.check)
  const res = await request('/api/market/trade/createBegBuyOrder', { ...form.value, pay_way: p.pay_way, collection_id: route.query.id || null })
  if (res.code === 1) {
    // 原站此处拉起支付组件（order_type 11）；独立项目支付通道未接入（标注推断）
    toast('下单成功，支付通道暂未接入')
  }
}

initPayList()
getInfo()
</script>

<style scoped src="../../styles/pages/market-submitBegBuy.css"></style>
