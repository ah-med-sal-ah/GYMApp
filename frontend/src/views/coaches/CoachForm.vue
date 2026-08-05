<script setup>
import { computed, onMounted, reactive, ref } from 'vue'
import { useRouter } from 'vue-router'
import { coachesApi } from '@/api/coaches'
import { sportsApi } from '@/api/sports'
import { extractErrorMessage, extractFieldErrors } from '@/lib/errors'
import { useToast } from '@/lib/toast'
import PageHeader from '@/components/ui/PageHeader.vue'
import AppCard from '@/components/ui/AppCard.vue'
import AppInput from '@/components/ui/AppInput.vue'
import AppSelect from '@/components/ui/AppSelect.vue'
import AppFileInput from '@/components/ui/AppFileInput.vue'
import AppButton from '@/components/ui/AppButton.vue'
import LoadingBlock from '@/components/ui/LoadingBlock.vue'

const props = defineProps({ id: { type: String, default: null } })
const router = useRouter()
const toast = useToast()

const isEdit = computed(() => !!props.id)

const form = reactive({
  photo: null,
  first_name: '',
  last_name: '',
  age: '',
  email: '',
  phone: '',
  salary: '',
  sport_id: '',
})
const currentPhotoUrl = ref('')
const errors = reactive({})
const formError = ref('')
const saving = ref(false)
const loading = ref(true)

const allSports = ref([])
const sportOptions = computed(() =>
  allSports.value.map((sport) => ({ value: sport.id, label: sport.name })),
)

function clearErrors() {
  Object.keys(errors).forEach((key) => delete errors[key])
}

onMounted(async () => {
  loading.value = true
  try {
    const sportsRes = await sportsApi.all()
    allSports.value = sportsRes.data.sports

    if (isEdit.value) {
      const { data } = await coachesApi.get(props.id)
      form.first_name = data.first_name
      form.last_name = data.last_name
      form.age = data.age
      form.email = data.email
      form.phone = data.phone
      form.salary = data.salary
      form.sport_id = data.sport?.id ?? ''
      currentPhotoUrl.value = data.photo || ''
    }
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
    photo: form.photo,
    first_name: form.first_name,
    last_name: form.last_name,
    age: form.age,
    email: form.email,
    phone: form.phone,
    salary: form.salary,
    sport_id: form.sport_id,
  }

  try {
    if (isEdit.value) {
      await coachesApi.update(props.id, payload)
      toast.success('Coach updated successfully.')
    } else {
      await coachesApi.create(payload)
      toast.success('Coach created successfully.')
    }
    router.push({ name: 'coaches.index' })
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
    <PageHeader :title="isEdit ? 'Edit Coach' : 'Add Coach'" />

    <LoadingBlock v-if="loading" />

    <AppCard v-else class="max-w-2xl">
      <form class="space-y-5" @submit.prevent="submit">
        <AppFileInput v-model="form.photo" :current-url="currentPhotoUrl" />

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
          <AppInput v-model="form.first_name" label="First name" required :error="errors.first_name" />
          <AppInput v-model="form.last_name" label="Last name" required :error="errors.last_name" />
          <AppInput v-model="form.age" type="number" min="18" max="80" label="Age" required :error="errors.age" />
          <AppInput v-model="form.email" type="email" label="Email" required :error="errors.email" />
          <AppInput v-model="form.phone" label="Phone" required :error="errors.phone" />
          <AppInput v-model="form.salary" type="number" min="0" step="0.01" label="Salary" required :error="errors.salary" />
        </div>

        <AppSelect
          v-model="form.sport_id"
          label="Sport"
          required
          :options="sportOptions"
          :error="errors.sport_id"
        />

        <p v-if="formError" class="rounded-md bg-red-50 px-3 py-2 text-sm text-red-700">{{ formError }}</p>

        <div class="flex justify-end gap-3 border-t border-slate-100 pt-4">
          <AppButton type="button" variant="secondary" @click="router.back()">Cancel</AppButton>
          <AppButton type="submit" :loading="saving">{{ isEdit ? 'Save changes' : 'Create coach' }}</AppButton>
        </div>
      </form>
    </AppCard>
  </div>
</template>
