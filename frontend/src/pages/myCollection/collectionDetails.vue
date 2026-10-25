<template>
  <!-- 持仓藏品详情（pages/myCollection/collectionDetails）：
       2026-10-24 源站实测（持仓弹窗点击明细行 → ?id=180409，「QBOX数字徽章」页）：
       导航标题=藏品名；居中 414 方图白框阴影；名称卡(名称 Bold + 蓝色"限量N份"胶囊 + 右侧价格)；
       信息卡三行(藏品持有者/藏品编号/认证时间)；       创作者/藏品介绍/购买须知卡(QBOX 水印)；
       底部固定按钮按链上状态切换：上链中(灰底 rgb(202,205,211) 678×92 圆角19) / 转赠(蓝底 #0018ff) -->
  <div class="cd-page">
    <!-- 导航栏 -->
    <div class="navbar">
      <img class="navbar-back" src="/static/cw/back_btn.png" alt="返回" @click="$router.back()" />
      <span class="nav-title">{{ navTitle }}</span>
    </div>

    <div class="body">
      <!-- 主图（居中方卡，白框 + 阴影） -->
      <div class="main-wrap">
        <img class="main-img" :src="goods.cover" :alt="goods.goods_name" />
      </div>

      <!-- 名称卡（名称 Bold + 限量胶囊；右侧价格） -->
      <div class="name-card">
        <div class="name-left">
          <span class="g-name">{{ goods.goods_name }}</span>
          <span class="num-capsule">限量{{ goods.limit_num }}份</span>
        </div>
        <span class="price"><span class="price-cny">¥</span>{{ goods.price }}</span>
      </div>

      <!-- 信息卡（两行：持有者/认证时间，label 27.2 #111 + 值 25.4 #111；
           源站登录态实测无「藏品编号」行） -->
      <div class="info-card">
        <div class="info-row">
          <span class="info-label">藏品持有者</span>
          <span class="info-value">{{ goods.owner }}</span>
        </div>
        <div class="info-row">
          <span class="info-label">认证时间</span>
          <span class="info-value">{{ goods.cert_time }}</span>
        </div>
      </div>

      <!-- 创作者卡（QBOX 水印 + 蓝色下划线标题 + 居中创作者名） -->
      <div class="creator-card">
        <span class="watermark">QBOX</span>
        <span class="creator-title">创作者</span>
        <span class="creator-name">{{ goods.creator }}</span>
      </div>

      <!-- 藏品介绍卡（ALLCOMEQBOX 水印 + 蓝色下划线标题） -->
      <div class="creator-card intro-pad">
        <span class="watermark watermark-wide">ALLCOMEQBOX ALLCOMEQBOX</span>
        <span class="creator-title">藏品介绍</span>
        <p class="intro-text">{{ goods.intro }}</p>
      </div>

      <!-- 购买须知卡（源站实测：无 QBOX 水印，直接是标题 + 说明文字） -->
      <div class="creator-card">
        <span class="creator-title">购买须知</span>
        <p class="notice-text">数字藏品为虚拟数字商品，而非实物，仅限实名认证为年满18周岁，并小于60周岁的中国大陆用户购买。数字藏品的版权由发行方或原创者拥有，除另行取得版权拥有者书面同意外，用户不得将数字藏品用于任何商业用途。本商品一经售出，不支持退换请勿对数字藏品进行炒作、场外交易、欺诈，或以任何其他非法方式进行使用。</p>
      </div>
    </div>

    <!-- 底部固定栏（源站实测：不透明底栏 bg=#f7f7f7 与页面同色，滚动文字不会从按钮下穿过）
         按钮文案按链上状态切换：
           上链中 → 灰底 rgb(202,205,211) 圆角 19px，678×92，不可点
           可转赠 → 蓝底 #0018ff 圆角 18.1px，678×92 -->
    <div class="bottom-bar">
      <div v-if="goods.chain_status === 'pending'" class="bottom-btn bottom-btn-disabled">上链中</div>
      <div v-else class="bottom-btn" @click="onRegift">转赠</div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { request } from '../../api/request'

const route = useRoute()
const router = useRouter()

/**
 * 详情数据来源：/api/user_collection/user_collection/detail/ids/{id}
 * 源站实测（登录态、无有效 id）接口返回 code:0 / data:null，页面降级渲染占位：
 *   名称「藏品名称」+「限量0份」+「¥0.00」+ 持有者 + 认证时间 + 创作者 QBOX + 介绍 QBOX
 * 有有效 id 时按接口字段正常渲染。
 */
const goods = ref({
  id: route.query.id || null,
  goods_name: '藏品名称',
  cover: '/uploads/20261013/891305447d1a31e997fd7a0e078488a5.png',
  limit_num: 0,
  price: '0.00',
  owner: 'Q9GSWCuI2',
  collection_no: route.query.no || '',
  cert_time: '',
  creator: 'QBOX',
  intro: 'QBOX',
  // 源站实测（2026-10-25 登录态）：该持仓藏品底部按钮为灰底「上链中」→ 链上状态 pending
  chain_status: 'pending'
})

// 源站实测：无有效 id 时导航栏无标题文本（页面仅名称卡显示「藏品名称」）
const navTitle = ref('')

const EMPTY_GOODS = { goods_name: '藏品名称', limit_num: 0, price: '0.00', creator: 'QBOX', intro: 'QBOX' }

function fmtDate(ts) {
  if (!ts) return ''
  const d = new Date(Number(ts) * 1000)
  if (Number.isNaN(d.getTime())) return ''
  const p = (n) => String(n).padStart(2, '0')
  return `${d.getFullYear()}.${p(d.getMonth() + 1)}.${p(d.getDate())}`
}

// 源站实测：认证时间降级值为当天日期（2026.10.25 → 2026.10.26 随日期滚动）
function todayText() {
  const d = new Date()
  const p = (n) => String(n).padStart(2, '0')
  return `${d.getFullYear()}.${p(d.getMonth() + 1)}.${p(d.getDate())}`
}

async function loadDetail() {
  const id = route.query.id
  goods.value.cert_time = todayText()
  if (!id) return
  const res = await request('/api/user_collection/user_collection/detail/ids/' + id, {})
  const d = res && res.code === 1 ? res.data : null
  if (!d) return
  navTitle.value = d.goods_name || ''
  goods.value = {
    ...goods.value,
    id,
    goods_name: d.goods_name || EMPTY_GOODS.goods_name,
    cover: d.cover || d.listimg || goods.value.cover,
    limit_num: d.issue_num ?? d.limit_num ?? 0,
    price: d.price ?? '0.00',
    owner: d.owner || d.nickname || goods.value.owner,
    cert_time: fmtDate(d.cert_time || d.create_time) || goods.value.cert_time,
    creator: d.creator || d.creator_name || EMPTY_GOODS.creator,
    intro: d.intro || d.description || EMPTY_GOODS.intro,
    chain_status: d.chain_status || d.chain_status_desc || 'pending'
  }
}

onMounted(loadDetail)

// 源站转赠链路（pages/regift/regift），支付/上链均 mock（规则9）
function onRegift() {
  router.push('/pages/regift/regift?id=' + (goods.value.id || ''))
}
</script>

<style scoped src="../../styles/pages/myCollection-collectionDetails.css"></style>
