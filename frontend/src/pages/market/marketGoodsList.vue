<template>
  <div class="mg-page">
    <!-- 导航栏（藏品名称 17px Bold #111 + 右上收藏心形） -->
    <div class="navbar">
      <img class="navbar-back" src="/static/cw/back_btn.png" alt="返回" @click="$router.back()" />
      <span class="nav-title">{{ goods.name || '藏品名称' }}</span>
      <img class="nav-fav" src="/static/qbox/detail-unfavorited.png" alt="收藏" />
    </div>

    <div class="body">
      <!-- 主图（源站实测 2026-10-25 15:11：藏品封面，圆角 28，居中展示） -->
      <img v-if="goods.main_image" class="main-img" :src="goods.main_image" :alt="goods.name" />
      <div v-else class="main-img main-img-empty"></div>

      <!-- 名称卡（源站实测 2026-10-25：卡片 rect=[33,640,684,177] bg #fff radius 32 padding 32/36，
           flex space-between align-center；左列 = 名称 32px #111 + 胶囊行 gap 12；
           右列 = 地板价 19px + ¥ 22px / -- 38px，align-items flex-end）
           胶囊：103×40 bg #0018ff radius 12 padding 0/16，文字 20px #fff -->
      <div class="name-card">
        <div class="name-left">
          <span class="g-name">{{ goods.name || '藏品名称' }}</span>
          <div class="num-row">
            <span class="num-capsule">限量{{ goods.total_num || 0 }}份</span>
            <span class="num-capsule num-capsule-black">流通{{ goods.flux || 0 }}份</span>
          </div>
        </div>
        <div class="floor-col">
          <span class="floor-label">地板价</span>
          <span class="floor-price"><span class="floor-cny">¥</span>{{ goods.min_price ?? '--' }}</span>
        </div>
      </div>

      <!-- Tabs（市场价格 #111 / 相关公告 / 成交记录 / 成交趋势 #8c8c8c，15px） -->
      <div class="m-tabs">
        <span
          v-for="(t, i) in mTabs"
          :key="t"
          class="m-tab"
          :class="{ 'm-tab-active': current === i }"
          @click="current = i"
        >{{ t }}</span>
      </div>

      <!-- 筛选行（售罄/求购/委托 小 tab + 价格/编号 排序箭头 8×13） -->
      <div class="filter-row">
        <div class="mini-tabs">
          <span class="mini-tab mini-tab-active">寄售</span>
          <span class="mini-tab">求购</span>
          <span class="mini-tab">委托</span>
        </div>
        <div class="sort-col">
          <span class="sort-label">价格</span>
          <img class="sort-icon" src="/static/qbox/market-sort.png" alt="" />
        </div>
        <div class="sort-col">
          <span class="sort-label">编号</span>
          <img class="sort-icon" src="/static/qbox/market-sort-asc.png" alt="" />
        </div>
      </div>

      <!-- 挂单列表（源站实测：684×124 白底卡片，点击行进入寄售详情） -->
      <SellOrderList :rows="sellList" :goods-name="goods.name" @select="goSellDetail" />
    </div>

    <!-- 底部快捷下单（源站实测 2026-10-25 11:58：
         固定条 [0,1214,750,120] 内按钮 [36,1230,678,88]，点击后弹出底部半屏弹窗。
         注：源站在无挂单数据（未带 id）时该按钮同样渲染，故此处不做条件渲染） -->
    <div class="quick-bar">
      <div class="quick-btn" @click="onQuickOrder">快捷下单</div>
    </div>

    <!-- 快捷下单弹窗（源站实测：底部半屏弹窗） -->
    <QuickOrderSheet
      :visible="showQuickPopup"
      :cover="goodsCover"
      :flux="goodsFlux"
      :total-num="goodsTotal"
      badge="A"
      method-name="A钱包"
      @close="showQuickPopup = false"
      @confirm="onQuickConfirm"
    />
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { request } from '../../api/request'
import { toast } from '../../utils/toast'
import SellOrderList from '../../components/market/SellOrderList.vue'
import QuickOrderSheet from '../../components/market/QuickOrderSheet.vue'

