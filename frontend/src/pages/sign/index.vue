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
const successions = computed(() => userStore.userinfo?.successions ?? 0)

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
  // 连续签到天数来自 getUserInfo（登录响应不含 successions 字段，原站同款逻辑）
  if (userStore.token) await userStore.getUserInfo()
  await loadMonthSign()
  const now2 = new Date()
  signedToday.value = signedDays.value.includes(now2.getDate())
})
</script>

<style scoped>
.sign-page {
  min-height: 100vh;
  background-color: #fff;
  background-image: url('/static/sign/sign_bg.png');
  background-repeat: no-repeat;
  background-size: 100% auto;
  background-position: center top;
}
.sign-navbar {
  position: relative;
  width: 100%;
  height: 79.7px;
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
  font-size: 30.8px;
  font-weight: 700;
  color: #111;
  font-family: var(--font-bold);
}
.sign-container {
  width: 100%;
  display: flex;
  flex-direction: column;
  align-items: center;
  padding: 0 24.5px;
  box-sizing: border-box;
  background-color: transparent;
  padding-top: 418.3px;
}
/* 用户行 */
.user-row {
  width: 693.8px;
  display: flex;
  align-items: center;
  gap: 16px;
}
.user-avatar {
  width: 108.7px;
  height: 108.7px;
  border-radius: 50%;
}
.user-info {
  display: flex;
  flex-direction: column;
  gap: 12px;
}
.user-name {
  max-width: 298.9px;
  font-size: 30.8px;
  font-weight: 600;
  color: #000;
  font-family: var(--font-bold);
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}
.user-uid {
  font-size: 23.6px;
  color: #0d0d0d;
  font-family: var(--font-regular);
  white-space: nowrap;
}
/* 连签 */
.streak-row {
  width: 693.8px;
  margin-top: 33px;
  display: flex;
  align-items: center;
}
.streak-label {
  font-size: 30.8px;
  color: #111;
  font-family: var(--font-bold);
}
.streak-num {
  margin: 0 12.7px;
  font-size: 70.7px;
  line-height: 85.1px;
  color: #2a51ef;
  font-family: var(--font-bold);
}
/* 日历卡 */
.calendar-card {
  width: 695.6px;
  margin-top: 16px;
  border-radius: 29px;
  overflow: hidden;
  background: #fff;
  box-shadow: rgba(0, 0, 0, 0.06) 0 3.6px 14.5px;
}
.cal-header {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 36.2px;
  padding: 25.4px 0 34.4px;
}
.cal-month { /* 14 渲染 */
  font-size: 25.4px;
  color: #111;
  font-family: var(--font-regular);
}
.cal-arrow {
  display: flex;
  align-items: center;
}
.cal-weeks {
  display: flex;
  padding: 0 7.2px;
}
.cal-week-label {
  flex: 1;
  text-align: center;
  font-size: 25.4px;
  color: #333;
  font-family: var(--font-regular);
}
.cal-grid {
  display: flex;
  flex-wrap: wrap;
  padding: 18px 7.2px 25.4px;
}
.cal-cell {
  position: relative;
  width: 14.2857%;
  height: 99.6px;
  display: flex;
  align-items: center;
  justify-content: center;
}
.cal-day {
  font-size: 25.4px;
  color: #111;
  font-family: var(--font-regular);
  display: flex;
  flex-direction: column;
  align-items: center;
}
.cal-day.today {
  font-weight: 400;
  color: #2a51ef;
}
.cal-today-tag {
  font-size: 21.7px;
  color: #2a51ef;
  font-family: var(--font-regular);
  margin-top: 3.6px;
}
.cal-day.dim {
  color: #c0c0c0;
}
/* 已签到徽章：原站 uni-calendar-item__weeks-extra 实测 32x35 渲染px */
.cal-extra {
  width: 58px;
  height: 63.7px;
  background: #2a51ef;
  border-radius: 9px;
  color: #fff;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  line-height: 1.15;
}
.cal-extra-line {
  font-size: 20px;
  font-family: var(--font-regular);
}

/* 底部固定按钮 */
.sign-btn-wrap {
  position: fixed;
  left: 0;
  right: 0;
  bottom: 23.6px;
  z-index: 99;
  display: flex;
  justify-content: center;
  padding: 0 54.3px;
  box-sizing: border-box;
}
.sign-btn {
  width: 639.4px;
  height: 90.6px;
  border-radius: 45.3px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: #0047ff;
}
.sign-btn.disabled {
  background: #cacdd3;
}
.sign-btn-text {
  font-size: 29px;
  color: #fff;
  font-family: var(--font-bold);
  font-weight: 600;
}
</style>
