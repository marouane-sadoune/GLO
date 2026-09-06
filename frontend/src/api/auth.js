import { apiClient, ensureCsrfCookie, forgetCsrfCookie } from './client'

export async function login({ email, password, remember }) {
  await ensureCsrfCookie()
  const { data } = await apiClient.post('/api/v1/login', { email, password, remember })
  return data.data
}

export async function logout() {
  await apiClient.post('/api/v1/logout')
  forgetCsrfCookie()
}

export async function fetchMe() {
  const { data } = await apiClient.get('/api/v1/me')
  return data.data
}
