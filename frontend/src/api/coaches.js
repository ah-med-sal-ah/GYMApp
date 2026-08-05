import { gymHttp } from '@/lib/http'
import { buildFormData } from '@/lib/formData'

export const coachesApi = {
  list(params) {
    return gymHttp.get('/coaches', { params }).then((res) => res.data)
  },
  bySport(sportId) {
    return gymHttp.get('/coaches/by-sport', { params: { sport_id: sportId } }).then((res) => res.data)
  },
  get(id) {
    return gymHttp.get(`/coaches/${id}`).then((res) => res.data)
  },
  create(payload) {
    return gymHttp.post('/coaches', buildFormData(payload)).then((res) => res.data)
  },
  update(id, payload) {
    const formData = buildFormData(payload)
    formData.append('_method', 'PUT')
    return gymHttp.post(`/coaches/${id}`, formData).then((res) => res.data)
  },
  remove(id) {
    return gymHttp.delete(`/coaches/${id}`).then((res) => res.data)
  },
}
