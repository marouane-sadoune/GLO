import { apiClient } from './client'

/**
 * Thin CRUD wrapper shared by every resource: departments, establishments,
 * logements, occupants, assignment-requests, occupations, vacations, documents
 * all expose the same index/show/store/update/destroy shape.
 */
export function createResourceApi(basePath) {
  const url = (suffix = '') => `/api/v1/${basePath}${suffix}`

  return {
    list: (params) => apiClient.get(url(), { params }).then((r) => r.data),
    get: (id) => apiClient.get(url(`/${id}`)).then((r) => r.data.data),
    create: (payload) => apiClient.post(url(), payload).then((r) => r.data.data),
    update: (id, payload) => apiClient.put(url(`/${id}`), payload).then((r) => r.data.data),
    remove: (id) => apiClient.delete(url(`/${id}`)),
  }
}
