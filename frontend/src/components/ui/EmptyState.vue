<script setup>
defineProps({
  message: { type: String, default: 'Nothing here yet.' },
})
</script>

<template>
  <div class="flex flex-col items-center justify-center gap-2 py-12 text-center">
    <p class="text-sm text-slate-500">{{ message }}</p>
    <slot />
  </div>
</template>
