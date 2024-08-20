<template>
  <div>
    <!-- <Head :title="`${form.name} `" /> -->
    <h1 class="mb-8 text-3xl font-bold">
      <Link class="text-indigo-400 hover:text-indigo-600" href="/roles">Permissions</Link>
      <span class="text-indigo-400 font-medium">/</span>
      {{ name }}
    </h1>
    <div class="w-full bg-white rounded-md shadow">
      <form @submit.prevent="update">
        <div class="max-h-[70vh] overflow-y-auto">
          <div class="-mb-4 p-4" v-for="(form, formKey) in forms.role_menus" :key="formKey">
            <div class="flex flex-wrap">
              <input :id="form.code" v-model="form.check" :true-value="1" :false-value="0" class="mr-1" type="checkbox" :key="formKey" />
              <label :for="form.code" class="block text-base font-bold text-gray-700">{{ form.name }}</label>
            </div>
            <div class="mt-2 ml-4 flex-col border-2 border-gray-400" v-if="form.module_task && form.module_task.length > 0">
              <div class="pl-4" v-for="(cm, cmKey) in form.module_task" :key="cmKey">
                <div class="flex py-1 border-spacing-1 border-gray-200">
                  <input :id="cm.code" v-model="cm.check" :true-value="1" :false-value="0" class="mr-1" type="checkbox" />
                  <label :for="cm.code" :label="cmKey" class="block text-sm font-medium text-gray-700">{{ cm.name }}</label>
                </div>
              </div>
            </div>
            <div class="w-full" v-if="form.child && form.child.length > 0">
              <div v-for="(child, childKey) in form.child" :key="childKey">
                <div class="flex py-2 pl-4 border-spacing-1 border-gray-200">
                  <input :id="child.code" v-model="child.check" :true-value="1" :false-value="0" class="mr-1" type="checkbox" />
                  <label :for="child.code" :label="childKey" class="block text-base font-semibold text"> {{ child.name }}</label>
                </div>
                <div class="ml-4 lex-col border-2 border-gray-400" v-if="child.module_task && child.module_task.length > 0">
                  <div class="pl-4" v-for="(cm, cmKey) in child.module_task" :key="cmKey">
                    <div class="flex py-1 pl-8 border-spacing-1 border-gray-200">
                      <input :id="cm.code" v-model="cm.check" :true-value="1" :false-value="0" class="mr-1" type="checkbox" />
                      <label :for="cm.code" :label="cmKey" class="block text-sm font-medium text-gray-700">{{ cm.name }}</label>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="flex items-center px-8 py-4 bg-gray-50 border-t border-gray-100">
          <loading-button :loading="forms.processing" class="btn-indigo ml-auto" type="submit">Update Permissions</loading-button>
        </div>
      </form>
    </div>
  </div>
</template>

<script>
import { Head, Link } from '@inertiajs/vue3'
import Layout from '@/Shared/Layout.vue'
import TextInput from '@/Shared/TextInput.vue'
import SelectInput from '@/Shared/SelectInput.vue'
import LoadingButton from '@/Shared/LoadingButton.vue'
import TrashedMessage from '@/Shared/TrashedMessage.vue'
import { stringify } from 'postcss'

export default {
  components: {
    Head,
    Link,
    LoadingButton,
    SelectInput,
    TextInput,
    TrashedMessage,
  },
  layout: Layout,
  props: {
    menu: Array,
    name: String,
    id: String,
  },
  remember: 'form',
  data() {
    return {
      forms: this.$inertia.form({
        role_menus: this.menu,
      }),
      id: this.id,
    }
  },
  methods: {
    update() {
      this.forms.put(`/permissions/${this.id}`)
    },
    destroy() {
      if (confirm('Are you sure you want to delete this role?')) {
        // this.$inertia.delete(`/roles/${this.role.id}`)
      }
    },
    restore() {
      if (confirm('Are you sure you want to restore this role?')) {
        // this.$inertia.put(`/roles/${this.role.id}/restore`)
      }
    },
  },
}
</script>
