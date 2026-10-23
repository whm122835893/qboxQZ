<template>
  <div class="addr-page">
    <!-- 导航栏（原站 u-navbar：返回 9×16 + 居中标题 18px/500） -->
    <div class="navbar">
      <img class="navbar-back" src="/static/cw/back_btn.png" alt="" @click="$router.back()" />
      <span class="nav-title">收货地址</span>
    </div>

    <!-- 加载中（原站 u-loading-icon，数据到达前展示） -->
    <div v-if="loading" class="loading-row">
      <span class="loading-spinner"></span>
      <span class="loading-text">加载中</span>
    </div>

    <!-- 空态（原站实测：addr-empty.png 146×112 + 蓝色按钮 145×36 r25 #0018ff） -->
    <div v-else-if="list.length === 0" class="empty-col">
      <img class="empty-img" src="/static/empty/addr-empty.png" alt="" />
      <div class="add-btn" @click="goEdit()">去添加收货地址</div>
    </div>

    <!-- 地址卡片列表 -->
    <!-- 说明：原站账号地址为空（address_list 实测 data:[]），有数据时的卡片结构不可观测，
         以下卡片为按 App 整体风格实现的推断结构（已标注），字段来自接口语义（name/mobile/area/address/isdef） -->
    <div v-else class="addr-list">
      <div v-for="a in list" :key="a.id" class="addr-card" @click="goEdit(a.id)">
        <div class="addr-main">
          <div class="addr-line1">
            <span class="addr-name">{{ a.s_name }}</span>
            <span class="addr-phone">{{ a.s_phone }}</span>
            <span v-if="a.isdef === 1" class="addr-def">默认</span>
          </div>
          <div class="addr-text">{{ a.province }}{{ a.city }}{{ a.area }} {{ a.address }}</div>
        </div>
        <span class="addr-del" @click.stop="delAddress(a)">删除</span>
      </div>
      <div class="add-btn add-btn-bottom" @click="goEdit()">去添加收货地址</div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { request } from '../../../api/request'
import { toast } from '../../../utils/toast'

const router = useRouter()
const loading = ref(true)
const list = ref([])

async function load() {
  loading.value = true
  const res = await request('/api/user/address_list')
  if (res.code === 1) list.value = res.data || []
  loading.value = false
}

function goEdit(id) {
  router.push('/pages/my/address/edit' + (id ? '?id=' + id : ''))
}

async function delAddress(a) {
  const res = await request('/api/user/delUserAddress', { id: a.id })
  if (res.code === 1) {
    toast(res.msg || '删除成功')
    load()
  }
}

onMounted(load)
</script>

<style scoped>
.addr-page {
  min-height: 100vh;
  background: #f4f6fa;
}
/* 导航栏 */
.navbar {
  position: relative;
  height: 79.7px;
  display: flex;
  align-items: center;
  padding: 0 13px;
  background: transparent;
}
.navbar-back {
  width: 16.3px;
  height: 29px;
  object-fit: contain;
}
.nav-title {
  position: absolute;
  left: 50%;
  top: 50%;
  transform: translate(-50%, -50%);
  width: 398.6px;
  text-align: center;
  font-size: 32.6px;
  font-weight: 700;
  color: #000;
  font-family: var(--font-bold);
  white-space: nowrap;
}
/* 加载中 */
.loading-row {
  margin-top: 217.4px;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 8px;
}
.loading-spinner {
  width: 25.4px;
  height: 25.4px;
  border: 3.6px solid #d6d6d6;
  border-top-color: #333;
  border-radius: 50%;
  animation: spin 0.8s linear infinite;
}
@keyframes spin { to { transform: rotate(360deg); } }
.loading-text {
  font-size: 25.4px;
  color: #333;
}
/* 空态：mt-120 + 图 146×112 + mt-16 文本 + mt-16 按钮（原站实测 y193/y339） */
.empty-col {
  margin-top: 269.9px;
  display: flex;
  flex-direction: column;
  align-items: center;
}
.empty-img {
  width: 264.5px;
  height: 202.9px;
}
.add-btn {
  margin-top: 61.6px;
  min-width: 262.7px;
  height: 65.2px;
  padding: 0 39.9px;
  box-sizing: border-box;
  background: #0018ff;
  border-radius: 45.3px;
  color: #fff;
  font-size: 23.6px;
  font-weight: 600;
  display: flex;
  align-items: center;
  justify-content: center;
}
/* 地址卡片（推断结构，原站不可观测） */
.addr-list {
  padding: 23.6px 30.8px;
  display: flex;
  flex-direction: column;
  gap: 13px;
}
.addr-card {
  background: #fff;
  border-radius: 23.6px;
  padding: 30.8px;
  display: flex;
  align-items: center;
}
.addr-main {
  flex: 1;
  min-width: 0;
}
.addr-line1 {
  display: flex;
  align-items: center;
  gap: 21.7px;
}
.addr-name {
  font-size: 23.6px;
  font-weight: 600;
  color: #000;
  font-family: var(--font-medium);
}
.addr-phone {
  font-size: 25.4px;
  color: #8c8c8c;
}
.addr-def {
  background: rgba(0, 24, 255, 0.08);
  color: #0018ff;
  font-size: 19.9px;
  border-radius: 23.6px;
  padding: 3.6px 16.3px;
}
.addr-text {
  margin-top: 14.5px;
  font-size: 25.4px;
  color: #4a4a4a;
  line-height: 1.5;
}
.addr-del {
  flex-shrink: 0;
  margin-left: 21.7px;
  font-size: 23.6px;
  color: #8c8c8c;
}
.add-btn-bottom {
  margin-top: 21.7px;
}
</style>
