<script setup>
import { onMounted, ref } from 'vue'
import { RouterLink, useRouter } from 'vue-router'
import { coachesApi } from '@/api/coaches'
import { usePaginatedList } from '@/composables/usePaginatedList'
import { formatCurrency } from '@/lib/format'
import { useToast } from '@/lib/toast'
import PageHeader from '@/components/ui/PageHeader.vue'
import AppCard from '@/components/ui/AppCard.vue'
import AppInput from '@/components/ui/AppInput.vue'
import AppButton from '@/components/ui/AppButton.vue'
import AppPagination from '@/components/ui/AppPagination.vue'
import SortableHeader from '@/components/ui/SortableHeader.vue'
import LoadingBlock from '@/components/ui/LoadingBlock.vue'
import EmptyState from '@/components/ui/EmptyState.vue'
import AppModal from '@/components/ui/AppModal.vue'

const router = useRouter()
const toast = useToast()

const { items, pagination, loading, loadError, filters, load, sort, changePage, search } =
  usePaginatedList(coachesApi.list, 'coaches', { sortBy: 'created_at' })

const searchTerm = ref('')
const coachToDelete = ref(null)
const deleting = ref(false)

onMounted(load)

function goToDetails(id) {
  router.push({ name: 'coaches.show', params: { id } })
}

async function confirmDelete() {
  if (!coachToDelete.value) return
  deleting.value = true
  try {
    await coachesApi.remove(coachToDelete.value.id)
    toast.success('Coach deleted.')
    coachToDelete.value = null
    load()
  } catch (error) {
    toast.error(error?.response?.data?.message || 'Failed to delete coach.')
  } finally {
    deleting.value = false
  }
}
</script>

<template>
  <div>
    <PageHeader title="Coaches" subtitle="Manage your coaching staff">
      <template #actions>
        <RouterLink :to="{ name: 'coaches.create' }">
          <AppButton>+ Add Coach</AppButton>
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
      <EmptyState v-else-if="!items.length" message="No coaches found." />

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
              <SortableHeader
                field="salary"
                label="Salary"
                :sort-by="filters.sort_by"
                :sort-order="filters.sort_order"
                @sort="sort"
              />
              <th class="px-4 py-3" />
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100">
            <tr
              v-for="coach in items"
              :key="coach.id"
              class="cursor-pointer hover:bg-slate-50"
              @click="goToDetails(coach.id)"
            >
              <td class="px-4 py-3">
                <img
                  v-if="coach.photo"
                  :src="coach.photo"
                  class="h-9 w-9 rounded-full object-cover"
                  alt=""
                />
                <div v-else class="flex h-9 w-9 items-center justify-center rounded-full bg-slate-200 text-xs text-slate-500">
                  {{ coach.first_name?.[0] }}
                </div>
              </td>
              <td class="px-4 py-3 text-sm font-medium text-slate-900">
                {{ coach.first_name }} {{ coach.last_name }}
              </td>
              <td class="px-4 py-3 text-sm text-slate-600">{{ coach.age }}</td>
              <td class="px-4 py-3 text-sm text-slate-600">{{ coach.email }}</td>
              <td class="px-4 py-3 text-sm text-slate-600">{{ formatCurrency(coach.salary) }}</td>
              <td class="px-4 py-3 text-right text-sm" @click.stop>
                <RouterLink
                  :to="{ name: 'coaches.edit', params: { id: coach.id } }"
                  class="font-medium text-brand-600 hover:text-brand-700"
                >
                  Edit
                </RouterLink>
                <button
                  type="button"
                  class="ml-3 font-medium text-red-600 hover:text-red-700"
                  @click="coachToDelete = coach"
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

    <AppModal :open="!!coachToDelete" title="Delete coach" @close="coachToDelete = null">
      Are you sure you want to delete
      <strong>{{ coachToDelete?.first_name }} {{ coachToDelete?.last_name }}</strong>? This cannot be undone.
      <template #actions>
        <AppButton variant="secondary" @click="coachToDelete = null">Cancel</AppButton>
        <AppButton variant="danger" :loading="deleting" @click="confirmDelete">Delete</AppButton>
      </template>
    </AppModal>
  </div>
</template>
