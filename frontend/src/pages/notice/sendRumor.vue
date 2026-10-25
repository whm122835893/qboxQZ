<template>
  <!--
    发布动态（pages/notice/sendRumor）
    源站登录态实测（750 视口，2026-10-25）：
      - 顶栏：左「取消」x32 w56（28px #555）、右「发表」橙色胶囊 x614 w104
      - 输入区：x32 y44 w686 h88，底色 #f5f6fa（bg-color-item），placeholder「请输入内容」
      - 照片上传区：x32 y132 w686 h232；上传占位块 x32 y164 w200 h200（同底色）
        内含图片图标 + 「照片」文字（20px #999）
      - 匿名发表行：x32 y364 w686 h104，文字 x32 y428（28px #7a7a7a）+ 右侧开关
  -->
  <div class="sr-page">
    <!-- 顶部操作行 -->
    <div class="top-row">
      <span class="cancel-btn" @click="$router.back()">取消</span>
      <span class="publish-btn" @click="onPublish">发表</span>
    </div>

    <!-- 内容输入 -->
    <textarea v-model="content" class="sr-textarea" placeholder="请输入内容"></textarea>

    <!-- 照片上传区 -->
    <div class="upload-area">
      <div class="upload-cell" @click="onPickPhoto">
        <img class="upload-icon" src="/static/qbox/upload-picture.png" alt="照片" />
        <span class="upload-text">照片</span>
      </div>
    </div>

    <!-- 匿名发表 -->
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
 * 原站 DOM 实测（2026-10-25 登录态）：
 *  - diy-nav 高 44；「取消」x32 y2 w56 h40（28px #555）；
 *    「发表」橙色胶囊 x614 w104 h56（btn-type-colo）
 *  - u-textarea x32 y44 w686 h88，底色 #f5f6fa，placeholder「请输入内容」
 *  - u-upload x32 y132 w686 h232；上传占位 x32 y164 w200 h200，
 *    内含图片图标 + 「照片」（font-size-10 = 20px #999）
 *  - 匿名发表行 x32 y364 w686 h104；文字 x32 y428 w112（28px #7a7a7a）+ 开关
 */
const router = useRouter()
const content = ref('')
const anon = ref(false)

function onPickPhoto() {
  toast('图片上传（接口未接入）')
}

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
  padding: 0 32px;
  box-sizing: border-box;
}

/* 顶栏 */
.top-row {
  height: 44px;
  display: flex;
  align-items: center;
  justify-content: space-between;
}
.cancel-btn {
  font-size: 28px;
  color: #555;
  font-family: var(--font-regular);
  line-height: 40px;
}
.publish-btn {
  padding: 0 32px;
  height: 56px;
  background: #ff7800;
  border-radius: 28px;
  font-size: 28px;
  color: #fff;
  font-family: var(--font-medium);
  display: flex;
  align-items: center;
}

/* 输入区 */
.sr-textarea {
  margin-top: 0;
  width: 100%;
  height: 88px;
  background: #f5f6fa;
  border: none;
  border-radius: 0;
  padding: 9px;
  box-sizing: border-box;
  font-size: 28px;
  color: #606266;
  resize: none;
  outline: none;
  font-family: var(--font-regular);
}
.sr-textarea::placeholder {
  color: #c0c4cc;
}

/* 照片上传区 */
.upload-area {
  margin-top: 0;
  width: 100%;
  height: 232px;
}
.upload-cell {
  width: 200px;
  height: 200px;
  margin-top: 32px;
  background: #f5f6fa;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
}
.upload-icon {
  width: 60px;
  height: 60px;
  object-fit: contain;
  opacity: 0.6;
}
.upload-text {
  margin-top: 12px;
  font-size: 20px;
  color: #999;
  font-family: var(--font-regular);
}

/* 匿名发表 */
.anon-row {
  margin-top: 0;
  height: 104px;
  display: flex;
  align-items: center;
  justify-content: flex-end;
  gap: 24px;
}
.anon-label {
  font-size: 28px;
  color: #7a7a7a;
  font-family: var(--font-regular);
}
.switch {
  width: 51px;
  height: 31px;
  border-radius: 16px;
  background: #e5e5e5;
  position: relative;
  transition: background 0.2s;
  flex-shrink: 0;
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
