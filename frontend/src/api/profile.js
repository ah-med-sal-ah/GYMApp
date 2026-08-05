import { gymHttp } from '@/lib/http'

export const profileApi = {
  get() {
    return gymHttp.get('/profile').then((res) => res.data)
  },
  update(payload) {
    return gymHttp.put('/profile', payload).then((res) => res.data)
  },
}
