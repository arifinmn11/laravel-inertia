import '../css/app.css'
import { createApp, h } from 'vue'
import { createInertiaApp } from '@inertiajs/vue3'
import { createPinia } from 'pinia'
import axios from 'axios'

const pinia = createPinia()

createInertiaApp({
  resolve: (name) => {
    const pages = import.meta.glob('./Pages/**/*.vue', { eager: true })
    return pages[`./Pages/${name}.vue`]
  },
  title: (title) => (title ? `${title} - MACMS 1.0` : 'MACMS 1.0'),
  setup({ el, App, props, plugin }) {
    const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content')

    if (token) {
      axios.defaults.headers.common['X-CSRF-TOKEN'] = token
    }

    createApp({ render: () => h(App, props) })
      .use(plugin)
      .use(pinia)
      .mount(el)
  },
})
