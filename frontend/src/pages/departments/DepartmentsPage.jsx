import { useState } from 'react'
import { departmentsApi } from '../../api/departments'
import { Can } from '../../auth/Can'
import { Button } from '../../components/ui/Button'
import { Field, Input } from '../../components/ui/Field'
import { Modal } from '../../components/ui/Modal'
import { Table } from '../../components/ui/Table'
import { Pagination } from '../../components/ui/Pagination'
import { useToast } from '../../components/ui/Toast'
import { useResourceQueries } from '../../hooks/useResource'
import { useI18n } from '../../i18n/I18nContext'

export function DepartmentsPage() {
  const { t } = useI18n()
  const toast = useToast()
  const [page, setPage] = useState(1)
  const [open, setOpen] = useState(false)
  const [form, setForm] = useState({ code: '', name_fr: '', name_ar: '' })

  const { useList, useCreate } = useResourceQueries('departments', departmentsApi)
  const { data, isLoading } = useList({ page })
  const create = useCreate()

  async function handleCreate(e) {
    e.preventDefault()
    try {
      await create.mutateAsync(form)
      toast.success(t('common.save'))
      setOpen(false)
      setForm({ code: '', name_fr: '', name_ar: '' })
    } catch (err) {
      toast.error(err.response?.data?.message ?? t('common.error'))
    }
  }

  const columns = [
    { key: 'code', header: t('department.code') },
    { key: 'name_fr', header: t('department.name_fr') },
    { key: 'name_ar', header: t('department.name_ar') },
    { key: 'establishments_count', header: t('department.establishments_count') },
  ]

  return (
    <div className="space-y-4">
      <div className="flex items-center justify-between">
        <h1 className="text-xl font-semibold text-slate-900">{t('nav.departments')}</h1>
        <Can permission="departments.manage">
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
            <Button form="department-form" type="submit" disabled={create.isPending}>
              {t('common.save')}
            </Button>
          </>
        }
      >
        <form id="department-form" onSubmit={handleCreate} className="space-y-4">
          <Field label={t('department.code')}>
            <Input required value={form.code} onChange={(e) => setForm({ ...form, code: e.target.value })} />
          </Field>
          <Field label={t('department.name_fr')}>
            <Input
              required
              value={form.name_fr}
              onChange={(e) => setForm({ ...form, name_fr: e.target.value })}
            />
          </Field>
          <Field label={t('department.name_ar')}>
            <Input
              required
              dir="rtl"
              value={form.name_ar}
              onChange={(e) => setForm({ ...form, name_ar: e.target.value })}
            />
          </Field>
        </form>
      </Modal>
    </div>
  )
}
