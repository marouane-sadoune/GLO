import { useAuth } from './AuthContext'

/**
 * Hides UI only — a convenience, never the security boundary. The API
 * rejects independently of what this component chooses to render.
 */
export function Can({ permission, children, fallback = null }) {
  const { can } = useAuth()
  return can(permission) ? children : fallback
}
