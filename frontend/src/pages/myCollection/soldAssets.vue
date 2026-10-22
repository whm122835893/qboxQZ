<template>
  <div class="sold-page">
    <!-- 导航栏（原站实测：白底 h79.7，标题 17px/500 #111，返回 16.3×29） -->
    <div class="navbar">
      <img class="navbar-back" src="/static/cw/back_btn.png" alt="" @click="$router.back()" />
      <span class="nav-title">已售出资产</span>
    </div>

    <!-- 列表区（原站 mescroll：padding 7.2 23.6 79.7；空态黑猫 282.6 图片自带文案；已售出接口 antMycollection types=3） -->
    <div class="list-wrap">
      <div v-if="!loading && list.length === 0" class="list-empty">
        <img class="list-empty-img" src="/static/qbox/common-empty.png" alt="" />
      </div>
      <!-- 已售卡片（原站账号无已售资产，卡片结构不可观测，按接口字段语义实现并标注推断） -->
      <div v-for="(g, i) in list" :key="i" class="sold-card">
        <img class="sold-img" :src="g.image || g.goods_image || '/static/qbox/common-empty.png'" alt="" />
        <div class="sold-info">
          <span class="sold-name">{{ g.name || g.goods_name }}</span>
          <span class="sold-price">¥{{ g.price || g.sale_price }}</span>
        </div>
      </div>
    </div>

    <!-- 回到顶部（原站 mescroll-totop：right 19.9 bottom 119.6 w70.7） -->
    <img
      v-show="showTop"
      class="totop"
      src="/static/mescroll-totop.png"
      alt=""
      @click="toTop"
    />
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import { request } from '../../api/request'

const loading = ref(true)
const list = ref([])
const showTop = ref(false)

// 原站抓包实测：已售出资产调用 antMycollection {types:3, page:1}（types=3 即已售出）
async function load() {
  loading.value = true
  const res = await request('/api/user_collection/user_collection/antMycollection', { types: 3, page: 1 })
  if (res.code === 1) list.value = res.data?.data || []
  loading.value = false
}

function onScroll() {
  showTop.value = window.scrollY > 300
}
function toTop() {
  window.scrollTo({ top: 0, behavior: 'smooth' })
}

onMounted(() => {
  load()
  window.addEventListener('scroll', onScroll, { passive: true })
})
onUnmounted(() => window.removeEventListener('scroll', onScroll))
</script>

<style scoped>
.sold-page {
  min-height: 100vh;
  background: #fff;
}
/* 导航栏（白底，与其他白底子页一致） */
.navbar {
  position: relative;
  height: 79.7px;
  background: #fff;
  display: flex;
  align-items: center;
  padding: 0 23.6px;
}
.navbar-back {
  width: 16.3px;
  height: 29px;
  object-fit: contain;
}
.nav-title {
  position: absolute;
  left: 50%;
  top: 50%;
  transform: translate(-50%, -50%);
  width: 398.6px;
  text-align: center;
  font-size: 30.8px;
  font-weight: 500;
  color: #111;
  font-family: var(--font-medium);
  white-space: nowrap;
}
/* 列表区（原站实测 padding 7.2 23.6 79.7） */
.list-wrap {
  padding: 7.2px 23.6px 79.7px;
  box-sizing: border-box;
}
.list-empty {
  width: 100%;
  min-height: 478.2px;
  display: flex;
  align-items: center;
  justify-content: center;
}
.list-empty-img {
  width: 282.6px;
  height: 282.6px;
  /* 原站 uni-image 实测：156×156 容器 + background-size:contain → 图等比缩放为 106.7×156 居中显示；
     object-fit:contain 与其完全等价（此前 fill 压扁 / height:auto 放大 均有偏差，已按实测修正） */
  object-fit: contain;
}
/* 已售卡片（推断结构，原站不可观测） */
.sold-card {
  background: #fff;
  border-radius: 23.6px;
  border: 1.8px solid #f0f0f0;
  padding: 21.7px;
  margin-bottom: 21.7px;
  display: flex;
  align-items: center;
  gap: 21.7px;
}
.sold-img {
  width: 126.8px;
  height: 126.8px;
  border-radius: 18.1px;
  object-fit: cover;
  flex-shrink: 0;
}
.sold-info {
  flex: 1;
  min-width: 0;
  display: flex;
  flex-direction: column;
  gap: 12.7px;
}
.sold-name {
  font-size: 27.2px;
  color: #111;
  font-family: var(--font-medium);
}
.sold-price {
  font-size: 27.2px;
  color: #0047ff;
  font-family: var(--font-bold);
}
/* 回到顶部（原站 mescroll-totop 位置） */
.totop {
  position: fixed;
  right: 19.9px;
  bottom: 119.6px;
  width: 70.7px;
  height: 72.5px;
  border-radius: 50%;
  z-index: 9990;
}
</style>
