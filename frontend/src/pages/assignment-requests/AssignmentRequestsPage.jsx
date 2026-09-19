import { useMutation, useQueryClient } from '@tanstack/react-query'
import { useState } from 'react'
import { assignmentRequestsApi } from '../../api/assignmentRequests'
import { logementsApi } from '../../api/logements'
import { occupantsApi } from '../../api/occupants'
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

const STATUS_TONE = { PENDING: 'amber', ACCEPTED: 'green', REJECTED: 'red' }

function AcceptModal({ request, onClose, onDone }) {
  const { t } = useI18n()
  const toast = useToast()
  const queryClient = useQueryClient()
  const [form, setForm] = useState({ assignment_type: 'MANDATORY', assignment_date: '', start_date: '' })

  const accept = useMutation({
    mutationFn: () => assignmentRequestsApi.accept(request.id, form),
    onSuccess: (result) => {
      queryClient.invalidateQueries({ queryKey: ['assignment-requests'] })
      toast.success(t('common.accept'))
      if (result.rival_requests?.data?.length) {
        toast.info(`${result.rival_requests.data.length} demande(s) concurrente(s) restent en attente.`)
      }
      onDone()
    },
    onError: () => toast.error(t('common.error')),
  })

  return (
    <Modal
      open
      onClose={onClose}
      title={t('common.accept')}
      footer={
        <>
          <Button variant="secondary" onClick={onClose}>
            {t('common.cancel')}
          </Button>
          <Button onClick={() => accept.mutate()} disabled={accept.isPending}>
            {t('common.save')}
          </Button>
        </>
      }
    >
      <div className="space-y-4">
        <Field label={t('request.status')}>
          <Select
            value={form.assignment_type}
            onChange={(e) => setForm({ ...form, assignment_type: e.target.value })}
          >
            <option value="MANDATORY">{t('assignmentType.MANDATORY')}</option>
            <option value="FREE">{t('assignmentType.FREE')}</option>
            <option value="BY_LAW">{t('assignmentType.BY_LAW')}</option>
            <option value="ACTUAL">{t('assignmentType.ACTUAL')}</option>
          </Select>
        </Field>
        <Field label={t('request.decision_date')}>
          <Input
            type="date"
            required
            value={form.assignment_date}
            onChange={(e) => setForm({ ...form, assignment_date: e.target.value })}
          />
        </Field>
        <Field label={t('common.details')}>
          <Input
            type="date"
            required
            value={form.start_date}
            onChange={(e) => setForm({ ...form, start_date: e.target.value })}
          />
        </Field>
      </div>
    </Modal>
  )
}

function CreateRequestModal({ logements, occupants, onClose, onDone }) {
  const { t } = useI18n()
  const toast = useToast()
  const [form, setForm] = useState({ logement_id: '', occupant_id: '', notes: '' })
  const create = useMutation({
    mutationFn: () => assignmentRequestsApi.create(form),
    onSuccess: () => {
      toast.success(t('common.send'))
      onDone()
    },
    onError: () => toast.error(t('common.error')),
  })

  return (
    <Modal
      open
      onClose={onClose}
      title={t('request.new')}
      footer={
        <>
          <Button variant="secondary" onClick={onClose}>
            {t('common.cancel')}
          </Button>
          <Button onClick={() => create.mutate()} disabled={create.isPending || !form.logement_id || !form.occupant_id}>
            {t('common.send')}
          </Button>
        </>
      }
    >
      <div className="space-y-4">
        <Field label={t('request.occupant')}>
          <Select
            required
            value={form.occupant_id}
            onChange={(e) => setForm({ ...form, occupant_id: e.target.value })}
          >
            <option value="">{t('common.select')}</option>
            {occupants?.data?.map((occupant) => (
              <option key={occupant.id} value={occupant.id}>
                {occupant.full_name_fr} ({occupant.employee_number})
              </option>
            ))}
          </Select>
        </Field>
        <Field label={t('request.logement')}>
          <Select
            required
            value={form.logement_id}
            onChange={(e) => setForm({ ...form, logement_id: e.target.value })}
          >
            <option value="">{t('common.select')}</option>
            {logements?.data?.map((logement) => (
              <option key={logement.id} value={logement.id}>
                {logement.inventory_number} - {logement.location_fr}
              </option>
            ))}
          </Select>
        </Field>
        <Field label={t('common.notes')}>
          <Input value={form.notes} onChange={(e) => setForm({ ...form, notes: e.target.value })} />
        </Field>
      </div>
    </Modal>
  )
}

