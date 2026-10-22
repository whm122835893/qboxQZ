<template>
  <div class="sh-page">
    <!-- 搜索行（原站渲染实测：padding 44/19px，back=common-arrow-right-black 旋转180° 13px，框 h39 #f5f5f5 r19） -->
    <div class="search-row">
      <img class="sh-back" src="/static/qbox/common-arrow-right-black.png" alt="返回" @click="$router.back()" />
      <div class="search-box">
        <img class="search-icon" src="/static/qbox/common-search-gray.png" alt="" />
        <input v-model="keyword" class="search-input" placeholder="请输入内容" @confirm="doSearch" />
      </div>
    </div>

    <!-- Tabs（数字藏品/盲盒；字 16px MiSans-Semibold，active #000 / #5b5b5b，线 26×3 #0047ff） -->
    <div class="tabs">
      <div v-for="(t, i) in tabs" :key="t.name" class="tab-item" @click="switchTab(i)">
        <span class="tab-text" :class="{ 'tab-text-active': current === i }">{{ t.name }}</span>
        <div v-if="current === i" class="tab-line"></div>
      </div>
    </div>

    <!-- 表头（藏品名称 flex1 / 地板价 w88 / 成交量 w66，右对齐，12px 500 #919191） -->
    <div class="table-head">
      <span class="th th-name">藏品名称</span>
      <span class="th th-price">地板价</span>
      <span class="th th-volume">成交量</span>
    </div>

    <!-- 空态（common-empty 156px 容器 min-height 264px） -->
    <div class="empty">
      <img class="empty-img" src="/static/qbox/common-empty.png" alt="" />
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { request } from '../../api/request'

/**
 * 原站 chunk 反解 + DOM 实测（pages-market-search，2026-10-23）：
 *  - 搜索页：getMarketList {goods_type:2, keywords} → 空数组（实测）
 *  - 渲染值（@414）：外层 padding 44/19px；箭头 13×13 旋转180°（common-arrow-right-black）；
 *    框 h39 #f5f5f5 r19 padding 0 15px；搜索图标 17px（common-search-gray）；输入 14px；
 *    tabs mt 15px 字 16px MiSans-Semibold active #000/#5b5b5b 线 26×3 #0047ff；
 *    表头 mt 23px 字 12px 500 #919191（地板价 w88/成交量 w66 右对齐）；
 *    空态容器 mt 15px min-height 264px，图 156×156（common-empty）
 *  - 源码值 = 渲染值 × 1.812（本项目 px→vw 约定）
 */
const tabs = [{ name: '数字藏品', goods_type: 2 }, { name: '盲盒', goods_type: 1 }]
const current = ref(0)
const keyword = ref('')
const list = ref([])

async function doSearch() {
  const res = await request('/api/market/market/getMarketList', { goods_type: tabs[current.value].goods_type, keywords: keyword.value || null })
  if (res.code === 1 && res.data) list.value = res.data
}
function switchTab(i) {
  current.value = i
  doSearch()
}
doSearch()
</script>

<style scoped>
.sh-page {
  min-height: 100vh;
  background: #fff;
  /* 原站外层容器：flex column + align-items center（tabs/表头/空态均为 374px 渲染宽居中） */
  display: flex;
  flex-direction: column;
  align-items: center;
}
/* ---- 搜索行（容器 374px 渲染居中；箭头+gap16+框 flex1；顶部 padding 44px 渲染） ---- */
.search-row {
  width: 677.7px;
  display: flex;
  align-items: center;
  padding-top: 79.7px;
  box-sizing: border-box;
}
.sh-back {
  width: 23.6px;
  height: 23.6px;
  object-fit: contain;
  transform: rotate(180deg);
  margin-right: 29px;
  flex-shrink: 0;
}
.search-box {
  flex: 1;
  height: 70.7px;
  background: #f5f5f5;
  border-radius: 34.4px;
  display: flex;
  align-items: center;
  padding: 0 27.2px;
  box-sizing: border-box;
}
.search-icon {
  width: 30.8px;
  height: 30.8px;
  flex-shrink: 0;
  object-fit: contain;
}
.search-input {
  flex: 1;
  margin-left: 14.5px;
  border: none;
  outline: none;
  background: transparent;
  font-size: 25.4px;
  color: #000;
}
.search-input::placeholder {
  color: #c0cccc;
}
/* ---- Tabs（mt 15px；兄弟 gap 22px 渲染——gap-pf-x-36 为兄弟间隙语义，首元素无偏移） ---- */
.tabs {
  width: 677.7px;
  margin-top: 27.2px;
  display: flex;
  align-items: flex-end;
  gap: 39.9px;
}
.tab-item {
  display: flex;
  flex-direction: column;
  align-items: center;
}
.tab-text {
  font-size: 29px;
  font-family: 'MiSans-Semibold', var(--font-medium);
  color: #5b5b5b;
}
.tab-text-active {
  color: #000;
}
.tab-line {
  margin-top: 5.4px;
  width: 47.1px;
  height: 5.4px;
  background: #0047ff;
  border-radius: 2px;
}
/* ---- 表头（mt 23px，字 12px 500 #919191） ---- */
.table-head {
  width: 677.7px;
  margin-top: 41.7px;
  display: flex;
  align-items: center;
}
.th {
  font-weight: 500;
  font-size: 21.7px;
  color: #919191;
  font-family: var(--font-regular);
}
.th-name {
  flex: 1;
}
.th-price {
  width: 159.5px;
  text-align: right;
}
.th-volume {
  width: 119.6px;
  text-align: right;
}
/* ---- 空态（mt 15px + padding-y 24px，min-height 264px，图 156px） ---- */
.empty {
  width: 677.7px;
  margin-top: 27.2px;
  padding: 43.5px 0;
  min-height: 478.4px;
  box-sizing: border-box;
  display: flex;
  align-items: center;
  justify-content: center;
}
.empty-img {
  width: 282.6px;
  height: 282.6px;
  object-fit: contain;
}
</style>
