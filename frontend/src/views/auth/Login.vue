<script setup>
import { reactive, ref } from 'vue'
import { RouterLink, useRoute, useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { extractErrorMessage, extractFieldErrors } from '@/lib/errors'
import AppInput from '@/components/ui/AppInput.vue'
import AppButton from '@/components/ui/AppButton.vue'
import { useToast } from '@/lib/toast'

const auth = useAuthStore()
const router = useRouter()
const route = useRoute()
const toast = useToast()

const form = reactive({ email: '', password: '' })
const errors = reactive({})
const loading = ref(false)
const formError = ref('')

async function submit() {
  loading.value = true
  formError.value = ''
  Object.keys(errors).forEach((key) => delete errors[key])

  try {
    await auth.login(form)
    toast.success('Welcome back!')
    router.push(route.query.redirect || { name: 'dashboard' })
  } catch (error) {
    formError.value = extractErrorMessage(error, 'Unable to login.')
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
        <h1 class="mt-2 text-lg font-semibold text-slate-900">Sign in to your gym</h1>
      </div>

      <form class="space-y-4" @submit.prevent="submit">
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

        <p v-if="formError" class="rounded-md bg-red-50 px-3 py-2 text-sm text-red-700">
          {{ formError }}
        </p>

        <AppButton type="submit" class="w-full justify-center" :loading="loading">
          Sign in
        </AppButton>
      </form>

      <p class="mt-6 text-center text-sm text-slate-500">
        Don't have a gym account?
        <RouterLink :to="{ name: 'register' }" class="font-semibold text-brand-600 hover:text-brand-700">
          Register
        </RouterLink>
      </p>
      <p class="mt-2 text-center text-xs text-slate-400">
        <RouterLink :to="{ name: 'admin.login' }" class="hover:text-slate-600">
          Super Admin login
        </RouterLink>
      </p>
    </div>
  </div>
</template>
