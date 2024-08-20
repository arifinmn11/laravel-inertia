import { defineStore } from 'pinia'

export const useStore = defineStore('store', {
  state: () => ({
    menus: [],
  }),
  getters: {
    getMenus() {
      return this.menus
    },
  },
  methods: {
    setMenus(menus) {
      this.menus = menus
    },
  },
  persist: true,
})
