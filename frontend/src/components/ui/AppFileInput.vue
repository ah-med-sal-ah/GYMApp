<script setup>
import { computed } from 'vue'

const props = defineProps({
  modelValue: { type: File, default: null },
  label: { type: String, default: 'Photo' },
  error: { type: String, default: '' },
  currentUrl: { type: String, default: '' },
})

const emit = defineEmits(['update:modelValue'])

const previewUrl = computed(() => {
  if (props.modelValue) return URL.createObjectURL(props.modelValue)
  return props.currentUrl || ''
})

function onChange(event) {
  emit('update:modelValue', event.target.files?.[0] ?? null)
}
</script>

<template>
  <div>
    <span v-if="label" class="mb-1 block text-sm font-medium text-slate-700">{{ label }}</span>
    <div class="flex items-center gap-4">
      <img
        v-if="previewUrl"
        :src="previewUrl"
        alt="Preview"
        class="h-16 w-16 rounded-full object-cover ring-1 ring-slate-200"
      />
      <div
        v-else
        class="flex h-16 w-16 items-center justify-center rounded-full bg-slate-100 text-xs text-slate-400"
      >
        No photo
      </div>
      <input
        type="file"
        accept="image/*"
        class="block w-full text-sm text-slate-600 file:mr-4 file:rounded-md file:border-0 file:bg-brand-50 file:px-3 file:py-2 file:text-sm file:font-semibold file:text-brand-700 hover:file:bg-brand-100"
        @change="onChange"
      />
    </div>
    <p v-if="error" class="mt-1 text-sm text-red-600">{{ error }}</p>
  </div>
</template>
