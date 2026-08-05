<script setup>
import { onMounted, ref } from 'vue'
import { RouterLink, useRouter } from 'vue-router'
import { EXPENSE_CATEGORIES, expensesApi } from '@/api/expenses'
import { usePaginatedList } from '@/composables/usePaginatedList'
import { formatCurrency, formatDate } from '@/lib/format'
import { useToast } from '@/lib/toast'
import PageHeader from '@/components/ui/PageHeader.vue'
import AppCard from '@/components/ui/AppCard.vue'
import AppInput from '@/components/ui/AppInput.vue'
import AppSelect from '@/components/ui/AppSelect.vue'
import AppButton from '@/components/ui/AppButton.vue'
import AppBadge from '@/components/ui/AppBadge.vue'
import AppPagination from '@/components/ui/AppPagination.vue'
import SortableHeader from '@/components/ui/SortableHeader.vue'
import LoadingBlock from '@/components/ui/LoadingBlock.vue'
import EmptyState from '@/components/ui/EmptyState.vue'
import AppModal from '@/components/ui/AppModal.vue'

const router = useRouter()
const toast = useToast()

const { items, pagination, loading, loadError, filters, load, sort, changePage, search, setFilter } =
  usePaginatedList(expensesApi.list, 'expenses', { sortBy: 'expense_date' })

const searchTerm = ref('')
const categoryFilter = ref('')
const expenseToDelete = ref(null)
const deleting = ref(false)

const categoryOptions = EXPENSE_CATEGORIES.map((category) => ({ value: category, label: category }))

onMounted(load)

function goToDetails(id) {
  router.push({ name: 'expenses.show', params: { id } })
}

function onCategoryChange(value) {
  categoryFilter.value = value
  setFilter('category', value)
}

async function confirmDelete() {
  if (!expenseToDelete.value) return
  deleting.value = true
  try {
    await expensesApi.remove(expenseToDelete.value.id)
    toast.success('Expense deleted.')
    expenseToDelete.value = null
    load()
  } catch (error) {
    toast.error(error?.response?.data?.message || 'Failed to delete expense.')
  } finally {
    deleting.value = false
  }
}
</script>

<template>
  <div>
    <PageHeader title="Expenses" subtitle="Track your gym's spending">
      <template #actions>
        <RouterLink :to="{ name: 'expenses.create' }">
          <AppButton>+ Add Expense</AppButton>
        </RouterLink>
      </template>
    </PageHeader>

    <AppCard>
      <div class="mb-4 grid grid-cols-1 gap-3 sm:grid-cols-3">
        <div class="sm:col-span-2">
          <AppInput
            v-model="searchTerm"
            placeholder="Search by title…"
            @update:model-value="search"
          />
        </div>
        <AppSelect
          :model-value="categoryFilter"
          placeholder="All categories"
          :options="categoryOptions"
          @update:model-value="onCategoryChange"
        />
      </div>

      <LoadingBlock v-if="loading" />
      <p v-else-if="loadError" class="rounded-md bg-red-50 px-4 py-3 text-sm text-red-700">{{ loadError }}</p>
      <EmptyState v-else-if="!items.length" message="No expenses found." />

      <div v-else class="overflow-x-auto">
        <table class="min-w-full divide-y divide-slate-200">
          <thead>
            <tr>
              <SortableHeader
                field="title"
                label="Title"
                :sort-by="filters.sort_by"
                :sort-order="filters.sort_order"
                @sort="sort"
              />
              <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Category</th>
              <SortableHeader
                field="amount"
                label="Amount"
                :sort-by="filters.sort_by"
                :sort-order="filters.sort_order"
                @sort="sort"
              />
              <SortableHeader
                field="expense_date"
                label="Date"
                :sort-by="filters.sort_by"
                :sort-order="filters.sort_order"
                @sort="sort"
              />
              <th class="px-4 py-3" />
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100">
            <tr
              v-for="expense in items"
              :key="expense.id"
              class="cursor-pointer hover:bg-slate-50"
              @click="goToDetails(expense.id)"
            >
              <td class="px-4 py-3 text-sm font-medium text-slate-900">{{ expense.title }}</td>
              <td class="px-4 py-3 text-sm text-slate-600"><AppBadge>{{ expense.category }}</AppBadge></td>
              <td class="px-4 py-3 text-sm text-slate-600">{{ formatCurrency(expense.amount) }}</td>
              <td class="px-4 py-3 text-sm text-slate-600">{{ formatDate(expense.expense_date) }}</td>
              <td class="px-4 py-3 text-right text-sm" @click.stop>
                <RouterLink
                  :to="{ name: 'expenses.edit', params: { id: expense.id } }"
                  class="font-medium text-brand-600 hover:text-brand-700"
                >
                  Edit
                </RouterLink>
                <button
                  type="button"
                  class="ml-3 font-medium text-red-600 hover:text-red-700"
                  @click="expenseToDelete = expense"
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

    <AppModal :open="!!expenseToDelete" title="Delete expense" @close="expenseToDelete = null">
      Are you sure you want to delete <strong>{{ expenseToDelete?.title }}</strong>? This cannot be undone.
      <template #actions>
        <AppButton variant="secondary" @click="expenseToDelete = null">Cancel</AppButton>
        <AppButton variant="danger" :loading="deleting" @click="confirmDelete">Delete</AppButton>
      </template>
    </AppModal>
  </div>
</template>
