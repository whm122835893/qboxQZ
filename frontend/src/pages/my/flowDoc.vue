<template>
  <div class="fd-page">
    <div class="navbar">
      <img class="navbar-back" src="/static/cw/back_btn.png" alt="返回" @click="$router.back()" />
      <span class="nav-title">流转记录</span>
    </div>

    <!-- 空态（getRecord 参数错误实测 → search-empty 空态「暂无内容」） -->
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
 * 原站 chunk 反解（pages-my-flowDoc）：
 *  - 流转记录页：/api/user_collection/user_collection/getRecord {page:1} →
 *    {code:0,msg:参数错误}（实测，直接访问无藏品 id）→ search-empty 空态「暂无内容」
 */
const loadingType = ref('none')

onMounted(async () => {
  const res = await request('/api/user_collection/user_collection/getRecord', { page: 1 })
  loadingType.value = 'empty'
})
</script>

<style scoped>
.fd-page {
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
.empty {
  display: flex;
  flex-direction: column;
  align-items: center;
  margin-top: 200px;
}
.empty-img {
  width: 265px;
  object-fit: contain;
}
.empty-text {
  margin-top: 6px;
  font-size: 26px;
  color: #999;
}
</style>
