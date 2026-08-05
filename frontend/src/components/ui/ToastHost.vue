<script setup>
import { useToast } from '@/lib/toast'

const { toasts, dismiss } = useToast()
</script>

<template>
  <div class="fixed top-4 right-4 z-50 flex w-full max-w-sm flex-col gap-2">
    <TransitionGroup name="toast">
      <div
        v-for="toast in toasts"
        :key="toast.id"
        class="flex items-start justify-between gap-3 rounded-lg border px-4 py-3 shadow-lg"
        :class="
          toast.type === 'success'
            ? 'border-emerald-200 bg-emerald-50 text-emerald-800'
            : 'border-red-200 bg-red-50 text-red-800'
        "
      >
        <p class="text-sm font-medium">{{ toast.message }}</p>
        <button
          type="button"
          class="shrink-0 text-lg leading-none opacity-60 hover:opacity-100"
          @click="dismiss(toast.id)"
        >
          &times;
        </button>
      </div>
    </TransitionGroup>
  </div>
</template>

<style scoped>
.toast-enter-active,
.toast-leave-active {
  transition: all 0.2s ease;
}
.toast-enter-from,
.toast-leave-to {
  opacity: 0;
  transform: translateX(20px);
}
</style>
