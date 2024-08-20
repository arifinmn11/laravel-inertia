<template>
  <div>
    <Head title="Users" />
    <h1 class="mb-8 text-3xl font-bold">Users</h1>
    <div class="flex items-center justify-between mb-6">
      <search-filter v-model="form.search" class="mr-4 w-full max-w-md" @reset="reset">
        <label class="block text-gray-700">Role:</label>
        <select v-model="form.role" class="form-select mt-1 w-full">
          <option :value="null">All</option>
          <option :value="'0'">Super Admin</option>
          <option v-for="role in roles" :value="role.id" :key="role.id">{{ role.name }}</option>
        </select>
        <label class="block mt-4 text-gray-700">Trashed:</label>
        <select v-model="form.trashed" class="form-select mt-1 w-full">
          <option :value="option.value" v-for="(option, key) in optionsTrashed" :key="key">{{ option.name }}</option>
        </select>
      </search-filter>
      <Link class="btn-indigo" href="/users/create" v-if="actions.includes('create')">
        <span>Create</span>
        <span class="hidden md:inline">&nbsp;User</span>
      </Link>
    </div>
    <div class="bg-white rounded-md shadow overflow-x-auto">
      <table class="w-full whitespace-nowrap">
        <tr class="text-left font-bold">
          <th class="pb-4 pt-6 px-6"><sort-by-filter :col="'first_name'" :form="form"> Name </sort-by-filter></th>
          <th class="pb-4 pt-6 px-6"><sort-by-filter :col="'email'" :form="form"> Email </sort-by-filter></th>
          <th class="pb-4 pt-6 px-6" colspan="2"><sort-by-filter :col="'role'" :form="form"> Role </sort-by-filter></th>
        </tr>
        <tr v-for="user in users.data" :key="user.id" class="hover:bg-gray-100 focus-within:bg-gray-100">
          <td class="border-t">
            <Link class="flex items-center px-6 py-4 focus:text-indigo-500" :href="`/users/${user.id}/edit`">
              <img v-if="user.photo" class="block -my-2 mr-2 w-5 h-5 rounded-full" :src="user.photo" />
              {{ user.name }}
              <icon v-if="user.deleted_at" name="trash" class="shrink-0 ml-2 w-3 h-3 fill-gray-400" />
            </Link>
          </td>
          <td class="border-t">
            <Link class="flex items-center px-6 py-4" :href="`/users/${user.id}/edit`" tabindex="-1">
              {{ user.email }}
            </Link>
          </td>
          <td class="border-t">
            <Link class="flex items-center px-6 py-4" :href="`/users/${user.id}/edit`" tabindex="-1">
              {{ user.role }}
            </Link>
          </td>
          <td class="w-px border-t">
            <Link class="flex items-center px-4" :href="`/users/${user.id}/edit`" tabindex="-1">
              <icon name="cheveron-right" class="block w-6 h-6 fill-gray-400" />
            </Link>
          </td>
        </tr>
        <tr v-if="users.length === 0">
          <td class="px-6 py-4 border-t" colspan="4">No users found.</td>
        </tr>
      </table>
    </div>
    <pagination class="mt-6" :data="users" :form="form" />
  </div>
</template>

<script>
import { Head, Link } from '@inertiajs/vue3'
import Icon from '@/Shared/Icon.vue'
import pickBy from 'lodash/pickBy'
import Layout from '@/Shared/Layout.vue'
import throttle from 'lodash/throttle'
import mapValues from 'lodash/mapValues'
import SearchFilter from '@/Shared/SearchFilter.vue'
import Pagination from '@/Shared/Pagination.vue'
import { optionsTrashed } from '@/Utils/Constants/option'
import LimitPagination from '@/Shared/LimitPagination.vue'
import SortByFilter from '@/Shared/SortByFilter.vue'

export default {
  components: {
    Head,
    Icon,
    Link,
    SearchFilter,
    Pagination,
    LimitPagination,
    SortByFilter,
  },
  layout: Layout,
  props: {
    filters: Object,
    users: Object,
    actions: Array,
    roles: Array,
  },
  data() {
    return {
      optionsTrashed,
      actions: this.actions,
      form: {
        search: this.filters.search,
        role: this.filters.role,
        trashed: this.filters.trashed,
        limit: this.filters.limit,
        sortBy: this.filters.sortBy,
      },
    }
  },
  watch: {
    form: {
      deep: true,
      handler: throttle(function () {
        this.$inertia.get('/users', pickBy(this.form), { preserveState: true })
      }, 150),
    },
  },
  methods: {
    reset() {
      this.form = mapValues(this.form, () => null)
    },
  },
}
</script>
