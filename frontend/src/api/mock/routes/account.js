/**
 * Mock 路由 · 账户域
 * 登录 / 注册 / 重置密码 / 用户资料 / 短信 / 支付密码 / 站点配置 / 收货地址
 * 路径与原站 API 完全一致，行为还原原站实测结果。
 */
import { MOCKS } from '../../mock-data'
import { profileStore, addressListStore, configStore } from '../state'

/**
 * 路由分发：命中返回响应体，未命中返回 undefined 交由上层继续匹配
 */
export function handleAccountRoute(path, data = {}) {
  if (path === '/api/user/login') {
    if (data.account === '17587881293' && data.password === 'whm981004') {
      const raw = JSON.parse(MOCKS['/api/user/login'])
      // 源站实测：token 位于 data.userinfo.token，每次登录签发新 uuid
      raw.data.userinfo.token = 'mock-' + Date.now() + '-' + Math.floor(Math.random() * 1e6)
      return raw
    }
    return { code: 0, msg: '密码不正确', time: String(Math.floor(Date.now() / 1000)), data: null }
  }
  if (path === '/api/index/afficheList' && data.search) {
    // 公告搜索结果（原站 afficheList {search:"QBOX"} 抓包实录，2026-10-21）
    return { code: 1, msg: '公告列表', time: String(Math.floor(Date.now() / 1000)), data: {
      total: 2, per_page: 15, current_page: 1, last_page: 1,
      data: [
        { id: 5, image: '', title: '【QBOX活动」QBOX数字艺术签到福利来袭～', createtime: 1792581028, type: '1', url: '', affiche_type: '活动' },
        { id: 3, image: '', title: '【QBOX官方】平台现已开放IOS、安卓下载入口！', createtime: 1792490989, type: '1', url: '', affiche_type: '官方' }
      ]
    } }
  }
  if (path === '/api/user/register') {
    return { code: 1, msg: '注册成功', time: String(Math.floor(Date.now() / 1000)), data: null }
  }
  if (path === '/api/user/resetpwd') {
    return { code: 1, msg: '重置成功', time: String(Math.floor(Date.now() / 1000)), data: null }
  }
  if (path === '/api/user/profile') {
    if (data.nickname !== undefined) profileStore.nickname = data.nickname
    if (data.avatar !== undefined) profileStore.avatar = data.avatar
    return { code: 1, msg: '修改成功', time: String(Math.floor(Date.now() / 1000)), data: null }
  }
  if (path === '/api/user/getUserInfo') {
    const key = Object.keys(MOCKS).find(k => k.includes('getUserInfo'))
    const base = key && MOCKS[key] ? (typeof MOCKS[key] === 'string' ? JSON.parse(MOCKS[key]) : MOCKS[key]) : { code: 1, data: {} }
    if (base.data) {
      if (profileStore.nickname) base.data.nickname = profileStore.nickname
      if (profileStore.avatar) base.data.avatar = profileStore.avatar
      // 原站账号已设操作密码（原站直接访问 setPwd 显示设置表单而非跳转短信验证），
      // mock 数据缺 pay_password 字段会导致 setPwd 页误跳 getCode，这里补齐（已在 API 文档登记）
      if (base.data.pay_password === undefined) base.data.pay_password = 1
    }
    return base
  }
  if (path === '/api/sms/send') {
    // 独立项目：mock 阶段验证码固定 123456（与登录 mock 同一约定，已在 API 文档登记）
    return { code: 1, msg: '发送成功', time: String(Math.floor(Date.now() / 1000)), data: null }
  }
  if (path === '/api/user/setPayPassword') {
    return { code: 1, msg: '设置成功', time: String(Math.floor(Date.now() / 1000)), data: null }
  }
  if (path === '/api/user/checkPaypass') {
    // 原站返回 data.code 用于换取 setPwd 跳转参数；mock 固定返回（接口无真实提交，已标注推断）
    return { code: 1, msg: '校验成功', time: String(Math.floor(Date.now() / 1000)), data: { code: '123456' } }
  }
  if (path === '/api/index/getConfig') {
    // 按 key 逗号分隔返回对应配置（原站行为：data 里只含请求的 key）
    const keys = String(data.key || '').split(',').filter(Boolean)
    const out = {}
    keys.forEach((k) => { if (configStore[k] !== undefined) out[k] = configStore[k] })
    return { code: 1, msg: '基本配置', time: String(Math.floor(Date.now() / 1000)), data: out }
  }
  if (path === '/api/user/address_list') {
    return { code: 1, msg: '地址列表', time: String(Math.floor(Date.now() / 1000)), data: addressListStore }
  }
  if (path === '/api/user/edit_address') {
    if (data.id) {
      const i = addressListStore.findIndex(a => a.id === data.id)
      if (i >= 0) {
        if (data.isdef === 1) addressListStore.forEach(a => { a.isdef = 0 })
        addressListStore[i] = { ...addressListStore[i], ...data }
      }
    } else {
      if (data.isdef === 1) addressListStore.forEach(a => { a.isdef = 0 })
      addressListStore.push({ ...data, id: Date.now() })
    }
    return { code: 1, msg: '保存成功', time: String(Math.floor(Date.now() / 1000)), data: null }
  }
  if (path === '/api/user/delUserAddress') {
    const i = addressListStore.findIndex(a => a.id === data.id)
    if (i >= 0) addressListStore.splice(i, 1)
    return { code: 1, msg: '删除成功', time: String(Math.floor(Date.now() / 1000)), data: null }
  }
  if (path === '/api/user/address_detail') {
    return { code: 1, msg: '详情', time: String(Math.floor(Date.now() / 1000)), data: addressListStore.find(a => a.id === data.id) || null }
  }
  return undefined
}
