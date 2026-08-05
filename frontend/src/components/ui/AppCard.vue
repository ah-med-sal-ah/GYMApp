<script setup>
defineProps({
  title: { type: String, default: '' },
})
</script>

<template>
  <div class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
    <div v-if="title || $slots.actions" class="mb-4 flex items-center justify-between">
      <h2 v-if="title" class="text-base font-semibold text-slate-900">{{ title }}</h2>
      <div v-if="$slots.actions"><slot name="actions" /></div>
    </div>
    <slot />
  </div>
</template>
