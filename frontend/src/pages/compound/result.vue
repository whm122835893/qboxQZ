<template>
  <div class="rs-page">
    <!-- 导航栏（绝对定位顶部，684rpx 行） -->
    <div class="navbar">
      <div class="navbar-inner">
        <img class="nav-back" src="/static/qbox/common-arrow-right-black.png" alt="返回" @click="$router.back()" />
        <span class="nav-title"> 合成结果 </span>
        <div class="nav-placeholder"></div>
      </div>
    </div>

    <!-- 内容（padding 200/33/220：result_box 大图 + 结果列表） -->
    <div class="body">
      <img class="result-box" src="/static/compound/result_box.png" alt="合成成功" />
      <div v-if="result.length" class="result-card">
        <div v-for="(item, i) in result" :key="i" class="result-item">
          <span class="item-name"> {{ item.name }} </span>
          <span v-if="item.type === 'C'" class="item-code"> #{{ item.user_collection_code }}/{{ item.total_num }} </span>
          <span v-else-if="item.type === 'M'" class="item-mul"> × {{ item.number }} </span>
          <div v-else-if="item.type === 'S'" class="item-s-row">
            <img class="item-s-icon" :src="'/static/icon/' + (ICON_MAP[item.crystal_type] || 'hsj') + '.png'" alt="" />
            <span class="item-mul"> {{ item.single_quantity }} </span>
          </div>
        </div>
      </div>
    </div>

    <!-- 底部按钮（fixed：我的藏品 #0e0f12 / 再合一次 #0018ff，88rpx r19） -->
    <div class="footer">
      <div class="ft-btn ft-dark" @click="$router.push('/pages/myCollection/myCollection')"><span class="ft-btn-text"> 我的藏品 </span></div>
      <div class="ft-btn ft-blue" @click="$router.back()"><span class="ft-btn-text"> 再合一次 </span></div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useRoute } from 'vue-router'
import { request, resolveImg } from '../../api/request'

/**
 * 原站 chunk 反解（pages-compound-result，整页逻辑完整）：
 *  - onLoad(id) → recordInfo({record_id}) → result/consume
 *  - result 项：name 28 Bold；type C → #code/total_num 24rpx；type M → ×number（AlimamaShuHei）；type S → 水晶图标+single_quantity
 *  - 大图 result_box.png 626rpx widthFix；底部 我的藏品(#0e0f12)/再合一次(#0018ff) 88rpx r19
 * recordInfo 失败（记录不存在，实测）时仅渲染大图 + 底部按钮。
 */
const route = useRoute()
const result = ref([])
const ICON_MAP = { yellow: 'hsj', purple: 'zsj', blue: 'lsj', yellow_log: 'hsj', purple_log: 'zsj', blue_log: 'lsj' }

async function getDetails() {
  const res = await request('/api/synthesis/recordInfo', { record_id: route.query.id || '' })
  if (res.code === 1 && res.data) {
    result.value = res.data.result || []
  }
}
getDetails()
</script>

<style scoped>
.rs-page {
  min-height: 100vh;
  background: #f7f7f7;
  background-image: url('/static/qbox/market-goods-bg.png');
  background-repeat: no-repeat;
  background-size: 100% 549.2px;
  background-position: top center;
}
/* ---- 导航栏（绝对顶，684rpx×72rpx 行） ---- */
.navbar {
  position: absolute;
  top: 0;
  left: 0;
  z-index: 2;
  width: 100%;
  display: flex;
  flex-direction: column;
  align-items: center;
}
.navbar-inner {
  width: 684px;
  height: 116px;
  padding-top: 36px;
  box-sizing: border-box;
  display: flex;
  align-items: center;
  justify-content: space-between;
}
.nav-back {
  width: 24px;
  height: 24px;
  transform: rotate(180deg);
  object-fit: contain;
}
.nav-title {
  flex: 1;
  padding: 0 24px;
  font-size: 30.8px;
  color: #111;
  font-family: var(--font-bold);
  text-align: center;
}
.nav-placeholder {
  width: 24px;
  height: 24px;
}
/* ---- 内容 ---- */
.body {
  width: 100%;
  display: flex;
  flex-direction: column;
  align-items: center;
  padding: 200px 33px 220px;
  box-sizing: border-box;
}
.result-box {
  width: 626px;
  object-fit: contain;
}
.result-card {
  width: 684px;
  margin-top: 40px;
  background: #fff;
  border-radius: 32px;
  padding: 28px;
  box-sizing: border-box;
  display: flex;
  flex-direction: column;
  gap: 16px;
}
.result-item {
  width: 100%;
  background: #f7f7f7;
  border-radius: 20px;
  padding: 24px 28px;
  box-sizing: border-box;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 16px;
}
.item-name {
  font-size: 28px;
  color: #111;
  font-family: var(--font-bold);
}
.item-code {
  font-size: 24px;
  color: #111;
}
.item-mul {
  font-size: 28px;
  color: #111;
  font-family: 'AlimamaFangYuanTiVF-Thin', var(--font-regular);
}
.item-s-row {
  display: flex;
  align-items: center;
  gap: 8px;
}
.item-s-icon {
  width: 40px;
  height: 40px;
  object-fit: contain;
}
/* ---- 底部按钮 ---- */
.footer {
  width: 100%;
  position: fixed;
  left: 0;
  bottom: 0;
  z-index: 10;
  padding: 16px 36px calc(16px + env(safe-area-inset-bottom));
  box-sizing: border-box;
  background: #f7f7f7;
  display: flex;
  align-items: center;
  gap: 32px;
}
.ft-btn {
  flex: 1;
  height: 88px;
  border-radius: 19px;
  display: flex;
  align-items: center;
  justify-content: center;
}
.ft-dark {
  background: #0e0f12;
}
.ft-blue {
  background: #0018ff;
}
.ft-btn-text {
  font-size: 30px;
  color: #fff;
  font-family: var(--font-medium);
}
</style>
