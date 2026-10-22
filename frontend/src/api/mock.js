import { MOCKS } from './mock-data'
import { getToken } from './request'

/**
 * Mock 路由：路径与原站 API 完全一致。
 * 行为还原原站实测：
 *  - /api/user/login 正确账号密码返回登录成功，错误密码返回 {code:0,msg:'密码不正确'}（原站实测）
 */
// 用户资料存储（内存态）：POST /api/user/profile 修改昵称/头像后，getUserInfo 动态返回
const profileStore = { nickname: null, avatar: null }

// 地址存储（内存态）：原站 address_list 实测为空数组；edit_address/delUserAddress
// 未抓包（避免向原站账号写入数据），行为按接口名语义实现，已在 API 文档标注推断
const addressListStore = []

// 站点配置存储：原站 POST /api/index/getConfig {key:"vip_agreement,vip_time,vip_service_charge"}
// 抓包实录（2026-10-21）：vip_time=2678400(31天/秒)、vip_service_charge=29.9、vip_agreement 为购买说明HTML
const configStore = {
  vip_time: '2678400',
  vip_service_charge: '29.9',
  vip_agreement:
    '<p>1. 开通 QBOX VIP 即表示您知悉并同意会员服务条款，支付成功后权益立即生效，有效期以开通时页面展示为准。</p>' +
    '<p>2. 会员权益含专属藏品空投、优先购买、批量购买及专属客服等服务，具体内容以平台公示及实际发放规则为准。</p>' +
    '<p>3. VIP 为虚拟数字商品，一经开通不支持退款或转让，请确认使用本人实名认证账户完成支付。</p>' +
    '<p>4. 平台有权根据运营需要调整权益内容并提前公告；请理性消费，谨防诈骗，勿轻信非官方渠道信息。</p>'
}

