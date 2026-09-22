import { useState } from 'react'
import { useMutation } from '@tanstack/react-query'
import { updatePassword } from '../api/auth'
import { Button } from './ui/Button'
import { Field, Input } from './ui/Field'
import { Modal } from './ui/Modal'
import { useToast } from './ui/Toast'
import { useI18n } from '../i18n/I18nContext'

const EMPTY_FORM = { current_password: '', password: '', password_confirmation: '' }

export function ChangePasswordModal({ onClose }) {
  const { t } = useI18n()
  const toast = useToast()
  const [form, setForm] = useState(EMPTY_FORM)
  const [errors, setErrors] = useState({})

  const submit = useMutation({
    mutationFn: () => updatePassword(form),
    onSuccess: () => {
      toast.success(t('user.passwordUpdated'))
      onClose()
    },
    onError: (err) => setErrors(err.response?.data?.errors ?? {}),
  })

  return (
    <Modal
      open
      onClose={onClose}
      title={t('user.changePassword')}
      footer={
        <>
          <Button variant="secondary" onClick={onClose}>
            {t('common.cancel')}
          </Button>
          <Button form="change-password-form" type="submit" disabled={submit.isPending}>
            {t('common.save')}
          </Button>
        </>
      }
    >
      <form
        id="change-password-form"
        className="space-y-4"
        onSubmit={(e) => {
          e.preventDefault()
          setErrors({})
          submit.mutate()
        }}
      >
        <Field label={t('user.currentPassword')} error={errors.current_password?.[0]}>
          <Input
            type="password"
            required
            autoComplete="current-password"
            value={form.current_password}
            onChange={(e) => setForm({ ...form, current_password: e.target.value })}
          />
        </Field>
        <Field label={t('user.newPassword')} error={errors.password?.[0]}>
          <Input
            type="password"
            required
            autoComplete="new-password"
            value={form.password}
            onChange={(e) => setForm({ ...form, password: e.target.value })}
          />
        </Field>
        <Field label={t('user.confirmPassword')}>
          <Input
            type="password"
            required
            autoComplete="new-password"
            value={form.password_confirmation}
            onChange={(e) => setForm({ ...form, password_confirmation: e.target.value })}
          />
        </Field>
      </form>
    </Modal>
  )
}
