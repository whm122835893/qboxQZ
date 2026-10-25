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

<style scoped src="../../styles/pages/compound-record.css"></style>
