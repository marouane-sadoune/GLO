import { apiClient } from './client'
import { createResourceApi } from './resource'

export const assignmentRequestsApi = {
  ...createResourceApi('assignment-requests'),
  accept: (id, payload) =>
    apiClient.post(`/api/v1/assignment-requests/${id}/accept`, payload).then((r) => r.data),
  reject: (id, payload) =>
    apiClient.post(`/api/v1/assignment-requests/${id}/reject`, payload).then((r) => r.data.data),
  reset: (id) =>
    apiClient.post(`/api/v1/assignment-requests/${id}/reset`).then((r) => r.data.data),
}
