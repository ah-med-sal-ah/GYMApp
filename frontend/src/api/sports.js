import { gymHttp } from '@/lib/http'
import { buildFormData } from '@/lib/formData'

export const PRICE_TIERS = [
  { key: 'day', label: 'Day' },
  { key: 'week', label: 'Week' },
  { key: 'month', label: 'Month' },
  { key: 'year', label: 'Year' },
]

const PRICE_FIELDS = PRICE_TIERS.map((tier) => `${tier.key}_price`)

export const sportsApi = {
  list(params) {
    return gymHttp.get('/sports', { params }).then((res) => res.data)
  },
  all() {
    return gymHttp.get('/sports', { params: { per_page: 100 } }).then((res) => res.data)
  },
  get(id) {
    return gymHttp.get(`/sports/${id}`).then((res) => res.data)
  },
  create(payload) {
    return gymHttp.post('/sports', buildFormData(payload, PRICE_FIELDS)).then((res) => res.data)
  },
  update(id, payload) {
    const formData = buildFormData(payload, PRICE_FIELDS)
    formData.append('_method', 'PUT')
    return gymHttp.post(`/sports/${id}`, formData).then((res) => res.data)
  },
  remove(id) {
    return gymHttp.delete(`/sports/${id}`).then((res) => res.data)
  },
}
