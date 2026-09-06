import { useMutation, useQueryClient } from '@tanstack/react-query'
import { useState } from 'react'
import { occupationsApi } from '../../api/occupations'
import { Can } from '../../auth/Can'
import { Badge } from '../../components/ui/Badge'
import { Button } from '../../components/ui/Button'
import { Field, Input, Select } from '../../components/ui/Field'
import { Modal } from '../../components/ui/Modal'
import { Pagination } from '../../components/ui/Pagination'
import { Table } from '../../components/ui/Table'
import { useToast } from '../../components/ui/Toast'
import { useResourceQueries } from '../../hooks/useResource'
import { useI18n } from '../../i18n/I18nContext'

function EndModal({ occupation, onClose, onDone }) {
  const { t } = useI18n()
  const toast = useToast()
  const queryClient = useQueryClient()
  const [form, setForm] = useState({ end_reason: 'ADMINISTRATIVE', end_date: '' })

  const end = useMutation({
    mutationFn: () => occupationsApi.end(occupation.id, form),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['occupations'] })
      toast.success(t('common.end'))
      onDone()
    },
    onError: () => toast.error(t('common.error')),
  })

  return (
    <Modal
      open
      onClose={onClose}
      title={t('common.end')}
      footer={
        <>
          <Button variant="secondary" onClick={onClose}>
            {t('common.cancel')}
          </Button>
          <Button onClick={() => end.mutate()} disabled={end.isPending}>
            {t('common.save')}
          </Button>
        </>
      }
    >
      <div className="space-y-4">
        <Field label={t('common.status')}>
          <Select value={form.end_reason} onChange={(e) => setForm({ ...form, end_reason: e.target.value })}>
            <option value="TRANSFER">Mutation</option>
            <option value="RETIREMENT">Retraite</option>
            <option value="DEATH">Décès</option>
            <option value="ADMINISTRATIVE">Administratif</option>
            <option value="OTHER">Autre</option>
          </Select>
        </Field>
        <Field label={t('common.details')}>
          <Input
            type="date"
            required
            value={form.end_date}
            onChange={(e) => setForm({ ...form, end_date: e.target.value })}
          />
        </Field>
      </div>
    </Modal>
  )
}

export function OccupationsPage() {
  const { t } = useI18n()
  const [page, setPage] = useState(1)
  const [status, setStatus] = useState('')
  const [endTarget, setEndTarget] = useState(null)

  const { useList } = useResourceQueries('occupations', occupationsApi)
  const { data, isLoading } = useList({ page, status: status || undefined })

  const columns = [
    { key: 'logement', header: t('request.logement'), render: (row) => row.logement?.inventory_number },
    { key: 'occupant', header: t('request.occupant'), render: (row) => row.occupant?.full_name_fr },
    { key: 'start_date', header: t('request.submitted_at') },
    {
      key: 'status',
      header: t('common.status'),
      render: (row) => (
        <Badge tone={row.status === 'ACTIVE' ? 'green' : 'slate'}>{t(`occupationStatus.${row.status}`)}</Badge>
      ),
    },
    {
      key: 'actions',
      header: t('common.actions'),
      render: (row) =>
        row.status === 'ACTIVE' ? (
          <Can permission="occupations.manage">
            <Button variant="secondary" onClick={() => setEndTarget(row)}>
              {t('common.end')}
            </Button>
          </Can>
        ) : null,
    },
  ]

  return (
    <div className="space-y-4">
      <h1 className="text-xl font-semibold text-slate-900">{t('nav.occupations')}</h1>

      <Select value={status} onChange={(e) => setStatus(e.target.value)} className="max-w-40">
        <option value="">{t('common.all')}</option>
        <option value="ACTIVE">{t('occupationStatus.ACTIVE')}</option>
        <option value="ENDED">{t('occupationStatus.ENDED')}</option>
      </Select>

      <Table columns={columns} rows={data?.data} isLoading={isLoading} />
      <Pagination meta={data?.meta} onPageChange={setPage} />

      {endTarget ? (
        <EndModal occupation={endTarget} onClose={() => setEndTarget(null)} onDone={() => setEndTarget(null)} />
      ) : null}
    </div>
  )
}
