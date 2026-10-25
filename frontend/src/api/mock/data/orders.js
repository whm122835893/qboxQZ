/**
 * 源站实录数据 · 订单列表（SOURCE_ORDER_LIST）
 * 抓包时间：2026-10-25 11:58，登录态「我的订单」页
 * POST /api/order/order/orderList {page:1,status:0,keywords:""} → 3 条真实订单
 * 字段（源站命名）：order_sn / order_status / total_price / goods_name /
 *   goods_cover_image / num / createtime / order_type / artel_order / collection_code
 *   order_type: 2 = 寄售（市场）单，3 = 商城（盲盒/抽盒）单
 *   order_status: 1 = 待支付，2 = 转入成功，3 = 已取消
 * 说明：源站订单号由服务端生成，形如 2026MMDDHHMMSS + 21 位数字。
 */
const SOURCE_ORDER_LIST = [
  {
    artel_order: 'mh_secondary_market_order',
    crystal_type: 'crystal_no',
    crystal_number: '0',
    uc_ids: null,
    id: 9538,
    num: '1',
    order_type: '2',
    order_status: 3,
    order_sn: '2026102510554448555297570810',
    total_price: '310.00',
    goods_name: 'QBOX赛博龙',
    goods_cover_image: '/uploads/20261025/7c674d998b7e86b520f0930c18825d3c.jpg',
    goods_id: 13,
    goods_type: 2,
    series_name: '全部区',
    createtime: 1792896944,
    updatetime: 1792896944,
    collection_code: []
  },
  {
    artel_order: 'order',
    crystal_type: null,
    crystal_number: '0',
    uc_ids: '180409',
    id: 8783,
    num: 1,
    order_type: '3',
    order_status: 2,
    order_sn: '2026102320534254491011244121',
    total_price: '0.00',
    goods_name: 'QBOX数字徽章',
    goods_cover_image: '/uploads/20261022/7c48bc4f036e99b7a7df990ceeeff459.png',
    goods_id: 10,
    goods_type: 2,
    series_name: '经典文化',
    createtime: 1792760022,
    updatetime: 1792760022,
    price: '0.00',
    collection_code: ['136081/500000']
  },
  {
    artel_order: 'order',
    crystal_type: null,
    crystal_number: '0',
    uc_ids: '389304',
    id: 8784,
    num: 1,
    order_type: '3',
    order_status: 2,
    order_sn: '2026102320534254504910680371',
    total_price: '0.00',
    goods_name: 'QBOX数字徽章',
    goods_cover_image: '/uploads/20261022/7c48bc4f036e99b7a7df990ceeeff459.png',
    goods_id: 10,
    goods_type: 2,
    series_name: '经典文化',
    createtime: 1792760022,
    updatetime: 1792760022,
    price: '0.00',
    collection_code: ['344976/500000']
  }
]

/** 生成源站格式订单号：2026MMDDHHMMSS + 21 位数字 */
function genOrderSn() {
  const d = new Date()
  const p = (n, l = 2) => String(n).padStart(l, '0')
  const head =
    d.getFullYear() +
    p(d.getMonth() + 1) +
    p(d.getDate()) +
    p(d.getHours()) +
    p(d.getMinutes()) +
    p(d.getSeconds())
  let tail = ''
  for (let i = 0; i < 21; i++) tail += Math.floor(Math.random() * 10)
  return head + tail
}

/**
 * 相对时间换算（复刻源站 time 字段规则，源站实测 1792847903 → "5小时前"）：
 *   <60s → 刚刚；<1h → N分钟前；<24h → N小时前；否则 N天前
 */
function relativeTime(ts) {
  const diff = Math.floor(Date.now() / 1000) - Number(ts || 0)
  if (diff < 60) return '刚刚'
  if (diff < 3600) return Math.floor(diff / 60) + '分钟前'
  if (diff < 86400) return Math.floor(diff / 3600) + '小时前'
  return Math.floor(diff / 86400) + '天前'
}

export { SOURCE_ORDER_LIST, genOrderSn, relativeTime }
