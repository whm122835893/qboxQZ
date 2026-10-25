/**
 * Mock 路由 · 市场域
 * 市场列表 / 今日热榜 / 挂单详情 / 挂单列表 / 交易信息 / 赠送记录 / 藏品详情
 * 路径与原站 API 完全一致，行为还原原站实测结果。
 */
import { getToken } from '../../request'
import { SOURCE_LEADERBOARD } from '../data/market-series'
import { SOURCE_MARKET_ALL_LIST } from '../data/market-all'
import { SOURCE_BOARD_BREAK_LIST } from '../data/market-board-break'
import { SOURCE_BOARD_CULTURE_LIST } from '../data/market-board-culture'
import { SOURCE_MARKET_GOODS_LIST } from '../data/market-goods'
import { SOURCE_MARKET_GOODS_DETAIL } from '../data/market-goods-detail'
import { SOURCE_GIFT_RECORDS } from '../data/gift'
import { relativeTime } from '../data/orders'

/**
 * 路由分发：命中返回响应体，未命中返回 undefined 交由上层继续匹配
 */
export function handleMarketRoute(path, data = {}) {
  if (path === '/api/market/market/getMarketList') {
    // 原站实测（2026-10-25 15:15 登录态抓包）：
    //  - series_id=-1（推荐，市场页默认选中）→ 当前无数据 → 返回空
    //  - series_id=11（破茧）→ 1 条真实藏品 小黑蛇
    //  - series_id=2（经典文化）→ 7 条真实藏品
    //  - 不带 series_id（搜索页）→ 返回全站 8 条真实藏品 SOURCE_MARKET_ALL_LIST
    //  字段与原站一致（name / listimg / total_num 发行 / flux 流通 / deal_num 成交 /
    //  min_price 地板价 / market_fluctuate_new 涨跌幅 / is_out 退市标记）。
    //  原站板块卡展示的价格 = 该板块藏品地板价均值（已在 market.vue 计算）。
    const sid = data.series_id
    let list = []
    if (sid === 11 || sid === '11') {
      list = SOURCE_BOARD_BREAK_LIST
    } else if (sid === 2 || sid === '2') {
      list = SOURCE_BOARD_CULTURE_LIST
    } else if (sid === undefined || sid === null || sid === '' || sid === 0 || sid === '0') {
      list = SOURCE_MARKET_ALL_LIST
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
    // 发起求购·藏品详情（原站直接访问无 id 实测）
    return { code: 0, msg: '藏品ID不能为空', time: String(Math.floor(Date.now() / 1000)), data: null }
  }
  if (path === '/api/market/Prepayment/collectionDetail') {
    // 委托购买·藏品详情（原站直接访问无 id 实测）
    return { code: 0, msg: '藏品ID不能为空', time: String(Math.floor(Date.now() / 1000)), data: null }
  }
  if (path.startsWith('/api/market/market/MarketDetail/ids/')) {
    // 挂单（寄售）详情：源站实测 GET /api/market/market/MarketDetail/ids/{挂单id}
    // 源站点击市场藏品列表挂单行后进入 /pages/market/detail?id={挂单id}，status=4 → 底部「锁定中」
    const sid = path.split('/ids/')[1]
    if (sid && sid !== 'undefined' && sid !== 'null') {
      // 按挂单 id 派生详情（源站实测：id=6385 → 编号 #27 / ¥310 / status=1 / 平台服务费 5%）
      const row = SOURCE_MARKET_GOODS_LIST.find((r) => String(r.id) === String(sid))
      const price = row ? row.price : 315
      const code = row ? row.collection_code : 564
      const st = row ? row.status : 4
      const fee = (price * 0.05).toFixed(2)
      return { code: 1, msg: '商品详情', time: String(Math.floor(Date.now() / 1000)), data: {
        id: Number(sid),
        sale_user_id: 22523,
        sale_user_nickname: null,
        sale_user_collection_id: 551117,
        series_id: 12,
        goods_type: 2,
        goods_id: 13,
        price,
        status: st,
        sale_time: null,
        buy_user_id: 0,
        pay_platform_service_rate: '0.05',
        platform_service_price: fee,
        creator_service_price: '0.00',
        real_income: (price - Number(fee)).toFixed(2),
        createtime: 1792864045,
        updatetime: 1792864045,
        deleted: 0,
        buy_version: 3,
        order_sn: '2026102501472510055575815684',
        goods_name: 'QBOX赛博龙',
        goods_cover_image: '/uploads/20261025/7c674d998b7e86b520f0930c18825d3c.jpg',
        series_name: '全部区',
        income_type: 'huifu',
        user: {
          id: 18516,
          nickname: 'db****',
          avatar: '/defautAvatar.png',
          mobile: '131****1615',
          url: '/u/18516'
        },
        user_collection: {
          id: 551117,
          collection_code: code,
          user_hash_text: '4Av1Ar****',
          user_hash: '4Av1Arxxxx',
          rz_code: 24487,
          task_id: '4Av1Ar1P5Wv5Iz9P'
        },
        product: {
          id: 13,
          series_id: 12,
          issuer_id: 0,
          flux: 1000,
          total_num: 1000,
          price: '0.00',
          collection_code: code,
          contract_address: '4Av1ArQxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx',
          cert_id: 24487,
          name: 'QBOX赛博龙',
          types: 1,
          compound_num: 1,
          release_time: 1792859520,
          purchase_information: '                            ',
          main_image: '/uploads/20261025/7c674d998b7e86b520f0930c18825d3c.jpg',
          listimg: '/uploads/20261025/7c674d998b7e86b520f0930c18825d3c.jpg',
          publisher: {
            name: 'qbox',
            company_logo: '/uploads/20240118/17b679f0562f639f96344f08e59e0729.jpg',
            user: []
          },
          imageinfo: [],
          issuer: null
        }
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
    // 市场藏品列表·挂单明细（源站 2026-10-25 15:11 登录态实测）
    //  - 无 id → list 空 / count 0 / ltnum 0
    //  - 带 id=13 → 17 条真实挂单，income_type=[huifu] 表示支付钱包 A钱包
    const gid = data.id
    if (gid === undefined || gid === null || gid === '' || gid === 0) {
      return { code: 1, msg: '操作成功', time: String(Math.floor(Date.now() / 1000)),
        data: { list: [], count: 0, ltnum: 0 } }
    }
    const rows = SOURCE_MARKET_GOODS_LIST
    return { code: 1, msg: '操作成功', time: String(Math.floor(Date.now() / 1000)),
      data: { list: rows, count: rows.length, ltnum: 0 } }
  }
  if (path === '/api/market/trade/getSellCollectionInfo') {
    // 出售求购单·藏品信息（原站直接访问实测）→ 页面重定向回首页
    return { code: 0, msg: '藏品不存在', time: String(Math.floor(Date.now() / 1000)), data: null }
  }
  if (path === '/api/market/market/marketGoodsDetail') {
    // 藏品详情（源站 2026-10-25 15:11 登录态实测：进入 marketGoodsList?id=13 时触发）
    //  返回藏品名 / 流通量 / 限量 / 地板价 / 最高价 / 主图 / 所属系列
    return { code: 1, msg: '操作成功', time: String(Math.floor(Date.now() / 1000)),
      data: SOURCE_MARKET_GOODS_DETAIL }
  }
  return undefined
}
