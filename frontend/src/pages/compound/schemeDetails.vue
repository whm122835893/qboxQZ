<template>
  <div class="sd-page">
    <!-- 导航栏（绝对顶部，684×72rpx 行，标题默认「合成活动」） -->
    <div class="navbar">
      <div class="navbar-inner">
        <img class="nav-back" src="/static/qbox/common-arrow-right-black.png" alt="返回" @click="$router.back()" />
        <span class="nav-title"> {{ title || '合成活动' }} </span>
        <div class="nav-placeholder"></div>
      </div>
    </div>

    <div class="body">
      <!-- 主图框（436rpx 黑底白边 r64 阴影） -->
      <div class="img-frame">
        <img v-if="image" class="frame-img" :src="resolveImg(image)" alt="" />
      </div>

      <!-- 时间卡（rgba(255,255,255,0.83) r32 padding 32/36） -->
      <div class="time-card">
        <span class="card-title"> {{ title }} </span>
        <span class="card-time"> 合成时间：{{ start_time }} - {{ end_time }} </span>
      </div>

      <!-- 合成材料卡（white r32 padding 32/28） -->
      <div class="material-card">
        <span class="card-title"> 合成材料 </span>
        <!-- 材料组（changeMaterials；playInfo 失败时 rule_text 为空不渲染） -->
        <div v-for="(g, gi) in ruleText" :key="gi" class="m-group">
          <div class="m-head">
            <div class="m-head-left">
              <span class="m-name"> {{ g.name || '合成条件' + (gi + 1) }} </span>
              <span class="m-sub"> 需要{{ g.number }}份材料;已选{{ selectedCount(g) }}份材料 </span>
            </div>
            <div class="m-select-btn" @click="oneClickSelect(g, gi)">
              <span class="m-select-text"> 一键选择材料 </span>
            </div>
          </div>
          <div class="m-grid">
            <div v-for="(cell, ci) in g.collectionList" :key="ci" class="m-cell" @click="openPicker(g, gi, ci)">
              <div class="m-cell-img">
                <img v-if="cell.id" :src="resolveImg(cell.img)" alt="" />
              </div>
              <span class="m-cell-name"> {{ cell.id ? cell.name : '一键放入材料' }} </span>
              <span class="m-cell-count"> {{ cell.id ? selectedCount(g) : 0 }}/{{ g.number }} </span>
              <span v-if="cell.id" class="m-cell-avail"> 可用{{ availCount(g, cell) }} </span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- 底部：立即合成（fixed，88rpx r19 #0018ff） -->
    <div class="footer">
      <div class="compose-btn" @click="confimPop = true"><span class="compose-btn-text"> 立即合成 </span></div>
    </div>

    <!-- 确认弹窗（w-toast：内容 + 再想想/确认） -->
    <div v-if="confimPop" class="mask" @click.self="confimPop = false">
      <div class="toast-modal">
        <span class="toast-text">{{ popContent }}</span>
        <div class="toast-ft">
          <span class="toast-btn" @click="confimPop = false">再想想</span>
          <span class="toast-btn toast-btn-primary" @click="onConfirm">确定</span>
        </div>
      </div>
    </div>

    <!-- 成功弹窗（u-popup center：恭喜您，合成成功 + 结果图 + 前往查看/继续合成） -->
    <div v-if="showSuccess" class="mask" @click.self="showSuccess = false">
      <div class="success-modal">
        <span class="success-title"> 恭喜您，合成成功 </span>
        <div class="success-body">
          <div class="success-img-box">
            <img v-if="resultImage" class="success-img" :src="resolveImg(resultImage)" alt="" />
          </div>
          <span class="success-label"> {{ resultLabel }} </span>
        </div>
        <div class="success-ft">
          <div class="s-btn s-btn-dark" @click="toMyCollection"><span class="s-btn-text"> 前往查看 </span></div>
          <div class="s-btn s-btn-blue" @click="onContinue"><span class="s-btn-text"> 继续合成 </span></div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { request, resolveImg } from '../../api/request'

