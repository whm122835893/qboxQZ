/**
 * Mock 路由 · 其他域
 * 合成 / 盲盒 / 抽奖 / 活动 / 龙晶 / 藏品流转 / 返利组 / 抽签
 * 路径与原站 API 完全一致，行为还原原站实测结果。
 */


/**
 * 路由分发：命中返回响应体，未命中返回 undefined 交由上层继续匹配
 */
import { getCollection, myCollectionIds } from '../data/collections'

export function handleMiscRoute(path, data = {}) {
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
    // 藏品商品详情（product/collection 页数据源）：
    //  按 id 从唯一藏品数据源查询；查无 → 源站实测错误形态（无 id 直接访问）
    const pid = path.split('/ids/')[1]
    const g = pid && pid !== 'undefined' && pid !== 'null' ? getCollection(pid) : null
    if (!g) {
      return { code: 0, msg: '找不到该商品，或者已经下架了', time: String(Math.floor(Date.now() / 1000)), data: null }
    }
    return { code: 1, msg: 'successfully !!!', time: String(Math.floor(Date.now() / 1000)), data: g }
  }
  if (path === '/api/user_collection/user_collection/antMycollection') {
    // 仓库藏品（我的收藏）：从唯一藏品数据源按 myCollectionIds 派生。
    //  源站实测（2026-10-25 登录态）：当前账号收藏为空（total:0），派生列表保持为空与源站一致；
    //  后续收藏写入后在此追加藏品 id 即可，页面数据自动跟随唯一数据源。
    const ids = myCollectionIds()
    const rows = ids.map((id) => getCollection(id)).filter(Boolean)
    return { code: 1, msg: '获取成功', time: String(Math.floor(Date.now() / 1000)),
      data: { total: rows.length, per_page: 15, current_page: 1, last_page: rows.length ? 1 : 0,
        data: rows, current_count: rows.length, current_consignment: 0 } }
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
  if (path === '/api/crystal_goods/crystalDetail' || path === '/api/crystal_goods/exchangeRecord') {
    // 龙晶明细 / 兑换记录（原站实测均返回空，2026-10-23 抓包）
    return { code: 1, msg: 'successfully !!!', time: String(Math.floor(Date.now() / 1000)),
      data: { total: 0, per_page: 15, current_page: 1, last_page: 0, data: [] } }
  }
  if (path.startsWith('/api/user_collection/user_collection/detail/ids/')) {
    // 盲盒详情：源站实测无 id 时返回 {"code":0,"msg":"收藏不存在或者已经被删除了","data":null}
    const cid = path.split('/ids/')[1]
    if (cid && cid !== 'undefined' && cid !== 'null') {
      // 有 id 时按持仓列表注入的演示持仓（id=1）返回藏品详情
      return { code: 1, msg: 'successfully !!!', time: String(Math.floor(Date.now() / 1000)), data: {
        id: Number(cid),
        goods_name: 'KUUKIE 怪物口袋·黑熊',
        cover: '/uploads/20261013/891305447d1a31e997fd7a0e078488a5.png',
        issue_num: 5000,
        limit_num: 5000,
        price: '39.90',
        owner: 'Q9GSWCuI2',
        cert_time: '2026.10.24',
        creator: 'QBOX',
        intro: 'KUUKIE 怪物口袋系列数字藏品，以街头潮流文化为灵感，每一只怪物都有独一无二的性格与故事。'
      } }
    }
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
  return undefined
}
