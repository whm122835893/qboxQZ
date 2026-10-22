/**
 * 轻提示（与登录页 toast 同款视觉：顶部黑色圆角，复刻原站 u-toast 样式）
 * 抽为公共工具供后续页面复用；登录页内联版本保持不动（已完成页面不可更改）
 */
export function toast(msg) {
  const el = document.createElement('div')
  el.textContent = msg
  el.style.cssText =
    'position:fixed;top:18%;left:50%;transform:translateX(-50%);background:rgba(0,0,0,0.75);color:#fff;font-size:14px;padding:9px 18px;border-radius:8px;z-index:99999;font-family:var(--font-regular);white-space:nowrap;'
  document.body.appendChild(el)
  setTimeout(() => el.remove(), 2000)
}
