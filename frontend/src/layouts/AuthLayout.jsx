import { Outlet } from 'react-router-dom'
import { useI18n } from '../i18n/I18nContext'

export function AuthLayout() {
  const { t, locale, setLocale } = useI18n()

  return (
    <div className="flex min-h-screen items-center justify-center bg-slate-100 px-4">
      <div className="w-full max-w-sm">
        <div className="mb-6 flex items-center justify-between">
          <div>
            <h1 className="text-2xl font-bold text-indigo-600">{t('app.name')}</h1>
            <p className="text-sm text-slate-500">{t('app.title')}</p>
          </div>
          <button
            type="button"
            onClick={() => setLocale(locale === 'fr' ? 'ar' : 'fr')}
            className="rounded-md px-2 py-1 text-sm font-medium text-slate-600 ring-1 ring-slate-300 hover:bg-slate-50"
          >
            {locale === 'fr' ? 'العربية' : 'Français'}
          </button>
        </div>
        <div className="rounded-xl bg-white p-6 shadow-md">
          <Outlet />
        </div>
      </div>
    </div>
  )
}