/**
 * 原站 chunk 反解（pages-compound-schemeDetails）：
 *  - onLoad(play_id/grp/classify) → playInfo → image/title/description/rule_text/start_time/end_time/user_max
 *  - 失败（玩法不存在，实测）时仅渲染壳：黑图框 + 「合成时间： - 」卡 + 空材料卡 + 立即合成
 *  - 立即合成 → w-toast「您选择了N个藏品，合成成功后将进行销毁/回收/保留，确认合成？」(取消=再想想)
 *  - 确认 → synthesizeNow（mock，推断）→ recordInfo → 成功弹窗（结果图 320rpx 黑底 + 名称）→ 前往查看/继续合成
 *  - isDisabled：所有材料组选满才可合成（原站按钮仍有点击，弹确认）
 */
const route = useRoute()
const router = useRouter()

const confimPop = ref(false)
const showSuccess = ref(false)
const recordId = ref('')
const resultList = ref([])
const resultImage = ref('')
const resultLabel = ref('')
const param = { play_id: '', grp: '', classify: '' }
const userJson = ref([])
const image = ref('')
const title = ref('')
const ruleText = ref([])
const start_time = ref('')
const end_time = ref('')

const ICON_MAP = { yellow: 'hsj', purple: 'zsj', blue: 'lsj', yellow_log: 'hsj', purple_log: 'zsj', blue_log: 'lsj' }

const isDisabled = computed(() => {
  const arr = userJson.value
  const done = arr.filter((g) => g.user_collection_ids && g.user_collection_ids.split(',').length == g.number)
  return arr.length != done.length
})

const popContent = computed(() => {
  // 原站 getPopContent：各组 number 求和
  let num = 0
  try {
    num = userJson.value.map((g) => g.number).reduce((a, b) => Number(a) + Number(b), 0)
  } catch (e) {
    num = 0
  }
  return '您选择了' + num + '个藏品，合成成功后将进行销毁/回收/保留，确认合成？'
})

function getMlData(list) {
  return (list || []).map((g) => ({
    rule_id: g.rule_id,
    collection_ids: g.collection_ids,
    user_collection_ids: (g.collectionList || []).filter((c) => !!c.id).map((c) => c.id).join(),
    number: g.number
  }))
}

function selectedCount(g) {
  return (g.collectionList || []).filter((c) => !!c.id).length
}
function availCount(g, cell) {
  return cell.available_count ?? 0
}

async function getDetails() {
  const res = await request('/api/synthesis/playInfo', { ...param })
  if (res.code === 1 && res.data) {
    const d = res.data
    image.value = d.image || ''
    title.value = d.title || ''
    ruleText.value = d.rule_text || []
    userJson.value = getMlData(ruleText.value)
    start_time.value = d.start_time || ''
    end_time.value = d.end_time || ''
  }
}

function oneClickSelect(g, gi) {
  /* 原站：一键选满该组材料（selectCollection 拉取可用藏品；mock 返回空，无可选藏品） */
}
function openPicker(g, gi, ci) {
  /* 原站：打开藏品选择底部弹窗（w-popup-bottom + checkbox）；mock 数据为空，无可选藏品 */
}

function onConfirm() {
  confimPop.value = false
  submit()
}

async function submit() {
  const res = await request('/api/synthesis/synthesizeNow', { ...param, user_json: btoa(unescape(encodeURIComponent(JSON.stringify(userJson.value)))) })
  if (res.code === 1 && res.data && res.data.recordId) {
    recordId.value = res.data.recordId
    fetchResult(recordId.value)
  }
}

async function fetchResult(id) {
  const res = await request('/api/synthesis/recordInfo', { record_id: id })
  const list = (res.data && res.data.result) || []
  resultList.value = list
  const hit = list.find((x) => x.type === 'C' || x.type === 'M') || list[0]
  resultImage.value = (hit && (hit.main_image || hit.image || hit.listimg)) || image.value
  resultLabel.value = hit ? (hit.name || title.value || '藏品名称') : (title.value || '藏品名称')
  showSuccess.value = true
}

function toMyCollection() {
  showSuccess.value = false
  router.push('/pages/myCollection/myCollection')
}
function onContinue() {
  showSuccess.value = false
  resultList.value = []
  resultImage.value = ''
  resultLabel.value = ''
  recordId.value = ''
  getDetails()
}

getDetails()
</script>

<style scoped src="../../styles/pages/compound-schemeDetails-p0.css"></style>
<style scoped src="../../styles/pages/compound-schemeDetails-p1.css"></style>
