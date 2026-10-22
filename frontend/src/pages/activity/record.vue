<template>
  <div class="rec-page">
    <!-- 导航栏 -->
    <div class="navbar">
      <img class="navbar-back" src="/static/cw/back_btn.png" alt="返回" @click="$router.back()" />
      <span class="nav-title">中签记录</span>
    </div>

    <!-- 空态（search1-empty + 暂无内容；drawLog 实测返回空数组） -->
    <div v-if="loadingType === 'empty'" class="empty">
      <img class="empty-img" src="/static/empty/search1-empty.png" alt="" />
      <span class="empty-text">暂无内容</span>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { request } from '../../api/request'

/**
 * 原站 chunk 反解（pages-activity-record）：
 *  - 中签记录页：/api/draw/drawLog → 空数组（实测）→ search1 空态
 */
const loadingType = ref('none')

onMounted(async () => {
  const res = await request('/api/draw/drawLog', {})
  loadingType.value = res.code === 1 && Array.isArray(res.data) && res.data.length ? 'none' : 'empty'
})
</script>

<style scoped>
.rec-page {
  min-height: 100vh;
  background: #fff;
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
  font-weight: 500;
  color: #000;
  font-family: var(--font-medium);
}
.empty {
  display: flex;
  flex-direction: column;
  align-items: center;
  margin-top: 485px;
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