export function mockRequest(path, data = {}) {
  // 登录接口需要动态校验，不能直接回放静态响应
  if (path === '/api/user/login') {
    if (data.account === '17587881293' && data.password === 'whm981004') {
      const raw = JSON.parse(MOCKS['/api/user/login'])
      raw.data.token = 'mock-token-' + Date.now()
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
  if (path === '/api/order/createVIPOrder') {
    // 原站下单接口未抓包（避免向原站账号真实下单），按接口语义返回订单号，已在 API 文档标注推断
    return { code: 1, msg: '下单成功', time: String(Math.floor(Date.now() / 1000)),
      data: { order_sn: 'VIPORDER' + Date.now(), order_id: Date.now() } }
  }
  if (path === '/api/synthesis/recordList') {
    // 合成活动记录（原站实测返回空，2026-10-23 抓包）
    return { code: 1, msg: 'successfully !!!', time: String(Math.floor(Date.now() / 1000)),
      data: { total: 0, per_page: 15, current_page: 1, last_page: 0, data: [] } }
  }
  if (path === '/api/synthesis/recordInfo') {
    // 合成结果详情（原站直接访问实测：记录不存在）
    return { code: 0, msg: '记录不存在', time: String(Math.floor(Date.now() / 1000)), data: null }
  }
  if (path === '/api/synthesis/playInfo') {
    // 合成玩法详情（原站直接访问实测：玩法不存在）
    return { code: 0, msg: '玩法不存在', time: String(Math.floor(Date.now() / 1000)), data: null }
  }
  if (path === '/api/synthesis/selectCollection') {
    // 选择材料藏品（未抓包，避免向原站账号写入；按接口语义返回空列表，已在 API 文档标注推断）
    return { code: 1, msg: 'successfully !!!', time: String(Math.floor(Date.now() / 1000)), data: [] }
  }
  if (path === '/api/synthesis/synthesizeNow') {
    // 立即合成（未抓包，避免向原站账号真实合成；按调用链语义返回 recordId，结果详情仍走 recordInfo mock，标注推断）
    return { code: 1, msg: '合成成功', time: String(Math.floor(Date.now() / 1000)), data: { recordId: 'MOCK' + Date.now() } }
  }
  if (path.startsWith('/api/box/blind_box/BoxDetail/ids/')) {
    // 盲盒商品详情（原站直接访问无 id 实测：找不到该盲盒）
    return { code: 0, msg: '找不到该盲盒，或者已经下架了', time: String(Math.floor(Date.now() / 1000)), data: null }
  }
  if (path.startsWith('/api/box/collection/ProductDetail/ids/')) {
    // 藏品商品详情（原站直接访问无 id 实测：找不到该商品）
    return { code: 0, msg: '找不到该商品，或者已经下架了', time: String(Math.floor(Date.now() / 1000)), data: null }
  }
  if (path === '/api/user_collection/user_collection/getRecord') {
    // 流转记录（原站直接访问无藏品 id 实测：参数错误）
    return { code: 0, msg: '参数错误', time: String(Math.floor(Date.now() / 1000)), data: null }
  }
  if (path === '/api/lottery/record') {
    // 抽奖记录（原站实测 {type:10,page:1} → total:0）
    return { code: 1, msg: 'successfully !!!', time: String(Math.floor(Date.now() / 1000)), data: { total: 0, per_page: 10, current_page: 1, last_page: 0, data: [] } }
  }
  if (path === '/api/user/commison_rank' || path === '/api/user/commision_info' || path === '/api/user/bei_yao_list') {
    // 返利组接口（原站直接访问实测均 404）
    return { code: 404, msg: 'An error occurred', time: Date.now(), data: null }
  }
  if (path === '/api/order/order/begOrderInfo' || path === '/api/order/order/entrustOrderInfo') {
    // 求购/委托订单详情（原站无参数直接访问实测：字段空 + 支付时间 Invalid Date）
    return { code: 1, msg: 'successfully !!!', time: String(Math.floor(Date.now() / 1000)), data: null }
  }
  if (path === '/api/Activity/category') {
    // 活动分类（原站实测 data:[]）
    return { code: 1, msg: '分类列表', time: String(Math.floor(Date.now() / 1000)), data: [] }
  }
  if (path === '/api/draw/get') {
    // 抽签活动详情（原站直接访问实测：活动不存在 → 页面渲染纯白）
    return { code: 0, msg: '活动不存在', time: String(Math.floor(Date.now() / 1000)), data: null }
  }
  if (path === '/api/draw/drawRecord' || path === '/api/draw/drawLog') {
    // 抽奖记录/中签记录（原站实测返回空数组）
    return { code: 1, msg: '获取成功', time: String(Math.floor(Date.now() / 1000)), data: [] }
  }
  if (path === '/api/market/market/getMarketList') {
    // 市场搜索（原站实测空：{"goods_type":2,"keywords":null} → data:[]）
    return { code: 1, msg: '操作成功', time: String(Math.floor(Date.now() / 1000)), data: [] }
  }
  if (path === '/api/market/trade/tradingInformation') {
    // 赠送记录（原站实测 list 空 + 计数字段）
    return { code: 1, msg: '交易信息', time: String(Math.floor(Date.now() / 1000)),
      data: { list: [], transaction_record_number: 0, current_buying: 0, current_sale: 0 } }
  }
  if (path.startsWith('/api/market/market/MarketDetail/ids/')) {
    // 市场藏品详情（原站直接访问无 id 实测：已售出）
    return { code: 0, msg: '已售出', time: String(Math.floor(Date.now() / 1000)), data: null }
  }
  if (path === '/api/market/trade/collectionDetail') {
    // 发起求购·藏品详情（原站直接访问无 id 实测）
    return { code: 0, msg: '藏品ID不能为空', time: String(Math.floor(Date.now() / 1000)), data: null }
  }
  if (path === '/api/market/Prepayment/collectionDetail') {
    // 委托购买·藏品详情（原站直接访问无 id 实测）
    return { code: 0, msg: '藏品ID不能为空', time: String(Math.floor(Date.now() / 1000)), data: null }
  }
  if (path === '/api/market/trade/getSellCollectionInfo') {
    // 出售求购单·藏品信息（原站直接访问实测）→ 页面重定向回首页
    return { code: 0, msg: '藏品不存在', time: String(Math.floor(Date.now() / 1000)), data: null }
  }
  if (path === '/api/market/market/marketGoodsDetail') {
    // 市场商品详情（原站直接访问实测 type:2 → 500）→ 页面重定向回首页
    return { code: 500, msg: 'An error occurred', time: String(Math.floor(Date.now() / 1000)), data: null }
  }
  if (path === '/api/market/trade/createBegBuyOrder' || path === '/api/market/Prepayment/createOrder') {
    // 创建求购/委托订单（未抓包，避免向原站账号真实下单），按调用链语义返回订单号，已在 API 文档标注推断
    return { code: 1, msg: '下单成功', time: String(Math.floor(Date.now() / 1000)),
      data: { order_id: Date.now(), order_sn: 'MK' + Date.now() } }
  }
  if (path === '/api/crystal_goods/crystalDetail' || path === '/api/crystal_goods/exchangeRecord') {
    // 龙晶明细 / 兑换记录（原站实测均返回空，2026-10-23 抓包）
    return { code: 1, msg: 'successfully !!!', time: String(Math.floor(Date.now() / 1000)),
      data: { total: 0, per_page: 15, current_page: 1, last_page: 0, data: [] } }
  }
  if (path.startsWith('/api/user_collection/user_collection/detail/ids/')) {
    // 盲盒详情（原站直接访问无 id 实测）：{"code":0,"msg":"收藏不存在或者已经被删除了","data":null}
    return { code: 0, msg: '收藏不存在或者已经被删除了', time: String(Math.floor(Date.now() / 1000)), data: null }
  }
  if (path === '/api/box/blind_box/openlog') {
    // 开盒记录（原站实测返回空）：{"total":0,"per_page":"20","current_page":1,"last_page":0,"data":[]}
    return { code: 1, msg: '开盲盒记录', time: String(Math.floor(Date.now() / 1000)),
      data: { total: 0, per_page: '20', current_page: 1, last_page: 0, data: [] } }
  }
  if (path === '/api/user_collection/user_collection/openbox' || path === '/api/user_collection/user_collection/openBoxs') {
    // 拆盒接口未抓包（避免向原站账号真实拆盒），按调用链语义返回 code:1 + data:null：
    // 原站 .then 后把 data 写入 storage 再跳结果页，data 为空时结果页即「藏品编号：#」空态（与原站直接访问结果页观测一致）
    return { code: 1, msg: '拆盒成功', time: String(Math.floor(Date.now() / 1000)), data: null }
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
  const key = Object.keys(MOCKS).find(k => path.startsWith(k) || k.startsWith(path))
  if (key && MOCKS[key]) {
    const v = MOCKS[key]
    return typeof v === 'string' ? JSON.parse(v) : v
  }
  return { code: 0, msg: 'mock 未覆盖该接口: ' + path, data: null }
}
