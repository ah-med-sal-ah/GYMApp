<script setup>
import { computed, onMounted, reactive, ref } from 'vue'
import { useRouter } from 'vue-router'
import { EXPENSE_CATEGORIES, expensesApi } from '@/api/expenses'
import { extractErrorMessage, extractFieldErrors } from '@/lib/errors'
import { useToast } from '@/lib/toast'
import PageHeader from '@/components/ui/PageHeader.vue'
import AppCard from '@/components/ui/AppCard.vue'
import AppInput from '@/components/ui/AppInput.vue'
import AppSelect from '@/components/ui/AppSelect.vue'
import AppTextarea from '@/components/ui/AppTextarea.vue'
import AppButton from '@/components/ui/AppButton.vue'
import LoadingBlock from '@/components/ui/LoadingBlock.vue'

const props = defineProps({ id: { type: String, default: null } })
const router = useRouter()
const toast = useToast()

const isEdit = computed(() => !!props.id)

const form = reactive({
  title: '',
  description: '',
  amount: '',
  expense_date: new Date().toISOString().slice(0, 10),
  category: '',
})
const errors = reactive({})
const formError = ref('')
const saving = ref(false)
const loading = ref(true)

const categoryOptions = EXPENSE_CATEGORIES.map((category) => ({ value: category, label: category }))

function clearErrors() {
  Object.keys(errors).forEach((key) => delete errors[key])
}

onMounted(async () => {
  if (!isEdit.value) {
    loading.value = false
    return
  }

  loading.value = true
  try {
    const { data } = await expensesApi.get(props.id)
    form.title = data.title
    form.description = data.description || ''
    form.amount = data.amount
    form.expense_date = data.expense_date
    form.category = data.category
  } catch {
    toast.error('Failed to load form data.')
  } finally {
    loading.value = false
  }
})

async function submit() {
  saving.value = true
  formError.value = ''
  clearErrors()

  const payload = {
    title: form.title,
    description: form.description || null,
    amount: form.amount,
    expense_date: form.expense_date,
    category: form.category,
  }

  try {
    if (isEdit.value) {
      await expensesApi.update(props.id, payload)
      toast.success('Expense updated successfully.')
    } else {
      await expensesApi.create(payload)
      toast.success('Expense created successfully.')
    }
    router.push({ name: 'expenses.index' })
  } catch (error) {
    formError.value = extractErrorMessage(error)
    Object.assign(errors, extractFieldErrors(error))
  } finally {
    saving.value = false
  }
}
</script>

<template>
  <div>
    <PageHeader :title="isEdit ? 'Edit Expense' : 'Add Expense'" />

    <LoadingBlock v-if="loading" />

    <AppCard v-else class="max-w-2xl">
      <form class="space-y-5" @submit.prevent="submit">
        <AppInput v-model="form.title" label="Title" required :error="errors.title" />

        <AppTextarea v-model="form.description" label="Description" :error="errors.description" />

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
          <AppInput
            v-model="form.amount"
            type="number"
            min="0"
            step="0.01"
            label="Amount"
            required
            :error="errors.amount"
          />
          <AppInput
            v-model="form.expense_date"
            type="date"
            label="Date"
            required
            :error="errors.expense_date"
          />
          <AppSelect
            v-model="form.category"
            label="Category"
            required
            :options="categoryOptions"
            :error="errors.category"
          />
        </div>

        <p v-if="formError" class="rounded-md bg-red-50 px-3 py-2 text-sm text-red-700">{{ formError }}</p>

        <div class="flex justify-end gap-3 border-t border-slate-100 pt-4">
          <AppButton type="button" variant="secondary" @click="router.back()">Cancel</AppButton>
          <AppButton type="submit" :loading="saving">{{ isEdit ? 'Save changes' : 'Create expense' }}</AppButton>
        </div>
      </form>
    </AppCard>
  </div>
</template>
