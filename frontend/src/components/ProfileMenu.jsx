import { useEffect, useRef, useState } from 'react'
import { useAuth } from '../auth/AuthContext'
import { useI18n } from '../i18n/I18nContext'
import { Badge } from './ui/Badge'

function initials(name) {
  if (!name) return '?'
  return name
    .trim()
    .split(/\s+/)
    .slice(0, 2)
    .map((part) => part[0]?.toUpperCase())
    .join('')
}

export function ProfileMenu({ onChangePassword }) {
  const { user, logout } = useAuth()
  const { t } = useI18n()
  const [open, setOpen] = useState(false)
  const containerRef = useRef(null)

  useEffect(() => {
    if (!open) return
    function handleClickOutside(e) {
      if (containerRef.current && !containerRef.current.contains(e.target)) setOpen(false)
    }
    document.addEventListener('mousedown', handleClickOutside)
    return () => document.removeEventListener('mousedown', handleClickOutside)
  }, [open])

  if (!user) return null

  const orgLabel = user.establishment?.name_fr ?? user.department?.name_fr ?? null

  return (
    <div className="relative" ref={containerRef}>
      <button
        type="button"
        onClick={() => setOpen((o) => !o)}
        className="flex items-center gap-2 rounded-full py-1 ps-1 pe-2 transition hover:bg-slate-100"
      >
        <span className="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-sky-600 text-sm font-semibold text-white">
          {initials(user.name)}
        </span>
        <span className="hidden text-start sm:block">
          <span className="block text-sm font-medium leading-tight text-slate-800">{user.name}</span>
          <span className="block text-xs leading-tight text-slate-500">{t(`role.${user.role}`)}</span>
        </span>
        <svg viewBox="0 0 20 20" fill="currentColor" className={`h-4 w-4 text-slate-400 transition ${open ? 'rotate-180' : ''}`}>
          <path
            fillRule="evenodd"
            d="M5.23 7.21a.75.75 0 0 1 1.06.02L10 11.293l3.71-4.06a.75.75 0 1 1 1.08 1.04l-4.25 4.65a.75.75 0 0 1-1.08 0L5.21 8.27a.75.75 0 0 1 .02-1.06Z"
            clipRule="evenodd"
          />
        </svg>
      </button>

      {open ? (
        <div className="absolute end-0 z-30 mt-2 w-64 overflow-hidden rounded-xl bg-white shadow-lg ring-1 ring-slate-200">
          <div className="flex items-center gap-3 border-b border-slate-100 bg-slate-50 px-4 py-3">
            <span className="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-sky-600 text-sm font-semibold text-white">
              {initials(user.name)}
            </span>
            <div className="min-w-0">
              <p className="truncate text-sm font-semibold text-slate-900">{user.name}</p>
              <p className="truncate text-xs text-slate-500">{user.email}</p>
            </div>
          </div>

          <div className="flex flex-wrap items-center gap-2 px-4 py-3">
            <Badge tone="blue">{t(`role.${user.role}`)}</Badge>
            {orgLabel ? <span className="text-xs text-slate-500">{orgLabel}</span> : null}
          </div>

          <div className="border-t border-slate-100 py-1">
            <button
              type="button"
              onClick={() => {
                setOpen(false)
                onChangePassword()
              }}
              className="block w-full px-4 py-2 text-start text-sm text-slate-700 transition hover:bg-slate-50"
            >
              {t('user.changePassword')}
            </button>
            <button
              type="button"
              onClick={logout}
              className="block w-full px-4 py-2 text-start text-sm text-red-600 transition hover:bg-red-50"
            >
              {t('nav.logout')}
            </button>
          </div>
        </div>
      ) : null}
    </div>
  )
}
