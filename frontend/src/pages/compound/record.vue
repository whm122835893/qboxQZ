<template>
  <div class="cr-page">
    <!-- 导航栏（原站自绘：88rpx 行，back 为右箭头图旋转180°；statusBarHeight H5 兜底 20px） -->
    <div style="padding-top: 20px;">
    <div class="navbar">
      <img class="nav-back" src="/static/qbox/common-arrow-right-black.png" alt="返回" @click="$router.back()" />
      <span class="nav-title"> 活动记录 </span>
      <div class="nav-placeholder"></div>
    </div>
    </div>

    <!-- 记录列表（padding 24/33/48，卡 684rpx r24 padding24） -->
    <div class="body">
      <div v-if="list.length" class="list">
        <div v-for="(item, idx) in list" :key="idx" class="row" @click="check(item)">
          <div class="row-img-box">
            <img v-if="item.result && item.result[0]" class="row-img" :src="resolveImg(item.result[0].main_image)" alt="" />
          </div>
          <div class="row-info">
            <span v-for="(r, ri) in item.result" :key="ri" class="row-name">{{ r.number > 1 ? r.name + ' x' + r.number : r.name }}</span>
            <span class="row-time"> 结束时间：{{ item.create_time }} </span>
          </div>
          <img class="row-arrow" src="/static/qbox/common-arrow-right-gray.png" alt="" />
        </div>
      </div>

      <!-- 空态（原站：min-height 480rpx 居中 common-empty.png 284rpx，纯图片无文字） -->
      <div v-if="loadingType !== 'loading' && !list.length" class="empty">
        <img class="empty-img" src="/static/qbox/common-empty.png" alt="" />
      </div>
    </div>

    <!-- 材料详情弹窗（原站 w-modal「材料详情」showButton:!1 close:!0） -->
    <div v-if="showDetails" class="mask" @click.self="showDetails = false">
      <div class="modal">
        <div class="modal-head">
          <span class="modal-title">材料详情</span>
          <span class="modal-close" @click="showDetails = false">×</span>
        </div>
        <div class="modal-body">
          <div v-for="(c, i) in consume" :key="i" class="consume-row">
            <img v-if="c.image" class="consume-img" :src="resolveImg(c.image)" alt="" />
            <div class="consume-info">
              <span class="consume-name">{{ c.name }}</span>
              <span class="consume-method"> {{ statusText[c.method] || '' }} </span>
            </div>
          </div>
        </div>
        <div class="modal-btn" @click="toPage"><span class="modal-btn-text"> 去查看我的数字藏品 </span></div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import { useRouter } from 'vue-router'
import { request, resolveImg } from '../../api/request'

/**
 * 原站 chunk 反解（pages-compound-record）：
 *  - recordList({page}) → 触底加载；空 → common-empty.png 284rpx 居中（图片自带「这里空空的！」文字）
 *  - 行点击 check(item) → 材料详情弹窗（consume 列表：图 42×42 r8 + 名称 + 销毁/保留/回收），底部「去查看我的数字藏品」
 */
const router = useRouter()
const list = ref([])
const consume = ref([])
const showDetails = ref(false)
const loadingType = ref('none')
const statusText = { 10: '销毁', 20: '保留', 30: '回收' }
let page = 1
let isNext = true

async function getList() {
  if (!isNext || loadingType.value === 'loading') return
  loadingType.value = 'loading'
  const res = await request('/api/synthesis/recordList', { page })
  if (res.code === 1 && res.data) {
    list.value.push(...(res.data.data || []))
    if (page < res.data.last_page) page++
    else isNext = false
    loadingType.value = (res.data.data || []).length ? 'none' : 'empty'
  } else {
    loadingType.value = 'empty'
  }
}

function check(item) {
  consume.value = item.consume || []
  showDetails.value = true
}

