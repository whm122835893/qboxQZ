<template>
  <div class="cs-page">
    <div class="navbar">
      <img class="navbar-back" src="/static/cw/back_btn.png" alt="返回" @click="$router.back()" />
      <span class="nav-title">我的收藏</span>
    </div>

    <!-- 搜索行（放大镜 common-search-black 19px 渲染 y54） -->
    <div class="search-row">
      <img class="search-icon" src="/static/qbox/common-search-black.png" alt="" />
      <input v-model="keyword" class="search-input" placeholder="请输入藏品名称" />
    </div>

    <!-- 结果列表（无结果时原站渲染空白列表区，实测） -->
  </div>
</template>

<script setup>
import { ref } from 'vue'

/**
 * 原站 DOM 实测（2026-10-23）：标题 18px 700 #000；搜索放大镜 19px（common-search-black）；
 * 直接访问无结果内容（收藏搜索依赖关键词，实测空）
 */
const keyword = ref('')
</script>

<style scoped>
.cs-page {
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
.search-row {
  margin: 0 17.7px;
  height: 34.4px;
  display: flex;
  align-items: center;
  border-bottom: 1px solid #f0f0f0;
}
.search-icon {
  width: 34.4px;
  height: 34.4px;
  object-fit: contain;
}
.search-input {
  flex: 1;
  margin-left: 12px;
  border: none;
  outline: none;
  background: transparent;
  font-size: 25.4px;
  color: #333;
}
.search-input::placeholder {
  color: #c0c4cc;
}
</style>
