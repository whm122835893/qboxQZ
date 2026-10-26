/**
 * Mock 路由 · 市场域
 * 市场列表 / 今日热榜 / 挂单详情 / 挂单列表 / 交易信息 / 赠送记录 / 藏品详情
 * 路径与原站 API 完全一致，行为还原原站实测结果。
 */
import { getToken } from '../../request'
import { SOURCE_LEADERBOARD } from '../data/market-series'
import {
  allCollections,
  collectionsBySeries,
  getCollection,
  getListing,
  listingsByGoods
} from '../data/collections'
import { SOURCE_GIFT_RECORDS } from '../data/gift'
import { relativeTime } from '../data/orders'

/**
 * 路由分发：命中返回响应体，未命中返回 undefined 交由上层继续匹配
 */
export function handleMarketRoute(path, data = {}) {
  if (path === '/api/market/market/getMarketList') {
    // 市场列表：全部从唯一藏品数据源 collections.js 派生（禁止独立写死数据）
    //  原站实测（2026-10-26）：板块归属见 collections.js 的 SERIES_BOARD_MAP；
    //  不带 series_id（搜索页）→ 返回全部非演示藏品；查无数据返回空数组。
    const sid = data.series_id
    let list = []
    if (sid === undefined || sid === null || sid === '' || sid === 0 || sid === '0') {
      list = allCollections().filter((c) => c.is_demo !== true)
    } else {
      list = collectionsBySeries(sid)
    }
    return { code: 1, msg: '操作成功', time: String(Math.floor(Date.now() / 1000)), data: list }
  }
  if (path === '/api/market/market/marketrebang') {
    // 原站实测（2026-10-24 22:25 登录态榜单页 GOOD 今日热榜）：
    // 返回 6 条真实藏品榜单，字段沿用原站（name / listimg / total_num 发行 / flux 流通 / min_price 地板价 / hotnum / is_like）
    return { code: 1, msg: 'ok', time: String(Math.floor(Date.now() / 1000)), data: SOURCE_LEADERBOARD }
  }
  if (path === '/api/market/trade/tradingInformation') {
    // 赠送记录（原站实测 2026-10-25 登录态：list 有 20 条真实流水；
    // 未登录时源站跳登录页，此处返回空 list 由前端做登录态拦截）
    // 源站 time 字段由 createtime 实时换算（<1h→分钟前 / <24h→小时前 / 否则天前），
    // 故此处按同一规则动态计算，保证随时间推移与源站表现一致
    const isGift = Number(data.status) === 4
    const list = isGift && getToken()
      ? SOURCE_GIFT_RECORDS.map((it) => ({ ...it, time: relativeTime(it.createtime) }))
      : []
    return { code: 1, msg: '交易信息', time: String(Math.floor(Date.now() / 1000)),
      data: { list, transaction_record_number: list.length, current_buying: 0, current_sale: 0 } }
  }
  if (path === '/api/market/trade/collectionDetail') {
    // 发起求购·藏品详情：按 goods_id 从唯一数据源查询；无 id → 源站实测错误形态
    const g = data.goods_id ? getCollection(data.goods_id) : null
    if (!g) return { code: 0, msg: '藏品ID不能为空', time: String(Math.floor(Date.now() / 1000)), data: null }
    return { code: 1, msg: '操作成功', time: String(Math.floor(Date.now() / 1000)), data: g }
  }
  if (path === '/api/market/Prepayment/collectionDetail') {
    // 委托购买·藏品详情：同上（唯一数据源）
    const g2 = data.goods_id ? getCollection(data.goods_id) : null
    if (!g2) return { code: 0, msg: '藏品ID不能为空', time: String(Math.floor(Date.now() / 1000)), data: null }
    return { code: 1, msg: '操作成功', time: String(Math.floor(Date.now() / 1000)), data: g2 }
  }
  if (path.startsWith('/api/market/market/MarketDetail/ids/')) {
    // 寄售（挂单）详情：POST /api/market/market/MarketDetail/ids/{挂单id}
    // 从唯一数据源派生：挂单表 getListing(挂单id) → 所属藏品 getCollection(goods_id)
    // 字段映射以源站实测为准（2026-10-26 挂单 16515）：合约地址 ← user_hash_text，
    // 认证标识 ← rz_code，编号 ← collection_code；status 1=在售（立即购买）4=锁单中
    const sid = path.split('/ids/')[1]
    if (sid && sid !== 'undefined' && sid !== 'null') {
      const row = getListing(sid)
      if (!row) {
        return { code: 500, msg: 'An error occurred', time: String(Math.floor(Date.now() / 1000)), data: null }
      }
      const g = getCollection(row.goods_id) || {}
      const price = row.price
      const code = row.collection_code
      const st = row.status
      const fee = (price * 0.05).toFixed(2)
      return { code: 1, msg: '商品详情', time: String(Math.floor(Date.now() / 1000)), data: {
        id: Number(sid),
        sale_user_id: 24255,
        sale_user_nickname: null,
        sale_user_collection_id: 551940,
        series_id: g.series_id ?? -1,
        goods_type: 2,
        goods_id: g.id ?? row.goods_id,
        price,
        status: st,
        sale_time: null,
        buy_user_id: 0,
        pay_platform_service_rate: '0.05',
        platform_service_price: fee,
        creator_service_price: '0.00',
        real_income: (price - Number(fee)).toFixed(2),
        createtime: row.createtime,
        updatetime: row.createtime,
        deleted: 0,
        buy_version: 3,
        order_sn: '2026102523414910099979229234',
        goods_name: g.name || '',
        goods_cover_image: g.main_image || g.listimg || '',
        series_name: g.series?.name || (Number(g.series_id) === -1 ? '推荐' : ''),
        income_type: 'huifu',
        user: { id: 24255, nickname: 'db****', avatar: '/defautAvatar.png', mobile: '130****9956', url: '/u/24255' },
        img: '',
        name: '',
        rush_status: 0,
        user_collection: {
          id: 551940,
          collection_code: code,
          user_hash_text: g.user_hash_text || '5Td4Ao****',
          user_hash: g.user_hash || '5Td4Ao****',
          zuzhao_info_id: null,
          task_id: g.task_id || '5Td4Ao7F7Bd4Yg3H',
          rz_code: g.rz_code || 26984
        },
        product: {
          id: g.id,
          series_id: g.series_id,
          issuer_id: 0,
          flux: g.flux,
          total_num: g.total_num,
          price: g.price || '0.00',
          name: g.name,
          types: 1,
          compound_num: 1,
          release_time: g.release_time,
          purchase_information: g.purchase_information || '                            ',
          main_image: g.main_image || g.listimg || '',
          listimg: g.listimg || '',
          publisher: g.publisher || { name: 'qbox', company_logo: '/uploads/20240118/17b679f0562f639f96344f08e59e0729.jpg', user: [] },
          imageinfo: [],
          issuer: null
        },
        pay_text: 0
      } }
    }
    return { code: 500, msg: 'An error occurred', time: String(Math.floor(Date.now() / 1000)), data: null }
  }
  if (path === '/api/index/customSeriesList') {
    // 分类管理页·自定义/平台分类（原站 2026-10-25 登录态实测：两个数组均为空）
    return { code: 1, msg: 'successfully!!!', time: String(Math.floor(Date.now() / 1000)),
      data: { custom_series: [], platform_series: [] } }
  }
  if (path === '/api/market/market/getMarketGoodsListByGoodsId') {
    // 市场藏品列表·挂单明细（源站 2026-10-25 23:4x 登录态实测）
    //  - 无 id → list 空 / count 0 / ltnum 0
    //  - 带 id=14 → 6 条真实挂单（暴富有道 #387 ¥9999 起），income_type=[huifu] 表示支付钱包 A钱包
    const gid = data.id
    if (gid === undefined || gid === null || gid === '' || gid === 0) {
      return { code: 1, msg: '操作成功', time: String(Math.floor(Date.now() / 1000)),
        data: { list: [], count: 0, ltnum: 0 } }
    }
    // 挂单列表：从唯一数据源按 goods_id 派生（源站按价格升序返回，数据源已按升序存放）
    const rows = listingsByGoods(gid)
    return { code: 1, msg: '操作成功', time: String(Math.floor(Date.now() / 1000)),
      data: { list: rows, count: rows.length, ltnum: 0 } }
  }
  if (path === '/api/market/trade/getSellCollectionInfo') {
    // 出售求购单·藏品信息（原站直接访问实测）→ 页面重定向回首页
    return { code: 0, msg: '藏品不存在', time: String(Math.floor(Date.now() / 1000)), data: null }
  }
  if (path === '/api/market/market/marketGoodsDetail') {
    // 藏品详情：按 id 从唯一数据源查询渲染（禁止写死/随机数据）。
    //  响应字段沿用源站实测形态（name/flux/total_num/min_price/max_price/main_image/series…）；
    //  查无此 id → 源站错误形态（code 500 / An error occurred）
    const gid = data.id
    const g = gid !== undefined && gid !== null && gid !== '' ? getCollection(gid) : null
    if (!g) {
      return { code: 500, msg: 'An error occurred', time: String(Math.floor(Date.now() / 1000)), data: null }
    }
    return { code: 1, msg: '操作成功', time: String(Math.floor(Date.now() / 1000)), data: {
      id: g.id,
      issuer_id: 0,
      flux: g.flux,
      face_status: 0,
      series_id: g.series_id,
      name: g.name,
      introduce_image: g.introduce_image || '',
      main_image: g.main_image || g.listimg || '',
      total_num: g.total_num,
      price: g.price || 0,
      sale_total_num: 0,
      release_time: g.release_time || 0,
      ant_chain: null,
      purchasing_num: 0,
      buyneed: null,
      listimg: g.listimg || '',
      soldout: 0,
      '3d_url_type': 'img',
      '3d_url': g.main_image || g.listimg || '',
      purchase_information: g.purchase_information || '                            ',
      batch_pay: g.batch_pay || 0,
      batch_pay_num: g.batch_pay_num || 9,
      collection_level: g.collection_level || '',
      is_img_diff: 0,
      reprice_limit: g.reprice_limit || '99999.00',
      is_like: 0,
      sell: g.sell || 0,
      is_has: 0,
      min_price: g.min_price,
      is_out: g.is_out || 0,
      yibao_count: 0,
      huifu_count: 0,
      buy: g.buy || 0,
      ll_count: 0,
      batch_sell: 1,
      key: g.key || '',
      prepaymen_pay: 0,
      max_price: g.max_price || g.reprice_limit || '99999.00',
      hot_count: 0,
      issuer: null,
      series: g.series || { id: g.series_id, name: g.series_name || '', pattern: 1 },
      batch: null
    } }
  }
  return undefined
}
