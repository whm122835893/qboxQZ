<template>
  <div class="pending">
    <span class="pending-title">页面建设中</span>
    <span class="pending-path">{{ $route.path }}</span>
    <span class="pending-tip">该页面尚未开发，等待上一个页面验收通过后按序推进</span>
  </div>
</template>
<script setup>
</script>
<style scoped>
.pending { min-height: 100vh; background: #f4f6fa; display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 16px; }
.pending-title { font-family: var(--font-medium); font-size: 18px; color: #1a1a1a; }
.pending-path { font-size: 13px; color: #0047ff; }
.pending-tip { font-size: 12px; color: #8c8c8c; }
</style>
