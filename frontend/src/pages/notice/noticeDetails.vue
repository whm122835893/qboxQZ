<template>
  <div class="detail-page">
    <!-- 导航栏（白底）：返回 + 居中"官方公告"（category 参数；新闻发声详情为 newsDetails 页） -->
    <div class="detail-navbar">
      <img class="nav-back" src="/static/cw/back_btn.png" alt="返回" @click="goBack" />
      <span class="nav-title">{{ navTitle }}</span>
    </div>

    <div class="detail-container">
      <!-- 面包屑：公告 › 官方 -->
      <div class="crumb-row">
        <span class="crumb-parent">{{ crumbRoot }}</span>
        <img class="crumb-arrow" src="/static/cw/notice_right.png" alt="" />
        <span class="crumb-current">{{ categoryName }}</span>
      </div>

      <!-- 标题 -->
      <div class="detail-title">{{ detail.title || '加载中...' }}</div>

      <!-- 头图（detail.image；原站官方公告无 image 字段，使用官方横幅） -->
      <div v-if="bannerUrl" class="detail-banner">
        <img class="detail-banner-img" :src="bannerUrl" alt="" />
      </div>

      <!-- 正文富文本（API content HTML；原站同款排版：字距 2px 行高 28px） -->
      <div class="detail-content" v-html="contentHtml"></div>

      <!-- 风险提示（原站前端固定展示的合规文案，实测 DOM 独立于 API content） -->
      <div class="disclaimer">
        <span class="d-title">【QBOX风险提示】:</span>
        <span class="d-text"> QBOX发售的数字藏品仅具备收藏欣赏价值，官方对藏品价格不构成任何指导意义，请谨慎购买，严防炒作。 </span>
        <span class="d-title d-mt">【QBOX郑重提醒广大用户】:</span>
        <span class="d-text"> 平台对于使用第三方工具锁单、抢单;以平台的名义进行承诺收益、承诺回购、非法集资、非法吸收公共存款;为了非法目的进行拉人头、要求缴纳入门费、团队计酬返利等涉嫌传销的行为或故意从事旨在对平台、平台用户或服务的履行造成不利影响的等行为秉持零容忍态度，一经发现将采取封号、冻结等措施。请大家规范抢购，不要轻信此类第三方工具或任何非官方的抢购信息，避免造成个人财产损失! </span>
        <span class="d-text d-mt"> 根据国家反洗钱、反诈骗等法律法规规定，特提醒广大用户严格遵守国家法律法规，禁止在QBOX平台进行洗钱、诈骗、赌博、开设赌场、非法集资、非法融资、传销等违法行为，否则一经平台查实，将对您的QBOX账号作出封禁、冻结等处理，并移交国家司法机关追究其法律责任。 </span>
      </div>
    </div>

    <!-- 回到顶部 -->
    <img
      v-show="showTotop"
      class="totop"
      src="/static/mescroll-totop.png"
      alt="回到顶部"
      @click="scrollTop"
    />
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { request } from '../../api/request'

const route = useRoute()
const router = useRouter()

const detail = ref({})
const showTotop = ref(false)

const categoryName = computed(() => route.query.category || '官方')
const navTitle = computed(() => categoryName.value === '官方' ? '官方公告' : categoryName.value)
const crumbRoot = computed(() => '公告')
// 原站实测：头图为正文第一张图裁剪 h220 cover；无 image 且无正文图时兜底官方横幅
const bannerUrl = computed(() => {
  if (detail.value.image) return detail.value.image
  const m = /<img[^>]+src=["']([^"']+)["']/.exec(detail.value.content || '')
  if (m) return m[1]
  return '/static/qbox/notice-banner-official.png'
})
// content 为 HTML 字符串（原站 rich-text 渲染）；图片路径已本地化
const contentHtml = computed(() => detail.value.content || '')

function goBack() {
  history.length > 1 ? router.back() : router.replace('/pages/notice/notice')
}
function onWinScroll() {
  showTotop.value = window.scrollY > 200
}
function scrollTop() {
  window.scrollTo({ top: 0, behavior: 'smooth' })
}

onMounted(async () => {
  const id = route.query.id || 3
  // 原站 GET /api/afficheDetail/ids/{id}
  const res = await request(`/api/afficheDetail/ids/${id}`)
  if (res.code === 1 && res.data) detail.value = res.data
  window.addEventListener('scroll', onWinScroll, { passive: true })
})
onUnmounted(() => {
  window.removeEventListener('scroll', onWinScroll)
})
</script>

<style scoped>
.detail-page {
  min-height: 100vh;
  background-color: #fff;
}
/* 导航栏：白底（原站实测 status-bar/navbar bg #fff） */
.detail-navbar {
  position: relative;
  width: 100%;
  height: 79.7px;
  background: #fff;
  display: flex;
  align-items: center;
  z-index: 5;
}
.nav-back {
  width: 16.3px;
  height: 29px;
  margin-left: 27.2px;
}
.nav-title {
  position: absolute;
  left: 50%;
  top: 50%;
  transform: translate(-50%, -50%);
  width: 398.5px;
  font-size: 32.6px;
  font-weight: 700;
  color: #000;
  font-family: var(--font-bold);
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  text-align: center;
}
.detail-container {
  width: 100%;
  padding: 0 30.8px 60px;
  box-sizing: border-box;
  background: #fff;
}

/* 面包屑：公告 › 官方 */
.crumb-row {
  margin-top: 27.2px;
  display: flex;
  align-items: center;
  font-size: 23.6px;
}
.crumb-parent {
  color: #333;
  font-family: var(--font-regular);
}
.crumb-arrow {
  width: 9px;
  height: 17px;
  margin: 0 7.2px;
}
.crumb-current {
  color: #393939;
  font-family: var(--font-regular);
}

/* 标题 19px Medium #161616 */
.detail-title {
  margin-top: 43.5px;
  font-size: 34.4px;
  color: #161616;
  font-family: var(--font-medium);
  line-height: 1.4;
}

/* 头图：全宽 h220 r8（有 image 字段时 220 高；content 图另算） */
.detail-banner {
  width: 100%;
  margin-top: 27.2px;
  border-radius: 14.5px;
  overflow: hidden;
  background: #f5f5f5;
}
.detail-banner-img {
  width: 100%;
  display: block;
  height: 398.5px;
  object-fit: cover;
}

/* 正文富文本：字距2px 行高28px（原站实测） */
.detail-content {
  margin-top: 36.2px;
  font-weight: 400;
  letter-spacing: 3.6px;
  color: #333;
  line-height: 50.7px;
  font-family: var(--font-regular);
}
.detail-content :deep(img) {
  width: 100%;
  display: block;
}

/* 风险提示固定合规文案 */
.disclaimer {
  margin-top: 58px;
  display: flex;
  flex-direction: column;
}
.d-title {
  font-size: 30.8px;
  color: #333;
  font-family: var(--font-bold);
}
.d-mt {
  margin-top: 21.7px;
}
.d-text {
  margin-top: 21.7px;
  font-size: 25.4px;
  color: #373737;
  letter-spacing: 1.8px;
  line-height: 1.6;
  font-family: var(--font-regular);
}

/* 回到顶部 */
.totop {
  position: fixed;
  right: 19.9px;
  bottom: 119.6px;
  width: 70.7px;
  height: 72.5px;
  border-radius: 50%;
  z-index: 9990;
}
</style>
