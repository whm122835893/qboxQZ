/**
 * Mock 内存态 · 全局共享 store
 * 说明：这些对象在 mock 生命周期内保持引用一致性，供各路由域读写，
 *       使「下单 → 支付 → 订单列表」形成可观测闭环。
 */
import { SOURCE_ORDER_LIST } from './data/orders'

/**
 * 订单内存态：市场下单（/api/market/market/createOrder）写入此列表，
 * 「我的订单」页（orderList）实时读取，使下单 → 支付 → 订单列表形成闭环。
 */
export const orderStore = SOURCE_ORDER_LIST.map((o) => ({ ...o }))

// 用户资料存储（内存态）：POST /api/user/profile 修改昵称/头像后，getUserInfo 动态返回
export const profileStore = { nickname: null, avatar: null }

// 地址存储（内存态）：原站 address_list 实测为空数组；edit_address/delUserAddress
// 未抓包（避免向原站账号写入数据），行为按接口名语义实现，已在 API 文档标注推断
export const addressListStore = []

// 站点配置存储：原站 POST /api/index/getConfig {key:"vip_agreement,vip_time,vip_service_charge"}
// 抓包实录（2026-10-21）：vip_time=2678400(31天/秒)、vip_service_charge=29.9、vip_agreement 为购买说明HTML
export const configStore = {
  vip_time: '2678400',
  vip_service_charge: '29.9',
  vip_agreement:
    '<p>1. 开通 QBOX VIP 即表示您知悉并同意会员服务条款，支付成功后权益立即生效，有效期以开通时页面展示为准。</p>' +
    '<p>2. 会员权益含专属藏品空投、优先购买、批量购买及专属客服等服务，具体内容以平台公示及实际发放规则为准。</p>' +
    '<p>3. VIP 为虚拟数字商品，一经开通不支持退款或转让，请确认使用本人实名认证账户完成支付。</p>' +
    '<p>4. 平台有权根据运营需要调整权益内容并提前公告；请理性消费，谨防诈骗，勿轻信非官方渠道信息。</p>'
}
