# QBOX 复刻项目 —— API 接口文档

> 依据：2026-10-21 对原站 api.xiyanwl.asia 的抓包实录（账号 17587881293 登录态）。
> 本项目接口路径与原站 1:1 对应，便于像素级还原与后续联调；后端为本项目自建 ThinkPHP 8，不依赖原站。
> Mock 实录已固化至 `frontend/src/api/mock-data.js`，前端当前以 Mock 独立运行（规则9）。

## 0. 通用约定（原站实测）

| 项 | 值 |
|----|-----|
| Base URL | `/api`（本项目指向自建 ThinkPHP，原站为 api.xiyanwl.asia） |
| Method | 全部 POST，Content-Type: application/json |
| 请求头 | `token`（登录后携带，36位）、`version: 1.0.0` |
| 响应结构 | `{code: 1成功/0失败, msg, time: "unix秒", data}` |
| 分页结构 | `data: {total, per_page, current_page, last_page, data: []}` |
| 空态 | 列表 data 为 []，前端展示黑猫「这里空空的！」 |

---

## 1. 认证模块

### 1.1 POST /api/user/login —— 账号密码登录

请求：
```json
{ "account": "17587881293", "password": "whm981004" }
```
成功响应（实测，节选）：
```json
{ "code": 1, "msg": "登录成功", "time": "1792553653", "data": {
  "token": "<36位token>",
  "userinfo": { "id": 19627, "username": "17587881293", "nickname": "Q9GSWCuI2",
    "mobile": "17587881293", "avatar": "/defautAvatar.png", "score": 1,
    "inviter_code": "Z72219627", "money": "0.00", "level": 1, ... } } }
```
失败（实测）：`{"code":0,"msg":"密码不正确"}`
前端行为（实测）：空账号 toast「请输入手机号」；成功 toast「登录成功」→ 跳首页。

### 1.2e 登录组页面实测（register/forgetPwd/setPwd/auth/verification，2026-10-21，chunk 反解）

| 页面 | 接口 | 要点 |
|------|------|------|
| 注册 register | POST /api/user/register `{mobile, password, code, inviter_code}`；短信 event:**register** | 4 行（手机号/验证码+获取验证码/密码/邀请码）+ 注册(蓝)/去登录(白描边) + 协议行；校验文案：请输入手机号/请输入验证码/请输入登录密码/密码内需同时包含数字、字母，长度在6-12之间/请先勾选用户协议；成功跳 login?loginType=1 |
| 忘记密码 forgetPwd | 短信 event:**resetpwd**；POST /api/user/resetpwd | 4 行（手机号/验证码/新密码/确认密码）+ 确认；成功跳 login?loginType=1 |
| 设置密码 setPwd | /api/user/resetpwd（chunk 无显式 url，共享模块推断） | 2 行（新密码/确认密码，带 显示 切换）+ 提示原文「密码内需同时包含数字、字母，长度在6-12之间」+ 确认 |
| 授权 auth | 无接口 | 无表单：手机号登录(蓝)/账号密码登录(白描边)/没有账号？点击注册/协议行；点击跳 login?loginType=1（1=验证码tab，2=密码tab 推断）；按钮 r24（比登录页 r27 略小，实测如此） |
| 验证 verification | 顶象/极验 webview 容器（verificationUrl + postMessage） | 原站为第三方托管页无法观测 UI；独立项目按规则9 复刻为本地滑块验证容器（标注推断） |

**坐标实测（414px）**：register/forgetPwd 4 行 y342/400/458/516（362×45 r10 bg#f5f5f5），按钮 y587/654（362×54 r27）；setPwd 按钮 y504（提示行 y458 13px #8c8c8c）；auth 按钮 y489/560 r24、注册链接 y631 13px

### 1.2d 个人信息页 + 资料编辑实测（pages/my/info + 设置页交互，2026-10-21）

| 接口 | 请求 | 说明 |
|------|------|------|
| POST /api/user/profile | `{nickname}` 或 `{avatar}` | 修改昵称/头像（chunk 提取 `updateUserInfo`）；成功 toast msg + 刷新 getUserInfo |
| 头像上传 | 原站：uploadFile → OSS → updateUserInfo({avatar: 路径}) | 独立项目改为本地上传（规则9），mock 直接保存本地状态 |

**my/info 页面实测（414px）**：白底导航「个人信息」；行1 头像 (0,61,414×100)（右侧圆头像 66×66，avatar=/defautAvatar.png 时页面实际显示 static/good/user_default_icon.png 默认图标）；行2 昵称 (0,162,414×55) 值 13px + 右箭头；行3 手机号 175****1293；行间 1px、底部 #f8f8f9
**设置页交互实测（原站行为）**：点昵称行 → 「修改昵称」弹窗（w-modal：标题+输入框 84rpx placeholder 请输入昵称 + 取消灰/确认蓝 #0018ff 圆角按钮）→ updateEvent 提交；点头像行 → **头像裁剪全屏弹层**（w-crop/uni-cropper：圆形裁剪框 + 底部 取消/重选/预览/完成 蓝字按钮，重选=chooseImage，完成=canvas 裁剪上传）——已按实测补齐到设置页（此前标注"待接入"的交互）；独立项目裁剪拖拽简化为固定居中圆框（标注推断）
**入口说明**：my/info 在原站为隐藏路由（我的页头像、设置页头像/昵称均不跳转该页，分别触发弹层）；项目保留直链可达

### 1.2c 操作密码模块实测（pages/my/actionPwd/*，2026-10-21，接口与流程从编译产物反解）

| 接口 | 请求 | 来源 |
|------|------|------|
| POST /api/sms/send | `{mobile, event:"resetpwd"}`（顶象验证码关闭时直发；开启时带 verify_token） | chunk 提取 |
| POST /api/user/setPayPassword | `{password, mobile, captcha}`（form 校验后提交，成功 toast + navigateBack 2） | chunk 提取 |
| POST /api/user/checkPaypass | `{pay_password}` → `data.code` 用于换取 setPwd 跳转参数 | chunk 提取 |

**流程（chunk 反解，已在代码注释同步标注）**：
- 设置页「操作密码」→ setPwd；setPwd 无 code 参数时**自动重定向** getCode
- getCode action 取值：`pay_password` 存在 → `set`（旧密码校验模式：输入当前操作密码 + 忘记密码?链接→action=reset）；不存在 → `reset`（短信验证模式：手机号预填+验证码，**进入页面自动 sendCode**）
- 两条路确认后均跳 `/pages/my/actionPwd/setPwd?code=<验证码/返回code>`
- **独立项目注意**：进入 getCode 会自动调用 /sms/send；mock 阶段固定验证码 123456（与登录 mock 同一约定），真实短信由本项目后端发送
- setPwd 布局实测（414px）：背景 login-bg.png（艺术字顶部）+ 表单 mt637.7 + 4 行（h48 r19 bg#f5f5f5 padding 0 17，标签 w66 15px，分隔竖线 1×15 #d9d9d9，输入 14px placeholder 请输入6位数字/请输入手机号码/请输入验证码）+ 获取验证码 #0047ff + 提示 y666 13px #8c8c8c + 确认 (26.5,822,361×48) r24 #0047ff
- getCode 布局实测：标题验证手机号；输入框两组 (41,253/330,332×54) r27 黑描边 pill；发送验证码 14px #555；确认 (13,820,388×49) r24.5 **橙 #ff7800**

### 1.2b 地址模块实测（pages/my/address/*，2026-10-21，含编译产物提取）

| 接口 | 请求 | 来源 |
|------|------|------|
| POST /api/user/address_list | `{}` | 抓包实测：`{code:1,msg:"地址列表",data:[]}` |
| POST /api/user/edit_address | 表单对象 | 编辑页 chunk 提取（`{url:"/user/edit_address"}`）；未实际提交（避免写原站账号数据），字段语义：id(编辑时)/s_name/s_phone/province/city/area/address/isdef(1/0) |
| POST /api/user/delUserAddress | `{id}` | chunk 提取 |
| POST /api/user/address_detail | `{id}` | chunk 提取 |

