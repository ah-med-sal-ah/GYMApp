<script setup>
import { computed, onMounted, reactive, ref } from 'vue'
import { useRouter } from 'vue-router'
import { PRICE_TIERS, sportsApi } from '@/api/sports'
import { extractErrorMessage, extractFieldErrors } from '@/lib/errors'
import { useToast } from '@/lib/toast'
import PageHeader from '@/components/ui/PageHeader.vue'
import AppCard from '@/components/ui/AppCard.vue'
import AppInput from '@/components/ui/AppInput.vue'
import AppFileInput from '@/components/ui/AppFileInput.vue'
import AppButton from '@/components/ui/AppButton.vue'
import LoadingBlock from '@/components/ui/LoadingBlock.vue'

const props = defineProps({ id: { type: String, default: null } })
const router = useRouter()
const toast = useToast()

const isEdit = computed(() => !!props.id)

const form = reactive({
  photo: null,
  name: '',
})
// One entry per tier: { enabled, price }
const tiers = reactive(
  Object.fromEntries(PRICE_TIERS.map((tier) => [tier.key, { enabled: false, price: '' }])),
)
const currentPhotoUrl = ref('')
const errors = reactive({})
const formError = ref('')
const saving = ref(false)
const loading = ref(true)

function clearErrors() {
  Object.keys(errors).forEach((key) => delete errors[key])
}

function toggleTier(key) {
  tiers[key].enabled = !tiers[key].enabled
  if (!tiers[key].enabled) tiers[key].price = ''
}

onMounted(async () => {
  if (!isEdit.value) {
    loading.value = false
    return
  }

  loading.value = true
  try {
    const { data } = await sportsApi.get(props.id)
    form.name = data.name
    currentPhotoUrl.value = data.photo || ''
    PRICE_TIERS.forEach(({ key }) => {
      const price = data[`${key}_price`]
      tiers[key].enabled = price !== null
      tiers[key].price = price !== null ? price : ''
    })
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
    name: form.name,
  }
  PRICE_TIERS.forEach(({ key }) => {
    payload[`${key}_price`] = tiers[key].enabled ? tiers[key].price : ''
  })

  try {
    if (isEdit.value) {
      await sportsApi.update(props.id, payload)
      toast.success('Sport updated successfully.')
    } else {
      await sportsApi.create(payload)
      toast.success('Sport created successfully.')
    }
    router.push({ name: 'sports.index' })
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
    <PageHeader :title="isEdit ? 'Edit Sport' : 'Add Sport'" />

    <LoadingBlock v-if="loading" />

    <AppCard v-else class="max-w-2xl">
      <form class="space-y-5" @submit.prevent="submit">
        <AppFileInput v-model="form.photo" :current-url="currentPhotoUrl" />

        <AppInput v-model="form.name" label="Name" required :error="errors.name" />

        <div>
          <span class="mb-1 block text-sm font-medium text-slate-700">
            Price <span class="text-red-500">*</span>
          </span>
          <p class="mb-2 text-xs text-slate-500">Tick the tiers this sport offers and set their price.</p>

          <div class="space-y-3 rounded-md border border-slate-200 p-3">
            <div v-for="tier in PRICE_TIERS" :key="tier.key" class="flex items-center gap-3">
              <label class="flex w-28 shrink-0 cursor-pointer items-center gap-2 text-sm text-slate-700">
                <input
                  type="checkbox"
                  class="h-4 w-4 rounded border-slate-300 text-brand-600 focus:ring-brand-600"
                  :checked="tiers[tier.key].enabled"
                  @change="toggleTier(tier.key)"
                />
                {{ tier.label }}
              </label>
              <AppInput
                v-if="tiers[tier.key].enabled"
                v-model="tiers[tier.key].price"
                type="number"
                min="0"
                step="0.01"
                placeholder="Price"
                :error="errors[`${tier.key}_price`]"
              />
            </div>
          </div>
          <p v-if="errors.day_price && !tiers.day.enabled" class="mt-1 text-sm text-red-600">
            {{ errors.day_price }}
          </p>
        </div>

        <p v-if="formError" class="rounded-md bg-red-50 px-3 py-2 text-sm text-red-700">{{ formError }}</p>

        <div class="flex justify-end gap-3 border-t border-slate-100 pt-4">
          <AppButton type="button" variant="secondary" @click="router.back()">Cancel</AppButton>
          <AppButton type="submit" :loading="saving">{{ isEdit ? 'Save changes' : 'Create sport' }}</AppButton>
        </div>
      </form>
    </AppCard>
  </div>
</template>
