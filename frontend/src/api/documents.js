import { apiClient } from './client'
import { createResourceApi } from './resource'

export const documentsApi = {
  ...createResourceApi('documents'),
  upload: (formData) =>
    apiClient
      .post('/api/v1/documents', formData, { headers: { 'Content-Type': 'multipart/form-data' } })
      .then((r) => r.data.data),
  downloadUrl: (id) => `${apiClient.defaults.baseURL}/api/v1/documents/${id}/download`,
}
