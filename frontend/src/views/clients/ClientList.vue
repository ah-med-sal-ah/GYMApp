<script setup>
import { onMounted, ref } from 'vue'
import { RouterLink, useRouter } from 'vue-router'
import { clientsApi } from '@/api/clients'
import { usePaginatedList } from '@/composables/usePaginatedList'
import { useToast } from '@/lib/toast'
import PageHeader from '@/components/ui/PageHeader.vue'
import AppCard from '@/components/ui/AppCard.vue'
import AppInput from '@/components/ui/AppInput.vue'
import AppButton from '@/components/ui/AppButton.vue'
import AppBadge from '@/components/ui/AppBadge.vue'
import AppPagination from '@/components/ui/AppPagination.vue'
import SortableHeader from '@/components/ui/SortableHeader.vue'
import LoadingBlock from '@/components/ui/LoadingBlock.vue'
import EmptyState from '@/components/ui/EmptyState.vue'
import AppModal from '@/components/ui/AppModal.vue'

const reminderBadge = {
  sent: { tone: 'green', label: '✔ Sent' },
  pending: { tone: 'amber', label: '⏳ Pending' },
  disabled: { tone: 'slate', label: '❌ Disabled' },
}

const router = useRouter()
const toast = useToast()

const { items, pagination, loading, loadError, filters, load, sort, changePage, search } =
  usePaginatedList(clientsApi.list, 'clients', { sortBy: 'created_at' })

const searchTerm = ref('')
const clientToDelete = ref(null)
const deleting = ref(false)

onMounted(load)

function goToDetails(id) {
  router.push({ name: 'clients.show', params: { id } })
}

async function confirmDelete() {
  if (!clientToDelete.value) return
  deleting.value = true
  try {
    await clientsApi.remove(clientToDelete.value.id)
    toast.success('Client deleted.')
    clientToDelete.value = null
    load()
  } catch (error) {
    toast.error(error?.response?.data?.message || 'Failed to delete client.')
  } finally {
    deleting.value = false
  }
}
</script>

<template>
  <div>
    <PageHeader title="Clients" subtitle="Manage your gym members">
      <template #actions>
        <RouterLink :to="{ name: 'clients.create' }">
          <AppButton>+ Add Client</AppButton>
        </RouterLink>
      </template>
    </PageHeader>

    <AppCard>
      <div class="mb-4">
        <AppInput
          v-model="searchTerm"
          placeholder="Search by name, email, phone…"
          @update:model-value="search"
        />
      </div>

      <LoadingBlock v-if="loading" />
      <p v-else-if="loadError" class="rounded-md bg-red-50 px-4 py-3 text-sm text-red-700">{{ loadError }}</p>
      <EmptyState v-else-if="!items.length" message="No clients found." />

      <div v-else class="overflow-x-auto">
        <table class="min-w-full divide-y divide-slate-200">
          <thead>
            <tr>
              <th class="px-4 py-3" />
              <SortableHeader
                field="first_name"
                label="Name"
                :sort-by="filters.sort_by"
                :sort-order="filters.sort_order"
                @sort="sort"
              />
              <SortableHeader
                field="age"
                label="Age"
                :sort-by="filters.sort_by"
                :sort-order="filters.sort_order"
                @sort="sort"
              />
              <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Email</th>
              <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Phone</th>
              <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Reminder</th>
              <th class="px-4 py-3" />
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100">
            <tr
              v-for="client in items"
              :key="client.id"
              class="cursor-pointer hover:bg-slate-50"
              @click="goToDetails(client.id)"
            >
              <td class="px-4 py-3">
                <img
                  v-if="client.photo"
                  :src="client.photo"
                  class="h-9 w-9 rounded-full object-cover"
                  alt=""
                />
                <div v-else class="flex h-9 w-9 items-center justify-center rounded-full bg-slate-200 text-xs text-slate-500">
                  {{ client.first_name?.[0] }}
                </div>
              </td>
              <td class="px-4 py-3 text-sm font-medium text-slate-900">
                {{ client.first_name }} {{ client.last_name }}
              </td>
              <td class="px-4 py-3 text-sm text-slate-600">{{ client.age }}</td>
              <td class="px-4 py-3 text-sm text-slate-600">{{ client.email }}</td>
              <td class="px-4 py-3 text-sm text-slate-600">{{ client.phone }}</td>
              <td class="px-4 py-3 text-sm">
                <AppBadge :tone="reminderBadge[client.reminder_status]?.tone || 'slate'">
                  {{ reminderBadge[client.reminder_status]?.label || '—' }}
                </AppBadge>
              </td>
              <td class="px-4 py-3 text-right text-sm" @click.stop>
                <RouterLink
                  :to="{ name: 'clients.edit', params: { id: client.id } }"
                  class="font-medium text-brand-600 hover:text-brand-700"
                >
                  Edit
                </RouterLink>
                <button
                  type="button"
                  class="ml-3 font-medium text-red-600 hover:text-red-700"
                  @click="clientToDelete = client"
                >
                  Delete
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <AppPagination :pagination="pagination" @change="changePage" />
    </AppCard>

    <AppModal :open="!!clientToDelete" title="Delete client" @close="clientToDelete = null">
      Are you sure you want to delete
      <strong>{{ clientToDelete?.first_name }} {{ clientToDelete?.last_name }}</strong>? This cannot be undone.
      <template #actions>
        <AppButton variant="secondary" @click="clientToDelete = null">Cancel</AppButton>
        <AppButton variant="danger" :loading="deleting" @click="confirmDelete">Delete</AppButton>
      </template>
    </AppModal>
  </div>
</template>
