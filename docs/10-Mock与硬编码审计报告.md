# Mock 数据与硬编码审计报告

> 日期：2026-10-26　范围：frontend/src + backend/app + sql
> 结论：**该修的 6 类已全部修复并验证**；保留项均有源站实测依据并已标注。

## 一、已修复（本次）

| # | 位置 | 问题 | 修复 |
|---|------|------|------|
| 1 | 后端 4 处 | 平台费率 `0.05`、支付时限 `300s` 散落硬编码 | 新增 `sysConfig()/platformFeeRate()/payTimeLimit()` 公共函数，从 `nft_system_configs` 读取（已种子 6 条配置），代码留默认值兜底 |
| 2 | 后端 Market | 寄售详情卖家昵称写死 `'db****'` | `maskNickname()` 由 users.username 实时脱敏（实测输出 db**** 形态一致） |
| 3 | 后端 Base | `JWT_KEY` 写死 const | `Base::jwtKey()` 从 `env('JWT_KEY')` 读取（`.env` 已建，含 DB/JWT/APP_DEBUG） |
| 4 | 后端 User | 短信 `mock_code` 无条件直返 | 仅 `APP_DEBUG=true` 时返回（生产移除） |
| 5 | 前端 consignments | 寄售上架页**整体演示**：藏品信息写死 KUUKIE、提交为假 toast | 改为按 URL `?id={持仓id}` 调持仓详情接口渲染 + 提交调真实 `POST /api/market/market/consign`；UI 实测：填价 55 → 挂单落库（费 2.75/到账 52.25/凭证 consigned） |
| 6 | 前端 my.vue | 持仓市值写死 `¥200`（源站抓包快照值） | 改为 `userinfo.collection_total_price`（后端按持仓累加） |

> 过程中发现并修复：consign 接口交易密码改为可选（与源站收银台语义一致）；定价上限校验实测生效（紫竹上限 66，出价 88 被正确拒绝）。

## 二、保留项（有明确依据，不修）

| 位置 | 内容 | 保留理由 |
|------|------|----------|
| `index.vue` 热门藏品演示卡 | KUUKIE 演示数据 1 条 | **用户拍板保留**（原站该板块当前为空态，无在售样本） |
| `regift.vue` 转赠页演示 | 表单演示数据 | **转赠功能用户明确移除**（transfers 表仅只读流水） |
| `ruleDetail.vue` / `cancellation.vue` | 纯静态硬编码文案 | **源站本身就是硬编码静态页**（chunk 反解实测），像素级复刻要求 |
| `api/mock/` + `mock-data.js`（1464 行） | mock 数据层 | 回退机制：`VITE_USE_MOCK=false` 已切真实后端；删除 mock 层即失去离线演示能力（数据已全部迁入 `collections.js` 唯一数据源 + MySQL 种子） |
| `/static/qbox/...`、`/uploads/...` 图片路径 | 本地化资源 | 项目约定（零外链依赖），非硬编码问题 |
| 路由字符串 `'/pages/...'` | 页面跳转路径 | 与源站路由一致的前端约定，属正常常量 |

## 三、生产部署前 checklist（已写入 backend/README）

- [ ] `.env` 中 `JWT_KEY` 换强随机值
- [ ] `APP_DEBUG=false`（mock_code 不再返回）
- [ ] 短信接入真实网关
- [ ] PayService 模拟支付替换为汇付验签回调
- [ ] `nft_system_configs` 按运营要求核定费率/时限
