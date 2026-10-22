<template>
  <div class="bid-page">
    <div class="navbar">
      <img class="navbar-back" src="/static/cw/back_btn.png" alt="返回" @click="$router.back()" />
      <span class="nav-title">被邀请人清单</span>
    </div>

    <!-- 5 列表头（8px 渲染 = 原站如此的小字表头，#555） -->
    <div class="table-head">
      <span class="th">昵称</span>
      <span class="th">加入时间</span>
      <span class="th">首次交易日期</span>
      <span class="th">总交易金额</span>
      <span class="th">本自然月交易金额</span>
    </div>

    <!-- 空态（search-empty 146×142 y222；暂无内容 14px #999 y368；bei_yao_list 404 实测） -->
    <div class="empty">
      <img class="empty-img" src="/static/empty/search-empty.png" alt="" />
      <span class="empty-text">暂无内容</span>
    </div>
  </div>
</template>

<script setup>
import { onMounted } from 'vue'
import { request } from '../../api/request'

/**
 * 原站 DOM 实测（2026-10-23）：标题 18px 700 #000；表头 5 列 8px #555（昵称/加入时间/
 * 首次交易日期/总交易金额/本自然月交易金额）；空态图 146×142（y222）、暂无内容 14px（y368）；
 * /api/user/bei_yao_list → 404（实测）
 */
onMounted(async () => {
  await request('/api/user/bei_yao_list', {})
})
</script>

<style scoped>
.bid-page {
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
}
.table-head {
  margin-top: 12px;
  display: flex;
  align-items: center;
  padding: 0 17.7px;
}
.th {
  font-size: 14.5px;
  color: #555;
}
.th:nth-child(1) { width: 67px; }
.th:nth-child(2) { width: 132px; }
.th:nth-child(3) { width: 167px; text-align: center; }
.th:nth-child(4) { width: 130px; text-align: center; }
.th:nth-child(5) { flex: 1; text-align: right; }
.empty {
  margin-top: 290px;
  display: flex;
  flex-direction: column;
  align-items: center;
}
.empty-img {
  width: 264.7px;
  height: 257.5px;
  object-fit: contain;
}
.empty-text {
  margin-top: 25px;
  font-size: 25.4px;
  color: #999;
}
</style>
