<script setup>
import { onMounted, ref } from 'vue'
import { RouterLink, useRouter } from 'vue-router'
import { sportsApi } from '@/api/sports'
import { formatOptionalCurrency } from '@/lib/format'
import { useToast } from '@/lib/toast'
import PageHeader from '@/components/ui/PageHeader.vue'
import AppCard from '@/components/ui/AppCard.vue'
import AppButton from '@/components/ui/AppButton.vue'
import LoadingBlock from '@/components/ui/LoadingBlock.vue'
import AppModal from '@/components/ui/AppModal.vue'

const props = defineProps({ id: { type: String, required: true } })
const router = useRouter()
const toast = useToast()

const sport = ref(null)
const loading = ref(true)
const loadError = ref('')
const confirmingDelete = ref(false)
const deleting = ref(false)

async function load() {
  loading.value = true
  loadError.value = ''
  try {
    const { data } = await sportsApi.get(props.id)
    sport.value = data
  } catch (error) {
    loadError.value = error?.response?.data?.message || 'Failed to load sport.'
  } finally {
    loading.value = false
  }
}

onMounted(load)

async function remove() {
  deleting.value = true
  try {
    await sportsApi.remove(props.id)
    toast.success('Sport deleted.')
    router.push({ name: 'sports.index' })
  } catch (error) {
    toast.error(error?.response?.data?.message || 'Failed to delete sport.')
  } finally {
    deleting.value = false
  }
}
</script>

<template>
  <div>
    <PageHeader title="Sport Details">
      <template #actions>
        <RouterLink :to="{ name: 'sports.index' }">
          <AppButton variant="ghost">← Back</AppButton>
        </RouterLink>
      </template>
    </PageHeader>

    <LoadingBlock v-if="loading" />
    <p v-else-if="loadError" class="rounded-md bg-red-50 px-4 py-3 text-sm text-red-700">{{ loadError }}</p>

    <AppCard v-else-if="sport" class="max-w-2xl">
      <div class="flex items-start gap-4">
        <img
          v-if="sport.photo"
          :src="sport.photo"
          class="h-20 w-20 rounded-full object-cover"
          alt=""
        />
        <div v-else class="flex h-20 w-20 items-center justify-center rounded-full bg-slate-200 text-xl text-slate-500">
          {{ sport.name?.[0] }}
        </div>

        <div class="flex-1">
          <h2 class="text-lg font-semibold text-slate-900">{{ sport.name }}</h2>
          <p class="text-sm text-slate-500">
            {{ sport.number_of_coaches }} coach(es) · {{ sport.number_of_clients }} client(s)
          </p>
        </div>

        <div class="flex gap-2">
          <RouterLink :to="{ name: 'sports.edit', params: { id: sport.id } }">
            <AppButton variant="secondary">Edit</AppButton>
          </RouterLink>
          <AppButton variant="danger" @click="confirmingDelete = true">Delete</AppButton>
        </div>
      </div>

      <dl class="mt-6 grid grid-cols-2 gap-4 border-t border-slate-100 pt-6 sm:grid-cols-4">
        <div>
          <dt class="text-xs font-medium uppercase tracking-wide text-slate-400">Day price</dt>
          <dd class="mt-1 text-sm text-slate-900">{{ formatOptionalCurrency(sport.day_price) }}</dd>
        </div>
        <div>
          <dt class="text-xs font-medium uppercase tracking-wide text-slate-400">Week price</dt>
          <dd class="mt-1 text-sm text-slate-900">{{ formatOptionalCurrency(sport.week_price) }}</dd>
        </div>
        <div>
          <dt class="text-xs font-medium uppercase tracking-wide text-slate-400">Month price</dt>
          <dd class="mt-1 text-sm text-slate-900">{{ formatOptionalCurrency(sport.month_price) }}</dd>
        </div>
        <div>
          <dt class="text-xs font-medium uppercase tracking-wide text-slate-400">Year price</dt>
          <dd class="mt-1 text-sm text-slate-900">{{ formatOptionalCurrency(sport.year_price) }}</dd>
        </div>
      </dl>
    </AppCard>

    <AppModal :open="confirmingDelete" title="Delete sport" @close="confirmingDelete = false">
      This action cannot be undone.
      <template #actions>
        <AppButton variant="secondary" @click="confirmingDelete = false">Cancel</AppButton>
        <AppButton variant="danger" :loading="deleting" @click="remove">Delete</AppButton>
      </template>
    </AppModal>
  </div>
</template>
