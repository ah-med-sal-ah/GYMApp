<script setup>
import { computed, onMounted, reactive, ref, watch } from 'vue'
import { useRouter } from 'vue-router'
import { clientsApi } from '@/api/clients'
import { coachesApi } from '@/api/coaches'
import { PRICE_TIERS, sportsApi } from '@/api/sports'
import { extractErrorMessage, extractFieldErrors } from '@/lib/errors'
import { useToast } from '@/lib/toast'
import PageHeader from '@/components/ui/PageHeader.vue'
import AppCard from '@/components/ui/AppCard.vue'
import AppInput from '@/components/ui/AppInput.vue'
import AppSelect from '@/components/ui/AppSelect.vue'
import AppCheckboxGroup from '@/components/ui/AppCheckboxGroup.vue'
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
  registration_type: '',
  sports: [],
  coach_id: '',
})
const currentPhotoUrl = ref('')
const errors = reactive({})
const formError = ref('')
const saving = ref(false)
const loading = ref(true)

const allSports = ref([])
const availableCoaches = ref([])
const loadingCoaches = ref(false)

const sportOptions = computed(() =>
  allSports.value.map((sport) => ({ value: sport.id, label: sport.name })),
)

// A registration type is only offered when every selected sport prices that tier.
const registrationTypeOptions = computed(() => {
  const selectedSports = allSports.value.filter((sport) => form.sports.includes(sport.id))
  if (!selectedSports.length) return []

  return PRICE_TIERS.filter((tier) =>
    selectedSports.every((sport) => sport[`${tier.key}_price`] !== null),
  ).map((tier) => ({ value: tier.key, label: tier.label }))
})

const coachOptions = computed(() =>
  availableCoaches.value.map((coach) => ({
    value: coach.id,
    label: `${coach.first_name} ${coach.last_name}`,
  })),
)

async function refreshAvailableCoaches() {
  if (!form.sports.length) {
    availableCoaches.value = []
    form.coach_id = ''
    return
  }

  loadingCoaches.value = true
  try {
    const results = await Promise.all(form.sports.map((sportId) => coachesApi.bySport(sportId)))
    const merged = new Map()
    results.forEach((response) => {
      response.data.forEach((coach) => merged.set(coach.id, coach))
    })
    availableCoaches.value = Array.from(merged.values())

    if (form.coach_id && !merged.has(Number(form.coach_id))) {
      form.coach_id = ''
    }
  } finally {
    loadingCoaches.value = false
  }
}

watch(() => [...form.sports], refreshAvailableCoaches)

watch(registrationTypeOptions, (options) => {
  if (form.registration_type && !options.some((option) => option.value === form.registration_type)) {
    form.registration_type = ''
  }
})

function clearErrors() {
  Object.keys(errors).forEach((key) => delete errors[key])
}

async function loadClient() {
  const { data } = await clientsApi.get(props.id)
  form.first_name = data.first_name
  form.last_name = data.last_name
  form.age = data.age
  form.email = data.email
  form.phone = data.phone
  form.sports = data.sports.map((sport) => sport.id)
  form.registration_type = data.registration_type
  currentPhotoUrl.value = data.photo || ''
  await refreshAvailableCoaches()
  form.coach_id = data.coach?.id ?? ''
}

onMounted(async () => {
  loading.value = true
  try {
    const sportsRes = await sportsApi.all()
    allSports.value = sportsRes.data.sports
    if (isEdit.value) {
      await loadClient()
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
    registration_type: form.registration_type,
    sports: form.sports,
    coach_id: form.coach_id,
  }

  try {
    if (isEdit.value) {
      await clientsApi.update(props.id, payload)
      toast.success('Client updated successfully.')
    } else {
      await clientsApi.create(payload)
      toast.success('Client created successfully.')
    }
    router.push({ name: 'clients.index' })
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
    <PageHeader :title="isEdit ? 'Edit Client' : 'Add Client'" />

    <LoadingBlock v-if="loading" />

    <AppCard v-else class="max-w-2xl">
      <form class="space-y-5" @submit.prevent="submit">
        <AppFileInput v-model="form.photo" :current-url="currentPhotoUrl" error="" />

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
          <AppInput v-model="form.first_name" label="First name" required :error="errors.first_name" />
          <AppInput v-model="form.last_name" label="Last name" required :error="errors.last_name" />
          <AppInput v-model="form.age" type="number" min="1" max="120" label="Age" required :error="errors.age" />
          <AppInput v-model="form.email" type="email" label="Email" required :error="errors.email" />
          <AppInput v-model="form.phone" label="Phone" required :error="errors.phone" />
        </div>

        <AppCheckboxGroup
          v-model="form.sports"
          label="Sports"
          required
          :options="sportOptions"
          :error="errors.sports || errors['sports.0']"
        />

        <AppSelect
          v-model="form.registration_type"
          label="Registration type"
          required
          :options="registrationTypeOptions"
          :placeholder="form.sports.length ? 'Select a registration type…' : 'Select a sport first'"
          :error="errors.registration_type"
        />
        <p
          v-if="form.sports.length && !registrationTypeOptions.length"
          class="-mt-3 text-sm text-amber-600"
        >
          The selected sport(s) don't share a common registration type yet.
        </p>

        <AppSelect
          v-model="form.coach_id"
          label="Coach"
          required
          :options="coachOptions"
          :placeholder="form.sports.length ? 'Select a coach…' : 'Select a sport first'"
          :error="errors.coach_id"
        />
        <p v-if="form.sports.length && !loadingCoaches && !coachOptions.length" class="-mt-3 text-sm text-amber-600">
          No coaches available for the selected sport(s) yet.
        </p>

        <p v-if="formError" class="rounded-md bg-red-50 px-3 py-2 text-sm text-red-700">{{ formError }}</p>

        <div class="flex justify-end gap-3 border-t border-slate-100 pt-4">
          <AppButton type="button" variant="secondary" @click="router.back()">Cancel</AppButton>
          <AppButton type="submit" :loading="saving">{{ isEdit ? 'Save changes' : 'Create client' }}</AppButton>
        </div>
      </form>
    </AppCard>
  </div>
</template>
