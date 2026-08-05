<script setup>
import { onMounted, ref } from 'vue'
import { RouterLink, RouterView, useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { useToast } from '@/lib/toast'

const auth = useAuthStore()
const router = useRouter()
const toast = useToast()

const sidebarOpen = ref(false)

const navItems = [
  { name: 'dashboard', label: 'Dashboard', icon: '📊' },
  { name: 'clients.index', label: 'Clients', icon: '🧑‍🤝‍🧑' },
  { name: 'coaches.index', label: 'Coach', icon: '🏋️' },
  { name: 'sports.index', label: 'Sports', icon: '🏅' },
  { name: 'expenses.index', label: 'Expenses', icon: '💳' },
]

onMounted(() => {
  if (!auth.gym) {
    auth.fetchMe().catch(() => {})
  }
})

async function logout() {
  try {
    await auth.logout()
  } catch {
    // Ignore network errors, token is cleared locally regardless.
  }
  toast.success('Logged out successfully.')
  router.push({ name: 'login' })
}
</script>

<template>
  <div class="flex min-h-full">
    <div
      v-if="sidebarOpen"
      class="fixed inset-0 z-20 bg-slate-900/40 lg:hidden"
      @click="sidebarOpen = false"
    />

    <aside
      class="fixed inset-y-0 left-0 z-30 w-64 -translate-x-full bg-slate-900 text-slate-200 transition-transform lg:static lg:translate-x-0"
      :class="{ 'translate-x-0': sidebarOpen }"
    >
      <div class="flex h-16 items-center gap-2 px-6 text-lg font-bold text-white">
        <span>🏋️</span> YourGYM
      </div>
      <nav class="mt-4 flex flex-col gap-1 px-3">
        <RouterLink
          v-for="item in navItems"
          :key="item.name"
          :to="{ name: item.name }"
          class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium text-slate-300 transition hover:bg-slate-800 hover:text-white"
          active-class="bg-brand-600! text-white!"
          @click="sidebarOpen = false"
        >
          <span>{{ item.icon }}</span> {{ item.label }}
        </RouterLink>
      </nav>

      <div class="absolute bottom-0 w-64 border-t border-slate-800 p-3">
        <RouterLink
          :to="{ name: 'profile' }"
          class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium text-slate-300 transition hover:bg-slate-800 hover:text-white"
          active-class="bg-brand-600! text-white!"
        >
          <span>👤</span> Profile
        </RouterLink>
        <button
          type="button"
          class="mt-1 flex w-full items-center gap-3 rounded-lg px-3 py-2.5 text-left text-sm font-medium text-slate-300 transition hover:bg-slate-800 hover:text-white"
          @click="logout"
        >
          <span>🚪</span> Logout
        </button>
      </div>
    </aside>

    <div class="flex min-h-full flex-1 flex-col">
      <header class="flex h-16 items-center justify-between border-b border-slate-200 bg-white px-4 sm:px-6">
        <button
          type="button"
          class="rounded-md p-2 text-slate-500 hover:bg-slate-100 lg:hidden"
          @click="sidebarOpen = true"
        >
          ☰
        </button>
        <div class="hidden lg:block" />
        <div class="text-sm font-medium text-slate-700">
          {{ auth.gym?.name || 'Loading…' }}
        </div>
      </header>

      <main class="flex-1 bg-slate-50 p-4 sm:p-6">
        <RouterView />
      </main>
    </div>
  </div>
</template>
