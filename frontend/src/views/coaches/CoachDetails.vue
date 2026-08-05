<script setup>
import { onMounted, ref } from 'vue'
import { RouterLink, useRouter } from 'vue-router'
import { coachesApi } from '@/api/coaches'
import { formatCurrency } from '@/lib/format'
import { useToast } from '@/lib/toast'
import PageHeader from '@/components/ui/PageHeader.vue'
import AppCard from '@/components/ui/AppCard.vue'
import AppButton from '@/components/ui/AppButton.vue'
import AppBadge from '@/components/ui/AppBadge.vue'
import LoadingBlock from '@/components/ui/LoadingBlock.vue'
import AppModal from '@/components/ui/AppModal.vue'

const props = defineProps({ id: { type: String, required: true } })
const router = useRouter()
const toast = useToast()

const coach = ref(null)
const loading = ref(true)
const loadError = ref('')
const confirmingDelete = ref(false)
const deleting = ref(false)

async function load() {
  loading.value = true
  loadError.value = ''
  try {
    const { data } = await coachesApi.get(props.id)
    coach.value = data
  } catch (error) {
    loadError.value = error?.response?.data?.message || 'Failed to load coach.'
  } finally {
    loading.value = false
  }
}

onMounted(load)

async function remove() {
  deleting.value = true
  try {
    await coachesApi.remove(props.id)
    toast.success('Coach deleted.')
    router.push({ name: 'coaches.index' })
  } catch (error) {
    toast.error(error?.response?.data?.message || 'Failed to delete coach.')
  } finally {
    deleting.value = false
  }
}
</script>

<template>
  <div>
    <PageHeader title="Coach Details">
      <template #actions>
        <RouterLink :to="{ name: 'coaches.index' }">
          <AppButton variant="ghost">← Back</AppButton>
        </RouterLink>
      </template>
    </PageHeader>

    <LoadingBlock v-if="loading" />
    <p v-else-if="loadError" class="rounded-md bg-red-50 px-4 py-3 text-sm text-red-700">{{ loadError }}</p>

    <AppCard v-else-if="coach" class="max-w-2xl">
      <div class="flex items-start gap-4">
        <img
          v-if="coach.photo"
          :src="coach.photo"
          class="h-20 w-20 rounded-full object-cover"
          alt=""
        />
        <div v-else class="flex h-20 w-20 items-center justify-center rounded-full bg-slate-200 text-xl text-slate-500">
          {{ coach.first_name?.[0] }}
        </div>

        <div class="flex-1">
          <h2 class="text-lg font-semibold text-slate-900">{{ coach.first_name }} {{ coach.last_name }}</h2>
          <p class="text-sm text-slate-500">{{ coach.email }} · {{ coach.phone }} · Age {{ coach.age }}</p>
          <div class="mt-2 flex items-center gap-2">
            <AppBadge v-if="coach.sport" tone="blue">{{ coach.sport.name }}</AppBadge>
            <span v-else class="text-sm text-slate-400">No sport assigned</span>
          </div>
        </div>

        <div class="flex gap-2">
          <RouterLink :to="{ name: 'coaches.edit', params: { id: coach.id } }">
            <AppButton variant="secondary">Edit</AppButton>
          </RouterLink>
          <AppButton variant="danger" @click="confirmingDelete = true">Delete</AppButton>
        </div>
      </div>

      <dl class="mt-6 grid grid-cols-1 gap-4 border-t border-slate-100 pt-6 sm:grid-cols-2">
        <div>
          <dt class="text-xs font-medium uppercase tracking-wide text-slate-400">Salary</dt>
          <dd class="mt-1 text-sm text-slate-900">{{ formatCurrency(coach.salary) }}</dd>
        </div>
        <div>
          <dt class="text-xs font-medium uppercase tracking-wide text-slate-400">Sport</dt>
          <dd class="mt-1 text-sm text-slate-900">{{ coach.sport ? coach.sport.name : 'Not assigned' }}</dd>
        </div>
      </dl>
    </AppCard>

    <AppModal :open="confirmingDelete" title="Delete coach" @close="confirmingDelete = false">
      This action cannot be undone.
      <template #actions>
        <AppButton variant="secondary" @click="confirmingDelete = false">Cancel</AppButton>
        <AppButton variant="danger" :loading="deleting" @click="remove">Delete</AppButton>
      </template>
    </AppModal>
  </div>
</template>
