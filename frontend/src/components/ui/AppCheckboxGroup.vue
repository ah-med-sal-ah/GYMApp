<script setup>
const props = defineProps({
  modelValue: { type: Array, default: () => [] },
  label: { type: String, default: '' },
  error: { type: String, default: '' },
  required: { type: Boolean, default: false },
  options: {
    type: Array,
    default: () => [],
    // [{ value, label }]
  },
})

const emit = defineEmits(['update:modelValue'])

function toggle(value) {
  const set = new Set(props.modelValue)
  if (set.has(value)) {
    set.delete(value)
  } else {
    set.add(value)
  }
  emit('update:modelValue', Array.from(set))
}
</script>

<template>
  <div>
    <span v-if="label" class="mb-1 block text-sm font-medium text-slate-700">
      {{ label }}
      <span v-if="required" class="text-red-500">*</span>
    </span>
    <div
      class="flex flex-wrap gap-2 rounded-md border p-3"
      :class="error ? 'border-red-400' : 'border-slate-200'"
    >
      <p v-if="!options.length" class="text-sm text-slate-400">No options available.</p>
      <label
        v-for="option in options"
        :key="option.value"
        class="flex cursor-pointer items-center gap-2 rounded-full border px-3 py-1.5 text-sm transition"
        :class="
          modelValue.includes(option.value)
            ? 'border-brand-600 bg-brand-50 text-brand-700'
            : 'border-slate-200 text-slate-600 hover:bg-slate-50'
        "
      >
        <input
          type="checkbox"
          class="hidden"
          :checked="modelValue.includes(option.value)"
          @change="toggle(option.value)"
        />
        {{ option.label }}
      </label>
    </div>
    <p v-if="error" class="mt-1 text-sm text-red-600">{{ error }}</p>
  </div>
</template>
