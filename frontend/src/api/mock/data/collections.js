/**
 * 唯一公共藏品数据源（SOURCE_COLLECTIONS / SOURCE_LISTINGS）
 * ============================================================
 * 全站所有「藏品」数据唯一来源，按藏品 id 唯一编号，挂单按挂单 id 唯一编号。
 * 消费方（全部经 mock 路由从此派生，禁止页面自造/写死/随机数据）：
 *   - 市场列表 getMarketList（板块过滤）
 *   - 挂单列表 getMarketGoodsListByGoodsId（goods_id 关联）
 *   - 藏品详情 marketGoodsDetail（按 id 查）
 *   - 寄售详情 MarketDetail/ids/{挂单id}（挂单 → 所属藏品）
 *   - 藏品商品详情 ProductDetail/ids/{id}（product/collection 页）
 *   - 仓库藏品 antMycollection（从本数据源派生，实测当前为空）
 * 数据全部来自源站（api.xiyanwl.asia）登录态抓包实录（2026-10-25/26）；
 * id=1 为首页演示卡引用的藏品（is_demo: true，仅样式预览用）。
 */

const SOURCE = {
  "SOURCE_COLLECTIONS": {
    "14": {
      "id": 14,
      "name": "暴富有道",
      "series_id": -1,
      "listimg": "/uploads/20261025/57c23372247ff516089bedf8bf8c4e90.png",
      "main_image": "/uploads/20261025/57c23372247ff516089bedf8bf8c4e90.png",
      "total_num": 500,
      "flux": "500",
      "min_price": 9999,
      "market_fluctuate_new": 3233,
      "deal_num": 404,
      "market_value": 4999500,
      "reprice_limit": "99999.00",
      "is_out": 0,
      "flag": 0,
      "select_status": 1,
      "openresale_time": 1792914641,
      "new_time": 1792914641,
      "goods_type": "2",
      "collection_level": "",
      "release_time": 1792902720,
      "purchase_information": "                            ",
      "introduce_image": "<img src=\"https://img.xiyanwl.asia/uploads/20261025/57c23372247ff516089bedf8bf8c4e90.png\" alt=\"\" />",
      "sell": 6,
      "price": 0,
      "series": {
        "id": -1,
        "name": "推荐",
        "pattern": 1,
        "resale_put_start_time": "00:00:00",
        "resale_put_end_time": "00:00:00",
        "resale_buy_start_time": "00:00:00",
        "resale_buy_end_time": "00:00:00",
        "resale_status": 0
      },
      "batch_pay": 0,
      "batch_pay_num": 9,
      "key": "fe831aa23ba710fb35ae9327996a5691",
      "max_price": 99999,
      "buy": 12,
      "publisher": {
        "name": "qbox",
        "company_logo": "/uploads/20240118/17b679f0562f639f96344f08e59e0729.jpg",
        "user": []
      }
    },
    "22": {
      "id": 22,
      "name": "忆-创世蛇凤里希",
      "series_id": 11,
      "listimg": "/uploads/20261025/3b50305cd2d12b669bd394033866f6f0.jpg",
      "main_image": "/uploads/20261025/3b50305cd2d12b669bd394033866f6f0.jpg",
      "total_num": 500,
      "flux": "497",
      "min_price": "3000.00",
      "market_fluctuate_new": 237.84,
      "deal_num": 51,
      "market_value": 1491000,
      "reprice_limit": "3000.00",
      "is_out": 1,
      "flag": 0,
      "select_status": 0,
      "openresale_time": 1792939290,
      "new_time": 1792939290,
      "goods_type": "2",
      "collection_level": "",
      "publisher": {
        "name": "qbox",
        "company_logo": "/uploads/20240118/17b679f0562f639f96344f08e59e0729.jpg",
        "user": []
      }
    },
    "17": {
      "id": 17,
      "name": "星火",
      "series_id": 11,
      "listimg": "/uploads/20261025/8667bb536151da31620733b277103b19.jpg",
      "main_image": "/uploads/20261025/8667bb536151da31620733b277103b19.jpg",
      "total_num": 4000,
      "flux": "4000",
      "min_price": "400.00",
      "market_fluctuate_new": 166.67,
      "deal_num": 269,
      "market_value": 1600000,
      "reprice_limit": "400.00",
      "is_out": 1,
      "flag": 0,
      "select_status": 0,
      "openresale_time": 1792927171,
      "new_time": 1792927171,
      "goods_type": "2",
      "collection_level": "",
      "publisher": {
        "name": "qbox",
        "company_logo": "/uploads/20240118/17b679f0562f639f96344f08e59e0729.jpg",
        "user": []
      }
    },
    "16": {
      "id": 16,
      "name": "紫竹",
      "series_id": 11,
      "listimg": "/uploads/20261025/fbf96cec1ec13a7528701848e7ee77f1.jpg",
      "main_image": "/uploads/20261025/fbf96cec1ec13a7528701848e7ee77f1.jpg",
      "total_num": 100000,
      "flux": "71260",
      "min_price": 66,
      "market_fluctuate_new": 6500,
      "deal_num": 3634,
      "market_value": 4703160,
      "reprice_limit": "66.00",
      "is_out": 0,
      "flag": 0,
      "select_status": 0,
      "openresale_time": 1792918669,
      "new_time": 1792918669,
      "goods_type": "2",
      "collection_level": "",
      "publisher": {
        "name": "qbox",
        "company_logo": "/uploads/20240118/17b679f0562f639f96344f08e59e0729.jpg",
        "user": []
      }
    },
    "15": {
      "id": 15,
      "name": "小黑蛇",
      "series_id": 11,
      "listimg": "/uploads/20261025/4e3ca648af38890f24805c6b2f540869.jpg",
      "main_image": "/uploads/20261025/4e3ca648af38890f24805c6b2f540869.jpg",
      "total_num": 15000,
      "flux": "15000",
      "min_price": "100.00",
      "market_fluctuate_new": 400,
      "deal_num": 2338,
      "market_value": 1500000,
      "reprice_limit": "100.00",
      "is_out": 1,
      "flag": 0,
      "select_status": 0,
      "openresale_time": 1792910531,
      "new_time": 1792910531,
      "goods_type": "2",
      "collection_level": "",
      "publisher": {
        "name": "qbox",
        "company_logo": "/uploads/20240118/17b679f0562f639f96344f08e59e0729.jpg",
        "user": []
      }
    },
    "13": {
      "id": 13,
      "name": "QBOX赛博龙",
      "series_id": 2,
      "listimg": "/uploads/20261025/7c674d998b7e86b520f0930c18825d3c.jpg",
      "main_image": "/uploads/20261025/7c674d998b7e86b520f0930c18825d3c.jpg",
      "total_num": 1000,
      "flux": "1000",
      "min_price": 190,
      "market_fluctuate_new": 90,
      "deal_num": 277,
      "market_value": 190000,
      "reprice_limit": "10000.00",
      "is_out": 0,
      "flag": 0,
      "select_status": 0,
      "openresale_time": 1792862152,
      "new_time": 1792862152,
      "goods_type": "2",
      "collection_level": "",
      "publisher": {
        "name": "qbox",
        "company_logo": "/uploads/20240118/17b679f0562f639f96344f08e59e0729.jpg",
        "user": []
      }
    },
    "3": {
      "id": 3,
      "name": "QBOX赛博虎",
      "series_id": 2,
      "listimg": "/uploads/20261024/6eeed83cda3fd71871d8634ad5e6c469.png",
      "main_image": "/uploads/20261024/6eeed83cda3fd71871d8634ad5e6c469.png",
      "total_num": 3713,
      "flux": "2257",
      "min_price": "200.00",
      "market_fluctuate_new": 102.02,
      "deal_num": 1254,
      "market_value": 451400,
      "reprice_limit": "200.00",
      "is_out": 1,
      "flag": 0,
      "select_status": 0,
      "openresale_time": 1792836034,
      "new_time": 1792836034,
      "goods_type": "2",
      "collection_level": "",
      "publisher": {
        "name": "qbox",
        "company_logo": "/uploads/20240118/17b679f0562f639f96344f08e59e0729.jpg",
        "user": []
      }
    },
    "12": {
      "id": 12,
      "name": "QBOX-关羽",
      "series_id": 2,
      "listimg": "/uploads/20261024/b6107de3e28d2223f0fefee280145047.png",
      "main_image": "/uploads/20261024/b6107de3e28d2223f0fefee280145047.png",
      "total_num": 1275,
      "flux": "1123",
      "min_price": "300.00",
      "market_fluctuate_new": 0,
      "deal_num": 117,
      "is_out": 1,
      "goods_type": "2",
      "publisher": {
        "name": "qbox",
        "company_logo": "/uploads/20240118/17b679f0562f639f96344f08e59e0729.jpg",
        "user": []
      }
    },
    "7": {
      "id": 7,
      "name": "创世蛇凤里希",
      "series_id": 2,
      "listimg": "/uploads/20261023/423f72131f8a7c6d66c6485bcec16b3e.jpg",
      "main_image": "/uploads/20261023/423f72131f8a7c6d66c6485bcec16b3e.jpg",
      "total_num": 700,
      "flux": "675",
      "min_price": 798,
      "market_fluctuate_new": 35.71,
      "deal_num": 289,
      "is_out": 0,
      "goods_type": "2",
      "publisher": {
        "name": "qbox",
        "company_logo": "/uploads/20240118/17b679f0562f639f96344f08e59e0729.jpg",
        "user": []
      }
    },
    "6": {
      "id": 6,
      "name": "黑熊精",
      "series_id": 2,
      "listimg": "/uploads/20261023/dd14e950859dae8f9911461d18cb16ef.jpg",
      "main_image": "/uploads/20261023/dd14e950859dae8f9911461d18cb16ef.jpg",
      "total_num": 3712,
      "flux": "754",
      "min_price": 100,
      "market_fluctuate_new": 0,
      "deal_num": 1381,
      "is_out": 0,
      "goods_type": "2",
      "publisher": {
        "name": "qbox",
        "company_logo": "/uploads/20240118/17b679f0562f639f96344f08e59e0729.jpg",
        "user": []
      }
    },
    "5": {
      "id": 5,
      "name": "银角大王",
      "series_id": 2,
      "listimg": "/uploads/20261023/f6ae9e8eea6944ccfb0cc2aca6ca754e.jpg",
      "main_image": "/uploads/20261023/f6ae9e8eea6944ccfb0cc2aca6ca754e.jpg",
      "total_num": 3712,
      "flux": "858",
      "min_price": 100,
      "market_fluctuate_new": 0,
      "deal_num": 1266,
      "is_out": 0,
      "goods_type": "2",
      "publisher": {
        "name": "qbox",
        "company_logo": "/uploads/20240118/17b679f0562f639f96344f08e59e0729.jpg",
        "user": []
      }
    },
    "4": {
      "id": 4,
      "name": "金角大王",
      "series_id": 2,
      "listimg": "/uploads/20261023/e9b8820389ffc8d61b21ac52c2a91a5e.jpg",
      "main_image": "/uploads/20261023/e9b8820389ffc8d61b21ac52c2a91a5e.jpg",
      "total_num": 3713,
      "flux": "900",
      "min_price": 125,
      "market_fluctuate_new": 0,
      "deal_num": 1388,
      "is_out": 0,
      "goods_type": "2",
      "publisher": {
        "name": "qbox",
        "company_logo": "/uploads/20240118/17b679f0562f639f96344f08e59e0729.jpg",
        "user": []
      }
    },
    "1": {
      "id": 1,
      "name": "KUUKIE 怪物口袋·黑熊",
      "series_id": 0,
      "series_name": "全部",
      "listimg": "/uploads/20261013/891305447d1a31e997fd7a0e078488a5.png",
      "main_image": "/uploads/20261013/891305447d1a31e997fd7a0e078488a5.png",
      "total_num": 5000,
      "flux": 3268,
      "min_price": "39.90",
      "market_fluctuate_new": 0,
      "deal_num": 0,
      "is_out": 0,
      "goods_type": "2",
      "reprice_limit": "99999.00",
      "is_demo": true,
      "publisher": {
        "name": "qbox",
        "company_logo": "/uploads/20240118/17b679f0562f639f96344f08e59e0729.jpg",
        "user": []
      }
    }
  },
  "SOURCE_LISTINGS": [
    {
      "id": 16515,
      "collection_code": 387,
      "price": 9999,
      "total_num": 500,
      "series_id": -1,
      "is_img_diff": 0,
      "status": 4,
      "income_type": [
        "huifu"
      ],
      "createtime": 1792942909,
      "img": "",
      "name": "",
      "goods_id": 14
    },
    {
      "id": 16511,
      "collection_code": 383,
      "price": 11000,
      "total_num": 500,
      "series_id": -1,
      "is_img_diff": 0,
      "status": 1,
      "income_type": [
        "huifu"
      ],
      "createtime": 1792942860,
      "img": "",
      "name": "",
      "goods_id": 14
    },
    {
      "id": 16375,
      "collection_code": 127,
      "price": 11111,
      "total_num": 500,
      "series_id": -1,
      "is_img_diff": 0,
      "status": 1,
      "income_type": [
        "huifu"
      ],
      "createtime": 1792940429,
      "img": "",
      "name": "",
      "goods_id": 14
    },
    {
      "id": 16372,
      "collection_code": 132,
      "price": 13140,
      "total_num": 500,
      "series_id": -1,
      "is_img_diff": 0,
      "status": 1,
      "income_type": [
        "huifu"
      ],
      "createtime": 1792940412,
      "img": "",
      "name": "",
      "goods_id": 14
    },
    {
      "id": 16451,
      "collection_code": 129,
      "price": 35000,
      "total_num": 500,
      "series_id": -1,
      "is_img_diff": 0,
      "status": 1,
      "income_type": [
        "huifu"
      ],
      "createtime": 1792941480,
      "img": "",
      "name": "",
      "goods_id": 14
    },
    {
      "id": 16449,
      "collection_code": 131,
      "price": 99999,
      "total_num": 500,
      "series_id": -1,
      "is_img_diff": 0,
      "status": 1,
      "income_type": [
        "huifu"
      ],
      "createtime": 1792941468,
      "img": "",
      "name": "",
      "goods_id": 14
    }
  ]
}

