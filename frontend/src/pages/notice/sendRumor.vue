<template>
  <!-- 发布动态（pages/notice/sendRumor）：取消/发表 + 内容输入 + 匿名发表开关 -->
  <div class="sr-page">
    <!-- 顶部操作行（取消 15px #555；发表 白字胶囊） -->
    <div class="top-row">
      <span class="cancel-btn" @click="$router.back()">取消</span>
      <span class="publish-btn" @click="onPublish">发表</span>
    </div>

    <!-- 内容输入（15px，placeholder #c0c4cc） -->
    <textarea v-model="content" class="sr-textarea" placeholder="请输入内容"></textarea>

    <!-- 匿名发表（15px #7a7a7a + 开关） -->
    <div class="anon-row">
      <span class="anon-label">匿名发表</span>
      <div class="switch" :class="{ 'switch-on': anon }" @click="anon = !anon">
        <div class="switch-dot"></div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { toast } from '../../utils/toast'

/**
 * 原站 DOM 实测（2026-10-23）：取消 15px rgb(85,85,85)；发表 15px 白字（胶囊）；
 * textarea 15px placeholder rgb(192,196,204)；「匿名发表」15px #7a7a7a + 开关 y298
 */
const router = useRouter()
const content = ref('')
const anon = ref(false)

function onPublish() {
  if (!content.value.trim()) {
    toast('请输入内容')
    return
  }
  toast('发表成功（接口未接入）')
  router.back()
}
</script>

<style scoped>
.sr-page {
  min-height: 100vh;
  background: #fff;
  padding: 0 17.7px;
  box-sizing: border-box;
}
.top-row {
  padding-top: 12px;
  display: flex;
  align-items: center;
  justify-content: space-between;
}
.cancel-btn {
  font-size: 27.2px;
  color: #555;
}
.publish-btn {
  padding: 5px 27px;
  background: #ff7800;
  border-radius: 22px;
  font-size: 27.2px;
  color: #fff;
}
.sr-textarea {
  margin-top: 36px;
  width: 100%;
  height: 300px;
  background: #f5f6fa;
  border: none;
  border-radius: 12px;
  padding: 16px;
  box-sizing: border-box;
  font-size: 27.2px;
  color: #606266;
  resize: none;
  outline: none;
}
.sr-textarea::placeholder {
  color: #c0c4cc;
}
.anon-row {
  margin-top: 200px;
  display: flex;
  align-items: center;
  justify-content: flex-end;
  gap: 14px;
}
.anon-label {
  font-size: 27.2px;
  color: #7a7a7a;
}
.switch {
  width: 51px;
  height: 31px;
  border-radius: 16px;
  background: #e5e5e5;
  position: relative;
  transition: background 0.2s;
}
.switch-on {
  background: #1a1a1a;
}
.switch-dot {
  position: absolute;
  top: 2px;
  left: 2px;
  width: 27px;
  height: 27px;
  border-radius: 50%;
  background: #fff;
  transition: left 0.2s;
}
.switch-on .switch-dot {
  left: 22px;
}
</style>
