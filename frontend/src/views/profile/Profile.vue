<script setup>
import { onMounted, reactive, ref } from 'vue'
import { useAuthStore } from '@/stores/auth'
import { extractErrorMessage, extractFieldErrors } from '@/lib/errors'
import { useToast } from '@/lib/toast'
import PageHeader from '@/components/ui/PageHeader.vue'
import AppCard from '@/components/ui/AppCard.vue'
import AppInput from '@/components/ui/AppInput.vue'
import AppButton from '@/components/ui/AppButton.vue'
import LoadingBlock from '@/components/ui/LoadingBlock.vue'

const auth = useAuthStore()
const toast = useToast()

const loading = ref(true)
const editingProfile = ref(false)
const savingProfile = ref(false)
const profileForm = reactive({ email: '', phone: '' })
const profileErrors = reactive({})
const profileFormError = ref('')

const passwordForm = reactive({
  current_password: '',
  password: '',
  password_confirmation: '',
})
const errors = reactive({})
const formError = ref('')
const saving = ref(false)

function clearErrors() {
  Object.keys(errors).forEach((key) => delete errors[key])
}

function clearProfileErrors() {
  Object.keys(profileErrors).forEach((key) => delete profileErrors[key])
}

function startEditingProfile() {
  profileForm.email = auth.gym?.email || ''
  profileForm.phone = auth.gym?.phone || ''
  profileFormError.value = ''
  clearProfileErrors()
  editingProfile.value = true
}

function cancelEditingProfile() {
  editingProfile.value = false
  profileFormError.value = ''
  clearProfileErrors()
}

onMounted(async () => {
  loading.value = true
  try {
    if (!auth.gym) await auth.fetchMe()
  } catch {
    // handled globally via 401 redirect
  } finally {
    loading.value = false
  }
})

async function saveProfile() {
  savingProfile.value = true
  profileFormError.value = ''
  clearProfileErrors()

  try {
    await auth.updateProfile(profileForm)
    toast.success('Profile updated successfully.')
    editingProfile.value = false
  } catch (error) {
    profileFormError.value = extractErrorMessage(error)
    Object.assign(profileErrors, extractFieldErrors(error))
  } finally {
    savingProfile.value = false
  }
}

async function submit() {
  saving.value = true
  formError.value = ''
  clearErrors()

  try {
    await auth.changePassword(passwordForm)
    toast.success('Password changed successfully.')
    passwordForm.current_password = ''
    passwordForm.password = ''
    passwordForm.password_confirmation = ''
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
    <PageHeader title="Profile" subtitle="Your gym account details" />

    <LoadingBlock v-if="loading" />

    <div v-else class="grid grid-cols-1 gap-6 lg:max-w-2xl">
      <AppCard title="Gym information">
        <template #actions>
          <AppButton v-if="!editingProfile" variant="secondary" @click="startEditingProfile">
            Edit Profile
          </AppButton>
        </template>

        <dl v-if="!editingProfile" class="grid grid-cols-1 gap-4 sm:grid-cols-2">
          <div>
            <dt class="text-xs font-medium uppercase tracking-wide text-slate-400">Gym name</dt>
            <dd class="mt-1 text-sm text-slate-900">{{ auth.gym?.name }}</dd>
          </div>
          <div>
            <dt class="text-xs font-medium uppercase tracking-wide text-slate-400">Username</dt>
            <dd class="mt-1 text-sm text-slate-900">{{ auth.gym?.username }}</dd>
          </div>
          <div>
            <dt class="text-xs font-medium uppercase tracking-wide text-slate-400">Email</dt>
            <dd class="mt-1 text-sm text-slate-900">{{ auth.gym?.email }}</dd>
          </div>
          <div>
            <dt class="text-xs font-medium uppercase tracking-wide text-slate-400">Phone number</dt>
            <dd class="mt-1 text-sm text-slate-900">{{ auth.gym?.phone || '—' }}</dd>
          </div>
        </dl>

        <form v-else class="space-y-4" @submit.prevent="saveProfile">
          <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div>
              <dt class="text-xs font-medium uppercase tracking-wide text-slate-400">Gym name</dt>
              <dd class="mt-1 text-sm text-slate-900">{{ auth.gym?.name }}</dd>
            </div>
            <div>
              <dt class="text-xs font-medium uppercase tracking-wide text-slate-400">Username</dt>
              <dd class="mt-1 text-sm text-slate-900">{{ auth.gym?.username }}</dd>
            </div>
          </dl>

          <AppInput
            v-model="profileForm.email"
            type="email"
            label="Email"
            required
            :error="profileErrors.email"
          />
          <AppInput
            v-model="profileForm.phone"
            type="text"
            label="Phone number"
            required
            :error="profileErrors.phone"
          />

          <p v-if="profileFormError" class="rounded-md bg-red-50 px-3 py-2 text-sm text-red-700">
            {{ profileFormError }}
          </p>

          <div class="flex justify-end gap-2 border-t border-slate-100 pt-4">
            <AppButton type="button" variant="ghost" @click="cancelEditingProfile">Cancel</AppButton>
            <AppButton type="submit" :loading="savingProfile">Save changes</AppButton>
          </div>
        </form>
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
            v-model="passwordForm.password"
            type="password"
            label="New password"
            required
            :error="errors.password"
          />
          <AppInput
            v-model="passwordForm.password_confirmation"
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
