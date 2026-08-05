<script setup>
import { computed } from 'vue'

const props = defineProps({
  // [{ label, value }]
  data: { type: Array, default: () => [] },
  valuePrefix: { type: String, default: '' },
})

const max = computed(() => Math.max(1, ...props.data.map((item) => item.value)))
</script>

<template>
  <div v-if="data.length" class="flex h-56 items-end gap-3">
    <div v-for="item in data" :key="item.label" class="flex h-full flex-1 flex-col items-center justify-end gap-2">
      <span class="text-xs font-medium text-slate-500">{{ valuePrefix }}{{ item.value }}</span>
      <div
        class="w-full rounded-t-md bg-brand-500 transition-all"
        :style="{ height: `${Math.max(2, (item.value / max) * 100)}%` }"
      />
      <span class="w-full truncate text-center text-xs text-slate-500" :title="item.label">
        {{ item.label }}
      </span>
    </div>
  </div>
  <p v-else class="py-12 text-center text-sm text-slate-500">No data available.</p>
</template>
