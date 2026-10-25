<template>
  <div class="lb-page">
    <!-- 顶部 Tab：日排行 / 周排行 / 月排行（源站 u-tabs 实测：x32 y76 w686，文字 15px #414141，三等分） -->
    <div class="tabs">
      <span
        v-for="t in tabs"
        :key="t.key"
        class="tab-item"
        :class="{ on: t.key === active }"
        @click="switchTab(t.key)"
      >{{ t.label }}</span>
    </div>

    <!-- 表头（排名/用户/返佣金额 30px #999，y115；源站实测列宽 124/187/311） -->
    <div class="table-head">
      <span class="th th-rank">排名</span>
      <span class="th th-user">用户</span>
      <span class="th th-amount">返佣金额</span>
    </div>

    <!-- 空态（源站实测：空态区 x32 y377 w686 h327，图居中 + 「暂无内容」y635） -->
    <div class="empty">
      <img class="empty-img" src="/static/empty/search-empty.png" alt="" />
      <span class="empty-text">暂无内容</span>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { request } from '../../api/request'

/**
 * 原站 DOM 实测（2026-10-25 登录态）：
 *  - Tab 行 u-tabs：x32 y76 w686，三等分（每项 w230），文字 15px rgb(65,65,65)
 *  - 表头行：x32 y97 w686 h80；「排名」x64 y115 w124、「用户」x188 y115 w187、
 *    「返佣金额」x375 y115 w311（右对齐），均 30px rgb(153,153,153)
 *  - 空态区：x32 y377 w686 h327；图 + 「暂无内容」x323 y635 w104 h37
 *  - 接口 /api/user/commison_rank 实测 404（该账号无返佣数据）
 */
const tabs = [
  { key: 1, label: '日排行' },
  { key: 2, label: '周排行' },
  { key: 3, label: '月排行' }
]
const active = ref(1)

async function switchTab(k) {
  active.value = k
  await request('/api/user/commison_rank', { type: k })
}

onMounted(async () => {
  await request('/api/user/commison_rank', { type: active.value })
})
</script>

<style scoped>
.lb-page {
  min-height: 100vh;
  background: #fff;
  padding: 76px 32px 0;
  box-sizing: border-box;
}

/* Tab 行 */
.tabs {
  width: 686px;
  height: 21px;
  display: flex;
}
.tab-item {
  flex: 1;
  height: 21px;
  font-size: 15px;
  color: #414141;
  font-family: var(--font-regular);
  text-align: center;
}
.tab-item.on {
  color: #0018ff;
  font-weight: 600;
}

/* 表头 */
.table-head {
  width: 686px;
  height: 80px;
  display: flex;
  align-items: center;
  padding: 0 32px;
  box-sizing: border-box;
}
.th {
  font-size: 30px;
  color: #999;
  font-family: var(--font-regular);
}
.th-rank {
  width: 124px;
}
.th-user {
  width: 187px;
}
.th-amount {
  flex: 1;
  text-align: right;
}

/* 空态 */
.empty {
  margin-top: 200px;
  width: 686px;
  height: 327px;
  display: flex;
  flex-direction: column;
  align-items: center;
}
.empty-img {
  width: 265px;
  height: 258px;
  object-fit: contain;
}
.empty-text {
  margin-top: 0;
  font-size: 28px;
  color: #999;
  font-family: var(--font-regular);
}
</style>
