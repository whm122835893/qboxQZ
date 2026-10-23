<template>
  <div class="gr-page">
    <div class="navbar">
      <img class="navbar-back" src="/static/cw/back_btn.png" alt="返回" @click="$router.back()" />
      <span class="nav-title">赠送记录</span>
    </div>

    <!-- list 容器（原站 DOM 实测：bg-color-item pa-12 br-8 的空列表容器，非 tabs 骨架） -->
    <div class="list-box"></div>

    <!-- 记录列表（tradingInformation status:4 → list 空，实测） -->
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { request } from '../../api/request'

/**
 * 原站 chunk 反解 + DOM 实测（pages-market-giftRecord，2026-10-23）：
 *  - 赠送记录页：tradingInformation {status:4, page:1} → list:[]（实测）
 *  - 主体为空 list 容器：class "list bg-color-item pa-12 br-8"（bg #f4f6fa、
 *    padding 12px 渲染、radius 8px 渲染，外层 plr-16）→ 空时仅剩 padding 高度
 */
const list = ref([])

onMounted(async () => {
  const res = await request('/api/market/trade/tradingInformation', { status: 4, page: 1 })
  if (res.code === 1 && res.data) list.value = res.data.list || []
})
</script>

<style scoped>
.gr-page {
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
  font-weight: 700;
  color: #000;
  font-family: var(--font-bold);
}
.list-box {
  margin: 0 29px;
  height: 43.5px;
  background: #f4f6fa;
  border-radius: 14.5px;
}
</style>
