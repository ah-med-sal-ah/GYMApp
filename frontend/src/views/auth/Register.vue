<script setup>
import { reactive, ref } from 'vue'
import { RouterLink, useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { extractErrorMessage, extractFieldErrors } from '@/lib/errors'
import AppInput from '@/components/ui/AppInput.vue'
import AppButton from '@/components/ui/AppButton.vue'
import { useToast } from '@/lib/toast'

const auth = useAuthStore()
const router = useRouter()
const toast = useToast()

const form = reactive({
  name: '',
  email: '',
  password: '',
  password_confirmation: '',
})
const errors = reactive({})
const loading = ref(false)
const formError = ref('')

async function submit() {
  loading.value = true
  formError.value = ''
  Object.keys(errors).forEach((key) => delete errors[key])

  try {
    await auth.register(form)
    toast.success('Gym account created successfully!')
    router.push({ name: 'dashboard' })
  } catch (error) {
    formError.value = extractErrorMessage(error, 'Unable to register.')
    Object.assign(errors, extractFieldErrors(error))
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <div class="flex min-h-full items-center justify-center bg-slate-50 px-4 py-12">
    <div class="w-full max-w-sm rounded-xl bg-white p-8 shadow-sm ring-1 ring-slate-200">
      <div class="mb-6 text-center">
        <p class="text-2xl font-bold text-brand-600">🏋️ YourGYM</p>
        <h1 class="mt-2 text-lg font-semibold text-slate-900">Create your gym account</h1>
      </div>

      <form class="space-y-4" @submit.prevent="submit">
        <AppInput
          v-model="form.name"
          label="Gym name"
          required
          :error="errors.name"
        />
        <AppInput
          v-model="form.email"
          type="email"
          label="Email"
          required
          :error="errors.email"
        />
        <AppInput
          v-model="form.password"
          type="password"
          label="Password"
          required
          :error="errors.password"
        />
        <AppInput
          v-model="form.password_confirmation"
          type="password"
          label="Confirm password"
          required
        />

        <p v-if="formError" class="rounded-md bg-red-50 px-3 py-2 text-sm text-red-700">
          {{ formError }}
        </p>

        <AppButton type="submit" class="w-full justify-center" :loading="loading">
          Create account
        </AppButton>
      </form>

      <p class="mt-6 text-center text-sm text-slate-500">
        Already have an account?
        <RouterLink :to="{ name: 'login' }" class="font-semibold text-brand-600 hover:text-brand-700">
          Sign in
        </RouterLink>
      </p>
    </div>
  </div>
</template>
