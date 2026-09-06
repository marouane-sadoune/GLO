import { useState } from 'react'
import { useNavigate } from 'react-router-dom'
import { establishmentsApi } from '../../api/establishments'
import { logementsApi } from '../../api/logements'
import { useAuth } from '../../auth/AuthContext'
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

const EMPTY_FORM = {
  establishment_id: '',
  inventory_number: '',
  location_fr: '',
  location_ar: '',
  housing_category: 'ADMINISTRATIVE',
  notes: '',
}

export function LogementsPage() {
  const { t } = useI18n()
  const { user } = useAuth()
  const toast = useToast()
  const navigate = useNavigate()

  const [page, setPage] = useState(1)
  const [q, setQ] = useState('')
  const [status, setStatus] = useState('')
  const [open, setOpen] = useState(false)
  const [form, setForm] = useState(EMPTY_FORM)

  const { useList, useCreate } = useResourceQueries('logements', logementsApi)
  const { data, isLoading } = useList({ page, q: q || undefined, housing_status: status || undefined })
  const create = useCreate()

  const isSuperAdmin = user?.role === 'SUPER_ADMIN'
  const { useList: useEstablishmentList } = useResourceQueries('establishments', establishmentsApi)
  const { data: establishments } = useEstablishmentList({ per_page: 100 })

  async function handleCreate(e) {
    e.preventDefault()
    try {
      await create.mutateAsync(form)
      toast.success(t('common.save'))
      setOpen(false)
      setForm(EMPTY_FORM)
    } catch (err) {
      toast.error(err.response?.data?.message ?? t('common.error'))
    }
  }

  const columns = [
    { key: 'inventory_number', header: t('logement.inventory_number') },
    { key: 'location_fr', header: t('logement.location_fr') },
    {
      key: 'housing_category',
      header: t('logement.housing_category'),
      render: (row) => t(`housingCategory.${row.housing_category}`),
    },
    {
      key: 'housing_status',
      header: t('logement.housing_status'),
      render: (row) => (
        <Badge tone={row.housing_status === 'VACANT' ? 'green' : 'amber'}>
          {t(`housingStatus.${row.housing_status}`)}
        </Badge>
      ),
    },
    { key: 'establishment', header: t('logement.establishment'), render: (row) => row.establishment?.name_fr },
  ]

  return (
    <div className="space-y-4">
      <div className="flex flex-wrap items-center justify-between gap-3">
        <h1 className="text-xl font-semibold text-slate-900">{t('nav.logements')}</h1>
        <Can permission="logements.create">
          <Button onClick={() => setOpen(true)}>{t('common.create')}</Button>
        </Can>
      </div>

      <div className="flex flex-wrap gap-3">
        <Input
          placeholder={t('common.search')}
          value={q}
          onChange={(e) => {
            setPage(1)
            setQ(e.target.value)
          }}
          className="max-w-xs"
        />
        <Select
          value={status}
          onChange={(e) => {
            setPage(1)
            setStatus(e.target.value)
          }}
          className="max-w-40"
        >
          <option value="">{t('common.all')}</option>
          <option value="VACANT">{t('housingStatus.VACANT')}</option>
          <option value="OCCUPIED">{t('housingStatus.OCCUPIED')}</option>
        </Select>
      </div>

      <Table
        columns={columns}
        rows={data?.data}
        isLoading={isLoading}
        onRowClick={(row) => navigate(`/logements/${row.id}`)}
      />
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
            <Button form="logement-form" type="submit" disabled={create.isPending}>
              {t('common.save')}
            </Button>
          </>
        }
      >
        <form id="logement-form" onSubmit={handleCreate} className="space-y-4">
          <Field label={t('logement.establishment')}>
            <Select
              required
              value={form.establishment_id}
              onChange={(e) => setForm({ ...form, establishment_id: e.target.value })}
            >
              <option value="" disabled>
                —
              </option>
              {establishments?.data
                ?.filter((e) => isSuperAdmin || e.id === user.establishment_id)
                .map((e) => (
                  <option key={e.id} value={e.id}>
                    {e.name_fr}
                  </option>
                ))}
            </Select>
          </Field>
          <Field label={t('logement.inventory_number')}>
            <Input
              required
              value={form.inventory_number}
              onChange={(e) => setForm({ ...form, inventory_number: e.target.value })}
            />
          </Field>
          <Field label={t('logement.location_fr')}>
            <Input
              required
              value={form.location_fr}
              onChange={(e) => setForm({ ...form, location_fr: e.target.value })}
            />
          </Field>
          <Field label={t('logement.housing_category')}>
            <Select
              value={form.housing_category}
              onChange={(e) => setForm({ ...form, housing_category: e.target.value })}
            >
              <option value="ADMINISTRATIVE">{t('housingCategory.ADMINISTRATIVE')}</option>
              <option value="FUNCTIONAL">{t('housingCategory.FUNCTIONAL')}</option>
            </Select>
          </Field>
        </form>
      </Modal>
    </div>
  )
}
