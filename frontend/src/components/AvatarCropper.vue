<template>
  <!-- 头像裁剪弹层（原站 w-crop/uni-cropper 实测：全屏黑底 + 圆形裁剪框 + 底部 取消/重选/预览/完成 蓝色文字按钮；
       独立项目按规则9 自建上传：本地文件选择 + 前端预览；拖拽微调简化为固定居中裁剪框（标注推断）；
       完成 → POST /api/user/profile {avatar} → 刷新用户信息） -->
  <div v-if="visible" class="cropper-mask">
    <div class="cropper-stage">
      <img class="cropper-img" :src="cropSrc || avatar" alt="" />
      <div class="cropper-circle"></div>
    </div>
    <div class="cropper-btns">
      <span class="cropper-btn" @click="$emit('close')">取消</span>
      <label class="cropper-btn">
        重选
        <input type="file" accept="image/*" class="cropper-file" @change="onPickFile" />
      </label>
      <span class="cropper-btn" @click="preview">预览</span>
      <span class="cropper-btn" @click="finish">完成</span>
    </div>

    <div v-if="showPreview" class="preview-mask" @click="showPreview = false">
      <img class="preview-img" :src="cropSrc || avatar" alt="" />
    </div>
  </div>
</template>

<script setup>
import { ref, watch } from 'vue'
import { request } from '../api/request'
import { toast } from '../utils/toast'
import { useUserStore } from '../stores/user'

const props = defineProps({
  visible: Boolean,
  avatar: { type: String, default: '' }
})
const emit = defineEmits(['close', 'saved'])

const userStore = useUserStore()
const cropSrc = ref('')
const showPreview = ref(false)

watch(() => props.visible, (v) => {
  if (v) { cropSrc.value = ''; showPreview.value = false }
})

function onPickFile(e) {
  const file = e.target.files && e.target.files[0]
  if (!file) return
  const reader = new FileReader()
  reader.onload = () => { cropSrc.value = reader.result }
  reader.readAsDataURL(file)
}

function preview() {
  showPreview.value = true
}

async function finish() {
  toast('正在修改')
  // 独立项目：头像上传由本项目后端实现（POST /api/user/profile {avatar}）；mock 直接保存本地状态
  const avatar = cropSrc.value || props.avatar
  const res = await request('/api/user/profile', { avatar })
  if (res.code === 1) {
    toast(res.msg || '修改成功')
    emit('close')
    await userStore.getUserInfo()
    emit('saved')
  }
}
</script>

<style scoped>
.cropper-mask {
  position: fixed;
  inset: 0;
  background: #000;
  z-index: 999;
  display: flex;
  flex-direction: column;
}
.cropper-stage {
  flex: 1;
  position: relative;
  display: flex;
  align-items: center;
  justify-content: center;
  overflow: hidden;
}
.cropper-img {
  max-width: 100%;
  max-height: 100%;
  opacity: 0.85;
}
.cropper-circle {
  position: absolute;
  width: 560px;
  height: 560px;
  border-radius: 50%;
  border: 3.6px solid #fff;
  box-shadow: 0 0 0 1999px rgba(0, 0, 0, 0.55);
}
.cropper-btns {
  height: 155.4px;
  display: flex;
  align-items: center;
  padding-bottom: 36.2px;
}
.cropper-btn {
  flex: 1;
  text-align: center;
  font-size: 29px;
  color: #0047ff;
  font-family: var(--font-regular);
}
.cropper-file {
  display: none;
}
.preview-mask {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.92);
  z-index: 1000;
  display: flex;
  align-items: center;
  justify-content: center;
}
.preview-img {
  max-width: 90%;
  max-height: 90%;
}
</style>
