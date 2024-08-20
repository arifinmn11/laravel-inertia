<template>
  <div class="px-2">
    <limit-pagination :data="data" :form="form" class="pb-4" />
    <div v-if="data.links.length > 3">
      <div class="flex flex-wrap -mb-1">
        <template v-for="(link, key) in data.links">
          <div v-if="link.url === null" :key="key" class="mb-1 mr-1 px-4 py-3 text-gray-400 text-sm leading-4 border rounded" v-html="link.label" />
          <Link v-else :key="`link-${key}`" class="mb-1 mr-1 px-4 py-3 focus:text-indigo-500 text-sm leading-4 hover:bg-white border focus:border-indigo-500 rounded" :class="{ 'bg-white': link.active }" :href="link.url" v-html="link.label" />
        </template>
      </div>
    </div>
  </div>
</template>

<script>
import { Link } from '@inertiajs/vue3'
import LimitPagination from '@/Shared/LimitPagination.vue'

export default {
  components: {
    Link,
    LimitPagination,
  },
  props: {
    data: Object,
    form: Object,
  },
}
</script>
