<template>
  <div class="ub-page">
    <!-- 导航栏（标题「XX的买入清单」白字 —— 原站白底白字不可见，如实复刻） -->
    <div class="navbar">
      <img class="navbar-back" src="/static/cw/back_btn.png" alt="返回" @click="$router.back()" />
      <span class="nav-title">{{ title }}的买入清单</span>
    </div>

    <!-- 骨架占位（原站实测：697×1421rpx 灰块 #b9babd r40，数据加载前的骨架/占位） -->
    <div class="skeleton"></div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useRoute } from 'vue-router'

/**
 * 原站 chunk 反解（pages-rank-userBuy）：
 *  - 用户买入清单页（「XX的买入清单」，依赖用户/藏品参数）；直接访问无参数 →
 *    原站渲染骨架占位灰块（2026-10-23 实测：697×1421rpx #b9babd r40），
 *    标题为白字（白底上不可见，原站如此），如实复刻
 */
const route = useRoute()
const title = ref(route.query.name || '')
</script>

<style scoped>
.ub-page {
  min-height: 100vh;
  background: #eaecf0;
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
  color: #fff;
  font-family: var(--font-medium);
}
.skeleton {
  width: 697px;
  height: 1421px;
  margin: 52px auto 0;
  background: #b9babd;
  border-radius: 40px;
}
</style>
