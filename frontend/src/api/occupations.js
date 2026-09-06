import { apiClient } from './client'
import { createResourceApi } from './resource'

export const occupationsApi = {
  ...createResourceApi('occupations'),
  end: (id, payload) => apiClient.post(`/api/v1/occupations/${id}/end`, payload).then((r) => r.data.data),
}
