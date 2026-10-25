<template>
  <!--
    分类管理（pages/market/categoryManager）
    源站登录态实测（750 视口，2026-10-25）：
      - 顶部导航：高 44，标题「分类」居中（34px/700 #000），右侧「确认」（24px #555）
      - 「我的分类」区块：标题 28px #555，y=76；下方 3 个胶囊标签
        「我的关注 / 推荐 / 全部」，w212 h72，圆角 86px，底色 #f4f6fa，文字 28px #555
      - 「其他分类」区块：标题 28px #555，y=276；下方为空（源站该账号无自定义分类）
      - 页面底色 #fff
  -->
  <div class="cm-page">
    <div class="cm-navbar">
      <span class="cm-back" @click="goBack">
        <svg viewBox="0 0 24 24" fill="none"><path d="M15 4 7 12l8 8" stroke="#000" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"/></svg>
      </span>
      <span class="cm-title">分类</span>
      <span class="cm-confirm" @click="onConfirm">确认</span>
    </div>

    <!-- 我的分类 -->
    <section class="cm-section">
      <span class="cm-section-title">我的分类</span>
      <div class="cm-tags">
        <span
          v-for="t in myTags"
          :key="t"
          class="cm-tag"
          :class="{ active: t === selected }"
          @click="selected = t"
        >{{ t }}</span>
      </div>
    </section>

    <!-- 其他分类 -->
    <section class="cm-section">
      <span class="cm-section-title">其他分类</span>
      <div class="cm-other">
        <span v-for="t in otherTags" :key="t" class="cm-tag cm-tag-other" @click="onPick(t)">{{ t }}</span>
      </div>
    </section>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { toast } from '../../utils/toast'

const router = useRouter()

/** 源站登录态实测：我的分类固定三项；该账号无自定义分类，故「其他分类」为空 */
const myTags = ['我的关注', '推荐', '全部']
const otherTags = []
const selected = ref('推荐')

function goBack() {
  history.length > 1 ? router.back() : router.replace('/pages/market/market')
}
function onConfirm() {
  router.replace('/pages/market/market')
}
function onPick(t) {
  toast(`已选择分类：${t}`)
}
</script>

<style scoped>
.cm-page {
  min-height: 100vh;
  background: #fff;
  box-sizing: border-box;
  padding-bottom: 60px;
}

/* 顶部导航：源站 u-navbar 高 44 */
.cm-navbar {
  position: relative;
  width: 100%;
  height: 44px;
  display: flex;
  align-items: center;
  justify-content: center;
}
.cm-back {
  position: absolute;
  left: 12px;
  top: 50%;
  transform: translateY(-50%);
  width: 40px;
  height: 40px;
  display: flex;
  align-items: center;
  justify-content: center;
}
.cm-back svg {
  width: 24px;
  height: 24px;
}
.cm-title {
  font-size: 34px;
  font-weight: 700;
  color: #000;
  font-family: var(--font-medium);
  line-height: 49px;
}
.cm-confirm {
  position: absolute;
  right: 13px;
  top: 50%;
  transform: translateY(-50%);
  font-size: 24px;
  color: #555;
  font-family: var(--font-regular);
}

/* 区块 */
.cm-section {
  padding: 32px 32px 0;
}
.cm-section-title {
  display: block;
  font-size: 28px;
  color: #555;
  font-family: var(--font-regular);
  line-height: 40px;
}

/* 标签行：源站 flex gap-12，胶囊 w212 h72 */
.cm-tags {
  margin-top: 24px;
  display: flex;
  gap: 24px;
}
.cm-tag {
  flex: 1;
  height: 72px;
  padding: 16px 24px;
  box-sizing: border-box;
  background: #f4f6fa;
  border-radius: 86px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 28px;
  color: #555;
  font-family: var(--font-regular);
  text-align: center;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}
.cm-tag.active {
  background: #e8ecfa;
  color: #0018ff;
}
.cm-other {
  margin-top: 24px;
  display: flex;
  flex-wrap: wrap;
  gap: 24px;
}
.cm-tag-other {
  flex: 0 0 212px;
}
</style>
