<script setup>
defineProps({
  open: { type: Boolean, default: false },
  title: { type: String, default: '' },
})

defineEmits(['close'])
</script>

<template>
  <Teleport to="body">
    <Transition name="fade">
      <div v-if="open" class="fixed inset-0 z-40 flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-slate-900/50" @click="$emit('close')" />
        <div class="relative w-full max-w-md rounded-xl bg-white p-6 shadow-xl">
          <h3 class="text-base font-semibold text-slate-900">{{ title }}</h3>
          <div class="mt-3 text-sm text-slate-600">
            <slot />
          </div>
          <div class="mt-6 flex justify-end gap-3">
            <slot name="actions" />
          </div>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<style scoped>
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.15s ease;
}
.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>
