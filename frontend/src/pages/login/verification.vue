<template>
  <div class="verify-page">
    <div class="verify-card">
      <span class="verify-title">安全验证</span>
      <span class="verify-sub">拖动滑块完成验证</span>
      <div class="slider-track">
        <span class="slider-tip">{{ passed ? '验证通过' : '按住滑块，拖动到最右侧' }}</span>
        <div class="slider-block" :class="{ ok: passed }" :style="{ left: sliderX + 'px' }"
          @pointerdown="onDown" @pointermove="onMove" @pointerup="onUp">
          <svg viewBox="0 0 24 24" width="18" height="18" :fill="passed ? '#0a0' : '#666'">
            <path v-if="passed" d="M9 16.2L4.8 12l-1.4 1.4L9 19 21 7l-1.4-1.4z"/>
            <path v-else d="M8.59 16.59L13.17 12 8.59 7.41 10 6l6 6-6 6z"/>
          </svg>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
/**
 * 原站 pages/login/verification 为第三方验证（极验/顶象）webview 容器：
 * verificationUrl 加载外部验证页，postMessage 回传结果（原站抓包时自跳转无法观测 UI）。
 * 独立项目按规则9 复刻为本地滑块验证容器（标注推断）：滑块拖到最右 → 验证通过语义（原站为验证成功回调后发送短信）。
 */
import { ref, onMounted, onUnmounted } from 'vue'

const passed = ref(false)
const sliderX = ref(0)
const track = ref(null)
let dragging = false
let startX = 0

function onDown(e) {
  if (passed.value) return
  dragging = true
  startX = e.clientX - sliderX.value
}
function onMove(e) {
  if (!dragging || passed.value) return
  const trackEl = document.querySelector('.slider-track')
  const max = trackEl ? trackEl.clientWidth - 60 : 300
  sliderX.value = Math.max(0, Math.min(max, e.clientX - startX))
  if (sliderX.value >= max - 2) {
    passed.value = true
    dragging = false
  }
}
function onUp() {
  dragging = false
  if (!passed.value) sliderX.value = 0
}
</script>

<style scoped>
.verify-page {
  min-height: 100vh;
  background: #f8f8f9;
  display: flex;
  align-items: center;
  justify-content: center;
}
.verify-card {
  width: 616px;
  background: #fff;
  border-radius: 25.4px;
  padding: 43.5px 36.2px 50.8px;
  box-sizing: border-box;
  display: flex;
  flex-direction: column;
  align-items: center;
  box-shadow: 0 7.2px 36.2px rgba(0, 0, 0, 0.08);
}
.verify-title {
  font-size: 30.8px;
  font-weight: 600;
  color: #111;
  font-family: var(--font-medium);
}
.verify-sub {
  margin-top: 14.5px;
  font-size: 23.6px;
  color: #8c8c8c;
  font-family: var(--font-regular);
}
.slider-track {
  position: relative;
  width: 100%;
  height: 90px;
  background: #eeeded;
  border-radius: 45px;
  margin-top: 36.2px;
  overflow: hidden;
}
.slider-tip {
  position: absolute;
  inset: 0;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 23.6px;
  color: #909399;
  font-family: var(--font-regular);
  user-select: none;
}
.slider-block {
  position: absolute;
  left: 0;
  top: 0;
  width: 90px;
  height: 90px;
  background: #fff;
  border-radius: 50%;
  box-shadow: 0 3.6px 14.5px rgba(0, 0, 0, 0.15);
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  touch-action: none;
}
.slider-block.ok {
  background: #eaf7ea;
}
</style>
