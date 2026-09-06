import { NavLink, Outlet } from 'react-router-dom'
import { useAuth } from '../auth/AuthContext'
import { Can } from '../auth/Can'
import { useI18n } from '../i18n/I18nContext'

const NAV_ITEMS = [
  { to: '/', label: 'nav.dashboard', end: true },
  { to: '/departments', label: 'nav.departments', permission: 'departments.view' },
  { to: '/establishments', label: 'nav.establishments', permission: 'establishments.view' },
  { to: '/logements', label: 'nav.logements', permission: 'logements.view' },
  { to: '/occupants', label: 'nav.occupants', permission: 'occupants.view' },
  { to: '/assignment-requests', label: 'nav.requests', permission: 'requests.view' },
  { to: '/occupations', label: 'nav.occupations', permission: 'occupations.view' },
]

function NavItem({ to, label, end }) {
  return (
    <NavLink
      to={to}
      end={end}
      className={({ isActive }) =>
        'block rounded-md px-3 py-2 text-sm font-medium transition ' +
        (isActive ? 'bg-indigo-600 text-white' : 'text-slate-600 hover:bg-slate-100')
      }
    >
      {label}
    </NavLink>
  )
}

export function AppLayout() {
  const { user, logout } = useAuth()
  const { t, locale, setLocale } = useI18n()

  return (
    <div className="flex min-h-screen bg-slate-50">
      <aside className="hidden w-64 shrink-0 border-e border-slate-200 bg-white p-4 sm:block">
        <div className="mb-6 px-2 text-lg font-bold text-indigo-600">{t('app.name')}</div>
        <nav className="space-y-1">
          {NAV_ITEMS.map((item) =>
            item.permission ? (
              <Can key={item.to} permission={item.permission}>
                <NavItem to={item.to} end={item.end} label={t(item.label)} />
              </Can>
            ) : (
              <NavItem key={item.to} to={item.to} end={item.end} label={t(item.label)} />
            ),
          )}
        </nav>
      </aside>

      <div className="flex min-h-screen flex-1 flex-col">
        <header className="flex items-center justify-between border-b border-slate-200 bg-white px-6 py-3">
          <div className="text-sm text-slate-500">{user ? t(`role.${user.role}`) : null}</div>
          <div className="flex items-center gap-4">
            <button
              type="button"
              onClick={() => setLocale(locale === 'fr' ? 'ar' : 'fr')}
              className="rounded-md px-2 py-1 text-sm font-medium text-slate-600 ring-1 ring-slate-300 hover:bg-slate-50"
            >
              {locale === 'fr' ? 'العربية' : 'Français'}
            </button>
            <span className="text-sm font-medium text-slate-800">{user?.name}</span>
            <button type="button" onClick={logout} className="text-sm text-slate-500 hover:text-slate-800">
              {t('nav.logout')}
            </button>
          </div>
        </header>

        <main className="flex-1 p-6">
          <Outlet />
        </main>
      </div>
    </div>
  )
}
