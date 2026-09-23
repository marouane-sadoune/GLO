import { useState } from 'react'
import { departmentsApi } from '../../api/departments'
import { establishmentsApi } from '../../api/establishments'
import { usersApi } from '../../api/users'
import { useAuth } from '../../auth/AuthContext'
import { Can } from '../../auth/Can'
import { Badge } from '../../components/ui/Badge'
import { Button } from '../../components/ui/Button'
import { ConfirmDialog } from '../../components/ui/ConfirmDialog'
import { Field, Input, Select } from '../../components/ui/Field'
import { Modal } from '../../components/ui/Modal'
import { Pagination } from '../../components/ui/Pagination'
import { Table } from '../../components/ui/Table'
import { useToast } from '../../components/ui/Toast'
import { useResourceQueries } from '../../hooks/useResource'
import { useI18n } from '../../i18n/I18nContext'

const ROLES = ['SUPER_ADMIN', 'DP_AGENT', 'ESTABLISHMENT_MANAGER', 'AREF_VALIDATOR', 'AREF_DIRECTOR']
const REGION_WIDE_ROLES = ['SUPER_ADMIN', 'AREF_VALIDATOR', 'AREF_DIRECTOR']

const EMPTY_FORM = {
  name: '',
  email: '',
  password: '',
  role: 'ESTABLISHMENT_MANAGER',
  department_id: '',
  establishment_id: '',
  active: true,
}

function toPayload(form) {
  const payload = { ...form }
  if (REGION_WIDE_ROLES.includes(payload.role)) payload.department_id = ''
  if (payload.role !== 'ESTABLISHMENT_MANAGER') payload.establishment_id = ''
  if (!payload.password) delete payload.password
  return payload
}

function UserFormModal({ title, initial, departments, establishments, onClose, onSubmit, busy }) {
  const { t } = useI18n()
  const [form, setForm] = useState(initial)

  const availableEstablishments = establishments?.data?.filter(
    (e) => !form.department_id || e.department_id === Number(form.department_id),
  )

  return (
    <Modal
      open
      onClose={onClose}
      title={title}
      footer={
        <>
          <Button variant="secondary" onClick={onClose}>
            {t('common.cancel')}
          </Button>
          <Button form="user-form" type="submit" disabled={busy}>
            {t('common.save')}
          </Button>
        </>
      }
    >
      <form id="user-form" className="space-y-4" onSubmit={(e) => (e.preventDefault(), onSubmit(toPayload(form)))}>
        <Field label={t('user.name')}>
          <Input required value={form.name} onChange={(e) => setForm({ ...form, name: e.target.value })} />
        </Field>
        <Field label={t('user.email')}>
          <Input
            type="email"
            required
            value={form.email}
            onChange={(e) => setForm({ ...form, email: e.target.value })}
          />
        </Field>
        <Field label={t('user.password')}>
          <Input
            type="password"
            placeholder={t('user.passwordPlaceholder')}
            value={form.password}
            onChange={(e) => setForm({ ...form, password: e.target.value })}
          />
        </Field>
        <Field label={t('user.role')}>
          <Select value={form.role} onChange={(e) => setForm({ ...form, role: e.target.value })}>
            {ROLES.map((role) => (
              <option key={role} value={role}>
                {t(`role.${role}`)}
              </option>
            ))}
          </Select>
        </Field>
        {!REGION_WIDE_ROLES.includes(form.role) ? (
          <Field label={t('user.department')}>
            <Select
              required
              value={form.department_id}
              onChange={(e) => setForm({ ...form, department_id: e.target.value, establishment_id: '' })}
            >
              <option value="" disabled>
                {t('common.select')}
              </option>
              {departments?.data?.map((d) => (
                <option key={d.id} value={d.id}>
                  {d.name_fr}
                </option>
              ))}
            </Select>
          </Field>
        ) : null}
        {form.role === 'ESTABLISHMENT_MANAGER' ? (
          <Field label={t('user.establishment')}>
            <Select
              required
              value={form.establishment_id}
              onChange={(e) => setForm({ ...form, establishment_id: e.target.value })}
            >
              <option value="" disabled>
                {t('common.select')}
              </option>
              {availableEstablishments?.map((e) => (
                <option key={e.id} value={e.id}>
                  {e.name_fr}
                </option>
              ))}
            </Select>
          </Field>
        ) : null}
        <Field label={t('common.status')}>
          <label className="flex items-center gap-2 text-sm text-slate-700">
            <input
              type="checkbox"
              checked={form.active}
              onChange={(e) => setForm({ ...form, active: e.target.checked })}
            />
            {t('user.active')}
          </label>
        </Field>
      </form>
    </Modal>
  )
}