/**
 * 原站 DOM 实测（2026-10-23，pages/market/marketGoodsList）：
 *  - 标题 17px Bold #111 + 右上收藏心形；主图黑占位；
 *  - 名称卡：藏品名称 17px Bold + 地板价 10px（￥--）+ 限量/流通胶囊；
 *  - tabs 15px：市场价格(#111 Bold)/相关公告/成交记录/成交趋势(#8c8c8c)；
 *  - 筛选：寄售/求购/委托 + 价格/编号排序箭头（market-sort 8×13）；
 *  - 挂单行（带 id 进入时实测）：「锁单中」+ #564 + ¥315 + 支付钱包：A钱包；
 *  - 空态 common-empty 156px（y586）；底部「快捷下单」15px 白字 #0018ff（y857）
 */
const mTabs = ['市场价格', '相关公告', '成交记录', '成交趋势']
const current = ref(0)

const route = useRoute()
const router = useRouter()
const sellList = ref([])
/* 藏品详情（源站实测：进入本页时请求 marketGoodsDetail，填充导航标题/主图/名称卡） */
const goods = ref({})

/* 快捷下单弹窗（源站实测：封面/规格取自当前藏品的商品详情） */
const showQuickPopup = ref(false)
const goodsCover = ref('')
const goodsFlux = ref(0)
const goodsTotal = ref(0)

async function loadSellList() {
  const id = route.query.id
  if (!id) return
  const res = await request('/api/market/market/getMarketGoodsListByGoodsId', {
    page: 1, sort: 'price', type: 2, id, order: 'asc'
  })
  sellList.value = res.code === 1 && Array.isArray(res.data?.list) ? res.data.list : []
  // 快捷下单弹窗的封面与发行/流通量取自挂单所属藏品
  const first = sellList.value[0]
  if (first) {
    goodsTotal.value = first.total_num || 0
    const dRes = await request('/api/market/market/MarketDetail/ids/' + first.id, {})
    if (dRes.code === 1 && dRes.data) {
      goodsCover.value = dRes.data.goods_cover_image || dRes.data.product?.main_image || ''
      goodsFlux.value = dRes.data.product?.flux ?? 0
    }
  }
}

/**
 * 加载藏品详情（源站实测 2026-10-25 15:11：进入本页即请求 marketGoodsDetail）
 * 返回字段：name / flux 流通 / total_num 限量 / min_price 地板价 / main_image 主图 / listimg 封面
 */
async function loadGoodsDetail() {
  const res = await request('/api/market/market/marketGoodsDetail', {
    id: route.query.id,
    goods_type: route.query.goodsType || 2
  })
  if (res.code === 1 && res.data) {
    goods.value = res.data
    goodsCover.value = res.data.main_image || res.data.listimg || ''
    goodsFlux.value = res.data.flux ?? 0
    goodsTotal.value = res.data.total_num ?? 0
  }
}

/** 快捷下单点击 → 打开弹窗（源站实测：无论有无挂单均弹出该弹窗） */
function onQuickOrder() {
  showQuickPopup.value = true
}

/** 快捷下单确认 → 进入该藏品第一件在售挂单的寄售详情（源站确认后进入锁单/购买流程） */
function onQuickConfirm() {
  showQuickPopup.value = false
  const target = sellList.value.find((r) => r.status !== 4) || sellList.value[0]
  if (target) router.push('/pages/market/detail?id=' + target.id)
  else toast('暂无可下单的挂单')
}

// 源站实测：点击挂单行 → /pages/market/detail?id={挂单id}（寄售详情页）
function goSellDetail(row) {
  router.push('/pages/market/detail?id=' + row.id)
}

onMounted(() => {
  loadGoodsDetail()
  loadSellList()
})
</script>

<style scoped src="../../styles/pages/market-marketGoodsList.css"></style>
