import { MOCKS } from './mock-data'
import { handleAccountRoute } from './mock/routes/account'
import { handleMarketRoute } from './mock/routes/market'
import { handleOrderRoute } from './mock/routes/order'
import { handleMiscRoute } from './mock/routes/misc'

/**
 * Mock 路由：路径与原站 API 完全一致。
 * 行为还原原站实测：
 *  - /api/user/login 正确账号密码返回登录成功，错误密码返回 {code:0,msg:'密码不正确'}（原站实测）
 *
 * 分层结构：
 *   mock/routes/*  —— 按业务域拆分的路由匹配（账户 / 市场 / 订单 / 其他）
 *   mock/state.js  —— 全局内存态 store（下单 → 支付 → 订单列表闭环）
 *   mock/data/*    —— 原站实录响应数据（按板块分片）
 */
export function mockRequest(path, data = {}) {
  const hit =
    handleAccountRoute(path, data) ||
    handleMarketRoute(path, data) ||
    handleOrderRoute(path, data) ||
    handleMiscRoute(path, data)
  if (hit) return hit

  const key = Object.keys(MOCKS).find(k => path.startsWith(k) || k.startsWith(path))
  if (key && MOCKS[key]) {
    const v = MOCKS[key]
    return typeof v === 'string' ? JSON.parse(v) : v
  }
  return { code: 0, msg: 'mock 未覆盖该接口: ' + path, data: null }
}
