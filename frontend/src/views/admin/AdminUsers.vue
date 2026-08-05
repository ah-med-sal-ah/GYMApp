<script setup>
import { onMounted, ref } from 'vue'
import { RouterLink, useRouter } from 'vue-router'
import { adminApi } from '@/api/admin'
import { usePaginatedList } from '@/composables/usePaginatedList'
import PageHeader from '@/components/ui/PageHeader.vue'
import AppCard from '@/components/ui/AppCard.vue'
import AppInput from '@/components/ui/AppInput.vue'
import AppPagination from '@/components/ui/AppPagination.vue'
import SortableHeader from '@/components/ui/SortableHeader.vue'
import LoadingBlock from '@/components/ui/LoadingBlock.vue'
import EmptyState from '@/components/ui/EmptyState.vue'

const router = useRouter()

const { items, pagination, loading, loadError, filters, load, sort, changePage, search } =
  usePaginatedList(adminApi.users, 'users', { sortBy: 'created_at' })

const searchTerm = ref('')

onMounted(load)

function goToDetails(id) {
  router.push({ name: 'admin.users.show', params: { id } })
}
</script>

<template>
  <div>
    <PageHeader title="Gyms" subtitle="All registered gym accounts" />

    <AppCard>
      <div class="mb-4">
        <AppInput
          v-model="searchTerm"
          placeholder="Search by gym name or username…"
          @update:model-value="search"
        />
      </div>

      <LoadingBlock v-if="loading" />
      <p v-else-if="loadError" class="rounded-md bg-red-50 px-4 py-3 text-sm text-red-700">{{ loadError }}</p>
      <EmptyState v-else-if="!items.length" message="No gyms found." />

      <div v-else class="overflow-x-auto">
        <table class="min-w-full divide-y divide-slate-200">
          <thead>
            <tr>
              <SortableHeader
                field="gym_name"
                label="Gym name"
                :sort-by="filters.sort_by"
                :sort-order="filters.sort_order"
                @sort="sort"
              />
              <SortableHeader
                field="username"
                label="Username"
                :sort-by="filters.sort_by"
                :sort-order="filters.sort_order"
                @sort="sort"
              />
              <th class="px-4 py-3" />
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100">
            <tr
              v-for="user in items"
              :key="user.id"
              class="cursor-pointer hover:bg-slate-50"
              @click="goToDetails(user.id)"
            >
              <td class="px-4 py-3 text-sm font-medium text-slate-900">{{ user.gym_name }}</td>
              <td class="px-4 py-3 text-sm text-slate-600">{{ user.username }}</td>
              <td class="px-4 py-3 text-right text-sm" @click.stop>
                <RouterLink
                  :to="{ name: 'admin.users.show', params: { id: user.id } }"
                  class="font-medium text-brand-600 hover:text-brand-700"
                >
                  View
                </RouterLink>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <AppPagination :pagination="pagination" @change="changePage" />
    </AppCard>
  </div>
</template>
