import { adminHttp } from '@/lib/http'

export const adminApi = {
  users(params) {
    return adminHttp.get('/admin/users', { params }).then((res) => res.data)
  },
  user(id) {
    return adminHttp.get(`/admin/users/${id}`).then((res) => res.data)
  },
}
