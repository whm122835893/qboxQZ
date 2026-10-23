<template>
  <div class="edit-page">
    <!-- 导航栏：标题随 id 变化（原站实测：编辑收货地址/添加收货地址） -->
    <div class="navbar">
      <img class="navbar-back" src="/static/cw/back_btn.png" alt="" @click="$router.back()" />
      <span class="nav-title">{{ form.id ? '编辑收货地址' : '添加收货地址' }}</span>
    </div>

    <!-- 表单卡（原站实测：17,61,380×311 r13 padding 8px 17px，行高 57，分隔线 #e6e6e6） -->
    <div class="form">
      <div class="ipt">
        <span class="ipt-label">收货人</span>
        <input v-model="form.s_name" class="ipt-input" type="text" placeholder="请输入姓名" />
      </div>
      <div class="line"></div>
      <div class="ipt">
        <span class="ipt-label">手机号码</span>
        <input v-model="form.s_phone" class="ipt-input" type="tel" maxlength="11" placeholder="请输入手机号" />
      </div>
      <div class="line"></div>
      <div class="ipt" @click="openPicker">
        <span class="ipt-label">所在地区</span>
        <input class="ipt-input" type="text" readonly :value="areaText" placeholder="请选择市区" />
      </div>
      <div class="line"></div>
      <div class="ipt">
        <span class="ipt-label">详细地址</span>
        <input v-model="form.address" class="ipt-input" type="text" placeholder="街道 小区 门牌号等" />
      </div>
      <div class="line"></div>
      <!-- 默认开关（原站 u-switch 42×22 activeColor #0018FF activeValue 1/inactiveValue 0） -->
      <div class="isdef">
        <!-- isdef 行标签为自适应宽度（原站「设置默认收货地址」8 字不换行，与表单行定宽标签不同） -->
        <span class="isdef-label">设置默认收货地址</span>
        <div class="switch" :class="{ on: form.isdef === 1 }" @click="toggleDef">
          <div class="switch-node"></div>
        </div>
      </div>
    </div>

    <!-- 保存按钮（原站实测 17,820,380×50 #0018ff r11 fw600） -->
    <div class="save-btn" @click="save">保存</div>

    <!-- 省市区选择器（原站底部弹层：取消 #4a4a4a / 确定 蓝色，三列 各138宽 行高44 选中加粗 #303133；
         原站数据源为 OSS address.json（抓包时 404），独立项目改用本地 china-division 真实行政区划（规则9） -->
    <div v-if="showPicker" class="picker-mask" @click.self="closePicker">
      <div class="picker-sheet">
        <div class="picker-head">
          <span class="picker-cancel" @click="closePicker">取消</span>
          <span class="picker-ok" @click="confirmPicker">确定</span>
        </div>
        <div class="picker-body">
          <div v-for="(col, ci) in pickerCols" :key="ci" class="picker-col">
            <div class="picker-pad"></div>
            <div
              v-for="(it, ii) in col"
              :key="it.code"
              class="picker-item"
              :class="{ sel: ii === selIdx[ci], adj: Math.abs(ii - selIdx[ci]) === 1 }"
              @click="pickItem(ci, ii)"
            >{{ it.name }}</div>
            <div class="picker-pad"></div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { request } from '../../../api/request'
import { toast } from '../../../utils/toast'
import pca from 'china-division/dist/pca-code.json'

const route = useRoute()
const router = useRouter()

const form = reactive({
  id: 0,
  s_name: '',
  s_phone: '',
  province: '',
  city: '',
  area: '',
  address: '',
  isdef: 0
})

// ---- 省市区选择器 ----
const showPicker = ref(false)
const selIdx = ref([0, 0, 0])
// 直辖市「市辖区」在原站选择器中显示为省名（实测：北京市/北京市/东城区）
const fixCity = (p, c) => (c.name === '市辖区' || c.name === '县' ? p.name : c.name)
const provinces = pca
const cities = computed(() => provinces[selIdx.value[0]]?.children || [])
const areas = computed(() => cities.value[selIdx.value[1]]?.children || [])
const pickerCols = computed(() => [
  provinces,
  cities.value.map(c => ({ ...c, name: fixCity(provinces[selIdx.value[0]], c) })),
  areas.value
])

const areaText = computed(() =>
  [form.province, form.city, form.area].filter(Boolean).join('') || ''
)

function openPicker() {
  // 回填已选位置
  const pi = provinces.findIndex(p => p.name === form.province)
  selIdx.value = [Math.max(0, pi), 0, 0]
  if (pi >= 0) {
    const ci = cities.value.findIndex(c => fixCity(provinces[pi], c) === form.city)
    selIdx.value[1] = Math.max(0, ci)
    const ai = areas.value.findIndex(a => a.name === form.area)
    selIdx.value[2] = Math.max(0, ai)
  }
  showPicker.value = true
}
function closePicker() {
  showPicker.value = false
}
function pickItem(col, idx) {
  const next = [...selIdx.value]
  next[col] = idx
  if (col === 0) { next[1] = 0; next[2] = 0 }
  if (col === 1) { next[2] = 0 }
  selIdx.value = next
}
function confirmPicker() {
  const [i0, i1, i2] = selIdx.value
  form.province = provinces[i0]?.name || ''
  form.city = fixCity(provinces[i0], cities.value[i1] || {})
  form.area = areas.value[i2]?.name || ''
  showPicker.value = false
}

