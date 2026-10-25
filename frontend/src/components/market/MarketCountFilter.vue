<template>
  <!--
    全部份数筛选弹窗（源站 pages/market/market 底部弹窗之一）
    源站实测：底部半屏弹窗，选项为胶囊按钮网格 + 底部「重置 / 确定」双按钮。
    单一职责：只展示选项并上抛选择/关闭事件，不含数据请求。
  -->
  <div class="mask" @click.self="$emit('close')">
    <div class="sheet">
      <div class="option-grid">
        <div
          v-for="(o, i) in options"
          :key="i"
          class="option-pill"
          :class="{ picked: o.label === picked }"
          @click="$emit('pick', o.label)"
        >{{ o.label }}</div>
      </div>
      <div class="sheet-btns option-btns">
        <div class="btn-reset" @click="$emit('reset')">重置</div>
        <div class="btn-dark" @click="$emit('close')">确定</div>
      </div>
    </div>
  </div>
</template>

<script setup>
/**
 * 全部份数筛选弹窗（单一职责组件）。
 * props: options —— 选项数组 [{ label, value }]；picked —— 当前选中项 label
 * emits: pick / reset / close
 */
defineProps({
  options: { type: Array, default: () => [] },
  picked: { type: String, default: '' }
})
defineEmits(['pick', 'reset', 'close'])
</script>

<style scoped src="../../styles/components/market-count-filter.css"></style>
