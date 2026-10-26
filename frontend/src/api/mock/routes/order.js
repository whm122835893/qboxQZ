/**
 * Mock 路由 · 订单域
 * 订单列表 / 会员下单 / 市场下单 / 支付 / 订单详情 / 我的寄售 / 求购委托下单
 * 路径与原站 API 完全一致，行为还原原站实测结果。
 */
import { orderStore } from '../state'
import { genOrderSn } from '../data/orders'

/**
 * 路由分发：命中返回响应体，未命中返回 undefined 交由上层继续匹配
 */
export function handleOrderRoute(path, data = {}) {
  if (path === '/api/order/createVIPOrder') {
    // 原站下单接口未抓包（避免向原站账号真实下单），按接口语义返回订单号，已在 API 文档标注推断
    return { code: 1, msg: '下单成功', time: String(Math.floor(Date.now() / 1000)),
      data: { order_sn: 'VIPORDER' + Date.now(), order_id: Date.now() } }
  }
  if (path === '/api/order/order/orderList') {
    // 我的订单列表（源站 2026-10-25 11:58 登录态实测实录 + 本地下单写入的订单）
    // 源站字段命名：order_sn / order_status / total_price / goods_cover_image / createtime
    // 状态筛选：0=全部 1=批量付 2=已完成 3=进行中/已取消 7=待支付（源站 tab 逐项抓包实测）
    const st = Number(data.status || 0)
    const kw = String(data.keywords || '').trim()
    let rows = orderStore.slice()
    if (st === 1) rows = rows.filter((o) => o.order_status === 1)
    else if (st === 2) rows = rows.filter((o) => o.order_status === 2)
    else if (st === 3) rows = rows.filter((o) => o.order_status === 3)
    else if (st === 7) rows = rows.filter((o) => o.order_status === 1)
    if (kw) {
      rows = rows.filter(
        (o) => String(o.goods_name).includes(kw) || String(o.order_sn).includes(kw)
      )
    }
    rows.sort((a, b) => b.createtime - a.createtime)
    return { code: 1, msg: '订单列表', time: String(Math.floor(Date.now() / 1000)),
      data: { total: rows.length, per_page: 15, current_page: 1, last_page: 1, data: rows } }
  }
  if (path === '/api/market/market/createOrder') {
    // 市场下单（源站点击「立即购买」→ 底部订单弹窗 →「立即支付」时服务端创建订单）
    // 源站未直接抓到该接口路径（实际由汇付收银台回调写入），此处按市场挂单语义实现，
    // 字段命名与源站订单列表一致（order_sn / order_status / total_price），已在 API 文档标注推断
    const price = Number(data.price || 0)
    const now = Math.floor(Date.now() / 1000)
    const order = {
      artel_order: 'mh_secondary_market_order',
      crystal_type: 'crystal_no',
      crystal_number: '0',
      uc_ids: null,
      id: Date.now(),
      num: String(data.num || 1),
      order_type: '2',
      order_status: 1,
      order_sn: genOrderSn(),
      total_price: price.toFixed(2),
      goods_name: data.goods_name || '',
      goods_cover_image: data.goods_cover_image || '',
      goods_id: data.goods_id || 0,
      goods_type: 2,
      series_name: data.series_name || '',
      createtime: now,
      updatetime: now,
      collection_code: [],
      market_order_id: data.market_order_id || 0,
      collection_code_text: data.collection_code || ''
    }
    orderStore.unshift(order)
    return { code: 1, msg: '下单成功', time: String(Math.floor(Date.now() / 1000)),
      data: { order_id: order.id, order_sn: order.order_sn, total_price: order.total_price } }
  }
  if (path === '/api/order/order/payOrder') {
    // 订单支付（源站由汇付收银台回调写入状态；独立项目未接支付通道，此处按语义置为已支付）
    const id = Number(data.order_id || 0)
    const o = orderStore.find((x) => x.id === id)
    if (o) o.order_status = 2
    return { code: 1, msg: '支付成功', time: String(Math.floor(Date.now() / 1000)), data: null }
  }
  if (path === '/api/order/order/cancelOrder') {
    // 取消订单（源站订单详情页「取消订单」按钮；接口未直接抓包，按语义置为已取消，
    // 已在 API 文档标注推断）
    const id = Number(data.order_id || data.id || 0)
    const o = orderStore.find((x) => x.id === id)
    if (o) o.order_status = 3
    return { code: 1, msg: '取消成功', time: String(Math.floor(Date.now() / 1000)), data: null }
  }
  if (path === '/api/order/order/saleMarketOrderDetail') {
    // 市场订单详情（源站实测：无 id → code 0「订单不存在」；带 id → 返回订单对象）
    const id = Number(data.id || data.order_id || 0)
    const o = orderStore.find((x) => x.id === id)
    if (!o) return { code: 0, msg: '订单不存在', time: String(Math.floor(Date.now() / 1000)), data: null }
    return { code: 1, msg: 'successfully !!!', time: String(Math.floor(Date.now() / 1000)), data: o }
  }
  if (path.startsWith('/api/order/order/orderDetail/ids/')) {
    // 商城订单详情（源站实测 2026-10-25：POST /api/order/order/orderDetail/ids/{id}）
    // 无 id → code 0「找不到该订单或已被删除」；带 id → 返回订单对象（源站字段命名）
    const sid = path.split('/ids/')[1]
    const o = orderStore.find((x) => String(x.id) === String(sid))
    if (!o) return { code: 0, msg: '找不到该订单或已被删除', time: String(Math.floor(Date.now() / 1000)), data: null }
    return { code: 1, msg: '订单详情', time: String(Math.floor(Date.now() / 1000)),
      data: { ...o, manager_id: null, series_id: o.series_id || 2, goods_id: o.goods_id,
        goods_type: o.goods_type || 2, goods_cover_image: o.goods_cover_image,
        pay_time: o.pay_time || null, transaction_id: o.transaction_id || null,
        pay_scene: o.pay_scene || null, pay_way: o.pay_way || null, version: o.version || 0,
        sure: 0, uc_ids: o.uc_ids || null, collection_code: o.collection_code || [],
        surplus_time_text: 0 } }
  }
  if (path.startsWith('/api/order/order/MarketOrderDetail/ids/')) {
    // 市场订单详情（源站实测 2026-10-25：订单列表点击市场单 order_type=2 →
    // /pages/order/defaultDetail?id={id}&orderType=2 → POST MarketOrderDetail/ids/{id}）
    // 实测响应字段：order_sn / order_status / pay_type / total_price / goods_cover_image /
    //   pay_way("huifu") / pay_time(null 时页面取 createtime 展示) / collection_code / surplus_time_text
    const sid = path.split('/ids/')[1]
    const o = orderStore.find((x) => String(x.id) === String(sid))
    if (!o) return { code: 0, msg: '找不到该订单或已被删除', time: String(Math.floor(Date.now() / 1000)), data: null }
    return { code: 1, msg: '订单详情', time: String(Math.floor(Date.now() / 1000)),
      data: { ...o, sale_user_id: 0, user_id: 19627, pay_type: o.pay_type || 4,
        transaction_id: o.order_sn, pay_scene: 'H5', pay_way: 'huifu', version: 1,
        sure: 0, real_trans_amt: null, order_type: '2', uc_ids: null,
        huifu_pay_type: null, c_goods_id: o.goods_id, batch_order_id: 0, is_rush: 0,
        collection_code: o.collection_code || [], surplus_time_text: 0 } }
  }
  if (path === '/api/order/order/myConsignment') {
    // 我的寄售（原站直接访问实测：空列表）
    return { code: 1, msg: 'successfully !!!', time: String(Math.floor(Date.now() / 1000)),
      data: { total: 0, per_page: 15, current_page: 1, last_page: 0, data: [] } }
  }
  if (path === '/api/market/trade/createBegBuyOrder' || path === '/api/market/Prepayment/createOrder') {
    // 创建求购/委托订单（未抓包，避免向原站账号真实下单），按调用链语义返回订单号，已在 API 文档标注推断
    return { code: 1, msg: '下单成功', time: String(Math.floor(Date.now() / 1000)),
      data: { order_id: Date.now(), order_sn: 'MK' + Date.now() } }
  }
  return undefined
}
