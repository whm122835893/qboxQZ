<template>
  <!-- 寄售详情（源站 pages/market/detail）
       源站 DOM 实测（750 视口，2026-10-25，登录态，市场藏品列表点击挂单行进入 ?id=挂单id）：
       - 导航栏「寄售详情」居中
       - 主图 436×436 黑色圆角底卡（radius 64），居中
       - 名称卡（半透明白 rgba(255,255,255,.83)，radius 32，padding 32/36）：
         左：名称 32px #2d2d2d + 胶囊行（限量N份 蓝底#0018ff / 流通N份 黑底#111，radius 12 padding 0/16，文字 22px #fff fw300）
         右：「售价」28px #9a9a9a + 「¥N」28px #2d2d2d（右对齐）
       - 信息卡（白底 radius 32 padding 16/32）：
         藏品编号 #xxx / 所属专辑 / 合约地址 xxxxxx**** / 认证标识
         label 26px fw600 #181717，value 26px #000 右对齐
       - 创作者卡（白底 radius 32 padding 32/28）：创作者 + 头像 122×122 radius 19 + 名称
       - 购买须知卡（白底 radius 32 padding 32/28）：标题 38px + 正文 22px fw300 #696969
       - 底部固定条：按钮 678×88 radius 44，在售「立即购买」蓝底#0018ff / 锁定中「锁定中」灰底#cacdd3 -->
  <div class="md-page">
    <!-- 导航栏 -->
    <div class="navbar">
      <img class="navbar-back" src="/static/cw/back_btn.png" alt="返回" @click="goBack" />
      <span class="nav-title">寄售详情</span>
    </div>

    <div v-if="detail" class="body">
      <!-- 主图 -->
      <div class="main-wrap">
        <img class="main-img" :src="detail.cover" :alt="detail.goods_name" />
      </div>

      <!-- 名称卡 -->
      <div class="name-card">
        <div class="name-left">
          <span class="g-name">{{ detail.goods_name }}</span>
          <div class="capsule-row">
            <span class="num-capsule num-capsule-blue">限量{{ detail.total_num }}份</span>
            <span class="num-capsule num-capsule-black">流通{{ detail.flux }}份</span>
          </div>
        </div>
        <div class="price-col">
          <span class="price-label">售价</span>
          <span class="price-value">¥{{ detail.price }}</span>
        </div>
      </div>

      <!-- 信息卡组（创作者卡 / 购买须知卡） -->
      <DetailInfoCards
        :collection-code="detail.collection_code"
        :series-name="detail.series_name"
        :contract="detail.contract"
        :cert-id="detail.cert_id"
        :publisher-logo="detail.publisher_logo"
        :publisher-name="detail.publisher_name"
        :notice-text="noticeText"
      />
    </div>

    <!-- 加载态（源站无有效 id 时停留在加载态，不重定向） -->
    <div v-else class="loading-mask">
      <div class="loading-wrap">
        <div class="loading-icon"><span class="spinner"></span></div>
        <span class="loading-text">加载中...</span>
      </div>
    </div>

    <!-- 底部操作条 -->
    <div v-if="detail" class="bottom-bar">
      <div class="bottom-btn" :class="{ locked: detail.status === 4 }" @click="onBuy">
        {{ detail.status === 4 ? '锁定中' : '立即购买' }}
      </div>
    </div>

    <!-- 订单确认弹窗（源站实测：点击「立即购买」弹出底部半屏弹窗） -->
    <OrderPaySheet
      :visible="showPayPopup"
      :cover="detail ? detail.cover : ''"
      :goods-name="detail ? detail.goods_name : ''"
      :flux="detail ? detail.flux : 0"
      :total-num="detail ? detail.total_num : 0"
      :amount="payAmount"
      badge="汇"
      method-name="汇付"
      @close="closePayPopup"
      @submit="onSubmitPay"
    />
  </div>
</template>

<script setup>
/**
 * 源站实测（2026-10-25 登录态，Playwright 抓包 + DOM 树还原）：
 *  入口：/pages/market/marketGoodsList?id={藏品id} → 点击挂单行 → /pages/market/detail?id={挂单id}
 *  接口：GET /api/market/market/MarketDetail/ids/{挂单id}
 *        → { id, sale_user_id, goods_id, price, status, goods_name, goods_cover_image,
 *            series_name, income_type, order_sn,
 *            product:{ flux, total_num, publisher:{ name, company_logo } },
 *            user_collection:{ collection_code, user_hash_text, rz_code } }
 *  status 语义：4 = 锁定中（底部灰底「锁定中」），其余 = 可购买（蓝底「立即购买」）
 *  字段映射（源站实测校正）：
 *    合约地址 ← user_collection.user_hash_text（源站已自带 xxxxxx**** 形式）
 *    认证标识 ← user_collection.rz_code
 *    藏品编号 ← user_collection.collection_code
 *  无有效 id / 已售出（接口 code=0）→ 页面停留「加载中...」
 */
