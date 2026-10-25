<template>
  <!--
    选择日期弹窗（源站 pages/market/market 顶部「今日」点击后弹出）
    源站实测：底部半屏弹窗，标题「选择日期」+ 关闭 ×，月历网格（周一起始），
    未来日期置灰不可选，底部「取消 / 确定」双按钮。
    单一职责：只渲染日历并上抛选择/确认/关闭事件，不含数据请求。
  -->
  <div class="mask" @click.self="$emit('close')">
    <div class="sheet">
      <div class="sheet-head">
        <span class="sheet-title">选择日期</span>
        <span class="sheet-close" @click="$emit('close')">
          <svg viewBox="0 0 24 24" width="16" height="16" stroke="#333" stroke-width="2.5" stroke-linecap="round" fill="none">
            <line x1="5" y1="5" x2="19" y2="19"></line><line x1="19" y1="5" x2="5" y2="19"></line>
          </svg>
        </span>
      </div>
      <div class="cal-month">{{ year }}年{{ month }}月</div>
      <div class="cal-week">
        <span v-for="w in ['一','二','三','四','五','六','日']" :key="w" class="cal-week-item">{{ w }}</span>
      </div>
      <div class="cal-month2">{{ monthText }}</div>
      <div class="cal-grid">
        <span class="cal-watermark">{{ month }}</span>
        <span v-for="i in lead" :key="'b'+i" class="cal-cell"></span>
        <span
          v-for="d in days"
          :key="d"
          class="cal-cell"
          :class="{ disabled: d > today, picked: d === picked }"
          @click="d <= today && $emit('pick', d)"
        >{{ d }}</span>
      </div>
      <div class="sheet-btns">
        <div class="btn-cancel" @click="$emit('close')">取消</div>
        <div class="btn-ok" @click="$emit('confirm')">确定</div>
      </div>
    </div>
  </div>
</template>

<script setup>
/**
 * 选择日期弹窗（单一职责组件）。
 * props: year / month / lead（首日偏移）/ days（当月天数）/ today（今天）/ picked（选中日）/ monthText
 * emits: pick（选中某日）/ confirm（确定）/ close（关闭）
 */
defineProps({
  year: { type: Number, required: true },
  month: { type: Number, required: true },
  lead: { type: Array, default: () => [] },
  days: { type: Number, default: 30 },
  today: { type: Number, default: 1 },
  picked: { type: Number, default: 1 },
  monthText: { type: String, default: '' }
})
defineEmits(['pick', 'confirm', 'close'])
</script>

<style scoped src="../../styles/components/market-date-picker.css"></style>
