<template>
  <div class="flex" @click="sortBy">
    <slot></slot>
    <div class="pl-2">
      <icon name="arrows-up-down" class="shrink-0 mr-2 w-4 h-4 fill-yellow-800" />
    </div>
  </div>
</template>

<script>
import Icon from '@/Shared/Icon.vue'

export default {
  components: {
    Icon,
  },
  props: {
    form: Object,
    col: String,
  },
  methods: {
    sortBy() {
      if (!this.form.sortBy) {
        this.form.sortBy = `${this.col}|asc`
      } else {
        if (this.form.sortBy === `${this.col}|asc`) {
          this.form.sortBy = `${this.col}|desc`
        } else {
          this.form.sortBy = `${this.col}|asc`
        }
      }
    },
  },
}
</script>
