<script setup>
defineProps({
  modelValue: { type: [String, Number], default: '' },
  label: { type: String, default: '' },
  error: { type: String, default: '' },
  required: { type: Boolean, default: false },
  placeholder: { type: String, default: 'Select…' },
  options: {
    type: Array,
    default: () => [],
    // [{ value, label }]
  },
})

defineEmits(['update:modelValue'])
</script>

<template>
  <label class="block">
    <span v-if="label" class="mb-1 block text-sm font-medium text-slate-700">
      {{ label }}
      <span v-if="required" class="text-red-500">*</span>
    </span>
    <select
      :value="modelValue"
      class="block w-full rounded-md border-0 px-3 py-2 text-sm text-slate-900 shadow-sm ring-1 ring-inset focus:ring-2 focus:ring-inset focus:ring-brand-600"
      :class="error ? 'ring-red-400 focus:ring-red-500' : 'ring-slate-300'"
      @change="$emit('update:modelValue', $event.target.value)"
    >
      <option value="" disabled>{{ placeholder }}</option>
      <option v-for="option in options" :key="option.value" :value="option.value">
        {{ option.label }}
      </option>
    </select>
    <p v-if="error" class="mt-1 text-sm text-red-600">{{ error }}</p>
  </label>
</template>