**地址列表页实测**：空态 addr-empty.png 146×112（y193）+ 蓝色按钮「去添加收货地址」145×36 r25 #0018ff（y339）；数据加载前展示「加载中」loading；有数据时的卡片结构原站不可观测（账号无地址），复刻按 App 风格实现并标注推断
**地址编辑页实测**：表单卡 (17,61,380×311) r13 padding 8px 17px；行高 57；标签 15px/700 定宽 99；placeholder 14px #c0c4cc（请输入姓名/请输入手机号/请选择市区/街道 小区 门牌号等）；分隔线 2rpx #E6E6E6；默认开关 u-switch 42×22 activeColor #0018FF activeValue 1；保存按钮 (17,820,380×50) r11 #0018ff fw600；导航标题按 id 切换「添加收货地址/编辑收货地址」（chunk 实测逻辑）
**省市区数据源**：原站从 OSS 拉取 `address/address.json`（抓包时 404，选择器数据来自缓存兜底）；独立项目改用本地 `china-division` 真实行政区划（规则9），直辖市「市辖区」列显示为省名（原站实测：北京市/北京市/东城区）；选择器底部弹层：取消 #4a4a4a / 确定 蓝，三列各 1/3 宽行高 44 选中加粗 #303133
**校验文案**：原站 chunk 未含保存校验提示，复刻按通用文案（请输入姓名/请输入手机号/请选择市区/请输入详细地址）并标注推断

### 1.2a 钱包页实测（pages/wallet/wallet，2026-10-21）

- 合规提示条：`* 所有资金均由第三方持牌支付机构进行支付、清算`（#eef2ff 底 #2a51ef 字）
- 三张钱包卡（378 宽 r13，卡间距 13px）：钱包A（汇付 icon、已开通、余额=userinfo.money、footer 蓝 rgb(42,81,239)）/ 钱包B（敬请期待对接中、footer 青 rgb(74,142,176)）/ 钱包C（footer 绿 rgb(107,154,74)）
- **钱包A 点击进入汇付钱包内嵌页**（原站为 webview 加载第三方汇付 H5：金色银行卡+余额打码+提现/充值+账单管理/银行卡管理/支付管理/账户管理/更多）
- 独立项目处理：内嵌页复刻为本地页面 `/pages/wallet/huifuDetail`（余额打码 *****、静态数据、图标以等效 SVG 绘制），不依赖汇付服务（规则9）；金色卡背景以 CSS 渐变近似（外部资源不可下载）

### 1.2 POST /api/user/getUserInfo —— 个人信息

请求 `{}`；响应 data 实测字段：id, group_id, username, nickname, email, mobile, avatar, level, gender, birthday, bio, money, agent_money, score, successions, maxsuccessions, prevtime, logintime, loginip, loginfailure...

**「我的」页依赖字段（2026-10-21 实测补充）**：
| 字段 | 实测值 | 页面用途 |
|------|--------|----------|
| nickname | Q9GSWCuI2 | 昵称 |
| avatar | /defautAvatar.png | 头像（默认值时前端显示 QBOX logo） |
| real_name_authentication | 1 | 实名/已认证徽章 |
| nft_url | 0x590920c9ee27704a7907118974a95586b29b783d | 区块链地址（显示前10位+...+后6位） |
| is_vip / vip_time | 0 / 0 | VIP 状态「未开通」 |

**设置页（pages/my/setting）实测（2026-10-21）**：
- 行列表（h52 r14 白底独立行）：头像(33×33 r8 缩略图)/昵称/手机号(打码 175****1293，无箭头)/我的认证/我的地址/操作密码/注销账号
- 跳转实测：我的认证→/pages/certification/certification、我的地址→/pages/my/address/list、操作密码→/pages/my/actionPwd/setPwd、注销账号→/pages/my/cancellation
- 退出登录：固定底部 378×45 r22 #0018ff；点击弹确认框（原站弹窗 UI 未观测到，复刻按 uni-modal 通用样式实现并标注推断），确定后清除 token 回登录页
- 头像/昵称编辑为原站内置裁剪/编辑弹层，待后端上传接口接入

**「我的」页入口跳转（全部实测）**：钱包→/pages/wallet/wallet、收藏→/pages/myCollection/myCollection、订单→/pages/order/list、求购→/pages/order/beg/list、委托→/pages/order/entrust/list、寄售→/pages/consignments/myConsignment、合成→/pages/compound/record、活动→/pages/compound/task、邀请→/pages/invitation/invitation、设置→/pages/my/setting、VIP横幅→/pages/my/vip、已售出资产→/pages/myCollection/soldAssets
持仓市值实测显示 ¥0（计算源接口未暴露，前端默认 0）

### 1.3 POST /api/user/sendsms —— 发送短信验证码（复刻差异点）

> 原站由顶象验证码 + 短信通道实现（puzzleCaptcha 开关）。独立项目改为自建验证码：mock 阶段固定验证码 123456，响应 `{code:1, msg:"验证码已发送"}`。

### 1.4 POST /api/user/mobilelogin —— 验证码登录/自动注册

请求 `{mobile, captcha}`；未注册自动注册（原站文案「未注册账号将自动注册」）。

---

## 2. 首页模块

### 2.1 POST /api/index/getConfig —— 基础配置（键值批量）

请求（实测 key 全量）：
```json
{"key":"resell_collection,copyright,transfer_collection,appLogo,appBigLogo,pay_audit,versionCode,base_apk_version,app_version,appName,puzzleCaptcha,cancellation,pay_time_limit,h5_domain,draw_notice,credit_goods_dhxz,credit_goods_zysx,service_image,se...(截断)"}
```
响应 data 实测节选：
```json
{"appName":"qbox","app_version":"1.0.2","base_apk_version":"1.0.0",
 "appBigLogo":"/uploads/20240118/17b679f0562f639f96344f08e59e0729.jpg",
 "pay_time_limit":"300","batchBuy_check":"0","puzzleCaptcha":"0",...}
```

### 2.2 POST /api/banner/show/getBanner —— 首页 Banner

请求 `{"type":1}`；响应 data.data 实测：
```json
[{"id":1,"image":"/uploads/20261012/be317f845530f581d20c38c14a41b8f2.jpg","url":"","url_type":1,"is_show":1}]
```

### 2.3 POST /api/index/afficheList —— 公告列表（跑马灯）

请求 `{}`；响应实测：
```json
{"total":1,"data":[{"id":3,"title":"【QBOX官方】平台现已开放IOS、安卓下载入口！","createtime":1792490989,"type":"1","url":"","affiche_type":"官方"}]}
```

### 2.4 POST /api/index/affichetypelist —— 公告类型（发现页筛选）

响应实测：
```json
[{"id":1,"name":"官方"},{"id":2,"name":"寄售"},{"id":3,"name":"运营"},{"id":4,"name":"限价 "},{"id":5,"name":"活动"},{"id":6,"name":"合成"},{"id":7,"name":"求购"},{"id":8,"name":"空投"}]
```

### 2.4a 发现页接口组（2026-10-21 交互实测补充）

| 接口 | 请求 | 说明 |
|------|------|------|
| POST /api/index/afficheList | `{"page":1,"category":0,"list_rows":25}` | 公告列表；category=分类id（0=全部），列表项 {id,title,image:'',createtime,type,affiche_type} |
| POST /api/discover/ant_news/category | `{}` | 新闻分类：`[{"id":1,"name":"新闻中心"}]` |
| POST /api/discover/ant_news/list | `{"page":1,"category":0,"list_rows":25}` | 新闻列表：{id:1,title:"QBOX开启数字文创合规规模化新时代",...} |
| GET /api/afficheDetail/ids/3 | （实测路径带 ids/{id}） | 公告详情：{id,title,describe,content:"<img...>",createtime,publish_time,affiche_type_id,...}，content 为 HTML 字符串（rich-text 渲染）

**公告详情页（pages/notice/noticeDetails）实测**：
- 面包屑「公告 › 官方」（category 取路由参数）；标题 19px Medium #161616
- 头图 = **正文第一张图裁剪 h220 cover**（非独立 image 字段；原站实测行为）
- 正文：rich-text 渲染 content HTML，字距 2px、行高 28px
- 尾部固定展示平台合规文案（风险提示/郑重提醒/反洗钱提醒）——前端硬编码，不属于 API content
- 正文图片已本地化至 /uploads/20261020/（原为 img.xiyanwl.asia CDN，规则9）

**发现页搜索页实测（pages/notice/search，2026-10-21，chunk 反解）**：
- 顶部：返回箭头(13px rotate180) + u-search 搜索框（placeholder「请输入搜索内容」；action「搜索」宽 0 不可见=showAction:false，触发为键盘确认）
- **搜索历史**：localStorage `notice-history`，≤10 条 unshift 去重；`showHistory = history.length && hasFocus`（失焦 100ms 收起）
- 搜索：`search(词)` → setHistory → `afficheList {search, page}`；空词 toast「搜索内容不可为空」；onLoad 支持 `?keyword=` 直搜；触底 loadMore page++
- **结果列表项**（与公告列表页灰卡不同版）：688×171rpx 白透明卡 rgba(255,255,255,.71) r19 padding 20/24；标题 21rpx fw600 #181818；tag padding 4/12rpx 文字 19rpx 白，**tagBg：含"运营" #9aa0b8、其余（含"寄售"）一律 #ff7800**；时间 16rpx #444 格式 YYYY/MM/DD HH:mm；右缩略图 238×137rpx r16，无 image 时 #e8e8e8 灰占位
- 空态 284rpx aspectFit(=contain)；typeArr 仅用于数据打标不渲染；点击项 → noticeDetails?id&category

