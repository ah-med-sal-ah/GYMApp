import { gymHttp } from '@/lib/http'

export const EXPENSE_CATEGORIES = ['Equipment', 'Maintenance', 'Salary', 'Rent', 'Utilities', 'Other']

export const expensesApi = {
  list(params) {
    return gymHttp.get('/expenses', { params }).then((res) => res.data)
  },
  get(id) {
    return gymHttp.get(`/expenses/${id}`).then((res) => res.data)
  },
  create(payload) {
    return gymHttp.post('/expenses', payload).then((res) => res.data)
  },
  update(id, payload) {
    return gymHttp.put(`/expenses/${id}`, payload).then((res) => res.data)
  },
  remove(id) {
    return gymHttp.delete(`/expenses/${id}`).then((res) => res.data)
  },
}
