import { ref, computed } from 'vue'

/**
 * 日历选择状态（单一职责 composable）。
 * 封装「今日 → 选择日期」弹窗所需的年月日状态与派生计算：
 * - calLead：首日偏移（周一起始，源站实测）
 * - calDays：当月天数
 * - calMonth2Text：月份标题文本
 *
 * @returns {Object} 响应式日期状态与 confirmDate
 */
export function useDatePicker() {
  const now = new Date()
  const calYear = ref(now.getFullYear())
  const calMonth = ref(now.getMonth() + 1)
  const todayDate = ref(now.getDate())
  const pickedDate = ref(now.getDate())

  const calLead = computed(() => {
    const first = new Date(calYear.value, calMonth.value - 1, 1).getDay()
    return (first + 6) % 7 // 周一开头
  })
  const calDays = computed(() => new Date(calYear.value, calMonth.value, 0).getDate())
  const calMonth2Text = computed(() => `${calYear.value}年${calMonth.value}月`)

  function confirmDate() {
    return pickedDate.value
  }

  return {
    calYear,
    calMonth,
    todayDate,
    pickedDate,
    calLead,
    calDays,
    calMonth2Text,
    confirmDate
  }
}
