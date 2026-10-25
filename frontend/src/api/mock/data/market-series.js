/**
 * 源站实录数据 · 市场板块（活动市场 / 全部区 / 榜单）
 * 抓包时间：2026-10-24 21:1x ~ 2026-10-25 11:58，登录态
 * 字段完全沿用源站接口命名，便于与原站响应逐字段比对。
 */
/**
 * 原站实测实录（2026-10-24 21:1x，活动市场 → 怀旧精品 series_id=3）：
 * 该板块下有两个真实寄售藏品，字段完全沿用原站接口命名。
 * 说明：地板价 / 涨跌幅为原站实时撮合数据，会随成交波动，此处取抓包瞬间值。
 */
const SOURCE_MARKET_LIST = [
  {
    name: '创世蛇凤里希',
    series_id: 3,
    id: 7,
    listimg: '/uploads/20261023/423f72131f8a7c6d66c6485bcec16b3e.jpg',
    total_num: 700,
    flux: '675',
    deal_num: 219,
    min_price: 750,
    market_fluctuate_new: 27.55,
    openresale_time: 1792832702,
    new_time: 1792832702,
    goods_type: '2',
    product: {
      id: 7,
      name: '创世蛇凤里希',
      listimg: '/uploads/20261023/423f72131f8a7c6d66c6485bcec16b3e.jpg',
      total_num: 700
    }
  },
  {
    name: '牛魔王',
    series_id: 3,
    id: 3,
    listimg: '/uploads/20261023/a5640f44bb97c9b5a2aa0aa8e48aeefa.jpg',
    total_num: 3713,
    flux: '3257',
    deal_num: 955,
    min_price: 141,
    market_fluctuate_new: 0,
    openresale_time: 1792836034,
    new_time: 1792836034,
    goods_type: '2',
    product: {
      id: 3,
      name: '牛魔王',
      listimg: '/uploads/20261023/a5640f44bb97c9b5a2aa0aa8e48aeefa.jpg',
      total_num: 3713
    }
  }
]

/**
 * 原站实测实录（2026-10-25 登录态抓包，市场页默认板块 series_id=12「全部区」）：
 * 1 条真实藏品 QBOX赛博虎 —— is_out=1（已退市），故列表行渲染「已退市」红色文案、
 * 地板价与涨跌幅显示「--」。封面已本地化至 /uploads/20261024/。
 */
const SOURCE_SERIES_ALL_LIST = [
  {
    name: 'QBOX赛博虎',
    series_id: 12,
    id: 3,
    listimg: '/uploads/20261024/6eeed83cda3fd71871d8634ad5e6c469.png',
    total_num: 3713,
    flux: '3257',
    // 源站实测 2026-10-25 市场首页（全部区）：「QBOX赛博虎 150 发行3713 流通3257 成交1159 0.00%」
    // 该藏品当时仍在售（非退市），此前快照误标 is_out=1 导致多出「已退市」
    deal_num: 1159,
    min_price: '150',
    market_fluctuate_new: 0,
    market_value: 488550,
    market_fluctuate: -10.71,
    reprice_limit: '150.00',
    is_like: 0,
    is_out: 0,
    goods_type: '2',
    openresale_time: 1792836034,
    new_time: 1792836034,
    product: {
      id: 3,
      name: 'QBOX赛博虎',
      listimg: '/uploads/20261024/6eeed83cda3fd71871d8634ad5e6c469.png?image_process=resize,l_350',
      total_num: 3713
    }
  }
]

/**
 * 原站实测实录（2026-10-24 22:25，GOOD 今日热榜 / marketrebang）：
 * 6 条真实藏品榜单。列表页按 hotnum 降序展示前 3 名带 rank1~3 奖牌图，
 * 价格取 min_price（整数则显示整数，字符串小数保留原样如 "200.00"）。
 */
const SOURCE_LEADERBOARD = [
  // 源站实测 2026-10-25 排行榜文本：¥150 / ¥63 / ¥63 / ¥55 / ¥498 / ¥300.00
  // 说明源站原样透传接口价格字符串（不强制两位小数），此处按实测值原样保留
  { id: 12, name: 'QBOX赛博虎', total_num: 3713, flux: 3257, listimg: '/uploads/20261024/6eeed83cda3fd71871d8634ad5e6c469.png', reprice_limit: '150', hotnum: 1040, is_like: 0, min_price: '150' },
  { id: 6, name: '黑熊精', total_num: 3712, flux: 3099, listimg: '/uploads/20261023/dd14e950859dae8f9911461d18cb16ef.jpg', reprice_limit: '63', hotnum: 1006, is_like: 0, min_price: '63' },
  { id: 4, name: '金角大王', total_num: 3713, flux: 3203, listimg: '/uploads/20261023/e9b8820389ffc8d61b21ac52c2a91a5e.jpg', reprice_limit: '63', hotnum: 965, is_like: 0, min_price: '63' },
  { id: 5, name: '银角大王', total_num: 3712, flux: 3210, listimg: '/uploads/20261023/f6ae9e8eea6944ccfb0cc2aca6ca754e.jpg', reprice_limit: '55', hotnum: 862, is_like: 0, min_price: '55' },
  { id: 7, name: '创世蛇凤里希', total_num: 700, flux: 675, listimg: '/uploads/20261023/423f72131f8a7c6d66c6485bcec16b3e.jpg', reprice_limit: '498', hotnum: 254, is_like: 0, min_price: '498' },
  { id: 5, name: 'QBOX-关羽', total_num: 1275, flux: 1123, listimg: '/uploads/20261024/b6107de3e28d2223f0fefee280145047.png', reprice_limit: '300.00', hotnum: 108, is_like: 0, min_price: '300.00' }
]

export { SOURCE_MARKET_LIST, SOURCE_SERIES_ALL_LIST, SOURCE_LEADERBOARD }
