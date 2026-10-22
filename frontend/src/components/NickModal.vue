<template>
  <!-- 修改昵称弹窗（原站 w-modal 实测：白卡居中、标题、输入框 84rpx 灰边、取消灰/确认蓝 #0018ff 圆角按钮；
       提交 POST /api/user/profile {nickname} → toast「正在修改/修改成功」→ 刷新用户信息） -->
  <div v-if="visible" class="modal-mask" @click.self="$emit('close')">
    <div class="modal-card">
      <span class="modal-title">修改昵称</span>
      <input v-model="nick" class="modal-input" type="text" maxlength="20" placeholder="请输入昵称" />
      <div class="modal-btns">
        <span class="modal-btn modal-btn-cancel" @click="$emit('close')">取消</span>
        <span class="modal-btn modal-btn-ok" @click="save">确认</span>
      </div>
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
  nickname: { type: String, default: '' }
})
const emit = defineEmits(['close', 'saved'])

const userStore = useUserStore()
const nick = ref('')

watch(() => props.visible, (v) => {
  if (v) nick.value = props.nickname === '--' ? '' : props.nickname
})

async function save() {
  const v = String(nick.value ?? '').trim()
  if (!v) return toast('请输入昵称')
  toast('正在修改')
  const res = await request('/api/user/profile', { nickname: v })
  if (res.code === 1) {
    toast(res.msg || '修改成功')
    emit('close')
    await userStore.getUserInfo()
    emit('saved')
  }
}
</script>

<style scoped>
.modal-mask {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.5);
  z-index: 999;
  display: flex;
  align-items: center;
  justify-content: center;
}
.modal-card {
  width: 616px;
  background: #fff;
  border-radius: 25.4px;
  padding: 39.9px 32.6px 32.6px;
  box-sizing: border-box;
  display: flex;
  flex-direction: column;
  align-items: center;
}
.modal-title {
  font-size: 30.8px;
  font-weight: 600;
  color: #111;
  font-family: var(--font-medium);
}
.modal-input {
  width: 100%;
  height: 84px;
  border: 1.8px solid #dcdfe6;
  border-radius: 16px;
  margin-top: 43.5px;
  padding: 0 58px;
  box-sizing: border-box;
  font-size: 27.2px;
  color: #222;
  outline: none;
}
.modal-input::placeholder {
  color: #c0c4cc;
}
.modal-btns {
  width: 100%;
  margin-top: 54.4px;
  display: flex;
  gap: 21.7px;
}
.modal-btn {
  flex: 1;
  height: 87px;
  border-radius: 43.5px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 29px;
  font-weight: 600;
}
.modal-btn-cancel {
  background: #f0f0f0;
  color: #333;
}
.modal-btn-ok {
  background: #0018ff;
  color: #fff;
}
</style>
