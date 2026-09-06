import { Navigate, Outlet } from 'react-router-dom'
import { LoadingBlock } from '../components/ui/Spinner'
import { useAuth } from './AuthContext'

export function ProtectedRoute() {
  const { status } = useAuth()

  if (status === 'loading') return <LoadingBlock />
  if (status === 'guest') return <Navigate to="/login" replace />

  return <Outlet />
}

export function GuestRoute() {
  const { status } = useAuth()

  if (status === 'loading') return <LoadingBlock />
  if (status === 'authenticated') return <Navigate to="/" replace />

  return <Outlet />
}