export function AssignmentRequestsPage() {
  const { t } = useI18n()
  const toast = useToast()
  const [page, setPage] = useState(1)
  const [status, setStatus] = useState('')
  const [acceptTarget, setAcceptTarget] = useState(null)
  const [createOpen, setCreateOpen] = useState(false)

  const { useList } = useResourceQueries('assignment-requests', assignmentRequestsApi)
  const { data, isLoading } = useList({ page, status: status || undefined })
  const { useList: useLogements } = useResourceQueries('logements', logementsApi)
  const { useList: useOccupants } = useResourceQueries('occupants', occupantsApi)
  const { data: logements } = useLogements({ per_page: 100, housing_status: 'VACANT' })
  const { data: occupants } = useOccupants({ per_page: 100 })

  const queryClient = useQueryClient()
  const invalidate = () => queryClient.invalidateQueries({ queryKey: ['assignment-requests'] })

  const reject = useMutation({
    mutationFn: (id) => assignmentRequestsApi.reject(id, {}),
    onSuccess: () => {
      invalidate()
      toast.success(t('common.reject'))
    },
    onError: () => toast.error(t('common.error')),
  })

  const reset = useMutation({
    mutationFn: (id) => assignmentRequestsApi.reset(id),
    onSuccess: () => {
      invalidate()
      toast.success(t('common.reset'))
    },
    onError: () => toast.error(t('common.error')),
  })

  const downloadPdf = useMutation({
    mutationFn: (id) => assignmentRequestsApi.pdf(id),
    onSuccess: (blob) => {
      const url = URL.createObjectURL(blob)
      window.open(url, '_blank', 'noopener,noreferrer')
      window.setTimeout(() => URL.revokeObjectURL(url), 60_000)
    },
    onError: () => toast.error(t('common.error')),
  })

  const columns = [
    { key: 'logement', header: t('request.logement'), render: (row) => row.logement?.inventory_number },
    { key: 'occupant', header: t('request.occupant'), render: (row) => row.occupant?.full_name_fr },
    { key: 'submitted_at', header: t('request.submitted_at') },
    {
      key: 'status',
      header: t('request.status'),
      render: (row) => <Badge tone={STATUS_TONE[row.status]}>{t(`requestStatus.${row.status}`)}</Badge>,
    },
    {
      key: 'actions',
      header: t('common.actions'),
      render: (row) => (
        <div className="flex gap-2">
          <Can permission="requests.decide">
            {row.status === 'PENDING' ? (
              <>
                <Button variant="secondary" onClick={() => setAcceptTarget(row)}>
                  {t('common.accept')}
                </Button>
                <Button variant="danger" onClick={() => reject.mutate(row.id)}>
                  {t('common.reject')}
                </Button>
              </>
            ) : null}
            {row.status === 'REJECTED' ? (
              <Button variant="secondary" onClick={() => reset.mutate(row.id)}>
                {t('common.reset')}
              </Button>
            ) : null}
          </Can>
          {row.status === 'ACCEPTED' ? (
            <Button variant="secondary" onClick={() => downloadPdf.mutate(row.id)} disabled={downloadPdf.isPending}>
              {t('common.downloadLetter')}
            </Button>
          ) : null}
        </div>
      ),
    },
  ]

  return (
    <div className="space-y-4">
      <div className="flex flex-wrap items-center justify-between gap-3">
        <h1 className="text-xl font-semibold text-slate-900">{t('nav.requests')}</h1>
        <Can permission="requests.create">
          <Button onClick={() => setCreateOpen(true)}>{t('request.new')}</Button>
        </Can>
      </div>

      <Select value={status} onChange={(e) => setStatus(e.target.value)} className="max-w-40">
        <option value="">{t('common.all')}</option>
        <option value="PENDING">{t('requestStatus.PENDING')}</option>
        <option value="ACCEPTED">{t('requestStatus.ACCEPTED')}</option>
        <option value="REJECTED">{t('requestStatus.REJECTED')}</option>
      </Select>

      <Table columns={columns} rows={data?.data} isLoading={isLoading} />
      <Pagination meta={data?.meta} onPageChange={setPage} />

      {acceptTarget ? (
        <AcceptModal
          request={acceptTarget}
          onClose={() => setAcceptTarget(null)}
          onDone={() => setAcceptTarget(null)}
        />
      ) : null}
      {createOpen ? (
        <CreateRequestModal
          logements={logements}
          occupants={occupants}
          onClose={() => setCreateOpen(false)}
          onDone={() => setCreateOpen(false)}
        />
      ) : null}
    </div>
  )
}
