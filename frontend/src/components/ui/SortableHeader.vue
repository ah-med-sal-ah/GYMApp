<script setup>
const props = defineProps({
  field: { type: String, required: true },
  label: { type: String, required: true },
  sortBy: { type: String, default: '' },
  sortOrder: { type: String, default: 'desc' },
})

const emit = defineEmits(['sort'])

function toggle() {
  const nextOrder = props.sortBy === props.field && props.sortOrder === 'asc' ? 'desc' : 'asc'
  emit('sort', { sortBy: props.field, sortOrder: nextOrder })
}
</script>

<template>
  <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
    <button type="button" class="inline-flex items-center gap-1 hover:text-slate-800" @click="toggle">
      {{ label }}
      <span v-if="sortBy === field" class="text-brand-600">
        {{ sortOrder === 'asc' ? '↑' : '↓' }}
      </span>
    </button>
  </th>
</template>

