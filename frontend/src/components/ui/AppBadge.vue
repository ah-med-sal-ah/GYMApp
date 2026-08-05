<script setup>
defineProps({
  tone: { type: String, default: 'slate' }, // slate | green | red | blue | amber
})

const tones = {
  slate: 'bg-slate-100 text-slate-700',
  green: 'bg-emerald-100 text-emerald-700',
  red: 'bg-red-100 text-red-700',
  blue: 'bg-blue-100 text-blue-700',
  amber: 'bg-amber-100 text-amber-700',
}
</script>

<template>
  <span
    class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium"
    :class="tones[tone]"
  >
    <slot />
  </span>
</template>
