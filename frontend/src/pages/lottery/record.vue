<template>
  <div class="lrd-page">
    <div class="navbar">
      <img class="navbar-back" src="/static/cw/back_btn.png" alt="返回" @click="$router.back()" />
      <span class="nav-title">抽奖记录</span>
    </div>

    <!-- 空态（common-empty 156px 渲染 y135，图内含「这里空空的!」；lottery/record 实测空） -->
    <div class="empty">
      <img class="empty-img" src="/static/qbox/common-empty.png" alt="" />
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { request } from '../../api/request'

/**
 * 原站 DOM 实测（2026-10-23）：标题 17px Bold #111；空态 common-empty 156px（y135）；
 * /api/lottery/record {type:10,page:1} → total:0（实测）
 */
const list = ref([])

onMounted(async () => {
  const res = await request('/api/lottery/record', { type: 10, page: 1 })
  if (res.code === 1 && res.data) list.value = res.data.data || []
})
</script>

<style scoped>
.lrd-page {
  min-height: 100vh;
  background: #fff;
}
.navbar {
  position: relative;
  width: 100%;
  height: 116px;
  display: flex;
  align-items: flex-end;
  justify-content: center;
  padding-bottom: 12px;
  box-sizing: border-box;
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
  font-size: 30.8px;
  font-weight: 700;
  color: #111;
}
.empty {
  margin-top: 100px;
  display: flex;
  flex-direction: column;
  align-items: center;
}
.empty-img {
  width: 282.6px;
  height: 282.6px;
  object-fit: contain;
}
</style>
