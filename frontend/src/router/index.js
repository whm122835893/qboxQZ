import { createRouter, createWebHashHistory } from 'vue-router'

// 路由表 1:1 对应原站 uni-app pages 路由（共 111 条，从原站主包 index.js 提取）
// 已开发页面映射到真实组件；未开发页面统一落 PendingPage（明确标注待开发，不臆造 UI）
const modules = import.meta.glob('../pages/**/*.vue')
const comp = (p) => modules[`../pages/${p}.vue`] || undefined

const pending = () => import('../pages/common/PendingPage.vue')

const PENDING_ROUTES = [
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
    
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
]

const routes = [
  { path: '/', redirect: '/pages/login/login' },
  { path: '/pages/login/login', component: comp('login/login') || pending },
  { path: '/pages/login/register', component: comp('login/register') || pending },
  { path: '/pages/login/forgetPwd', component: comp('login/forgetPwd') || pending },
  { path: '/pages/login/verification', component: comp('login/verification') || pending },
  { path: '/pages/login/setPwd', component: comp('login/setPwd') || pending },
  { path: '/pages/login/auth', component: comp('login/auth') || pending },
  { path: '/pages/index/index', component: comp('index/index') || pending },
  { path: '/pages/market/market', component: comp('market/market') || pending },
  { path: '/pages/activity/draw', component: comp('activity/draw') || pending },
  { path: '/pages/index/downloadApp', component: comp('index/downloadApp') || pending },
  { path: '/pages/index/lanuch', component: comp('index/lanuch') || pending },
  { path: '/pages/market/marketGoodsList', component: comp('market/marketGoodsList') || pending },
  { path: '/pages/notice/newsDetails', component: comp('notice/newsDetails') || pending },
  { path: '/pages/notice/sendRumor', component: comp('notice/sendRumor') || pending },
  { path: '/pages/notice/wallDetails', component: comp('notice/wallDetails') || pending },
  { path: '/pages/myCollection/collectionDetails', component: comp('myCollection/collectionDetails') || pending },
  { path: '/pages/myCollection/search', component: comp('myCollection/search') || pending },
  { path: '/pages/order/beg/info', component: comp('order/beg/info') || pending },
  { path: '/pages/order/entrust/info', component: comp('order/entrust/info') || pending },
  { path: '/pages/invitation/invitationrecord', component: comp('invitation/invitationrecord') || pending },
  { path: '/pages/activeSelected/activeSelected', component: comp('activeSelected/activeSelected') || pending },
  { path: '/pages/kline/buy', component: comp('kline/buy') || pending },
  { path: '/pages/kline/info', component: comp('kline/info') || pending },
  { path: '/pages/lottery/record', component: comp('lottery/record') || pending },
  { path: '/pages/lottery/rule', component: comp('lottery/rule') || pending },
  { path: '/pages/rebate/leaderboard', component: comp('rebate/leaderboard') || pending },
  { path: '/pages/rebate/rebate', component: comp('rebate/rebate') || pending },
  { path: '/pages/rebate/rebateInviteDetail', component: comp('rebate/rebateInviteDetail') || pending },
  { path: '/pages/rules/rules', component: comp('rules/rules') || pending },
  { path: '/pages/webview/webview', component: comp('webview/webview') || pending },
  { path: '/pages/webview/webview-storage', component: comp('webview/webview-storage') || pending },
  { path: '/pages/leaderboard/leaderboard', component: comp('leaderboard/leaderboard') || pending },
  { path: '/pages/rank/masterRank', component: comp('rank/masterRank') || pending },
  { path: '/pages/rank/userBuy', component: comp('rank/userBuy') || pending },
  { path: '/pages/marketCapRanking/marketCapRanking', component: comp('marketCapRanking/marketCapRanking') || pending },
  { path: '/pages/consignments/consignments', component: comp('consignments/consignments') || pending },
  { path: '/pages/regift/regift', component: comp('regift/regift') || pending },
  { path: '/pages/order/orderSubmit', component: comp('order/orderSubmit') || pending },
  { path: '/pages/order/marketDetail', component: comp('order/marketDetail') || pending },
  { path: '/pages/order/defaultDetail', component: comp('order/defaultDetail') || pending },
  { path: '/pages/order/regiftDetail', component: comp('order/regiftDetail') || pending },
  { path: '/pages/regift/record', component: comp('regift/record') || pending },
  { path: '/pages/activity/record', component: comp('activity/record') || pending },
  { path: '/pages/draw/communityShare/communityShare', component: comp('draw/communityShare/communityShare') || pending },
  { path: '/pages/draw/drawResult/drawResult', component: comp('draw/drawResult/drawResult') || pending },
  { path: '/pages/draw/InvitationFriendAssist/InvitationFriendAssist', component: comp('draw/InvitationFriendAssist/InvitationFriendAssist') || pending },
  { path: '/pages/draw/Leaderboard/Leaderboard', component: comp('draw/Leaderboard/Leaderboard') || pending },
  { path: '/pages/draw/shareCommunityGroup/shareCommunityGroup', component: comp('draw/shareCommunityGroup/shareCommunityGroup') || pending },
  { path: '/pages/market/submitBegBuy', component: comp('market/submitBegBuy') || pending },
  { path: '/pages/market/detail', component: comp('market/detail') || pending },
  { path: '/pages/market/search', component: comp('market/search') || pending },
  { path: '/pages/market/categoryManager', component: comp('market/categoryManager') || pending },
  { path: '/pages/market/giftRecord', component: comp('market/giftRecord') || pending },
  { path: '/pages/market/tradeUnion', component: comp('market/tradeUnion') || pending },
  { path: '/pages/market/batchPay', component: comp('market/batchPay') || pending },
  { path: '/pages/market/submitEntrust', component: comp('market/submitEntrust') || pending },
  { path: '/pages/market/sellBegBuy', component: comp('market/sellBegBuy') || pending },
  { path: '/pages/market/sellInfo', component: comp('market/sellInfo') || pending },
  { path: '/pages/notice/notice', component: comp('notice/notice') || pending },
  { path: '/pages/my/my', component: comp('my/my') || pending },
  { path: '/pages/product/box', component: comp('product/box') || pending },
  { path: '/pages/product/collection', component: comp('product/collection') || pending },
  { path: '/pages/product/ruleDetail', component: comp('product/ruleDetail') || pending },
  { path: '/pages/shop/detail/box', component: comp('shop/detail/box') || pending },
  { path: '/pages/shop/detail/collection', component: comp('shop/detail/collection') || pending },
  { path: '/pages/shop/detail/goods', component: comp('shop/detail/goods') || pending },
  { path: '/pages/shop/detailList', component: comp('shop/detailList') || pending },
  { path: '/pages/shop/exchange', component: comp('shop/exchange') || pending },
  { path: '/pages/shop/record', component: comp('shop/record') || pending },
  { path: '/pages/blindBox/index', component: comp('blindBox/index') || pending },
  { path: '/pages/blindBox/blindBoxDetails', component: comp('blindBox/blindBoxDetails') || pending },
  { path: '/pages/blindBox/openRecord', component: comp('blindBox/openRecord') || pending },
  { path: '/pages/blindBox/result', component: comp('blindBox/result') || pending },
  { path: '/pages/blindBox/result2', component: comp('blindBox/result2') || pending },
  { path: '/pages/compound/task', component: comp('compound/task') || pending },
  { path: '/pages/compound/record', component: comp('compound/record') || pending },
  { path: '/pages/compound/result', component: comp('compound/result') || pending },
  { path: '/pages/compound/schemeDetails', component: comp('compound/schemeDetails') || pending },
  { path: '/pages/compound/compound', component: comp('compound/compound') || pending },
  { path: '/pages/lottery/lottery', component: comp('lottery/lottery') || pending },
  { path: '/pages/sign/index', component: comp('sign/index') || pending },
  { path: '/pages/invitation/invitation', component: comp('invitation/invitation') || pending },
  { path: '/pages/shop/shop', component: comp('shop/shop') || pending },
  { path: '/pages/notice/noticeDetails', component: comp('notice/noticeDetails') || pending },
  { path: '/pages/notice/noticeList', component: comp('notice/noticeList') || pending },
  { path: '/pages/notice/search', component: comp('notice/search') || pending },
  { path: '/pages/my/setting', component: comp('my/setting') || pending },
  { path: '/pages/my/vip', component: comp('my/vip') || pending },
  { path: '/pages/my/about', component: comp('my/about') || pending },
  { path: '/pages/my/join', component: comp('my/join') || pending },
  { path: '/pages/my/service', component: comp('my/service') || pending },
  { path: '/pages/my/privacy', component: comp('my/privacy') || pending },
  { path: '/pages/my/flowDoc', component: comp('my/flowDoc') || pending },
  { path: '/pages/my/cancellation', component: comp('my/cancellation') || pending },
  { path: '/pages/my/enablingcenter', component: comp('my/enablingcenter') || pending },
  { path: '/pages/my/depositinquiry', component: comp('my/depositinquiry') || pending },
  { path: '/pages/wallet/wallet', component: comp('wallet/wallet') || pending },
  { path: '/pages/wallet/huifuDetail', component: comp('wallet/huifuDetail') || pending },
  { path: '/pages/certification/certification', component: comp('certification/certification') || pending },
  { path: '/pages/certification/certificationStatus', component: comp('certification/certificationStatus') || pending },
  { path: '/pages/myCollection/myCollection', component: comp('myCollection/myCollection') || pending },
  { path: '/pages/myCollection/soldAssets', component: comp('myCollection/soldAssets') || pending },
  { path: '/pages/order/list', component: comp('order/list') || pending },
  { path: '/pages/my/address/list', component: comp('my/address/list') || pending },
  { path: '/pages/my/address/edit', component: comp('my/address/edit') || pending },
  { path: '/pages/order/beg/list', component: comp('order/beg/list') || pending },
  { path: '/pages/order/entrust/list', component: comp('order/entrust/list') || pending },
  { path: '/pages/consignments/myConsignment', component: comp('consignments/myConsignment') || pending },
  { path: '/pages/my/actionPwd/setPwd', component: comp('my/actionPwd/setPwd') || pending },
  { path: '/pages/my/info', component: comp('my/info') || pending },
  { path: '/pages/my/actionPwd/getCode', component: comp('my/actionPwd/getCode') || pending },
  ...PENDING_ROUTES.map(p => ({ path: '/' + p, component: comp(p) || pending }))
]

const router = createRouter({
  history: createWebHashHistory(),
  routes
})

export default router
