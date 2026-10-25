import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'

// 原站为 uni-app rpx 规范（750 设计稿）。本项目 CSS 中直接书写原站 rpx 数值（以 px 单位书写），
// 由下方自定义插件统一转换为 vw（1px → 100/750 vw），实现像素级自适应还原。
function pxToVw() {
  const RATIO = 100 / 750
  return {
    name: 'qbox-px-to-vw',
    enforce: 'pre',
    transform(code, id) {
      // 仅处理样式上下文：纯 CSS 文件 + Vue SFC 的 style 块（避免误伤 JS/模板中的 px 字符串）
      const isVueStyle = /\.vue\?vue&type=style/.test(id)
      const isCssFile = /\.(css|scss|sass|less)(\?|$)/.test(id)
      if (!isVueStyle && !isCssFile) return null
      if (id.includes('node_modules')) return null
      // 豁免标记：原站本身使用固定 px（非 rpx 体系）的页面，加 /*@nopx*/ 后跳过转换
      if (code.includes('/*@nopx*/')) return null
      // 仅转换纯 CSS 声明中的 px；100vh 等其他单位不受影响
      const converted = code.replace(/([+-]?\d*\.?\d+)px\b/g, (m, num) => {
        const v = parseFloat(num)
        if (v === 0) return '0'
        return (v * RATIO).toFixed(5).replace(/0+$/, '').replace(/\.$/, '') + 'vw'
      })
      if (converted !== code) return { code: converted, map: null }
      return null
    }
  }
}

export default defineConfig({
  plugins: [vue(), pxToVw()],
  server: {
    host: '0.0.0.0',
    port: Number(process.env.PORT) || 5173,
    allowedHosts: true,
    proxy: {
      // 独立项目：默认走本项目 ThinkPHP 后端；后端未启动时前端使用 mock（见 src/api/request.js）
      '/api': { target: 'http://127.0.0.1:8000', changeOrigin: true }
    }
  }
})
