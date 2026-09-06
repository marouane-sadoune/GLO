import { QueryClientProvider } from '@tanstack/react-query'
import { BrowserRouter, Route, Routes } from 'react-router-dom'
import { AuthProvider } from './auth/AuthContext'
import { GuestRoute, ProtectedRoute } from './auth/ProtectedRoute'
import { ToastProvider } from './components/ui/Toast'
import { I18nProvider } from './i18n/I18nContext'
import { AppLayout } from './layouts/AppLayout'
import { AuthLayout } from './layouts/AuthLayout'
import { queryClient } from './lib/queryClient'
import { AssignmentRequestsPage } from './pages/assignment-requests/AssignmentRequestsPage'
import { DashboardPage } from './pages/DashboardPage'
import { DepartmentsPage } from './pages/departments/DepartmentsPage'
import { EstablishmentsPage } from './pages/establishments/EstablishmentsPage'
import { LoginPage } from './pages/LoginPage'
import { LogementDetailPage } from './pages/logements/LogementDetailPage'
import { LogementsPage } from './pages/logements/LogementsPage'
import { NotFoundPage } from './pages/NotFoundPage'
import { OccupantsPage } from './pages/occupants/OccupantsPage'
import { OccupationsPage } from './pages/occupations/OccupationsPage'

export default function App() {
  return (
    <QueryClientProvider client={queryClient}>
      <I18nProvider>
        <ToastProvider>
          <BrowserRouter>
            <AuthProvider>
              <Routes>
                <Route element={<GuestRoute />}>
                  <Route element={<AuthLayout />}>
                    <Route path="/login" element={<LoginPage />} />
                  </Route>
                </Route>

                <Route element={<ProtectedRoute />}>
                  <Route element={<AppLayout />}>
                    <Route path="/" element={<DashboardPage />} />
                    <Route path="/departments" element={<DepartmentsPage />} />
                    <Route path="/establishments" element={<EstablishmentsPage />} />
                    <Route path="/logements" element={<LogementsPage />} />
                    <Route path="/logements/:id" element={<LogementDetailPage />} />
                    <Route path="/occupants" element={<OccupantsPage />} />
                    <Route path="/assignment-requests" element={<AssignmentRequestsPage />} />
                    <Route path="/occupations" element={<OccupationsPage />} />
                  </Route>
                </Route>

                <Route path="*" element={<NotFoundPage />} />
              </Routes>
            </AuthProvider>
          </BrowserRouter>
        </ToastProvider>
      </I18nProvider>
    </QueryClientProvider>
  )
}
