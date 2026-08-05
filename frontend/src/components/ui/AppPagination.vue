<script setup>
const props = defineProps({
  pagination: {
    type: Object,
    default: () => ({ current_page: 1, per_page: 15, total: 0, last_page: 1 }),
  },
})

const emit = defineEmits(['change'])

function go(page) {
  if (page < 1 || page > props.pagination.last_page || page === props.pagination.current_page) return
  emit('change', page)
}
</script>

<template>
  <div
    v-if="pagination.total > 0"
    class="flex items-center justify-between border-t border-slate-200 px-1 py-3"
  >
    <p class="text-sm text-slate-500">
      Showing page {{ pagination.current_page }} of {{ pagination.last_page }}
      <span class="text-slate-400">({{ pagination.total }} total)</span>
    </p>
    <div class="flex gap-2">
      <button
        type="button"
        class="rounded-md px-3 py-1.5 text-sm font-medium text-slate-600 ring-1 ring-inset ring-slate-300 hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-50"
        :disabled="pagination.current_page <= 1"
        @click="go(pagination.current_page - 1)"
      >
        Previous
      </button>
      <button
        type="button"
        class="rounded-md px-3 py-1.5 text-sm font-medium text-slate-600 ring-1 ring-inset ring-slate-300 hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-50"
        :disabled="pagination.current_page >= pagination.last_page"
        @click="go(pagination.current_page + 1)"
      >
        Next
      </button>
    </div>
  </div>
</template>
