<script setup>
import { computed } from 'vue'

const props = defineProps({
  // [{ label, value, percentage }]
  data: { type: Array, default: () => [] },
})

const COLORS = ['#3478f6', '#22c55e', '#f59e0b', '#ef4444', '#8b5cf6', '#06b6d4']

const segments = computed(() =>
  props.data.map((item, index) => ({ ...item, color: COLORS[index % COLORS.length] })),
)

const hasData = computed(() => props.data.some((item) => item.value > 0))

const gradient = computed(() => {
  if (!hasData.value) return '#e2e8f0'

  let cursor = 0
  const stops = segments.value.map((segment) => {
    const start = cursor
    cursor += segment.percentage
    return `${segment.color} ${start}% ${cursor}%`
  })
  return `conic-gradient(${stops.join(', ')})`
})
</script>

<template>
  <div class="flex flex-col items-center gap-6 sm:flex-row sm:items-center">
    <div
      class="h-40 w-40 shrink-0 rounded-full"
      :style="{ background: gradient }"
    >
      <div class="flex h-full w-full items-center justify-center">
        <div class="flex h-24 w-24 items-center justify-center rounded-full bg-white text-sm font-medium text-slate-500">
          {{ hasData ? '' : 'No data' }}
        </div>
      </div>
    </div>
    <ul class="w-full space-y-2">
      <li
        v-for="segment in segments"
        :key="segment.label"
        class="flex items-center justify-between text-sm"
      >
        <span class="flex items-center gap-2 text-slate-600">
          <span class="h-2.5 w-2.5 rounded-full" :style="{ backgroundColor: segment.color }" />
          {{ segment.label }}
        </span>
        <span class="font-medium text-slate-900">
          {{ segment.value }} <span class="text-slate-400">({{ segment.percentage }}%)</span>
        </span>
      </li>
    </ul>
  </div>
</template>
