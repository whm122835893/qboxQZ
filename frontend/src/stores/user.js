import { defineStore } from 'pinia'
import { request, setToken, clearToken, getToken } from '../api/request'

export const useUserStore = defineStore('user', {
  state: () => ({
    token: getToken(),
    userinfo: JSON.parse(localStorage.getItem('userinfo') || 'null')
  }),
  actions: {
    async login(account, password) {
      const res = await request('/api/user/login', { account, password })
      if (res.code === 1) {
        this.token = res.data.token
        this.userinfo = res.data.userinfo
        setToken(res.data.token)
        localStorage.setItem('userinfo', JSON.stringify(res.data.userinfo))
      }
      return res
    },
    async getUserInfo() {
      const res = await request('/api/user/getUserInfo')
      if (res.code === 1) this.userinfo = res.data
      return res
    },
    logout() {
      this.token = ''
      this.userinfo = null
      clearToken()
    }
  }
})