function toggleDef() {
  form.isdef = form.isdef === 1 ? 0 : 1
}

// ---- 加载详情（编辑态：address_detail） ----
onMounted(async () => {
  const id = Number(route.query.id || 0)
  if (id) {
    form.id = id
    const res = await request('/api/user/address_detail', { id })
    if (res.code === 1 && res.data) Object.assign(form, res.data)
  }
})

// ---- 保存（edit_address）----
// 校验提示原文不可观测（原站 chunk 未含校验文案），按通用文案实现并已在文档标注推断
async function save() {
  const name = String(form.s_name || '').trim()
  const phone = String(form.s_phone || '').trim()
  if (!name) return toast('请输入姓名')
  if (!phone) return toast('请输入手机号')
  if (phone.length !== 11) return toast('请输入11位手机号')
  if (!areaText.value) return toast('请选择市区')
  if (!String(form.address || '').trim()) return toast('请输入详细地址')
  const res = await request('/api/user/edit_address', { ...form, id: form.id || undefined })
  if (res.code === 1) {
    toast(res.msg || '保存成功')
    setTimeout(() => router.back(), 800)
  }
}
</script>

<style scoped>
.edit-page {
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
/* 表单卡（原站实测 17,61,380×311 r13） */
.form {
  width: 688.5px;
  margin: 30.8px auto 0;
  background: #f4f6fa;
  border-radius: 23.6px;
  padding: 14.5px 30.8px;
  box-sizing: border-box;
}
.ipt {
  height: 103.3px;
  display: flex;
  align-items: center;
}
.ipt-label {
  width: 179.4px;
  flex-shrink: 0;
  font-size: 27.2px;
  font-weight: 700;
  color: #000;
  font-family: var(--font-regular);
}
.ipt-input {
  flex: 1;
  min-width: 0;
  height: 100%;
  border: none;
  outline: none;
  background: transparent;
  font-size: 25.4px;
  color: #000;
  font-family: var(--font-regular);
}
.ipt-input::placeholder {
  color: #c0c4cc;
}
.line {
  border-top: 1.1px solid #e6e6e6;
}
/* 默认开关行（原站实测 h55 mt8） */
.isdef {
  height: 99.6px;
  margin-top: 14.5px;
  display: flex;
  align-items: center;
  justify-content: space-between;
}
.isdef-label {
  font-size: 27.2px;
  font-weight: 700;
  color: #000;
  font-family: var(--font-regular);
  white-space: nowrap;
}
.switch {
  width: 76.1px;
  height: 39.9px;
  background: #fff;
  border: 1px solid #dadbde;
  border-radius: 100px;
  box-sizing: border-box;
  position: relative;
  transition: background 0.2s;
}
.switch-node {
  position: absolute;
  left: 1.8px;
  top: 0;
  width: 36.2px;
  height: 36.2px;
  background: #fff;
  border-radius: 50%;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.15);
  transform: translateX(0);
  transition: transform 0.2s;
}
.switch.on {
  background: #0018ff;
  border-color: #0018ff;
}
.switch.on .switch-node {
  transform: translateX(36.2px);
}
/* 保存按钮（原站实测 17,820,380×50 r11 fw600） */
.save-btn {
  position: fixed;
  left: 30.8px;
  bottom: 47.1px;
  width: 688.5px;
  height: 90.6px;
  background: #0018ff;
  border-radius: 19.9px;
  color: #fff;
  font-size: 29px;
  font-weight: 600;
  display: flex;
  align-items: center;
  justify-content: center;
  box-sizing: border-box;
}
/* 选择器（原站实测：白底弹层，头部 取消/确定，三列各138 行高44 选中加粗） */
.picker-mask {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.5);
  z-index: 999;
  display: flex;
  align-items: flex-end;
}
.picker-sheet {
  width: 100%;
  background: #fff;
  border-radius: 23.6px 23.6px 0 0;
}
.picker-head {
  height: 79.7px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 0 30.8px;
}
.picker-cancel {
  font-size: 29px;
  color: #4a4a4a;
}
.picker-ok {
  font-size: 29px;
  color: #0018ff;
}
.picker-body {
  display: flex;
  height: 398.6px;
  overflow: hidden;
}
.picker-col {
  width: 33.333%;
  overflow-y: auto;
  scroll-behavior: smooth;
}
.picker-pad {
  height: 159.4px;
  flex-shrink: 0;
}
.picker-item {
  height: 79.7px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 29px;
  color: #303133;
  font-family: var(--font-regular);
}
.picker-item.sel {
  font-weight: 700;
}
.picker-item.adj {
  font-weight: 400;
}
</style>