import { ref, computed, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { request } from '../../api/request'
import { toast } from '../../utils/toast'
import OrderPaySheet from '../../components/market/OrderPaySheet.vue'
import DetailInfoCards from '../../components/market/DetailInfoCards.vue'

const route = useRoute()
const router = useRouter()

const detail = ref(null)

/** 购买须知正文（源站原文，随产品 purchase_information 动态变化，此处取接口值，缺省用平台默认文案） */
const DEFAULT_NOTICE =
  '数字藏品为虚拟数字商品，而非实物，仅限实名认证为年满18周岁，并小于60周岁的中国大陆用户购买。' +
  '数字藏品的版权由发行方或原创者拥有，除另行取得版权拥有者书面同意外，用户不得将数字藏品用于任何商业用途。' +
  '本商品一经售出，不支持退换请勿对数字藏品进行炒作、场外交易、欺诈，或以任何其他非法方式进行使用。'

const noticeText = ref(DEFAULT_NOTICE)

async function loadDetail() {
  const id = route.query.id
  if (!id) return
  const res = await request('/api/market/market/MarketDetail/ids/' + id, {})
  const d = res && res.code === 1 ? res.data : null
  if (!d) return
  const p = d.product || {}
  const uc = d.user_collection || {}
  const publisher = p.publisher || {}
  const rawNotice = String(p.purchase_information || '').trim()
  if (rawNotice) noticeText.value = rawNotice
  detail.value = {
    id: d.id,
    goods_id: d.goods_id,
    goods_name: d.goods_name || '',
    cover: d.goods_cover_image || p.main_image || p.listimg || '',
    price: d.price ?? 0,
    status: d.status,
    series_name: d.series_name || '',
    total_num: p.total_num ?? d.total_num ?? 0,
    flux: p.flux ?? 0,
    collection_code: uc.collection_code ?? p.collection_code ?? d.collection_code ?? '',
    // 合约地址：源站直接给 user_hash_text（形如 2Fl5Ia****）
    contract: uc.user_hash_text || '--',
    // 认证标识：源站取该用户藏品的 rz_code
    cert_id: uc.rz_code ?? p.cert_id ?? '--',
    publisher_name: publisher.name || '',
    publisher_logo: publisher.company_logo || '',
    order_sn: d.order_sn || '',
    income_type: d.income_type || 'huifu'
  }
}

function goBack() {
  history.length > 1 ? router.back() : router.replace('/pages/market/market')
}

/* ---------------- 订单确认弹窗（源站实测链路） ----------------
 * 源站点击「立即购买」不跳页：弹出底部半屏订单确认弹窗 → 点「立即支付」→ 拉起汇付收银台。
 * 独立项目未接支付通道，故「立即支付」改为创建本地订单（/api/market/market/createOrder）
 * 并跳转「我的订单」列表，使下单 → 支付 → 订单列表形成可走通闭环。
 */
const showPayPopup = ref(false)

/** 支付渠道：源站 income_type=huifu → 徽标「汇」+ 名称「汇付」 */
const payBadge = '汇'
const payMethodName = '汇付'

const payAmount = computed(() => {
  const p = Number(detail.value?.price)
  return Number.isFinite(p) ? p.toFixed(2) : '0.00'
})

function onBuy() {
  // 锁定中的挂单（status=4）不可下单，源站此时按钮为灰底不可点
  if (detail.value?.status === 4) return
  showPayPopup.value = true
}

function closePayPopup() {
  showPayPopup.value = false
}

async function onSubmitPay() {
  const d = detail.value
  if (!d) return
  const res = await request('/api/market/market/createOrder', {
    market_order_id: d.id,
    goods_id: d.goods_id,
    goods_name: d.goods_name,
    goods_cover_image: d.cover,
    series_name: d.series_name,
    collection_code: d.collection_code,
    price: d.price,
    num: 1
  })
  if (res.code !== 1) {
    toast(res.msg || '下单失败')
    return
  }
  showPayPopup.value = false
  toast('下单成功')
  router.replace('/pages/order/list')
}

onMounted(loadDetail)
</script>

<style scoped src="../../styles/pages/market-detail.css"></style>
