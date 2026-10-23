<template>
  <div class="dl-page">
    <!-- 吸顶：导航栏 + Tab（原站 u-sticky + w-navbar fixed:!1 + w-tabs） -->
    <div class="sticky">
      <div class="navbar">
        <img class="navbar-back" src="/static/cw/back_btn.png" alt="返回" @click="$router.back()" />
        <span class="nav-title">龙晶明细</span>
      </div>
      <div class="tab">
        <div v-for="(t, i) in tabList" :key="t.name" class="tab-item" @click="changeTab(i)">
          <span class="tab-text" :class="{ 'tab-text-active': current === i }">{{ t.name }}</span>
          <div v-if="current === i" class="tab-line"></div>
        </div>
      </div>
    </div>

    <!-- 明细列表（原站 .content：calc(100%-64rpx) ml32 padding32 r20 bg-color-item） -->
    <div v-if="list.length" class="content">
      <div v-for="(item, idx) in list" :key="idx" class="item">
        <div class="item-left">
          <span class="memo">{{ item.memo }}</span>
          <span class="time">{{ formatTime(item.createtime) }}</span>
        </div>
        <div class="item-right">
          <img class="crystal-icon" :src="'/static/icon/' + crystalIcon(item.model) + '.png'" alt="" />
          <span class="crystal-num">{{ item.crystal }}</span>
        </div>
      </div>
    </div>

    <!-- 空态（原站 w-loading emptyType=search1 emptyTop=200：search1-empty.png + 暂无内容 26rpx #999） -->
    <div v-if="loadingType === 'empty'" class="empty" style="margin-top: 220px;">
      <img class="empty-img" src="/static/empty/search1-empty.png" alt="" />
      <span class="empty-text">暂无内容</span>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import { request } from '../../api/request'

/**
 * 原站 chunk 反解（pages-shop-detailList）：
 *  - 筛选组件 a59c：tabList 全部('' )/龙晶获取('1')/龙晶消耗('-1')；种类(黄水晶yellow/紫水晶purple/蓝水晶blue)、时间(两年月份)下拉（isshow 初始 false，无可观测触发入口，仅实现结构）
 *  - 列表：crystalDetail({page,...})，行 = memo(32rpx #555) + 时间(20rpx #999 yyyy.mm.dd hh:MM:ss) | 水晶图标(hsj/zsj/lsj by model) + 数值(40rpx #000)
 *  - 空态：emptyType=search1（search1-empty.png 265rpx + 暂无内容 26rpx #999），total==0 触发
 */
const tabList = [
  { name: '全部', cat: '' },
  { name: '龙晶获取', cat: '1' },
  { name: '龙晶消耗', cat: '-1' }
]
const current = ref(0)
const list = ref([])
const loadingType = ref('none')
const param = { page: 1, type: '', m: '', cat: '' }
let isNext = true

const ICON_MAP = { yellow: 'hsj', purple: 'zsj', blue: 'lsj', yellow_log: 'hsj', purple_log: 'zsj', blue_log: 'lsj' }
const crystalIcon = (model) => ICON_MAP[model] || 'hsj'

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
  const res = await request('/api/crystal_goods/crystalDetail', { ...param })
  if (res.code === 1 && res.data) {
    list.value.push(...(res.data.data || []))
    if (param.page < res.data.last_page) param.page++
    else isNext = false
    if (!(res.data.data || []).length) loadingType.value = 'empty'
    else loadingType.value = 'none'
  } else {
    loadingType.value = 'empty'
  }
}

function changeTab(i) {
  current.value = i
  param.page = 1
  param.type = ''
  param.m = ''
  param.cat = tabList[i].cat
  list.value = []
  isNext = true
  getList()
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
.dl-page {
  min-height: 100vh;
  background: #f4f6fa;
  padding-bottom: 55.3px;
}
/* ---- 吸顶区 ---- */
.sticky {
  position: sticky;
  top: 0;
  z-index: 5;
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
/* ---- Tab（inactiveStyle/activeStyle 28rpx #333，行高 80rpx；下划线 18×4 #393939） ---- */
.tab {
  width: 100%;
  position: relative;
  padding: 20px 60px;
  display: flex;
}
.tab-item {
  flex: 1;
  height: 80px;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  position: relative;
}
.tab-text {
  font-size: 28px;
  color: #333;
  font-family: var(--font-regular);
}
.tab-line {
  position: absolute;
  bottom: 14px;
  width: 18px;
  height: 4px;
  background: #393939;
  border-radius: 2px;
}
/* ---- 列表 ---- */
.content {
  width: calc(100% - 64px);
  margin-left: 32px;
  padding: 32px;
  border-radius: 20px;
  background: #f4f6fa;
  box-sizing: border-box;
}
.item {
  display: flex;
  align-items: center;
  justify-content: space-between;
}
.item-left {
  display: flex;
  flex-direction: column;
  min-width: 0;
}
.memo {
  font-size: 32px;
  color: #555;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}
.time {
  margin-top: 4px;
  font-size: 20px;
  color: #999;
}
.item-right {
  display: flex;
  align-items: center;
  flex-shrink: 0;
}
.crystal-icon {
  width: 40px;
  height: 40px;
  object-fit: contain;
}
.crystal-num {
  margin-left: 10px;
  font-size: 40px;
  color: #000;
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