function toPage() {
  showDetails.value = false
  router.push('/pages/myCollection/myCollection')
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
.cr-page {
  min-height: 100vh;
  background: #f7f7f7;
}
/* ---- 导航栏（88rpx 行，padding 0 36rpx） ---- */
.navbar {
  width: 100%;
  height: 88px;
  padding: 0 36px;
  box-sizing: border-box;
  display: flex;
  align-items: center;
  justify-content: space-between;
}
.nav-back {
  width: 24px;
  height: 24px;
  transform: rotate(180deg);
  object-fit: contain;
}
.nav-title {
  font-size: 32px;
  color: #111;
  font-family: var(--font-bold);
}
.nav-placeholder {
  width: 24px;
  height: 24px;
}
/* ---- 列表（padding 24/33/48rpx，列表 684rpx，行 gap-pf-y-20=40rpx） ---- */
.body {
  width: 100%;
  padding: 24px 33px 48px;
  box-sizing: border-box;
  display: flex;
  flex-direction: column;
  align-items: center;
}
.list {
  width: 684px;
  display: flex;
  flex-direction: column;
  gap: 40px;
}
.row {
  width: 100%;
  background: #fff;
  border-radius: 24px;
  padding: 24px;
  box-sizing: border-box;
  display: flex;
  align-items: center;
}
.row-img-box {
  width: 120px;
  height: 120px;
  border-radius: 16px;
  overflow: hidden;
  background: #0e0f12;
  flex-shrink: 0;
  display: flex;
  align-items: center;
  justify-content: center;
}
.row-img {
  width: 120px;
  height: 120px;
  border-radius: 8px;
  object-fit: cover;
}
.row-info {
  flex: 1;
  min-width: 0;
  display: flex;
  flex-direction: column;
  gap: 16px;
  margin: 0 16px;
}
.row-name {
  font-size: 28px;
  color: #111;
  font-family: var(--font-bold);
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}
.row-time {
  font-size: 22px;
  color: #8c8c8c;
}
.row-arrow {
  width: 16px;
  height: 24px;
  flex-shrink: 0;
  object-fit: contain;
}
/* ---- 空态（480rpx 居中，common-empty.png 284rpx） ---- */
.empty {
  width: 100%;
  min-height: 480px;
  display: flex;
  align-items: center;
  justify-content: center;
}
.empty-img {
  width: 284px;
  height: 284px;
  object-fit: contain;
}
/* ---- 材料详情弹窗（w-modal 标准 652rpx 宽） ---- */
.mask {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.6);
  z-index: 999;
  display: flex;
  align-items: center;
  justify-content: center;
}
.modal {
  width: 652px;
  background: #fff;
  border-radius: 24px;
  padding: 44px 36px 36px;
  box-sizing: border-box;
  display: flex;
  flex-direction: column;
}
.modal-head {
  display: flex;
  align-items: center;
  justify-content: center;
  position: relative;
}
.modal-title {
  font-size: 32px;
  color: #000;
  font-family: var(--font-medium);
  font-weight: 600;
}
.modal-close {
  position: absolute;
  right: 0;
  top: -6px;
  font-size: 40px;
  line-height: 32px;
  color: #999;
}
.modal-body {
  width: 100%;
  max-height: 40vh;
  overflow-y: auto;
  display: flex;
  flex-direction: column;
  gap: 16px;
  margin-top: 16px;
}
.consume-row {
  display: flex;
  align-items: center;
  gap: 16px;
}
.consume-img {
  width: 42px;
  height: 42px;
  border-radius: 8px;
  object-fit: cover;
}
.consume-info {
  flex: 1;
  min-width: 0;
  display: flex;
  flex-direction: column;
  gap: 6px;
}
.consume-name {
  font-size: 28px;
  color: #111;
}
.consume-method {
  font-size: 22px;
  color: #8c8c8c;
}
.modal-btn {
  width: 100%;
  height: 80px;
  margin-top: 40px;
  border-radius: 19px;
  background: #0018ff;
  display: flex;
  align-items: center;
  justify-content: center;
}
.modal-btn-text {
  font-size: 30px;
  color: #fff;
  font-family: var(--font-medium);
}
</style>