**公告列表页实测（pages/notice/noticeList，2026-10-21）**：
- **本质是公告搜索结果页**（chunk 反解 + 原站两次实测确认）：onLoad 读 `keyword` 参数 → `afficheList {search: keyword, page:1}`；**无 keyword 时页面稳定渲染为空白**（原站自身的状态，按原样复刻）
- 带关键词实测（?keyword=QBOX）：返回 2 条（id=5 活动 / id=3 官方）；卡片：padding 18px 17px、bg #f2f4f5、r17、间距 11px、标题 16px fw300 #000 两行截断、时间右对齐 12px #a3a3a3（格式 Y-m-d H:i:s）
- 点击 → `/pages/notice/noticeDetails?id=5&category=活动`（与原站跳转逐字符一致）
- 空结果分支（search-empty.png + 暂无内容）存在于 chunk 但未被观测到，按 chunk 结构实现并标注推断

**发现页交互实测**：
- 顶部双 Tab：平台公告 / 新闻发声（选中黑色加粗，未选中 #8c8c8c）
- 分类横滑卡：全部(0) + 分类；「全部」「官方」为文字盒（选中深色渐变 linear-gradient(49deg,#161616,#362c1f,#222)+金字 #fbf2d5，未选中 #bdbdbd），其余分类为原站图片 notice-cat-*.png（opacity .55）；选中卡片加阴影 rgba(0,0,0,.23) 0 1px 6px
- 列表项点击跳转：`/pages/notice/noticeDetails?id=3&category=官方`（带分类名）
- 搜索框点击跳转：`/pages/notice/search`
- 列表缩略图：无 image 字段时官方公告显示 notice-banner-official.png（原站实测同款）

### 2.5 POST /addons/diypage/index —— 首页 DIY 装修数据

请求 `{}`；响应实测（节选）：
```json
[{"id":3037,"type":"menu","category":"home","content":"{\"style\":4,\"list\":[
  {\"image\":\"/uploads/20241209/a265b5f1becc0a42a954a6e7926d6056.png\",\"path\":\"/pages/compound/compound?thm=1\",\"name\":\"活动入口\",\"path_type\":2},...]"}]
```

### 2.6 POST /api/box/blind_box/list —— 发售日历（首页）

请求 `{"list_rows":50,"page":1}`；实测当前 `data: []`（发售日历空）。

---

## 3. 市场模块

### 3.1 POST /api/index/getSeries —— 系列/板块

请求 `?type=10`；响应实测节选：
```json
[{"series_name":"我的关注","series_id":-2,"is_def":0,"flag":0,"pattern":1,
  "resale_put_start_time":"00:00:00","resale_put_end_time":"00:00:00",...}]
```

### 3.2 POST /api/market/market/getMarketList —— 市场列表

请求（实测）：`{"goods_type":2,"page":1,"list_rows":10,"series_id":2,"add_deal_num":1}`
说明：goods_type 2=权益品；当前实测 `data: []`。

**series_id 实测语义（Tab/板块切换抓包）**：
| series_id | 含义 | 实测来源 |
|-----------|------|----------|
| 2 / 3 … | 活动市场的具体板块（经典文化=2 默认选中 is_def=1、怀旧精品=3） | 活动市场 Tab |
| -1 | 推荐（板块卡显示，值 "--"） | getSeries 返回 |
| -2 | 我的关注 | 「我的关注」Tab |
| -13 | 自由市场（个人集市） | 「自由市场」Tab |
| 关键词搜索 | `{"goods_type":2,"keywords":"xxx"}` | 点击搜索框跳 /pages/market/search |

**市场页交互实测（2026-10-21）**：
- 「今日」→ 底部弹窗「选择日期」：月历（周一起始）、当月水印数字、未来日期置灰、选中日期蓝底(#0024ff)白字圆角、底部 取消(白底描边)/确定(蓝底)
- 「全部份数」→ 底部弹窗 6 选项：全部份数（选中态白底黑描边）/大于10000份/小于10000份/小于5000份/小于1000份/小于100份；底部 重置(白)/确定(黑)
- 板块卡选中态：bg linear-gradient(#eee,#fdfdfd)、圆角 19.9rpx、白描边；未选中 linear-gradient(#fff,#fefefe)
- 自由市场 Tab 仅显示「个人集市」标题+列表；我的关注 Tab 仅显示「我的关注」标题+列表（无板块/筛选/搜索行）

---

## 4. 我的 / 资产模块

### 4.1 POST /api/user_collection/user_collection/antMycollection —— 我的收藏

请求 `{"types":2,"page":1}`（2=权益品，1=盲盒/其他）；响应分页结构 + `current_count`、`current_consignment`（当前寄售数）。

**空态图渲染规则（2026-10-21 深度比对后二次修正）**：common-empty.png 原图 277×405（竖版含"这里空空的！"文字）。原站 uni-image 实测结构：**156×156 正方容器 + `background-size: contain`** → 图等比缩放为 **106.7×156** 水平居中显示。修正过程：① 初版固定正方+fill → 压扁；② 改 height:auto → 宽156×高228 比原站大 46%；③ 最终 `width/height:282.6px + object-fit:contain`（与 uni-image contain 完全等价，猫宽像素级一致 100.3pt=100.3pt @393）。共 12 个页面。

**原站改版监测（2026-10-21 晚间）**：收藏页（myCollection）当天早上实测含搜索框+2 分类卡，晚间复测**已不渲染**（登录态正常，仅剩导航+空态图）。项目当前保留早上的完整复刻版本；若需跟随原站最新（去掉搜索区），待用户确认后调整。

**已售出资产页实测（2026-10-21）**：调用 `antMycollection {types:3, page:1}` —— **types=3 即已售出**（收藏页 types=2 权益品；补充完整 types 语义：1=盲盒/其他、2=权益品、3=已售出）。页面：白底、白底导航（标题 17px/500 #111，与其他页 18px 略不同，实测如此）、mescroll 列表（padding 4px 13px 44px）+ 黑猫空态 156×156（y102）+ mescroll-totop 回顶（right 11 bottom 66 w39）。有已售资产时的卡片结构原站不可观测（账号无已售），复刻按接口字段语义实现并标注推断。

**收藏页实测（2026-10-21）**：
- 分类为 **2 张组合卡**（非 4 个独立Tab）：「权益品」主标签+「数字藏品」副标签（共用 collection-nav-equity.png 圆形图标）、「盲盒/其他」主标签+「其他个人资产」副标签（共用 collection-nav-blindbox.png）
- 选中卡：白底；未选中：#f2f2f2 底；边框 3px #fefefe；主标签选中 #111 / 未选中 #c8c8c8，副标签 #8c8c8c / #d0d0d0
- 搜索框 placeholder「输入藏品名称关键词搜索」，右侧放大镜（common-search-black）
- 列表为空 → 黑猫空态；默认请求 types=2

### 4.2a POST /api/market/trade/begBuyOrderList —— 我的求购（2026-10-21 实测）

请求 `{"page":1,"status":null,"keywords":""}`；响应 `{code:1,msg:"订单信息",data:{total:0,per_page:15,current_page:1,last_page:0,data:[]}}`。

**求购页 Tab→status 映射（逐 Tab 抓包实测）**：全部=null、求购中=2、已成交=3、已取消=4
- 搜索：输入藏品名称或订单号，回车/点放大镜 → keywords 参数重新请求
- 布局实测（414px）：导航行 y20 h48（返回 13px 黑箭头旋转180°、标题 17px Bold 居中、右占位）；Tabs 4 项**等分** flex1（h44，项内文字居中，选中 #111 未选中 #8c8c8c，蓝下划线 18×2 #0018ff 300ms 滑动）；行尾搜索图标 14×14；搜索框 (19,123,376×39) r19 bg#eee 输入 13px 右灰放大镜 19×19；空态黑猫 156×156（y244，图片自带「这里空空的！」文案）
- 有求购单时的卡片结构原站不可观测（账号无求购单），复刻按接口字段语义实现并标注推断

### 4.2b POST /api/market/Prepayment/myOrderlist —— 我的委托（2026-10-21 实测）

请求 `{"page":1,"status":null,"keywords":""}`；响应与求购列表同构 `{code:1,msg:"订单信息",data:{total:0,...,data:[]}}`。
**委托页 Tab→status 映射（逐 Tab 抓包实测）**：全部=null、委托中=2、已成交=3、已取消=4（与求购页一致）
页面 DOM 结构与求购页完全一致（仅标题「我的委托」、Tab 文案「委托中」、接口不同）。

### 4.2c POST /api/order/order/myConsignment —— 我的寄售（2026-10-21 实测）

请求 `{status:"", goods_type:2, page:1}`；响应 `{code:1,msg:"寄售列表",data:{total:0,per_page:10,...,data:[]}}`。
**寄售页 Tab→status 映射（逐 Tab 抓包实测）**：全部=`""`（空字符串，与求购/委托的 null 不同！）、寄售中=`"1"`、已成交=`"2"`；goods_type 固定 2。
- 搜索 placeholder「输入藏品名称搜索」（无"订单号"字样，与求购页不同）
- 3 个 Tab 等分（item 127.7×44 @414，下划线 translate 54.83）；结构与其余订单列表页一致
- 有寄售单时的卡片结构原站不可观测（账号无寄售单），复刻按接口字段语义实现并标注推断

### 4.2 POST /api/order/order/orderList —— 我的订单

请求 `{"page":1,"status":0,"keywords":""}`；status 0=全部。

**订单页 Tab→status 映射（逐 Tab 抓包实测，2026-10-21）**：
| Tab | status |
|-----|--------|
| 全部 | 0 |
| 已完成 | 2 |
| 批量付 | 1 |
| 待支付 | 7 |
| 进行中 | 3 |
| 已取消 | 3（与进行中相同，原站实测两次独立验证） |

- 搜索：输入藏品名称或订单号，回车/点放大镜 → keywords 参数重新请求
- Tab 下划线 18×2 蓝色，选中黑字未选中 #8c8c8c，tabs 可横向滚动且自动滚到选中项

### 4.3 POST /api/market/trade/begBuyOrderList —— 我的求购

请求 `{"page":1,"status":null,"keywords":""}`。

### 4.4 POST /api/market/Prepayment/myOrderlist —— 我的委托

请求 `{"page":1,"status":null,"keywords":""}`。

### 4.5 POST /api/user/ranking —— 邀请统计/排行

请求 `{"page":1,"types":2}`；响应实测：
```json
{"data":{"my_list":[],"vip_time_num":0,"real_name_num":0,"yx_num":0,"total_num":0,"buy_num":0},"my":"","activityRule":""}
```

---

## 5. 签到（signin 插件）

### 5.1 POST /addons/signin/api.index/index —— 签到页数据

响应实测：
```json
{"signinscore":{"s1":"1","s2":"2","s3":"3","s4":"4","s5":"5","s6":"6","s7":"7","sn":"7"},
 "isfillup":"1","fillupscore":"100","fillupdays":"3","ranklist":[...]}
```

### 5.2 POST /addons/signin/api.index/monthSign —— 本月已签日期

响应实测：`{"21":"21"}`（key=已签日期）。

### 5.3 签到/邀请页补充实测（2026-10-21）

- 连续签到天数来自 `getUserInfo.successions`（登录响应不含该字段）；已签日（21 号）在日历中渲染为蓝色「已签到」徽章（#2a51ef）；当日已签时底部按钮置灰「已签到」（#cacdd3）
- 连签数字颜色实测 rgb(42,81,239)；日历表头 SUN~SAT、跨月日期置灰、固定 6 行
- 邀请页二维码原站由 `/addons/qrcode/index/build?text=<邀请码>` 接口生成；独立项目改为本地 `qrcode` 库生成（内容 = 站点登录页链接 + 邀请码），规则9 合规
- 邀请页按钮实测：保存图片 115×39 蓝描边(r10)；复制邀请链接 flex1 黑底白字(r10)；点击复制邀请码/链接 → 剪贴板 + toast
- 邀请记录：`user/ranking` 的 my_list 为空 → 黑猫空态

---

## 5a. 抽奖模块（2026-10-21 实测）

| 接口 | 请求 | 响应实测 |
|------|------|----------|
| POST /api/lottery/info | `{"id":"1"}` | `{"code":0,"msg":"暂无抽奖活动","data":null}`（前端展示深色气泡 + 剩余次数 0） |
| POST /api/lottery/statistics | `{"lottery_id":"1"}` | `{"code":1,"data":[{"id":1,"type":"10","num":0,"name":"奖品一：藏品《小玩偶》","image":"/uploads/20261013/8913...png","lottery_image":"/uploads/20261013/8913...png"}]}` |

**抽奖页交互实测**：
- 页面 `/pages/lottery/lottery?id=1`；右上「抽奖记录」胶囊 → /pages/lottery/record
- 本期开池蓝卡的「本期开池/每次抽奖可获得 10 积分/QBOX LOTTERY ACTIVITY」文字全部烙在 lottery-bg.png 背景图中（DOM 无文字节点），前端只叠加 4 个奖槽（43×43，首格显示首个奖品 lottery_image）+「全部奖品」白色胶囊
- 当前奖池卡：奖品图 88×88 + 名称；全部奖品按钮跳转未观测
- 抽奖五次/一次按钮：无活动时点击行为未观测，保持无操作

## 6. 积分商城 / 合成

### 6.1 POST /api/crystal_goods/list —— 积分兑换商品

请求 `{"type":"30","page":1,"crystal_type":""}`（30=兑换藏品）；实测 `data: []`。

**积分商城页（pages/shop/shop）实测**：
- 可用积分显示 0（getUserInfo.score=1 但页面显示 0 → 积分另有数据源未在抓包出现，前端默认 0）
- 「积分兑换藏品」区：列表为空时原站**停留显示加载中**（u-loading-icon + 加载中），如实复刻
- 「积分兑换实物」区：标题图 points-physical-title.png + 黑猫空态
- 背景图 points-bg.png（QBOX 水印 + 金币插画，size 100% auto 顶部）

### 6.2 POST /api/synthesis/themeinfo —— 合成活动主题

请求 `{"thm":0}`；实测 `{"code":0,"msg":"未开启","data":null}`（合成未开启时的原文案）。

### 6.3 POST /api/synthesis/themeList —— 活动中心主题列表

请求 `{"thm":"1","type":1}`；响应实测：
```json
[{"id":1,"types":1,"name":"1","image":"1","rule_text":"1","play":[]}]
```
说明：当前主题为测试占位数据（image="1" 非法），活动中心「热门活动」区原站实际渲染为黑猫空态。
活动中心页（pages/compound/task）交互实测：抽奖活动卡片无跳转；合成活动卡片 → /pages/compound/compound?thm=1

---

## 6a. VIP 会员页（pages/my/vip，2026-10-23 实测）

### 6a.1 POST /api/index/getConfig —— 站点配置（VIP keys）

请求 `{"key":"vip_agreement,vip_time,vip_service_charge"}`；响应实录：
```json
{"code":1,"msg":"基本配置","data":{
  "vip_time":"2678400",              // 31 天（秒）
  "vip_service_charge":"29.9",       // 月费
  "vip_agreement":"<p>1. 开通 QBOX VIP 即表示您知悉并同意会员服务条款…</p><p>2. …</p><p>3. …</p><p>4. …</p>"
}}
```
说明：`getConfig` 为通用配置接口（key 逗号分隔，data 只含所请求 key），本页取 VIP 三项。

### 6a.2 页面逻辑（chunk pages-my-vip.b73c7be4.js 反解）

- `init()`：getConfig 拉配置 → `getUserInfo()`，`havetime = data.vip_time - data.time`；`isVip = userinfo.vip_time && havetime > 0`
- `isVip=true`：卡片二显示 头像(88 圆) + 昵称(28 #fff4d9) + mine-member.png 皇冠(28) + VIP 徽章 + 「会员有效期：YYYY-MM-DD」（vip_time 秒→日期，22 #8c8c8c），按钮文案「立即续费」
- `isVip=false`：「成为VIP会员 / 开启你的数字藏品之旅 / ¥29.9/月」（价格字体 AlimamaShuHeiTi-Bold，原站 CSS 中该字体别名映射到 AlimamaFangYuanTiVF-Thin.ttf），按钮「立即开通」
- 特权列表（写死在 chunk）：专属藏品/独家空投、优先购买/抢先拥有、专属权益/总量购买、专属客服/优先响应（图标 88rpx 4 枚）
- `onOpen()`：清空勾选；`config_vip_time > 0` 弹「开通VIP协议」弹窗，否则 toast「暂未开放VIP开通」
- `onAction()`：未勾选 → toast「请先勾选是否同意协议」；通过 → `createVIPOrder({})` 后拉起支付（order_type: 21，pay_type 取 basePayList[0]）

### 6a.3 协议弹窗（vip-modal 组件实测 @414 视口）

- 白底圆角弹窗宽 360px，标题「开通VIP协议」；协议滚动区高 600rpx（正文 14px/行距21px，4 段 12 行全部可见）
- 圆形勾选框（选中 #0018ff）+「我已阅读并同意以上协议」（12px #333）
- 底部按钮 138×44 r6：取消（#f1f0f0 黑字）/ 确认（#0018ff 白字），间距 28px

### 6a.4 布局实测（@414 视口，原站 vs 克隆）

| 元素 | 原站 y | 克隆 y |
|------|--------|--------|
| 特权卡顶 | 244 | 245 |
| 开通卡顶 | 420 | 422 |
| 购买说明顶 | 555 | 555 |

注：原站 w-navbar 为 fixed+占位，内容区 padding-top 364rpx 基础上整体下移导航高 79.7rpx，背景图（vip-bg.png，含 QBOX VIP 金字+皇冠）不随动。

### 6a.5 POST /api/order/createVIPOrder —— 创建 VIP 订单（推断）

原站下单接口未抓包（避免向原站账号真实下单）。按 chunk 调用语义 mock：返回 `{code:1, msg:"下单成功", data:{order_sn:"VIPORDER+时间戳", order_id:...}}`；克隆版确认协议后 toast「下单成功，支付通道暂未接入」（支付拉起不在本页范围，见差异点 #4）。

---

## 6b. 盲盒模块（pages/blindBox/*，2026-10-23 实测）

### 6b.1 接口实录

| 接口 | 请求 | 响应实测 |
|------|------|----------|
| POST /api/box/blind_box/openlog | `{page:1,list_rows:20}`（+blind_box_id 由详情页入口传入） | `{"code":1,"msg":"开盲盒记录","data":{"total":0,"per_page":"20","current_page":1,"last_page":0,"data":[]}}` |
| POST /api/user_collection/user_collection/detail/ids/{id} | 路径参数 id | 无效 id：`{"code":0,"msg":"收藏不存在或者已经被删除了","data":null}`（详情页仍渲染壳：主图/名称/发行数空白 + 藏品介绍/购买须知卡） |
| POST /api/user_collection/user_collection/openbox | `{openid: blindBoxId}` | 未抓包（避免真实拆盒），mock 返回 code:1 + data:null（结果页呈「藏品编号：#」空态） |
| POST /api/user_collection/user_collection/openBoxs | `{openid: blindBoxId}` | 同上（一键开启，结果页仅 hint + 底部按钮） |

### 6b.2 页面逻辑（chunk 反解）

- **我的盲盒 index**：`info = storage('blindBoxInfo')`（收藏页盲盒入口写入 blindBoxId/listimg/num/id）；直接访问即观测空态（图空 + 「共有个」）。一键开启 `openBoxs` → storage('blindBoxResultList') → redirectTo result；逐个拆开 `openBox` → 全屏纯黑 `pop_con` 5 秒 → storage('blindBoxResult') → redirectTo result2；立即寄售 → /pages/consignments/consignments {id, pageType:1}
- **开盒记录 openRecord**：`getOpenRecord({blind_box_id, page, list_rows:20})`，触底加载更多；total==0 → search-empty.png 空态「暂无内容」；行结构：昵称(28rpx)+时间(24rpx #7a7a7a) | 「抽中」+藏品名
- **盲盒结果 result**（一键开启）：列表项 listimg 59×59 r4 + name(26rpx 500) + 「藏品编号: #code」(24rpx) + rarity_icon 59×55（uni 默认纵向布局，标注推断）；hint「商品陆续从链上转入个人中心，如有延迟请耐心等待」(20rpx 居中)；底部 我的数字藏品(navigateBack)/继续购买(switchTab 首页)，按钮 320×92 r50 #f1f0f0/#ff7800
- **盲盒结果 result2**（逐个拆开）：rarity_icon 80×59 + main_image 200×200 r10 + name(32rpx #555) + 「藏品编号：#code」(24rpx #999)，纵向居中；底部按钮同上（我的数字藏品 → redirectTo myCollection）
- **盲盒详情 blindBoxDetails**：背景 `market-goods-bg.png`（#f7f7f7 + 顶部大图）；主图 422×422 r45 于 436 盒内；深色卡 #1b1d20 r24 padding 27/34 mx45（name 32rpx + 「发行数 total_num」21rpx #a3a3a3，showPrice 时显示 ¥42rpx #0018ff）；白色卡×2（r32 padding 32/28）：QBOX 水印(51rpx rgba(104,104,104,.08) NanoByongGyeHei) + 标题 32rpx AlimamaShuHeiTi(映射 FangYuanTiVF-Thin) #2d2d2d + 蓝条 #0018ff 10rpx；购买须知为固定文案；底部操作按钮（详情成功时）：status==1 → 立即寄售(weither_resale==0)/立即转赠(weither_regift==0)，status==2 → 锁定中(is_lock==1,#cacdd3)/取消寄售(确认弹窗「确认取消此寄售？」)，按钮 88rpx r44 #0018ff 白字

### 6b.3 布局对齐（@414 视口，原站 vs 克隆）

| 元素 | 原站 | 克隆 |
|------|------|------|
| 我的盲盒·一键开启按钮 | x70-344, y660-707 | x70-344, y659-706 |
| 详情页·发行数黑卡 | y287-342 | y289-340 |
| 开盒记录空态图 | y67-205 | 一致 |

---

## 6c. 积分商城剩余页（pages/shop/*，2026-10-23 实测）

### 6c.1 接口实录

| 接口 | 请求 | 响应实测 |
|------|------|----------|
| POST /api/crystal_goods/crystalDetail | `{page:1}`（筛选后带 cat/type/m） | 空列表：`{"code":1,"msg":"successfully !!!","data":{"total":0,"per_page":15,"current_page":1,"last_page":0,"data":[]}}` |
| POST /api/crystal_goods/exchangeRecord | `{page:1}` | 同上（空列表） |

（龙晶相关另有 crystal_goods/exchange（兑换）、getECardPass 等接口，未抓包）

### 6c.2 页面逻辑（chunk 反解）

- **龙晶明细 detailList**：吸顶（导航「龙晶明细」+ w-tabs 全部('')/龙晶获取('1')/龙晶消耗('-1')，下划线 18×4rpx #393939，字 28rpx #333）；筛选组件含 种类（黄水晶yellow/紫水晶purple/蓝水晶blue）与时间（两年月份）下拉（isshow 初始 false，原站无可观测触发入口，仅实现结构）；列表行 = memo(32rpx #555 溢出省略) + 时间(20rpx #999，格式 yyyy.mm.dd hh:MM:ss) ‖ 水晶图标（model→hsj/zsj/lsj，40rpx）+ 数值(40rpx #000)；空态 emptyType=search1（search1-empty.png 265rpx + 暂无内容 26rpx #999）
- **兑换记录 record**：行卡片 200rpx 高 r16 白底 padding18 mb24：list_image 83×83 r8 + goods_name(32rpx 500) + 水晶图标+crystal_number(32rpx) + 时间(20rpx #ababab)；点击 → /pages/order/defaultDetail {id: order_id, orderType:4}；行尾 right3.png 箭头（原站该图标 404 实际不可见，如实省略）；空态同 search1
- **兑换确认 exchange**：直接访问原站渲染纯白空白（无导航无请求，实测截图），需商品参数进入；有效 UI 不可观测，按规则7仅复刻空白壳
- **商品详情 detail/box|collection|goods**：直接访问（无参数）原站重定向回首页（实测 URL 变为 /pages/index/index）；有效 UI 不可观测，复刻重定向行为
- 水晶图标（/static/icon/hsj|zsj|lsj.png）已下载至本项目

### 6c.3 布局对齐（@414 视口，原站 vs 克隆）

| 元素 | 原站 | 克隆 |
|------|------|------|
| 龙晶明细空态 | y364-489, x160-252 | y364-493, x160-252 |
| 兑换记录空态 | y315-440, x160-252 | y315-443, x160-252 |

---

## 6d. 合成模块剩余页（pages/compound/*，2026-10-23 实测）

### 6d.1 接口实录

| 接口 | 请求 | 响应实测 |
|------|------|----------|
| POST /api/synthesis/recordList | `{page:1}` | 空列表（同 crystalDetail 结构） |
| POST /api/synthesis/recordInfo | `{record_id}` | 无效 id：`{"code":0,"msg":"记录不存在","data":null}` |
| POST /api/synthesis/playInfo | `{play_id,grp,classify}` | 无效参数：`{"code:0,"msg":"玩法不存在","data":null}` |
| POST /api/synthesis/synthesizeNow / selectCollection / material | — | 未抓包（避免真实合成/写入），mock 按调用链语义实现，已标注推断 |

### 6d.2 页面逻辑（chunk 反解）

- **活动记录 record**：导航为自绘 88rpx 行（back = 右箭头图旋转180°；statusBarHeight H5 兜底 20px）；行卡 684rpx r24：result[0] 图（120rpx 黑底 #0e0f12）+ 产物名（28rpx Bold，多件「name xN」）+ 「结束时间：create_time」22rpx；空态 common-empty.png 284rpx（图内含「这里空空的！」文字）；行点击 → 材料详情弹窗（w-modal 652rpx：consume 列表 图42+名称+销毁/保留/回收 statusText{10,20,30}）+「去查看我的数字藏品」
- **合成结果 result**：bg market-goods-bg（100% 994.87rpx）；result_box.png 626rpx 大图（合成成功盾牌金字）；结果列表（recordInfo 成功时）：白卡 r32 → 项 #f7f7f7 r20 居中，type C → #code/total_num、type M → ×number（AlimamaShuHei）、type S → 水晶图标+single_quantity；底部 我的藏品(#0e0f12)/再合一次(#0018ff) 88rpx r19
- **合成活动详情 schemeDetails**：黑图框 436rpx r64 白边6px 阴影（mt 139rpx）；时间卡 rgba(255,255,255,.83) r32（title 空 + 「合成时间：start - end」22rpx #8c8c8c）；合成材料卡（changeMaterials：材料组「需要N份材料;已选M份材料」+ 一键选择材料按钮 + 148rpx 材料格）；立即合成 → w-toast「您选择了N个藏品，合成成功后将进行销毁/回收/保留，确认合成？」(再想想)；确认 → synthesizeNow → 成功弹窗 590rpx（恭喜您，合成成功 + 320rpx 结果图黑底 + 前往查看/继续合成）；isDisabled=材料组未全部选满（原站按钮仍可点弹确认）
- captchaStatus = config.synthetic_check（当前 "0"，不走验证码）

### 6d.3 布局对齐（@414 视口，原站 vs 克隆）

| 元素 | 原站 | 克隆 |
|------|------|------|
| 活动记录空态猫 | y135-290, x154-260 | y136-292, x153-260 |

---

## 6e. 产品详情模块（pages/product/*，2026-10-23 实测）

### 6e.1 接口实录

| 接口 | 请求 | 响应实测 |
|------|------|----------|
| POST /api/box/blind_box/BoxDetail/ids/{id}?origin=&crystal_goods_id= | 直接访问 id=undefined | `{"code:0,"msg":"找不到该盲盒，或者已经下架了","data":null}` |
| POST /api/box/collection/ProductDetail/ids/{id}?batch_id= | 直接访问 id=undefined | `{"code:0,"msg":"找不到该商品，或者已经下架了","data":null}` |

### 6e.2 页面行为（chunk 反解）

- **申购规则 ruleDetail**：原站为纯静态硬编码页（组件名 GoodCardRules），固定 px 布局（非 rpx 体系）。内容：蓝紫渐变（#667eea→#764ba2，原站设计原样保留）信息卡（藏品名称 Good 夺宝卡 / 申购价格 99元/份 / 发行总量 100,000份 / 申购时间 2026.01.01-2026.01.02）+ 申购规则 + 申购流程 + 结果与退款（全额原路退回 绿色强调）
  - ⚠️ **P0 差异登记**：原站该页使用 emoji 表情字符作图标；克隆版按项目 P0 绝对规则以统一 SVG 描边图标等义替换（几何占位一致），1️⃣2️⃣3️⃣ 序号以圆形数字徽章替代
  - vite 插件新增 `@nopx` 豁免标记：style 块含该标记时跳过 px→vw 转换（仅本页使用，不影响其他页面）
- **product/box、product/collection**：需商品参数进入；直接访问请求详情失败（实录见上）后原站重定向回首页（实测最终 URL=/pages/index/index）；有效参数下的详情 UI 不可观测（商品数据无法获取），按规则7仅复刻「请求详情→失败→重定向首页」行为

---

## 6f. 委托/求购提交模块（pages/market/submit* 与 sell*，2026-10-23 实测）

### 6f.1 接口实录

| 接口 | 请求 | 响应实测 |
|------|------|----------|
| POST /api/market/trade/collectionDetail | `{collection_id}` | 无 id：`{"code:0,"msg":"藏品ID不能为空","data":null}` |
| POST /api/market/Prepayment/collectionDetail | `{collection_id}` | 同上 |
| POST /api/market/trade/getSellCollectionInfo | — | `{"code:0,"msg":"藏品不存在","data":null}` → 页面重定向首页 |
| POST /api/market/market/marketGoodsDetail | `{type:2}` | `{"code:500,"msg":"An error occurred"}` → 页面重定向首页 |
| POST /api/market/trade/createBegBuyOrder、/api/market/Prepayment/createOrder | — | 未抓包（避免真实下单），mock 返回 order_id/order_sn，标注推断 |

### 6f.2 页面逻辑（chunk 反解）

- **发起求购 submitBegBuy**：商品卡 678rpx r22（图 140rpx 黑底 r20 + 名称 + 当前藏品最高价/求购价 ¥0 + 返回市场按钮 #0018ff r13）；求购数量步进器（76/72/160rpx bg #f5f5f5，- + 40rpx）；求购单价输入（¥ 前缀 + 元/份 后缀，placeholder 请输入价格）；支付方式 = basePayList（反解主包：`[{name:汇付, aname:A, auth_key:huifu_account, pay_type:4, pay_way:huifu}]`，able=userInfo[auth_key]，单选勾选 SVG）；求购说明两段固定文案（24rpx #686868）；底部 fixed 合计：¥totalPrice（price*number 两位小数，空→--）+ 预付 330×88rpx r44 #0018ff（disable 时 opacity .5）
  - createOrder：price<地板价 → toast「当前求购价不得低于地板价的X%」；成功 → 拉起支付（order_type 11）
- **委托购买 submitEntrust**：商品卡（图 140 + 名称）；委托价格输入（border 2rpx #d2d2d2 r16）；购买数量步进器（bg #f9fafb，76/60/160rpx）；支付方式卡 134rpx r16 border #262626（选中 checkmark 实心 #262626/未选描边 #999，不可用→「去开通>」）；委托说明卡（chargeInfo.tips，无数据不渲染）；底部 合计 + 预付 330rpx **#ff7800**
- **sellBegBuy / sellInfo**：需参数进入；直接访问详情失败（实录见上）→ 原站重定向回首页，复刻该行为

### 6f.3 交互验证

| 场景 | 结果 |
|------|------|
| 数量 + → 2，单价 96 | 合计 ¥192.00 ✓ |
| 未选钱包/未填价 | 预付按钮 opacity .5 ✓ |
| 选钱包+填价+预付 | 下单成功 toast（支付通道未接入，推断） ✓ |
| sellBegBuy/sellInfo 直接访问 | 重定向首页 ✓ |

---

## 6g. 抽奖活动模块（pages/activity/* 与 pages/draw/*，2026-10-23 实测）

### 6g.1 接口实录

| 接口 | 请求 | 响应实测 |
|------|------|----------|
| POST /api/draw/get | — | `{"code:0,"msg":"活动不存在","data":null}` → 抽签活动页渲染纯白 |
| POST /api/draw/drawRecord | — | `{"code:1,"msg":"获取成功","data":[]}` |
| POST /api/draw/drawLog | — | 同上（中签记录空） |

（活动不存在时 sharing/助力/排行榜等 draw 系列页面无接口调用，为静态壳）

### 6g.2 页面逻辑（chunk 反解 + 截图实测）

- **抽签活动 activity/draw**：draw/get 活动不存在 → 纯白空白页（无导航，实测）
- **中签记录 activity/record**：drawLog 空 → search1 空态「暂无内容」
- **社群分享 communityShare**：深棕胶囊 #302920（您已成功获得助力 0 次 + 分享图片橙字）+ 上传图片格子（160rpx #e5e5e5，≤5M 3张）+ 表头（日期/上传图片数量/审核结果）+ 文件空态「暂无分享记录」+ 分享社群规则深棕按钮 + 底部 fixed 橙色「提交」
- **中签结果 drawResult**：依赖记录 id；直接访问 → 持续「加载中...」（circle spinner，实测）
- **邀请好友助力 InvitationFriendAssist**：邀请橙色胶囊 + 深棕胶囊（助力 0 次）+ 表头（日期/用户类型/为您助力用户/结果）+ 空态「数据为空」+ 助力规则按钮
- **参与人数 Leaderboard**：深棕胶囊「参与人数 0 人」（**全宽**）+ 底部 fixed 橙色「邀请好友助力」
- **分享社群 shareCommunityGroup**：深棕胶囊「分享社群，已获得 0 积分」+ 上传/表头/规则按钮同上；底部提交按钮为**黑色 #141416**（与社群分享页橙色不同）
- 布局对齐：社群分享规则按钮 y517-571（原站 523-579）；参与人数胶囊 (0,413,61,115)（原站 61-117）；提交按钮 y831-878（原站 830-879）

---

## 6h. 寄售与转赠模块（pages/consignments、pages/regift，2026-10-23 实测）

### 6h.1 页面行为

- **转赠记录 regift/record**：页面为转赠记录展示，**顶部演示卡为原站硬编码数据**（chunk 反解证实：古代中国与外国交通贸易 / PBCE/2000000000000000 / ✓待接收(rjs.png 绿勾) / 赠送者:数藏爱好者 / 转赠时间:2018-12-12 09:09 / 退回时间:1812061059386431 / 记录单号:5871545254154715741）；tabs 数据（我转赠的/我收到的）存在但渲染层未输出（原站如此）；卡内缩略图 rimg=../../static/img/testl.png **在原站已 404**，实际渲染为 u-image 错误占位（灰底圆+信息图标），如实复刻；下方 search-empty 空态「暂无内容」；无导航栏（原站如此）
- **转赠 regift/regift**：需 user_collection 参数；直接访问原站重定向回首页（实测），复刻该行为
- **寄售提交 consignments/consignments**：需 collection 参数；直接访问原站渲染**纯白空白页**（实测截图），复刻空白壳
  - chunk 反解的完整寄售表单逻辑（供后端联调参考，未实现 UI）：商品卡（当前最高价/求购价+返回市场）→ 藏品编号（仅支持单笔）→ 寄售单价（请输入寄售单价）→ 总售价 / 综合服务费(rate%) / 预计收入 → 收款方式（汇付钱包/易宝钱包，未开通「去开通」）→ 说明两段（未购买可取消挂单/三分钟禁止挂单）→ 发起寄售 → w-toast「您确认以X元出售吗?」(再次确认) → 「市场价格实时变动中…」(最终确认) → 低于市场最低价20%风险提示 → 操作密码弹窗 → 提交
  - consignments/search 同属 pending（未抓包未开发）

---

## 6i. order 详情系列（pages/order/*，2026-10-23 实测）

### 6i.1 接口实录

| 接口 | 请求 | 响应实测 |
|------|------|----------|
| POST /api/order/order/saleMarketOrderDetail | — | `{"code:0,"msg":"订单不存在","data":null}` → marketDetail 持续「加载中...」+ uni toast |
| POST /api/order/order/orderDetail/ids/{id} | id=undefined | `{"code:0,"msg":"找不到该订单或已被删除","data":null}` → defaultDetail 同上（深色 toast） |
| POST /api/order/order/giveOrderDetail/ids/{id} | id=undefined | 同上 → regiftDetail 重定向回首页 |
| createOrder / createDefaultOrder / createMarketOrder | — | 未抓包（避免真实下单），标注推断 |

### 6i.2 页面逻辑（chunk 反解）

- **确认订单 orderSubmit**（完整复刻）：w-container page-bg-colorfull pa-16 + navbar「确认订单」+ 商品卡（83rpx 图 + 名称 + order_type==1 数量步进器）+ 信息卡（创作者: / 藏品编号:随机编号 / 区块链地址: hideUrl 打码 **0x590****b783d**（slice(0,5)+****+slice(-5)，与我的页打码格式不同）+ 复制图标 / 订单金额: ￥totalPrice）+ PayType 支付方式（radio 组 activeColor #0018FF，黑圆首字母 48rpx；有 auth_key 不可用 → 「未支持该支付通道收款」22rpx #8c8c8c；无 auth → 去开通蓝按钮）+ 底部 fixed ￥totalPrice（12/20rpx）+ 立即支付 480rpx circle #ff7800（disabled 仍饱和橙，实测）
  - 直接访问无参数：product 空、totalPrice=NaN —— **原站即显示 ￥NaN（bug 原样复刻，实测）**
  - createOrder（order_type 1/2/11）→ pay() 拉起支付组件
- **marketDetail / defaultDetail**：依赖订单 id；直接访问详情失败 → 页面持续「加载中...」spinner（u-loading-icon circle）+ uni toast（实测截图，marketDetail 浅灰/defaultDetail 深灰）
- **regiftDetail**：直接访问详情失败 → 原站重定向回首页（实测），复刻该行为

---

## 6j. 排行模块（leaderboard、rank/*、marketCapRanking，2026-10-23 实测）

### 6j.1 接口实录

| 接口 | 请求 | 响应实测 |
|------|------|----------|
| POST /api/user/ranking | `{page:1,types:2}` | `{"data":{"my_list":[],"vip_time_num":0,"real_name_num":0,"yx_num":0,"total_num":0,"buy_num":0},"my":""}`（空榜） |

（masterRank/userBuy 直接访问无接口调用，依赖藏品/用户参数；marketCapRanking 同）

### 6j.2 页面逻辑（chunk 反解 + DOM 实测）

- **今日热榜 leaderboard**：头部 `rank_top_bg.png`（336rpx 高，含 GOOD 黑块/今日热榜白字/奖杯，100% 100%）+ `rank_title.png`（380×85rpx，mt 206rpx）；返回钮为 w-navbar **rank 模式**（67rpx 半透明白圆 rgba(244,244,244,.5) + back_btn 26rpx）；榜单类型 购买榜(￥buy_collection_total_price)/邀请榜(yao_count)，发行/流通字段；user/ranking 空 → search-empty 空态「暂无内容」（mt 160rpx）
- **大师动态 rank/masterRank**：深棕黑 #1a1002 全页 + 白字标题；依赖藏品参数拉动态，无参数 → 空列表（实测全黑）
- **买入清单 rank/userBuy**：「XX的买入清单」标题（**白字白底不可见，原站如此**）；无参数 → 骨架占位灰块 697×1421rpx #b9babd r40（实测）
- **市值排行榜 marketCapRanking**：流通: 字段 + 「市值排行榜规则」弹窗（知道了）；无数据 → 纯白空白（实测）
- 布局对齐：userBuy 骨架 (15,398,73,856)（原站 14,399,79,864）；热榜头部高 185px（原站 186）

---

## 6k. my 长尾 8 页（pages/my/about|join|service|privacy|flowDoc|cancellation|enablingcenter|depositinquiry，2026-10-23 实测）

| 页面 | 实测形态与实现 |
|------|----------------|
| 关于我们 about | logo.png 318rpx + 「关于我们：」（config 内容实测为空）+ 底部《用户服务协议》《隐私政策》灰蓝链接；含版本检查 toast（您已经是最新版本了） |
| 加入社群 join | config 群内容为空 → 空白页仅导航（实测） |
| 客服社群 service | 标题「加入社群」（原站如此）；欢迎加入QBOX官方社群! 36rpx Bold + 红色说明段落（28rpx #f5222d）+ 灰提示；二维码图 config 未渲染出（无图，实测） |
| 隐私设置 privacy | 「对外隐藏所有数字藏品」+ u-switch 黑色开启态（实测）；切换接口未抓包，本地切换（标注推断） |
| 流转记录 flowDoc | getRecord {page:1} → {code:0,msg:参数错误}（实测）→ search-empty 空态「暂无内容」 |
| 注销账号申请 cancellation | 「注销账号」44rpx + 您正在注销账号/请谨慎操作(红) + 帐号安全状态白卡（文案硬编码）+ 底部「确认注销」#0018ff；确认 → QBOX注销注意事项弹窗（365天冷静期 + 《QBOX软件注销协议》 + 勾选协议，未勾选 toast「请先勾选注销协议」）；提交接口未抓包（推断） |
| 赋能中心 enablingcenter | config 内容为空 → 空白页仅导航（实测） |
| 存证查询 depositinquiry | 静态表单：请输入区块链信息（textarea 可输入合约地址）+ 查询结果（这里显示查询结果）；无接口调用（实测） |

---

## 6l. market 长尾 6 页（pages/market/detail|search|categoryManager|giftRecord|tradeUnion|batchPay，2026-10-23 实测）

| 页面 | 实测形态与实现 |
|------|----------------|
| 市场详情 detail | MarketDetail/ids/undefined → {code:0,msg:已售出}（实录）→ 原站重定向首页，复刻该行为 |
| 搜索 search | getMarketList {goods_type:2,keywords:null} → data:[]（实录）；DOM 实测复刻：padding 44/19px、箭头 13px 旋转180°（common-arrow-right-black）、框 h39 #f5f5f5 r19 图标 common-search-gray 17px、tabs 字 16px MiSans-Semibold active #000/#5b5b5b 线 26×3 **#0047ff**、表头 12px 500 #919191（地板价 w88/成交量 w66 右对齐）、空态容器 min-height 264px 图 156px |
| 分类管理 categoryManager | 直接访问实测为 uni-app 框架 .uni-async-error 提示（「The connection timed out, click the screen to try again.」，浏览器默认 serif 字体浅灰，文字 bbox x31-381 y102-117 @414），复刻该状态 |
| 赠送记录 giftRecord | tradingInformation {status:4} → list:[]（实录）；主体为空 list 容器（DOM 实测 class "list bg-color-item pa-12 br-8"：bg #f4f6fa padding 12px r8，外层 plr-16，非 tabs 骨架条） |
| GM工会 tradeUnion | **背景为纹理图 /static/icon/gh/d-bg.png（750×656，computed style 实测）** + 兜底 #3d281c，container padding-top 55px；lock 60px + 「您的公会未解锁」16px bold **黑字 #26292d** + 「赶快购买…」12px #FCD8B1；tabs 三等分字 17px（active #E3A86A bold / #CAB6A1，线 20×3 bottom 20rpx）；个人权益卡 card-view **686×470rpx #513724 r20 padding 50rpx**（手续费减免/公会成员人数，标题 17px bold #E3A86A，正文 12px #CAB6A1 lh19px，分隔线 337×1 margin 22px）；图标 fee/d-msg/divider 为 chunk 内嵌 base64 已提取本地；我的工会/申请入会 tab → search 空态 |
| 批量支付 batchPay | 依赖订单参数；直接访问实测纯白空白，复刻空白壳 |

## 6m. 收官批次：剩余 22 页（2026-10-23 实测，DOM 渲染值驱动）

> 本轮全部页面以 Playwright 批量提取原站 DOM 渲染值（字号/颜色/几何）为唯一依据，源码值 = 渲染值 × 1.812，杜绝单位偏差。至此 **111 页全部完成，PENDING_ROUTES 清空**。

| 页面 | 实测形态与实现（字号为原站渲染值） |
|------|----------------|
| 启动页 index/lanuch | 蓝紫渐变 + QBOX 盒 logo + 「跳过 1s」13px 白字胶囊，1s 倒计时/点击 → 首页 |
| APP下载 index/downloadApp | 标题 18px 700 + download-bg 大图 + 双按钮（ios 白底蓝字 16px #0018ff / android 黑底白字，图标 24px） |
| 商品列表 market/marketGoodsList | 完整复刻：主图黑占位 + 名称卡（17px Bold + 地板价 10px + 售罄/流通胶囊）+ tabs 15px（市场价格 Bold/相关公告/成交记录/成交趋势）+ 售罄/求购/委托 + 价格/编号排序箭头（market-sort 8×13）+ common-empty 156px + 底部「快捷下单」15px 白字 #0018ff |
| 公告富文本 newsDetails | 标题「潮玩有话说」18px 700；正文依赖 newsInfo（无参数为空） |
| 发布动态 notice/sendRumor | 取消 15px #555 / 发表白字胶囊 + textarea 15px（placeholder #c0c4cc）+ 匿名发表 15px #7a7a7a + 开关 |
| 灵感墙详情 wallDetails | 持续「加载中...」12px #c8c8c8 居中 |
| 藏品详情 myCollection/collectionDetails | 完整复刻：主图黑占位 + 限量/流通胶囊 11px 白 + ¥0.00 19px AlimamaShuHeiTi + 持有者卡（15 Bold/14）+ 创作者卡（QBOX 水印 28px NanoByongGyeHei + 头像 67px + QBOX 12px）+ 藏品介绍/购买须知卡（固定文案 12px #696969）+ 底部「上链中」16px 灰按钮 |
| 收藏搜索 myCollection/search | 标题 18px 700 + 放大镜 19px（common-search-black） |
| 求购/委托订单详情 order/beg/info、entrust/info | 完整复刻：标题 17px Bold + 商品卡（黑图 + 当前最高价/求购价 12px #8c8c8c）+ 字段卡（label 15px Bold / 价格 15px AlimamaShuHeiTi / 值 14px）+ 订单组（订单编号/支付方式 --/支付时间 **Invalid Date**——原站无数据原样输出，如实复刻） |
| 邀请列表 invitation/invitationrecord | 标题 18px 700 + 有效/无效邀请 tabs + search-empty 146×142（y116）+ 暂无内容 14px |
| 活动中心 activeSelected | 标题 18px 700 + 最新活动/抽奖中心深色胶囊 tabs 16px 白字 + 空态（y254/y400） |
| 行情购买 kline/buy | 黑底页：banner（img.xiyanwl.asia 机器人图，已下载本地）+ 凤吹晚霞 19px 600 白 / qbox 13px / -0.53% 13px / 112.34CNY 22px + tabs 16px（流通/相关公告/成交额）+ K线黑区 |
| 行情详情 kline/info | 黑底：标题 18px 700 + 切换购买 13px + 藏品名称 17px + 6 项数据网格 13px（流通/5min/24h高/1h量/24h低/今日量）+ 行情等 tabs 13px + 底部 开启行情提醒(粉)/批量买卖(橙) 158×41 + 关注 24px 图标 + 11px |
| 抽奖记录 lottery/record | lottery/record {type:10} 空实测 → common-empty 156px（y135） |
| 抽奖规则 lottery/rule、活动规则 rules/rules | 标题 18px 700 + 灰色规则卡（config 内容为空） |
| 返佣排行 rebate/leaderboard | 表头 排名/用户/返佣金额 16px #999（y92）+ 空态（y236/y382）；commison_rank 404 实测 |
| 被邀请人清单 rebate/rebateInviteDetail | 标题 18px 700 + **5 列表头 8px #555**（原站即超小字）+ 空态（y222/y368）；bei_yao_list 404 实测 |
| 返利中心 rebate/rebate | commision_info 404（实测）→ 纯白空白 |
| webview、webview-storage | web-view 容器页，无 url 参数 → 纯白空白（实测） |

### 6m.1 过程问题与修复

1. order/*/{beg,entrust}/info.vue 三层目录 import 深度误写两层（`../../api/request`）→ 修正三层并回归
2. lanuch 初版误用 home-bg.png（首页顶部图）→ 改纯 CSS 蓝紫渐变
3. mock 补：lottery/record（空实录）、commison_rank/commision_info/bei_yao_list（404 实录）、begOrderInfo/entrustOrderInfo、Activity/category（空实录）

---

---

## 7. 复刻差异点登记（规则9 · 独立性说明）

| # | 原站依赖 | 独立项目处理 |
|---|----------|--------------|
| 1 | 顶象验证码 SDK（cdn.dingxiang-inc.com） | 不引入；登录风控由自建后端承担，mock 阶段无验证码 |
| 2 | 短信通道 | 自建 sendsms 接口，mock 固定验证码 123456 |
| 3 | 原站图片 CDN（img.xiyanwl.asia） | 图片全部下载至本项目 `frontend/public/`（60+ 文件），完全离线 |
| 4 | 钱包A 三方持牌支付通道 | 仅复刻 UI 与状态展示，不对接真实支付 |
| 5 | 部分历史 uploads 图片已失效 | 以本项目静态库等价资源替代并逐页记录 |

## 8. 抓包原始数据

- 全部接口原始请求/响应：`/workspace/recon/pages/apis-merged.json`（19 接口去重实录）
- 页面截图与渲染 DOM：`/workspace/recon/pages/`、`/workspace/recon/*.html`
- 后续每开发一个页面，均按「先抓原站接口 → 补充本文档 → 实现」流程推进（规则2/8）

### 6l+. market 长尾字号修正（2026-10-23 二次实测）

用户反馈搜索/工会等页「字体偏小」。根因：首轮实现把原站**渲染 px**（@414）直接当源码 px 写入（本项目源码 px = rpx 基准，构建时 ÷7.5），导致整页缩小约 45%。已按 **源码值 = 渲染值 × 1.812** 全面修正 search/tradeUnion/giftRecord/categoryManager 四页，并量化验证：

| 元素 | 原站 y | 克隆 y |
|------|--------|--------|
| 搜索·tab 文字+下划线 | 101-126 | 101-125 |
| 搜索·表头 | 153-164 | 152-163 |
| 工会·赶快购买 | 231-243 | 231-242 |
| 工会·tab 文字 | 316-332 | 318-334 |
| 工会·权益卡 | 382-640 | 384-642 |

新发现并修正的事实：工会页背景为纹理图 d-bg.png（非纯色）、标题为黑字、tabs 下划线 #0047ff（非 #0018ff）、giftRecord 的灰条实为空 list 容器、categoryManager 为框架 async-error（serif 字体）。