export function UsersPage() {
  const { t } = useI18n()
  const { user: currentUser } = useAuth()
  const toast = useToast()
  const [page, setPage] = useState(1)
  const [editing, setEditing] = useState(null) // null | 'new' | row
  const [deleteTarget, setDeleteTarget] = useState(null)

  const { useList, useCreate, useUpdate, useRemove } = useResourceQueries('users', usersApi)
  const { data, isLoading } = useList({ page })
  const { useList: useDepartments } = useResourceQueries('departments', departmentsApi)
  const { useList: useEstablishments } = useResourceQueries('establishments', establishmentsApi)
  const { data: departments } = useDepartments({ per_page: 100 })
  const { data: establishments } = useEstablishments({ per_page: 100 })

  const create = useCreate()
  const update = useUpdate()
  const remove = useRemove()

  async function handleSubmit(payload) {
    try {
      if (editing === 'new') {
        await create.mutateAsync(payload)
      } else {
        await update.mutateAsync({ id: editing.id, payload })
      }
      toast.success(t('common.save'))
      setEditing(null)
    } catch (err) {
      toast.error(err.response?.data?.message ?? t('common.error'))
    }
  }

  async function handleDelete() {
    try {
      await remove.mutateAsync(deleteTarget.id)
      toast.success(t('common.delete'))
      setDeleteTarget(null)
    } catch (err) {
      toast.error(err.response?.data?.message ?? t('common.error'))
    }
  }

  const columns = [
    { key: 'name', header: t('user.name') },
    { key: 'email', header: t('user.email') },
    { key: 'role', header: t('user.role'), render: (row) => t(`role.${row.role}`) },
    { key: 'department', header: t('user.department'), render: (row) => row.department?.name_fr ?? '—' },
    { key: 'establishment', header: t('user.establishment'), render: (row) => row.establishment?.name_fr ?? '—' },
    {
      key: 'active',
      header: t('common.status'),
      render: (row) => (
        <Badge tone={row.active ? 'green' : 'slate'}>{t(row.active ? 'user.active' : 'user.inactive')}</Badge>
      ),
    },
    {
      key: 'actions',
      header: t('common.actions'),
      render: (row) => (
        <Can permission="users.manage">
          <div className="flex gap-2">
            <Button variant="secondary" onClick={() => setEditing(row)}>
              {t('common.edit')}
            </Button>
            {row.id !== currentUser?.id ? (
              <Button variant="danger" onClick={() => setDeleteTarget(row)}>
                {t('common.delete')}
              </Button>
            ) : null}
          </div>
        </Can>
      ),
    },
  ]

  return (
    <div className="space-y-4">
      <div className="flex items-center justify-between">
        <h1 className="text-xl font-semibold text-slate-900">{t('nav.users')}</h1>
        <Can permission="users.manage">
          <Button onClick={() => setEditing('new')}>{t('common.create')}</Button>
        </Can>
      </div>

      <Table columns={columns} rows={data?.data} isLoading={isLoading} />
      <Pagination meta={data?.meta} onPageChange={setPage} />

      {editing ? (
        <UserFormModal
          key={editing === 'new' ? 'new' : editing.id}
          title={editing === 'new' ? t('common.create') : t('common.edit')}
          initial={
            editing === 'new'
              ? EMPTY_FORM
              : {
                  name: editing.name,
                  email: editing.email,
                  password: '',
                  role: editing.role,
                  department_id: editing.department_id ?? '',
                  establishment_id: editing.establishment_id ?? '',
                  active: editing.active,
                }
          }
          departments={departments}
          establishments={establishments}
          onClose={() => setEditing(null)}
          onSubmit={handleSubmit}
          busy={create.isPending || update.isPending}
        />
      ) : null}

      <ConfirmDialog
        open={Boolean(deleteTarget)}
        onConfirm={handleDelete}
        onCancel={() => setDeleteTarget(null)}
        busy={remove.isPending}
      />
    </div>
  )
}
