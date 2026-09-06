import { createContext, useCallback, useContext, useEffect, useMemo, useState } from 'react'
import { fetchMe, login as loginRequest, logout as logoutRequest } from '../api/auth'

const AuthContext = createContext(null)

export function AuthProvider({ children }) {
  const [user, setUser] = useState(null)
  const [status, setStatus] = useState('loading') // loading | authenticated | guest

  useEffect(() => {
    fetchMe()
      .then((me) => {
        setUser(me)
        setStatus('authenticated')
      })
      .catch(() => {
        setUser(null)
        setStatus('guest')
      })
  }, [])

  const login = useCallback(async (credentials) => {
    const me = await loginRequest(credentials)
    setUser(me)
    setStatus('authenticated')
    return me
  }, [])

  const logout = useCallback(async () => {
    await logoutRequest().catch(() => {})
    setUser(null)
    setStatus('guest')
  }, [])

  const can = useCallback((permission) => Boolean(user?.permissions?.includes(permission)), [user])

  const value = useMemo(
    () => ({ user, status, isAuthenticated: status === 'authenticated', login, logout, can }),
    [user, status, login, logout, can],
  )

  return <AuthContext.Provider value={value}>{children}</AuthContext.Provider>
}

export function useAuth() {
  const ctx = useContext(AuthContext)
  if (!ctx) throw new Error('useAuth must be used within AuthProvider')
  return ctx
}
