import { apiClient } from './client'
import { createResourceApi } from './resource'

export const logementsApi = {
  ...createResourceApi('logements'),
  history: (id, params) =>
    apiClient.get(`/api/v1/logements/${id}/history`, { params }).then((r) => r.data),
}