const SOURCE_COLLECTIONS = SOURCE.SOURCE_COLLECTIONS
const SOURCE_LISTINGS = SOURCE.SOURCE_LISTINGS

export { SOURCE_COLLECTIONS, SOURCE_LISTINGS }

/** 按藏品 id 取主数据（唯一入口；查无返回 null） */
export function getCollection(id) {
  return SOURCE[String(id)] || SOURCE_COLLECTIONS[Number(id)] || null
}

/** 全部藏品（无序保证，由调用方排序） */
export function allCollections() {
  return Object.values(SOURCE_COLLECTIONS)
}

/**
 * 板块归属映射（2026-10-26 原站实测快照：getSeries?type=10 + 各板块 getMarketList）
 * 说明：早期抓包（板块更名前）的 series_id 与今日实测存在冲突（如「经典文化」更名
 * 「生肖系列」后归属缩减），故板块过滤以此显式映射为准，主数据 series_id 字段保持抓包原值。
 */
export const SERIES_BOARD_MAP = {
  '-1': [14],
  '11': [22, 17, 16, 15],
  '2': [13, 3]
}

/** 按板块取市场列表行（getMarketList 行字段，直接引用主数据，零拷贝） */
export function collectionsBySeries(seriesId) {
  const ids = SERIES_BOARD_MAP[String(seriesId)]
  if (!ids) return []
  return ids.map((id) => getCollection(id)).filter(Boolean)
}

/** 按藏品 id 取挂单列表（getMarketGoodsListByGoodsId 行） */
export function listingsByGoods(goodsId) {
  return SOURCE_LISTINGS.filter((l) => String(l.goods_id) === String(goodsId))
}

/** 按挂单 id 取挂单（MarketDetail/ids/{挂单id} 数据源） */
export function getListing(listingId) {
  return SOURCE_LISTINGS.find((l) => String(l.id) === String(listingId)) || null
}

/** 按藏品 id 取所属挂单（详情页「快捷下单/立即购买」兜底取在售挂单） */
export function firstListingOf(goodsId) {
  return SOURCE_LISTINGS.find((l) => String(l.goods_id) === String(goodsId) && l.status === 1) || null
}

/** 仓库藏品（我的收藏）持有 id 列表：源站实测当前账号收藏为空；收藏写入后在此追加 */
export function myCollectionIds() {
  return []
}
