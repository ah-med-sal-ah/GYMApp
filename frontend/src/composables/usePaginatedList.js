import { reactive, ref } from 'vue'

/**
 * Shared list-fetching logic (search, sort, pagination) for resource index pages.
 *
 * @param {(params: object) => Promise<{data: object}>} fetcher
 * @param {string} itemsKey - key inside response.data holding the array of rows
 * @param {object} [options]
 * @param {string} [options.sortBy]
 * @param {string} [options.sortOrder]
 * @param {object} [options.extraFilters]
 */
export function usePaginatedList(fetcher, itemsKey, options = {}) {
  const items = ref([])
  const pagination = ref({ current_page: 1, per_page: 15, total: 0, last_page: 1 })
  const loading = ref(false)
  const loadError = ref('')

  const filters = reactive({
    search: '',
    sort_by: options.sortBy || '',
    sort_order: options.sortOrder || 'desc',
    page: 1,
    per_page: 15,
    ...(options.extraFilters || {}),
  })

  let debounceTimer = null

  async function load() {
    loading.value = true
    loadError.value = ''
    try {
      const response = await fetcher({ ...filters })
      items.value = response.data[itemsKey]
      pagination.value = response.data.pagination
    } catch (error) {
      loadError.value = error?.response?.data?.message || 'Failed to load data.'
    } finally {
      loading.value = false
    }
  }

  function sort({ sortBy, sortOrder }) {
    filters.sort_by = sortBy
    filters.sort_order = sortOrder
    filters.page = 1
    load()
  }

  function changePage(page) {
    filters.page = page
    load()
  }

  function setFilter(key, value) {
    filters[key] = value
    filters.page = 1
    load()
  }

  function search(value) {
    filters.search = value
    filters.page = 1
    clearTimeout(debounceTimer)
    debounceTimer = setTimeout(load, 350)
  }

  return { items, pagination, loading, loadError, filters, load, sort, changePage, search, setFilter }
}
