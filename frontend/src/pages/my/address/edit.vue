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

<style scoped src="../../../styles/pages/my-address-edit.css"></style>
