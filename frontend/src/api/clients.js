import { gymHttp } from '@/lib/http'
import { buildFormData } from '@/lib/formData'

export const clientsApi = {
  list(params) {
    return gymHttp.get('/clients', { params }).then((res) => res.data)
  },
  get(id) {
    return gymHttp.get(`/clients/${id}`).then((res) => res.data)
  },
  create(payload) {
    return gymHttp.post('/clients', buildFormData(payload)).then((res) => res.data)
  },
  update(id, payload) {
    const formData = buildFormData(payload)
    formData.append('_method', 'PUT')
    return gymHttp.post(`/clients/${id}`, formData).then((res) => res.data)
  },
  remove(id) {
    return gymHttp.delete(`/clients/${id}`).then((res) => res.data)
  },
}
