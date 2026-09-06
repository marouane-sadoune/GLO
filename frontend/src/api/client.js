import axios from 'axios'

export const API_BASE_URL = import.meta.env.VITE_API_URL ?? 'http://localhost:8000'

export const apiClient = axios.create({
  baseURL: API_BASE_URL,
  withCredentials: true,
  withXSRFToken: true,
})

let csrfPrimed = false

export async function ensureCsrfCookie() {
  if (csrfPrimed) return
  await apiClient.get('/sanctum/csrf-cookie')
  csrfPrimed = true
}

export function forgetCsrfCookie() {
  csrfPrimed = false
}
