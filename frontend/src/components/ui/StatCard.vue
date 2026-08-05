<script setup>
defineProps({
  label: { type: String, required: true },
  value: { type: [String, Number], required: true },
  tone: { type: String, default: 'default' }, // default | positive | negative
})
</script>

<template>
  <div class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
    <p class="text-sm font-medium text-slate-500">{{ label }}</p>
    <p
      class="mt-2 text-2xl font-semibold"
      :class="{
        'text-emerald-600': tone === 'positive',
        'text-red-600': tone === 'negative',
        'text-slate-900': tone === 'default',
      }"
    >
      {{ value }}
    </p>
  </div>
</template>
