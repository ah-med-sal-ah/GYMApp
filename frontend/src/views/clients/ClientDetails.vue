<script setup>
import { onMounted, ref } from 'vue'
import { RouterLink, useRouter } from 'vue-router'
import { clientsApi } from '@/api/clients'
import { formatDate } from '@/lib/format'
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

const client = ref(null)
const loading = ref(true)
const loadError = ref('')
const confirmingDelete = ref(false)
const deleting = ref(false)

const reminderBadge = {
  sent: { tone: 'green', label: 'Reminder Sent' },
  pending: { tone: 'amber', label: 'Not Yet Sent' },
  disabled: { tone: 'slate', label: 'Reminder Disabled' },
}

async function load() {
  loading.value = true
  loadError.value = ''
  try {
    const { data } = await clientsApi.get(props.id)
    client.value = data
  } catch (error) {
    loadError.value = error?.response?.data?.message || 'Failed to load client.'
  } finally {
    loading.value = false
  }
}

onMounted(load)

async function remove() {
  deleting.value = true
  try {
    await clientsApi.remove(props.id)
    toast.success('Client deleted.')
    router.push({ name: 'clients.index' })
  } catch (error) {
    toast.error(error?.response?.data?.message || 'Failed to delete client.')
  } finally {
    deleting.value = false
  }
}
</script>

<template>
  <div>
    <PageHeader title="Client Details">
      <template #actions>
        <RouterLink :to="{ name: 'clients.index' }">
          <AppButton variant="ghost">← Back</AppButton>
        </RouterLink>
      </template>
    </PageHeader>

    <LoadingBlock v-if="loading" />
    <p v-else-if="loadError" class="rounded-md bg-red-50 px-4 py-3 text-sm text-red-700">{{ loadError }}</p>

    <AppCard v-else-if="client" class="max-w-2xl">
      <div class="flex items-start gap-4">
        <img
          v-if="client.photo"
          :src="client.photo"
          class="h-20 w-20 rounded-full object-cover"
          alt=""
        />
        <div v-else class="flex h-20 w-20 items-center justify-center rounded-full bg-slate-200 text-xl text-slate-500">
          {{ client.first_name?.[0] }}
        </div>

        <div class="flex-1">
          <h2 class="text-lg font-semibold text-slate-900">{{ client.first_name }} {{ client.last_name }}</h2>
          <p class="text-sm text-slate-500">{{ client.email }} · {{ client.phone }} · Age {{ client.age }}</p>
          <div class="mt-2 flex items-center gap-2">
            <AppBadge :tone="client.status === 'active' ? 'green' : 'red'">{{ client.status }}</AppBadge>
            <span class="text-sm text-slate-500">{{ client.remaining_days }} day(s) remaining</span>
          </div>
        </div>

        <div class="flex gap-2">
          <RouterLink :to="{ name: 'clients.edit', params: { id: client.id } }">
            <AppButton variant="secondary">Edit</AppButton>
          </RouterLink>
          <AppButton variant="danger" @click="confirmingDelete = true">Delete</AppButton>
        </div>
      </div>

      <dl class="mt-6 grid grid-cols-1 gap-4 border-t border-slate-100 pt-6 sm:grid-cols-2">
        <div>
          <dt class="text-xs font-medium uppercase tracking-wide text-slate-400">Coach</dt>
          <dd class="mt-1 text-sm text-slate-900">{{ client.coach ? client.coach.name : 'Not assigned' }}</dd>
        </div>
        <div>
          <dt class="text-xs font-medium uppercase tracking-wide text-slate-400">Sports</dt>
          <dd class="mt-1 flex flex-wrap gap-1">
            <AppBadge v-for="sport in client.sports" :key="sport.id" tone="blue">{{ sport.name }}</AppBadge>
            <span v-if="!client.sports.length" class="text-sm text-slate-400">None</span>
          </dd>
        </div>
        <div>
          <dt class="text-xs font-medium uppercase tracking-wide text-slate-400">Registration start</dt>
          <dd class="mt-1 text-sm text-slate-900">{{ formatDate(client.registration_start) }}</dd>
        </div>
        <div>
          <dt class="text-xs font-medium uppercase tracking-wide text-slate-400">Registration end</dt>
          <dd class="mt-1 text-sm text-slate-900">{{ formatDate(client.registration_end) }}</dd>
        </div>
        <div>
          <dt class="text-xs font-medium uppercase tracking-wide text-slate-400">Reminder Email Status</dt>
          <dd class="mt-1">
            <AppBadge :tone="reminderBadge[client.reminder_status]?.tone || 'slate'">
              {{ reminderBadge[client.reminder_status]?.label || 'Unknown' }}
            </AppBadge>
          </dd>
        </div>
        <div v-if="client.reminder_sent_at">
          <dt class="text-xs font-medium uppercase tracking-wide text-slate-400">Reminder Sent Date</dt>
          <dd class="mt-1 text-sm text-slate-900">{{ formatDate(client.reminder_sent_at) }}</dd>
        </div>
      </dl>
    </AppCard>

    <AppModal :open="confirmingDelete" title="Delete client" @close="confirmingDelete = false">
      This action cannot be undone.
      <template #actions>
        <AppButton variant="secondary" @click="confirmingDelete = false">Cancel</AppButton>
        <AppButton variant="danger" :loading="deleting" @click="remove">Delete</AppButton>
      </template>
    </AppModal>
  </div>
</template>
