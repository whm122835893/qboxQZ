<template>
  <div class="rec-page">
    <!-- 导航栏 -->
    <div class="navbar">
      <img class="navbar-back" src="/static/cw/back_btn.png" alt="返回" @click="$router.back()" />
      <span class="nav-title">兑换记录</span>
    </div>

    <div class="wrap">
      <!-- 记录卡（原站 .item 200rpx r16 white padding18 mb24；点击 → order/defaultDetail {orderType:4}） -->
      <div v-for="(item, idx) in list" :key="idx" class="item" @click="goDetail(item)">
        <img v-if="item.list_image" class="item-img" :src="resolveImg(item.list_image)" alt="" />
        <div class="right">
          <div class="info">
            <span class="name">{{ item.goods_name }}</span>
            <div class="crystal-row">
              <img class="crystal-icon" :src="'/static/icon/' + crystalIcon(item.crystal_type) + '.png'" alt="" />
              <span class="crystal-num">{{ item.crystal_number }}</span>
            </div>
            <span class="time">{{ formatTime(item.create_time) }}</span>
          </div>
          <!-- 原站 right3.png 图标（原站该图标 404，实际不可见，如实省略） -->
        </div>
      </div>

      <!-- 空态（search1-empty.png + 暂无内容） -->
      <div v-if="loadingType === 'empty'" class="empty" style="margin-top: 227px;">
        <img class="empty-img" src="/static/empty/search1-empty.png" alt="" />
        <span class="empty-text">暂无内容</span>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import { useRouter } from 'vue-router'
import { request, resolveImg } from '../../api/request'

/**
 * 原站 chunk 反解（pages-shop-record）：
 *  - exchangeRecord({page:1}) → 空实录；行 = list_image 83×83 r8 + goods_name(32rpx 500) + 水晶图标+数值(32rpx) + 时间(20rpx #ababab)
 *  - 点击行 → /pages/order/defaultDetail {id: order_id, orderType: 4}
 *  - 空态 emptyType=search1 emptyTop=200
 */
const router = useRouter()
const list = ref([])
const loadingType = ref('none')
const param = { page: 1 }
let isNext = true

const ICON_MAP = { yellow: 'hsj', purple: 'zsj', blue: 'lsj', yellow_log: 'hsj', purple_log: 'zsj', blue_log: 'lsj' }
const crystalIcon = (t) => ICON_MAP[t] || 'hsj'

function formatTime(ts) {
  if (!ts) return ''
  const ms = ts > 1e12 ? ts : ts * 1000
  const d = new Date(ms)
  const p = (n) => String(n).padStart(2, '0')
  return `${d.getFullYear()}.${p(d.getMonth() + 1)}.${p(d.getDate())} ${p(d.getHours())}:${p(d.getMinutes())}:${p(d.getSeconds())}`
}

async function getList() {
  if (!isNext || loadingType.value === 'loading') return
  loadingType.value = 'loading'
  const res = await request('/api/crystal_goods/exchangeRecord', { ...param })
  if (res.code === 1 && res.data) {
    list.value.push(...(res.data.data || []))
    if (param.page < res.data.last_page) param.page++
    else isNext = false
    loadingType.value = (res.data.data || []).length ? 'none' : 'empty'
  } else {
    loadingType.value = 'empty'
  }
}

function goDetail(item) {
  router.push({ path: '/pages/order/defaultDetail', query: { id: item.order_id, orderType: 4 } })
}

function onScroll() {
  if (window.innerHeight + window.scrollY >= document.body.scrollHeight - 40) getList()
}

onMounted(() => {
  getList()
  window.addEventListener('scroll', onScroll)
})
onUnmounted(() => window.removeEventListener('scroll', onScroll))
</script>

<style scoped>
.rec-page {
  min-height: 100vh;
  background: #f4f6fa;
}
.navbar {
  position: relative;
  width: 100%;
  height: 79.7px;
  display: flex;
  align-items: center;
  justify-content: center;
}
.navbar-back {
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
  font-family: var(--font-bold);
}
/* pa-16 = 32rpx */
.wrap {
  padding: 17.7px;
}
.item {
  width: 100%;
  height: 200px;
  border-radius: 16px;
  background: #fff;
  padding: 18px;
  margin-bottom: 24px;
  box-sizing: border-box;
}
.item-img {
  width: 83px;
  height: 83px;
  border-radius: 8px;
  object-fit: cover;
}
.right {
  display: flex;
  align-items: center;
  justify-content: space-between;
}
.info {
  display: flex;
  flex-direction: column;
  min-width: 0;
}
.name {
  font-size: 32px;
  font-weight: 500;
  color: #000;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}
.crystal-row {
  display: flex;
  align-items: center;
  margin-top: 12px;
}
.crystal-icon {
  width: 40px;
  height: 40px;
  object-fit: contain;
}
.crystal-num {
  margin-left: 16px;
  font-size: 32px;
  color: #1b1d20;
}
.time {
  margin-top: 8px;
  font-size: 20px;
  color: #ababab;
}
/* ---- 空态 ---- */
.empty {
  display: flex;
  flex-direction: column;
  align-items: center;
}
.empty-img {
  width: 265px;
  object-fit: contain;
}
.empty-text {
  margin-top: 6px;
  font-size: 26px;
  color: #999;
  font-family: var(--font-regular);
}
</style>
