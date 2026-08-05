import { gymHttp } from '@/lib/http'

export const dashboardApi = {
  summary() {
    return gymHttp.get('/dashboard').then((res) => res.data)
  },
  registrationsChart() {
    return gymHttp.get('/dashboard/charts/registrations').then((res) => res.data)
  },
  incomeBySportChart() {
    return gymHttp.get('/dashboard/charts/income-by-sport').then((res) => res.data)
  },
}
