import { useI18n } from '../../i18n/I18nContext'
import { Button } from './Button'
import { Modal } from './Modal'

export function ConfirmDialog({ open, title, body, onConfirm, onCancel, busy = false }) {
  const { t } = useI18n()

  return (
    <Modal
      open={open}
      onClose={onCancel}
      title={title ?? t('common.confirmDeleteTitle')}
      footer={
        <>
          <Button variant="secondary" onClick={onCancel} disabled={busy}>
            {t('common.cancel')}
          </Button>
          <Button variant="danger" onClick={onConfirm} disabled={busy}>
            {t('common.confirm')}
          </Button>
        </>
      }
    >
      <p className="text-sm text-slate-600">{body ?? t('common.confirmDeleteBody')}</p>
    </Modal>
  )
}
