<script setup>
import { onMounted, ref } from 'vue'
import { RouterLink, useRouter } from 'vue-router'
import { sportsApi } from '@/api/sports'
import { usePaginatedList } from '@/composables/usePaginatedList'
import { formatOptionalCurrency } from '@/lib/format'
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
  usePaginatedList(sportsApi.list, 'sports', { sortBy: 'created_at' })

const searchTerm = ref('')
const sportToDelete = ref(null)
const deleting = ref(false)

onMounted(load)

function goToDetails(id) {
  router.push({ name: 'sports.show', params: { id } })
}

async function confirmDelete() {
  if (!sportToDelete.value) return
  deleting.value = true
  try {
    await sportsApi.remove(sportToDelete.value.id)
    toast.success('Sport deleted.')
    sportToDelete.value = null
    load()
  } catch (error) {
    toast.error(error?.response?.data?.message || 'Failed to delete sport.')
  } finally {
    deleting.value = false
  }
}
</script>

<template>
  <div>
    <PageHeader title="Sports" subtitle="Manage the sports your gym offers">
      <template #actions>
        <RouterLink :to="{ name: 'sports.create' }">
          <AppButton>+ Add Sport</AppButton>
        </RouterLink>
      </template>
    </PageHeader>

    <AppCard>
      <div class="mb-4">
        <AppInput
          v-model="searchTerm"
          placeholder="Search by name…"
          @update:model-value="search"
        />
      </div>

      <LoadingBlock v-if="loading" />
      <p v-else-if="loadError" class="rounded-md bg-red-50 px-4 py-3 text-sm text-red-700">{{ loadError }}</p>
      <EmptyState v-else-if="!items.length" message="No sports found." />

      <div v-else class="overflow-x-auto">
        <table class="min-w-full divide-y divide-slate-200">
          <thead>
            <tr>
              <th class="px-4 py-3" />
              <SortableHeader
                field="name"
                label="Name"
                :sort-by="filters.sort_by"
                :sort-order="filters.sort_order"
                @sort="sort"
              />
              <SortableHeader
                field="day_price"
                label="Day price"
                :sort-by="filters.sort_by"
                :sort-order="filters.sort_order"
                @sort="sort"
              />
              <SortableHeader
                field="week_price"
                label="Week price"
                :sort-by="filters.sort_by"
                :sort-order="filters.sort_order"
                @sort="sort"
              />
              <SortableHeader
                field="month_price"
                label="Month price"
                :sort-by="filters.sort_by"
                :sort-order="filters.sort_order"
                @sort="sort"
              />
              <SortableHeader
                field="year_price"
                label="Year price"
                :sort-by="filters.sort_by"
                :sort-order="filters.sort_order"
                @sort="sort"
              />
              <th class="px-4 py-3" />
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100">
            <tr
              v-for="sport in items"
              :key="sport.id"
              class="cursor-pointer hover:bg-slate-50"
              @click="goToDetails(sport.id)"
            >
              <td class="px-4 py-3">
                <img
                  v-if="sport.photo"
                  :src="sport.photo"
                  class="h-9 w-9 rounded-full object-cover"
                  alt=""
                />
                <div v-else class="flex h-9 w-9 items-center justify-center rounded-full bg-slate-200 text-xs text-slate-500">
                  {{ sport.name?.[0] }}
                </div>
              </td>
              <td class="px-4 py-3 text-sm font-medium text-slate-900">{{ sport.name }}</td>
              <td class="px-4 py-3 text-sm text-slate-600">{{ formatOptionalCurrency(sport.day_price) }}</td>
              <td class="px-4 py-3 text-sm text-slate-600">{{ formatOptionalCurrency(sport.week_price) }}</td>
              <td class="px-4 py-3 text-sm text-slate-600">{{ formatOptionalCurrency(sport.month_price) }}</td>
              <td class="px-4 py-3 text-sm text-slate-600">{{ formatOptionalCurrency(sport.year_price) }}</td>
              <td class="px-4 py-3 text-right text-sm" @click.stop>
                <RouterLink
                  :to="{ name: 'sports.edit', params: { id: sport.id } }"
                  class="font-medium text-brand-600 hover:text-brand-700"
                >
                  Edit
                </RouterLink>
                <button
                  type="button"
                  class="ml-3 font-medium text-red-600 hover:text-red-700"
                  @click="sportToDelete = sport"
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

    <AppModal :open="!!sportToDelete" title="Delete sport" @close="sportToDelete = null">
      Are you sure you want to delete <strong>{{ sportToDelete?.name }}</strong>? This cannot be undone.
      <template #actions>
        <AppButton variant="secondary" @click="sportToDelete = null">Cancel</AppButton>
        <AppButton variant="danger" :loading="deleting" @click="confirmDelete">Delete</AppButton>
      </template>
    </AppModal>
  </div>
</template>
