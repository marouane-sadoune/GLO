import { useState } from 'react'
import { departmentsApi } from '../../api/departments'
import { establishmentsApi } from '../../api/establishments'
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

const EMPTY_FORM = { department_id: '', code: '', name_fr: '', name_ar: '', type: '', address: '' }

export function EstablishmentsPage() {
  const { t } = useI18n()
  const { user } = useAuth()
  const toast = useToast()
  const [page, setPage] = useState(1)
  const [open, setOpen] = useState(false)
  const [form, setForm] = useState(EMPTY_FORM)

  const { useList, useCreate } = useResourceQueries('establishments', establishmentsApi)
  const { data, isLoading } = useList({ page })
  const create = useCreate()

  const isSuperAdmin = user?.role === 'SUPER_ADMIN'
  const { useList: useDepartmentList } = useResourceQueries('departments', departmentsApi)
  const { data: departments } = useDepartmentList({ per_page: 100 }, { enabled: isSuperAdmin })

  async function handleCreate(e) {
    e.preventDefault()
    try {
      const payload = { ...form, department_id: isSuperAdmin ? form.department_id : user.department_id }
      await create.mutateAsync(payload)
      toast.success(t('common.save'))
      setOpen(false)
      setForm(EMPTY_FORM)
    } catch (err) {
      toast.error(err.response?.data?.message ?? t('common.error'))
    }
  }

  const columns = [
    { key: 'code', header: t('establishment.code') },
    { key: 'name_fr', header: t('establishment.name_fr') },
    { key: 'department', header: t('establishment.department'), render: (row) => row.department?.name_fr },
    { key: 'logements_count', header: t('establishment.logements_count') },
  ]

  return (
    <div className="space-y-4">
      <div className="flex items-center justify-between">
        <h1 className="text-xl font-semibold text-slate-900">{t('nav.establishments')}</h1>
        <Can permission="establishments.create">
          <Button onClick={() => setOpen(true)}>{t('common.create')}</Button>
        </Can>
      </div>

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
            <Button form="establishment-form" type="submit" disabled={create.isPending}>
              {t('common.save')}
            </Button>
          </>
        }
      >
        <form id="establishment-form" onSubmit={handleCreate} className="space-y-4">
          {isSuperAdmin ? (
            <Field label={t('establishment.department')}>
              <Select
                required
                value={form.department_id}
                onChange={(e) => setForm({ ...form, department_id: e.target.value })}
              >
                <option value="" disabled>
                  —
                </option>
                {departments?.data?.map((d) => (
                  <option key={d.id} value={d.id}>
                    {d.name_fr}
                  </option>
                ))}
              </Select>
            </Field>
          ) : null}
          <Field label={t('establishment.code')}>
            <Input required value={form.code} onChange={(e) => setForm({ ...form, code: e.target.value })} />
          </Field>
          <Field label={t('establishment.name_fr')}>
            <Input
              required
              value={form.name_fr}
              onChange={(e) => setForm({ ...form, name_fr: e.target.value })}
            />
          </Field>
          <Field label={t('establishment.address')}>
            <Input value={form.address} onChange={(e) => setForm({ ...form, address: e.target.value })} />
          </Field>
        </form>
      </Modal>
    </div>
  )
}
