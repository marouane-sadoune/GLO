import { useQuery } from '@tanstack/react-query'
import { assignmentRequestsApi } from '../api/assignmentRequests'
import { logementsApi } from '../api/logements'
import { occupantsApi } from '../api/occupants'
import { useAuth } from '../auth/AuthContext'
import { Spinner } from '../components/ui/Spinner'
import { useI18n } from '../i18n/I18nContext'

function StatCard({ label, query }) {
  const { data, isLoading } = query
  return (
    <div className="rounded-xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
      <p className="text-sm text-slate-500">{label}</p>
      <p className="mt-2 text-3xl font-semibold text-slate-900">
        {isLoading ? <Spinner className="h-6 w-6 text-slate-300" /> : (data?.meta?.total ?? '—')}
      </p>
    </div>
  )
}

export function DashboardPage() {
  const { t } = useI18n()
  const { user } = useAuth()

  const logements = useQuery({
    queryKey: ['dashboard', 'logements'],
    queryFn: () => logementsApi.list({ per_page: 1 }),
  })
  const vacant = useQuery({
    queryKey: ['dashboard', 'logements', 'vacant'],
    queryFn: () => logementsApi.list({ per_page: 1, housing_status: 'VACANT' }),
  })
  const occupants = useQuery({
    queryKey: ['dashboard', 'occupants'],
    queryFn: () => occupantsApi.list({ per_page: 1 }),
  })
  const pendingRequests = useQuery({
    queryKey: ['dashboard', 'requests', 'pending'],
    queryFn: () => assignmentRequestsApi.list({ per_page: 1, status: 'PENDING' }),
  })

  return (
    <div className="space-y-6">
      <div>
        <h1 className="text-xl font-semibold text-slate-900">{t('dashboard.welcome', { name: user?.name })}</h1>
        <p className="text-sm text-slate-500">{t('dashboard.role', { role: t(`role.${user?.role}`) })}</p>
      </div>

      <div className="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <StatCard label={t('nav.logements')} query={logements} />
        <StatCard label={t('housingStatus.VACANT')} query={vacant} />
        <StatCard label={t('nav.occupants')} query={occupants} />
        <StatCard label={t('requestStatus.PENDING')} query={pendingRequests} />
      </div>
    </div>
  )
}
