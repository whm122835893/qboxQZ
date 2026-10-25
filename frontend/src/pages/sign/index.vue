<template>
  <div class="sign-page">
    <!-- 导航栏 -->
    <div class="sign-navbar">
      <img class="nav-back" src="/static/cw/back_btn.png" alt="返回" @click="goBack" />
      <span class="nav-title">每日签到</span>
    </div>

    <div class="sign-container">
      <!-- 用户信息行 -->
      <div class="user-row">
        <img class="user-avatar" :src="avatarUrl" alt="头像" />
        <div class="user-info">
          <span class="user-name">{{ nickname }}</span>
          <span class="user-uid">UID {{ uid }}</span>
        </div>
      </div>

      <!-- 连续签到 -->
      <div class="streak-row">
        <span class="streak-label">您已连续签到</span>
        <span class="streak-num">{{ successions }}</span>
        <span class="streak-label">天</span>
      </div>

      <!-- 日历卡（原站 uni-calendar 实测样式：SUN-SAT 表头、跨月灰字、签到日蓝色"已签到"徽章） -->
      <div class="calendar-card">
        <div class="cal-header">
          <span class="cal-arrow" @click="changeMonth(-1)">
            <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="#333" stroke-width="2.5" stroke-linecap="round"><polyline points="15 18 9 12 15 6"/></svg>
          </span>
          <span class="cal-month">{{ calYear }} / {{ String(calMonth).padStart(2, '0') }}</span>
          <span class="cal-arrow" @click="changeMonth(1)">
            <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="#333" stroke-width="2.5" stroke-linecap="round"><polyline points="9 18 15 12 9 6"/></svg>
          </span>
        </div>
        <div class="cal-weeks">
          <span v-for="w in weekHeads" :key="w" class="cal-week-label">{{ w }}</span>
        </div>
        <div class="cal-grid">
          <div v-for="(cell, i) in cells" :key="i" class="cal-cell">
            <template v-if="cell.day">
              <span v-if="isSigned(cell)" class="cal-extra"><span class="cal-extra-line">已</span><span class="cal-extra-line">签到</span></span>
              <span v-else class="cal-day" :class="{ dim: !cell.current, today: isToday(cell) }">
                {{ cell.day }}
                <span v-if="isToday(cell)" class="cal-today-tag">today</span>
              </span>
            </template>
          </div>
        </div>
      </div>
    </div>

    <!-- 底部固定按钮：已签到置灰（原站实测）；未签到时蓝色"立即签到" -->
    <div class="sign-btn-wrap">
      <div class="sign-btn" :class="{ disabled: signedToday }" @click="doSign">
        <span class="sign-btn-text">{{ signedToday ? ' 已签到 ' : '立即签到' }}</span>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useUserStore } from '../../stores/user'
import { request } from '../../api/request'

const router = useRouter()
const userStore = useUserStore()

const nickname = computed(() => userStore.userinfo?.nickname || '--')
const uid = computed(() => userStore.userinfo?.id || '')
const avatarUrl = computed(() => {
  const a = userStore.userinfo?.avatar
  return !a || a === '/defautAvatar.png' ? '/static/logo/logo.png' : a
})
// 连续签到天数来源：源站实测 /addons/signin/api.index/index → data.successions
// （不是 userinfo.successions；源站登录态实测 successions=0、is_signin=false）
const successions = ref(0)

const weekHeads = ['SUN', 'MON', 'TUE', 'WED', 'THU', 'FRI', 'SAT']
const now = new Date()
const calYear = ref(now.getFullYear())
const calMonth = ref(now.getMonth() + 1)
const signedDays = ref([]) // 当月已签日期数组（monthSign 实测：{"21":"21"}）
const signedToday = ref(false)

const cells = computed(() => {
  const y = calYear.value
  const m = calMonth.value
  const first = new Date(y, m - 1, 1).getDay()
  const lead = first // SUN 开头
  const daysInMonth = new Date(y, m, 0).getDate()
  const prevDays = new Date(y, m - 1, 0).getDate()
  const arr = []
  for (let i = lead - 1; i >= 0; i--) arr.push({ day: prevDays - i, current: false })
  for (let d = 1; d <= daysInMonth; d++) arr.push({ day: d, current: true })
  // 原站 uni-calendar 固定渲染 6 行（42 格），末尾补下月日期（灰色）
  let nd = 1
  while (arr.length < 42) arr.push({ day: nd++, current: false })
  return arr
})

function isSigned(cell) {
  return cell.current && signedDays.value.includes(cell.day)
}

function isToday(cell) {
  const n = new Date()
  return cell.current && cell.day === n.getDate() && calYear.value === n.getFullYear() && calMonth.value === n.getMonth() + 1
}

function changeMonth(delta) {
  let m = calMonth.value + delta
  let y = calYear.value
  if (m < 1) { m = 12; y-- }
  if (m > 12) { m = 1; y++ }
  calYear.value = y
  calMonth.value = m
  loadMonthSign()
}

async function loadMonthSign() {
  const res = await request('/addons/signin/api.index/monthSign')
  if (res.code === 1 && res.data && typeof res.data === 'object') {
    const ym = `${calYear.value}-${String(calMonth.value).padStart(2, '0')}`
    signedDays.value = Object.keys(res.data)
      .map(k => Number(k))
      .filter(d => {
        // monthSign 返回当月数据；日期 key 需校验属于当前展示月份
        return d >= 1 && d <= 31
      })
    void ym
  }
}

async function doSign() {
  if (signedToday.value) return
  const res = await request('/addons/signin/api.index/sign', {})
  if (res.code === 1) {
    const today = new Date().getDate()
    if (calMonth.value === new Date().getMonth() + 1) {
      signedDays.value = [...signedDays.value, today]
    }
    signedToday.value = true
  }
}

function goBack() {
  history.length > 1 ? router.back() : router.replace('/pages/index/index')
}

onMounted(async () => {
  // 连续签到天数来自签到首页接口（源站实测 /addons/signin/api.index/index）
  if (userStore.token) await userStore.getUserInfo()
  const idx = await request('/addons/signin/api.index/index', {})
  if (idx.code === 1 && idx.data) {
    successions.value = idx.data.successions ?? 0
  }
  await loadMonthSign()
  const now2 = new Date()
  signedToday.value = signedDays.value.includes(now2.getDate())
})
</script>

<style scoped src="../../styles/pages/sign-index.css"></style>
