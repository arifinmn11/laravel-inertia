<template>
  <div class="flex items-center justify-between">
    <select v-model="form.limit" class="px-2 py-3 bg-white border-1 border-gray-500">
      <option :value="null">10</option>
      <option :value="25">25</option>
      <option :value="50">50</option>
    </select>
    <span> Show {{ data.from }} to {{ data.to }} of {{ data.total }} entries </span>
  </div>
</template>

<script>
import { Link } from '@inertiajs/vue3'

export default {
  components: {
    Link,
  },
  props: {
    data: Object,
    form: Object,
  },
}
</script>
