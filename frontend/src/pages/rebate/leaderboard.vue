<template>
  <div class="lb-page">
    <!-- 表头（排名/用户/返佣金额 16px #999，y92 渲染） -->
    <div class="table-head">
      <span class="th th-rank">排名</span>
      <span class="th th-user">用户</span>
      <span class="th th-amount">返佣金额</span>
    </div>

    <!-- 空态（search-empty 146×142 y236；暂无内容 14px #999 y382；commison_rank 404 实测） -->
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
 * 原站 DOM 实测（2026-10-23）：表头 16px #999（排名/用户/返佣金额）；
 * 空态图 146×142（y236）、暂无内容 14px #999（y382）；
 * /api/user/commison_rank {type:1} → 404（实测）
 */
onMounted(async () => {
  await request('/api/user/commison_rank', { type: 1 })
})
</script>

<style scoped>
.lb-page {
  min-height: 100vh;
  background: #fff;
}
.table-head {
  margin-top: 166px;
  display: flex;
  padding: 0 33px;
}
.th {
  font-size: 29px;
  color: #999;
}
.th-rank {
  width: 125px;
}
.th-user {
  flex: 1;
}
.th-amount {
  width: 313px;
  text-align: right;
}
.empty {
  margin-top: 200px;
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
