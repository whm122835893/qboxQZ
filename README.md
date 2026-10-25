# qboxQZ

QBOX 数字藏品平台 H5 复刻项目（Vue 3 + Vite 5 + Pinia + Vant 4）。

## 目录结构

```
qboxQZ/
├─ frontend/          Vue3 前端工程
│  ├─ src/
│  │  ├─ api/         request.js（统一封装）+ mock.js（入口）+ mock/（routes 路由域 + state 内存态 + data 源站实录）
│  │  ├─ pages/       111 个路由页面（与源站一一对应）
│  │  ├─ components/  通用组件
│  │  ├─ composables/ 组合式逻辑（如 useDatePicker）
│  │  ├─ router/      hash 路由 + 登录守卫
│  │  ├─ stores/      Pinia
│  │  └─ styles/      fonts.css（本地字体）+ global.css（Design Token）+ pages/ + components/（页面/组件样式）
│  └─ public/static/  图片/字体等本地化资源
└─ docs/
   ├─ 01-网站深度分析报告.md   页面地图 / Design Token / 全站一致性审计 / 市场交易链路 / 代码组织门禁整改
   ├─ 02-数据库设计文档.md
   └─ 03-API接口文档.md        含 3.3 节「市场交易链路全接口实测」
```

## 开发

```bash
cd frontend
npm install
npm run dev      # http://127.0.0.1:5173
npm run build
```

默认 `VITE_USE_MOCK=true`，所有接口走本地 mock（数据全部来自源站抓包实录）。接入自建后端时置 `VITE_USE_MOCK=false`，接口路径与源站保持一致。

## 关键约定

- **零运行时外链依赖**：不引用任何 `xiyanwl.asia` 资源，图片/字体全部本地化于 `public/static/**`。
- **静态资源零缺失**：全站 111 页破图扫描为 0。`src/` 下所有 `/static/*`、`/uploads/*` 引用均已本地化；**禁止裸 `<img :src="可能为空的变量" />`**（空 `src` 会被浏览器解析为当前页 URL 而破图），须有占位块或已本地化封面回落。
- **CSS 单位**：样式中直接书写源站 rpx 数值，由 `vite.config.js` 的 `pxToVw` 插件按 `1px → 100/750vw` 换算。
  - 例外：源站本身为固定 px 布局的页面（如 `ruleDetail`）在 style 块首行加 `/*@nopx*/` 豁免转换。
  - 注意：`@import` 引入的 CSS **不参与** px→vw 转换，页面样式请用 `<style scoped src="...">` 外置（一个 SFC 可挂多个块）。
- **P0 规则**：禁止 emoji 作功能图标（统一 SVG）、禁止紫粉渐变、禁止 AI 模板味文案、禁止弹跳缓动。
- **单文件 ≤ 300 行**（代码组织硬门禁）：全站零超标。页面样式外置到 `src/styles/pages/`，组件样式到 `src/styles/components/`；mock 层按「routes 路由域 / state 内存态 / data 源站实录」三层拆分。
- **数据契约**：字段映射以源站实测为准（如寄售详情页取 `user_collection.user_hash_text` / `user_collection.rz_code`，非 `product.contract_address`）。

## 市场交易链路（已实现并走通）

```
/pages/market/market
  └─ 点击藏品 → /pages/market/marketGoodsList?id={藏品id}&goodsType=2&minPrice={地板价}
                                                          挂单列表 + 底部「快捷下单」
       ├─ 点击挂单行 → /pages/market/detail?id={挂单id}        寄售详情
       │     └─ 「立即购买」→ 订单确认半屏弹窗 → 「立即支付」
       │           → POST /api/market/market/createOrder
       │           → /pages/order/list（我的订单，新单「待支付」）
       │                 └─ 「去支付」→ /pages/order/marketDetail?id={id}
       │                       └─ 「去支付」→ POST /api/order/order/payOrder → 状态「转入成功」
       └─ 「快捷下单」→ 快捷下单半屏弹窗 → 「确认」→ 进入在售挂单详情
```

源站实测要点：
- **「立即购买」不跳路由，弹底部半屏弹窗**；「快捷下单」同为半屏弹窗。
- 两个弹窗的渠道行为灰底卡 `#f5f5f5` radius 20、徽标 48×48 黑圆、右侧 18×18 蓝色选中点。
- **市场页板块卡只渲染 3 个**（源站 `/api/index/getSeries?type=10` 返回 5 个板块）：推荐(-1，默认选中) / 破茧(11) / 经典文化(2)；「我的关注(-2)」由顶部 Tab 承载、「全部(0)」不作板块卡。板块卡价格 = 该板块藏品地板价均价，切换板块后**保留**已加载板块的价格。
- **源站图片 CDN 域名是 `img.xiyanwl.asia`**（`h5.` / `api.` 域名下的 `/uploads/*` 返回 156 字节 HTML 错误页）。
- **空状态图尺寸按源站 142×142**（=284rpx，`object-fit: contain`）；源站图片 `common-empty.png` 原始为 277×405，不约束尺寸会撑成原始大图。
- **`scoped` 样式归属边界**（本项目踩坑 3 次）：页面级元素的规则必须写在页面 CSS，组件内部元素的规则必须写在组件 CSS，否则规则**静默失效**（不报错、只是不生效）。
- 订单列表字段为源站真实命名：`order_sn` / `order_status`（1 待支付 / 2 转入成功 / 3 已取消）/ `total_price` / `goods_cover_image` / `createtime` / `order_type`。
- 源站「立即支付」跳转第三方汇付收银台（`hfpay.cloudpnr.com`），本项目不接入外部支付通道，改为本地 mock 订单闭环。
