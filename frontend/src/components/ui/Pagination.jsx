import { useI18n } from '../../i18n/I18nContext'
import { Button } from './Button'

export function Pagination({ meta, onPageChange }) {
  const { t } = useI18n()
  if (!meta || meta.last_page <= 1) return null

  return (
    <div className="flex items-center justify-between border-t border-slate-200 px-1 py-3 text-sm text-slate-600">
      <span>{t('common.page', { page: meta.current_page, last: meta.last_page })}</span>
      <div className="flex gap-2">
        <Button
          variant="secondary"
          disabled={meta.current_page <= 1}
          onClick={() => onPageChange(meta.current_page - 1)}
        >
          {t('common.previous')}
        </Button>
        <Button
          variant="secondary"
          disabled={meta.current_page >= meta.last_page}
          onClick={() => onPageChange(meta.current_page + 1)}
        >
          {t('common.next')}
        </Button>
      </div>
    </div>
  )
}
