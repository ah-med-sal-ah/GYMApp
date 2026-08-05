<script setup>
import { onMounted, ref } from 'vue'
import { RouterLink, useRouter } from 'vue-router'
import { expensesApi } from '@/api/expenses'
import { formatCurrency, formatDate } from '@/lib/format'
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

const expense = ref(null)
const loading = ref(true)
const loadError = ref('')
const confirmingDelete = ref(false)
const deleting = ref(false)

async function load() {
  loading.value = true
  loadError.value = ''
  try {
    const { data } = await expensesApi.get(props.id)
    expense.value = data
  } catch (error) {
    loadError.value = error?.response?.data?.message || 'Failed to load expense.'
  } finally {
    loading.value = false
  }
}

onMounted(load)

async function remove() {
  deleting.value = true
  try {
    await expensesApi.remove(props.id)
    toast.success('Expense deleted.')
    router.push({ name: 'expenses.index' })
  } catch (error) {
    toast.error(error?.response?.data?.message || 'Failed to delete expense.')
  } finally {
    deleting.value = false
  }
}
</script>

<template>
  <div>
    <PageHeader title="Expense Details">
      <template #actions>
        <RouterLink :to="{ name: 'expenses.index' }">
          <AppButton variant="ghost">← Back</AppButton>
        </RouterLink>
      </template>
    </PageHeader>

    <LoadingBlock v-if="loading" />
    <p v-else-if="loadError" class="rounded-md bg-red-50 px-4 py-3 text-sm text-red-700">{{ loadError }}</p>

    <AppCard v-else-if="expense" class="max-w-2xl">
      <div class="flex items-start justify-between">
        <div>
          <h2 class="text-lg font-semibold text-slate-900">{{ expense.title }}</h2>
          <div class="mt-2 flex items-center gap-2">
            <AppBadge>{{ expense.category }}</AppBadge>
            <span class="text-sm text-slate-500">{{ formatDate(expense.expense_date) }}</span>
          </div>
        </div>

        <div class="flex gap-2">
          <RouterLink :to="{ name: 'expenses.edit', params: { id: expense.id } }">
            <AppButton variant="secondary">Edit</AppButton>
          </RouterLink>
          <AppButton variant="danger" @click="confirmingDelete = true">Delete</AppButton>
        </div>
      </div>

      <dl class="mt-6 grid grid-cols-1 gap-4 border-t border-slate-100 pt-6 sm:grid-cols-2">
        <div>
          <dt class="text-xs font-medium uppercase tracking-wide text-slate-400">Amount</dt>
          <dd class="mt-1 text-sm text-slate-900">{{ formatCurrency(expense.amount) }}</dd>
        </div>
        <div>
          <dt class="text-xs font-medium uppercase tracking-wide text-slate-400">Date</dt>
          <dd class="mt-1 text-sm text-slate-900">{{ formatDate(expense.expense_date) }}</dd>
        </div>
        <div class="sm:col-span-2">
          <dt class="text-xs font-medium uppercase tracking-wide text-slate-400">Description</dt>
          <dd class="mt-1 text-sm text-slate-900">{{ expense.description || '—' }}</dd>
        </div>
      </dl>
    </AppCard>

    <AppModal :open="confirmingDelete" title="Delete expense" @close="confirmingDelete = false">
      This action cannot be undone.
      <template #actions>
        <AppButton variant="secondary" @click="confirmingDelete = false">Cancel</AppButton>
        <AppButton variant="danger" :loading="deleting" @click="remove">Delete</AppButton>
      </template>
    </AppModal>
  </div>
</template>
