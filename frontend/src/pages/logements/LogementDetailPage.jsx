import { useQuery } from '@tanstack/react-query'
import { useState } from 'react'
import { useNavigate, useParams } from 'react-router-dom'
import { logementsApi } from '../../api/logements'
import { Can } from '../../auth/Can'
import { Badge } from '../../components/ui/Badge'
import { Button } from '../../components/ui/Button'
import { ConfirmDialog } from '../../components/ui/ConfirmDialog'
import { LoadingBlock } from '../../components/ui/Spinner'
import { useToast } from '../../components/ui/Toast'
import { useResourceQueries } from '../../hooks/useResource'
import { useI18n } from '../../i18n/I18nContext'

const TABS = ['info', 'history']

function InfoRow({ label, value }) {
  return (
    <div className="flex justify-between border-b border-slate-100 py-2 text-sm">
      <span className="text-slate-500">{label}</span>
      <span className="font-medium text-slate-800">{value ?? '—'}</span>
    </div>
  )
}

function HistoryTab({ logementId }) {
  const { t } = useI18n()
  const { data, isLoading } = useQueryHistory(logementId)

  if (isLoading) return <LoadingBlock label={t('common.loading')} />
  if (!data?.data?.length) return <p className="py-8 text-center text-slate-500">{t('common.noResults')}</p>

  return (
    <ul className="space-y-3">
      {data.data.map((entry) => (
        <li key={entry.id} className="rounded-lg bg-slate-50 p-3 text-sm">
          <div className="flex items-center justify-between">
            <span className="font-medium text-slate-800">{entry.description}</span>
            <span className="text-xs text-slate-400">{new Date(entry.created_at).toLocaleString()}</span>
          </div>
          {entry.user ? <p className="mt-1 text-xs text-slate-500">{entry.user.name}</p> : null}
        </li>
      ))}
    </ul>
  )
}

function useQueryHistory(logementId) {
  return useQuery({
    queryKey: ['logements', logementId, 'history'],
    queryFn: () => logementsApi.history(logementId),
    enabled: Boolean(logementId),
  })
}

export function LogementDetailPage() {
  const { id } = useParams()
  const { t } = useI18n()
  const navigate = useNavigate()
  const toast = useToast()
  const [tab, setTab] = useState('info')
  const [confirmOpen, setConfirmOpen] = useState(false)

  const { useOne, useRemove } = useResourceQueries('logements', logementsApi)
  const { data: logement, isLoading } = useOne(id)
  const remove = useRemove()

  async function handleDelete() {
    try {
      await remove.mutateAsync(id)
      toast.success(t('common.delete'))
      navigate('/logements')
    } catch {
      toast.error(t('common.error'))
    } finally {
      setConfirmOpen(false)
    }
  }

  if (isLoading || !logement) return <LoadingBlock label={t('common.loading')} />

  return (
    <div className="space-y-4">
      <div className="flex items-center justify-between">
        <div>
          <h1 className="text-xl font-semibold text-slate-900">{logement.inventory_number}</h1>
          <p className="text-sm text-slate-500">{logement.location_fr}</p>
        </div>
        <div className="flex items-center gap-2">
          <Badge tone={logement.housing_status === 'VACANT' ? 'green' : 'amber'}>
            {t(`housingStatus.${logement.housing_status}`)}
          </Badge>
          <Can permission="logements.delete">
            <Button variant="danger" onClick={() => setConfirmOpen(true)}>
              {t('common.delete')}
            </Button>
          </Can>
        </div>
      </div>

      <div className="border-b border-slate-200">
        <nav className="flex gap-4">
          {TABS.map((key) => (
            <button
              key={key}
              type="button"
              onClick={() => setTab(key)}
              className={
                'border-b-2 px-1 py-2 text-sm font-medium ' +
                (tab === key ? 'border-indigo-600 text-indigo-600' : 'border-transparent text-slate-500')
              }
            >
              {t(key === 'info' ? 'common.details' : 'common.history')}
            </button>
          ))}
        </nav>
      </div>

      {tab === 'info' ? (
        <div className="max-w-xl rounded-lg bg-white p-4 shadow-sm ring-1 ring-slate-200">
          <InfoRow label={t('logement.establishment')} value={logement.establishment?.name_fr} />
          <InfoRow label={t('logement.housing_category')} value={t(`housingCategory.${logement.housing_category}`)} />
          <InfoRow label={t('logement.notes')} value={logement.notes} />
        </div>
      ) : (
        <HistoryTab logementId={id} />
      )}

      <ConfirmDialog
        open={confirmOpen}
        onConfirm={handleDelete}
        onCancel={() => setConfirmOpen(false)}
        busy={remove.isPending}
      />
    </div>
  )
}
