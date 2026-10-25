<template>
  <!--
    市场列表表头（源站 pages/market/market）
    源站实测：四列 —— 藏品名称 / 地板价 / 成交量 / 涨跌幅，
    后三列各带一个排序箭头图标（market-sort.png）。
    单一职责：纯展示，无交互逻辑。
  -->
  <div class="list-header">
    <span class="lh-name">藏品名称</span>
    <span class="lh-col">
      地板价
      <img class="lh-sort" src="/static/qbox/market-sort.png" alt="" />
    </span>
    <span class="lh-col">
      成交量
      <img class="lh-sort" src="/static/qbox/market-sort.png" alt="" />
    </span>
    <span class="lh-col lh-last">
      涨跌幅
      <img class="lh-sort" src="/static/qbox/market-sort.png" alt="" />
    </span>
  </div>
</template>

<script setup>
/** 市场列表表头（单一职责组件，纯展示）。 */
</script>

<style scoped src="../../styles/components/market-list-header.css"></style>
