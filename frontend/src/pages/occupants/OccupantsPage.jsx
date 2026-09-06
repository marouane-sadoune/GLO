import { useState } from 'react'
import { establishmentsApi } from '../../api/establishments'
import { occupantsApi } from '../../api/occupants'
import { useAuth } from '../../auth/AuthContext'
import { Can } from '../../auth/Can'
import { Button } from '../../components/ui/Button'
import { Field, Input, Select } from '../../components/ui/Field'
import { Modal } from '../../components/ui/Modal'
import { Pagination } from '../../components/ui/Pagination'
import { Table } from '../../components/ui/Table'
import { useToast } from '../../components/ui/Toast'
import { useResourceQueries } from '../../hooks/useResource'
import { useI18n } from '../../i18n/I18nContext'

const EMPTY_FORM = { establishment_id: '', first_name_fr: '', last_name_fr: '', employee_number: '' }

export function OccupantsPage() {
  const { t } = useI18n()
  const { user } = useAuth()
  const toast = useToast()
  const [page, setPage] = useState(1)
  const [q, setQ] = useState('')
  const [open, setOpen] = useState(false)
  const [form, setForm] = useState(EMPTY_FORM)

  const { useList, useCreate } = useResourceQueries('occupants', occupantsApi)
  const { data, isLoading } = useList({ page, q: q || undefined })
  const create = useCreate()

  const isSuperAdmin = user?.role === 'SUPER_ADMIN'
  const { useList: useEstablishmentList } = useResourceQueries('establishments', establishmentsApi)
  const { data: establishments } = useEstablishmentList({ per_page: 100 })

  async function handleCreate(e) {
    e.preventDefault()
    try {
      const payload = isSuperAdmin
        ? form
        : { ...form, establishment_id: user.establishment_id ?? form.establishment_id }
      await create.mutateAsync(payload)
      toast.success(t('common.save'))
      setOpen(false)
      setForm(EMPTY_FORM)
    } catch (err) {
      toast.error(err.response?.data?.message ?? t('common.error'))
    }
  }

  const columns = [
    { key: 'full_name_fr', header: t('occupant.full_name_fr') },
    { key: 'employee_number', header: t('occupant.employee_number') },
    { key: 'position', header: t('occupant.position') },
    {
      key: 'status',
      header: t('occupant.status'),
      render: (row) => t(`occupantStatus.${row.status}`),
    },
    { key: 'establishment', header: t('occupant.establishment'), render: (row) => row.establishment?.name_fr },
  ]

  return (
    <div className="space-y-4">
      <div className="flex items-center justify-between">
        <h1 className="text-xl font-semibold text-slate-900">{t('nav.occupants')}</h1>
        <Can permission="occupants.create">
          <Button onClick={() => setOpen(true)}>{t('common.create')}</Button>
        </Can>
      </div>

      <Input
        placeholder={t('common.search')}
        value={q}
        onChange={(e) => {
          setPage(1)
          setQ(e.target.value)
        }}
        className="max-w-xs"
      />

      <Table columns={columns} rows={data?.data} isLoading={isLoading} />
      <Pagination meta={data?.meta} onPageChange={setPage} />

      <Modal
        open={open}
        onClose={() => setOpen(false)}
        title={t('common.create')}
        footer={
          <>
            <Button variant="secondary" onClick={() => setOpen(false)}>
              {t('common.cancel')}
            </Button>
            <Button form="occupant-form" type="submit" disabled={create.isPending}>
              {t('common.save')}
            </Button>
          </>
        }
      >
        <form id="occupant-form" onSubmit={handleCreate} className="space-y-4">
          {isSuperAdmin ? (
            <Field label={t('occupant.establishment')}>
              <Select
                value={form.establishment_id}
                onChange={(e) => setForm({ ...form, establishment_id: e.target.value })}
              >
                <option value="">—</option>
                {establishments?.data?.map((e) => (
                  <option key={e.id} value={e.id}>
                    {e.name_fr}
                  </option>
                ))}
              </Select>
            </Field>
          ) : null}
          <Field label={t('occupant.first_name_fr')}>
            <Input
              required
              value={form.first_name_fr}
              onChange={(e) => setForm({ ...form, first_name_fr: e.target.value })}
            />
          </Field>
          <Field label={t('occupant.last_name_fr')}>
            <Input
              required
              value={form.last_name_fr}
              onChange={(e) => setForm({ ...form, last_name_fr: e.target.value })}
            />
          </Field>
          <Field label={t('occupant.employee_number')}>
            <Input
              value={form.employee_number}
              onChange={(e) => setForm({ ...form, employee_number: e.target.value })}
            />
          </Field>
        </form>
      </Modal>
    </div>
  )
}
