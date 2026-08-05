<script setup>
import { onMounted, ref } from 'vue'
import { RouterLink } from 'vue-router'
import { adminApi } from '@/api/admin'
import PageHeader from '@/components/ui/PageHeader.vue'
import AppCard from '@/components/ui/AppCard.vue'
import AppButton from '@/components/ui/AppButton.vue'
import LoadingBlock from '@/components/ui/LoadingBlock.vue'

const props = defineProps({ id: { type: String, required: true } })

const user = ref(null)
const loading = ref(true)
const loadError = ref('')

async function load() {
  loading.value = true
  loadError.value = ''
  try {
    const { data } = await adminApi.user(props.id)
    user.value = data
  } catch (error) {
    loadError.value = error?.response?.data?.message || 'Failed to load gym.'
  } finally {
    loading.value = false
  }
}

onMounted(load)
</script>

<template>
  <div>
    <PageHeader title="Gym Details">
      <template #actions>
        <RouterLink :to="{ name: 'admin.users.index' }">
          <AppButton variant="ghost">← Back</AppButton>
        </RouterLink>
      </template>
    </PageHeader>

    <LoadingBlock v-if="loading" />
    <p v-else-if="loadError" class="rounded-md bg-red-50 px-4 py-3 text-sm text-red-700">{{ loadError }}</p>

    <AppCard v-else-if="user" class="max-w-2xl">
      <h2 class="text-lg font-semibold text-slate-900">{{ user.gym_name }}</h2>
      <p class="text-sm text-slate-500">@{{ user.username }}</p>

      <dl class="mt-6 grid grid-cols-1 gap-4 border-t border-slate-100 pt-6 sm:grid-cols-2">
        <div>
          <dt class="text-xs font-medium uppercase tracking-wide text-slate-400">Email</dt>
          <dd class="mt-1 text-sm text-slate-900">{{ user.email }}</dd>
        </div>
        <div>
          <dt class="text-xs font-medium uppercase tracking-wide text-slate-400">Phone</dt>
          <dd class="mt-1 text-sm text-slate-900">{{ user.phone || '—' }}</dd>
        </div>
      </dl>
    </AppCard>
  </div>
</template>
