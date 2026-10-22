/**
 * 统一请求封装（独立项目，不依赖原站）
 * - VITE_USE_MOCK=true 或后端不可用时走 mock 数据（mock 数据均来自原站抓包实录，见 docs/03-API接口文档.md）
 * - 后端为本项目自建 ThinkPHP，接口路径与原站保持一致，便于联调
 */
const USE_MOCK = import.meta.env.VITE_USE_MOCK !== 'false'

export function getToken() {
  return localStorage.getItem('token') || ''
}
export function setToken(t) {
  localStorage.setItem('token', t)
}
export function clearToken() {
  localStorage.removeItem('token')
  localStorage.removeItem('userinfo')
}

export async function request(path, data = {}) {
  if (USE_MOCK) {
    const { mockRequest } = await import('./mock')
    return mockRequest(path, data)
  }
  const res = await fetch('/api' + path, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      token: getToken(),
      version: '1.0.0'
    },
    body: JSON.stringify(data)
  })
  return res.json()
}

export function resolveImg(url) {
  if (!url) return ''
  if (/^https?:/.test(url)) return url
  return url
}
