<template>
  <div>
    <div class="text-white" v-if="menus.length === 0">Loading...</div>
    <div v-else>
      <div class="mb-4" v-for="menu in menus" :key="menu.id">
        <Link class="group flex items-center py-3" :href="menu.url">
          <Icon :name="menu.icon" class="mr-2 w-4 h-4" :class="isUrl(menu.url) ? 'fill-white' : 'fill-indigo-400 group-hover:fill-white'" />
          <div :class="isUrl(menu.url) ? 'text-white' : 'text-indigo-300 group-hover:text-white'">{{ menu.name }}</div>
        </Link>
      </div>
    </div>
  </div>
</template>

<script>
import { Link } from '@inertiajs/vue3'
import Icon from '@/Shared/Icon.vue'
import axios from 'axios'
import { useStore } from '@/Store/store'
import { toRef, toRefs } from 'vue'

export default {
  components: {
    Icon,
    Link,
  },
  setup() {
    const store = useStore()
    const menus = toRef([])

    // Load menus from the server and store them in the Pinia store
    const loadMenus = async () => {
      try {
        const resMenus = await axios.get('/menu')
        if (resMenus.status === 200) {
          store.$state.menus = resMenus.data // Use the action to update the store reactively
          menus.value = resMenus.data
        }
      } catch (error) {
        console.error('Error loading menus:', error)
      }
    }

    loadMenus()

    return {
      menus, // Reactive reference to the menus state
    }
  },
  methods: {
    isUrl(...urls) {
      let currentUrl = this.$page.url.substr(1)
      if (urls[0] === '') {
        return currentUrl === ''
      }
      return urls.some((url) => currentUrl.startsWith(url))
    },
  },
  watch: {
    //create watch menus from reactive reference
    menus: {
      handler: function (val) {
        // console.log('menus changed', val)
      },
      deep: true,
    },
  },
}
</script>
