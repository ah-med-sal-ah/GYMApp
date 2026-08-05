<script setup>
import { onMounted, reactive, ref } from 'vue'
import { useAdminAuthStore } from '@/stores/admin'
import { extractErrorMessage, extractFieldErrors } from '@/lib/errors'
import { useToast } from '@/lib/toast'
import PageHeader from '@/components/ui/PageHeader.vue'
import AppCard from '@/components/ui/AppCard.vue'
import AppInput from '@/components/ui/AppInput.vue'
import AppButton from '@/components/ui/AppButton.vue'
import LoadingBlock from '@/components/ui/LoadingBlock.vue'

const adminAuth = useAdminAuthStore()
const toast = useToast()

const loading = ref(true)

const passwordForm = reactive({
  current_password: '',
  new_password: '',
  new_password_confirmation: '',
})
const errors = reactive({})
const formError = ref('')
const saving = ref(false)

function clearErrors() {
  Object.keys(errors).forEach((key) => delete errors[key])
}

onMounted(async () => {
  loading.value = true
  try {
    if (!adminAuth.admin) await adminAuth.fetchProfile()
  } catch {
    // handled globally via 401 redirect
  } finally {
    loading.value = false
  }
})

async function submit() {
  saving.value = true
  formError.value = ''
  clearErrors()

  try {
    await adminAuth.changePassword(passwordForm)
    toast.success('Password changed successfully.')
    passwordForm.current_password = ''
    passwordForm.new_password = ''
    passwordForm.new_password_confirmation = ''
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
    <PageHeader title="Profile" subtitle="Your admin account details" />

    <LoadingBlock v-if="loading" />

    <div v-else class="grid grid-cols-1 gap-6 lg:max-w-2xl">
      <AppCard title="Admin information">
        <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2">
          <div>
            <dt class="text-xs font-medium uppercase tracking-wide text-slate-400">Name</dt>
            <dd class="mt-1 text-sm text-slate-900">{{ adminAuth.admin?.name }}</dd>
          </div>
          <div>
            <dt class="text-xs font-medium uppercase tracking-wide text-slate-400">Username</dt>
            <dd class="mt-1 text-sm text-slate-900">{{ adminAuth.admin?.username }}</dd>
          </div>
          <div>
            <dt class="text-xs font-medium uppercase tracking-wide text-slate-400">Email</dt>
            <dd class="mt-1 text-sm text-slate-900">{{ adminAuth.admin?.email }}</dd>
          </div>
        </dl>
      </AppCard>

      <AppCard title="Change password">
        <form class="space-y-4" @submit.prevent="submit">
          <AppInput
            v-model="passwordForm.current_password"
            type="password"
            label="Current password"
            required
            :error="errors.current_password"
          />
          <AppInput
            v-model="passwordForm.new_password"
            type="password"
            label="New password"
            required
            :error="errors.new_password"
          />
          <AppInput
            v-model="passwordForm.new_password_confirmation"
            type="password"
            label="Confirm new password"
            required
          />

          <p v-if="formError" class="rounded-md bg-red-50 px-3 py-2 text-sm text-red-700">{{ formError }}</p>

          <div class="flex justify-end border-t border-slate-100 pt-4">
            <AppButton type="submit" :loading="saving">Update password</AppButton>
          </div>
        </form>
      </AppCard>
    </div>
  </div>
</template>
