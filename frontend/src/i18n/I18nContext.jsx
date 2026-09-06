import { createContext, useContext, useEffect, useMemo, useState } from 'react'
import ar from './ar.json'
import fr from './fr.json'

const DICTS = { fr, ar }
const DIRECTIONS = { fr: 'ltr', ar: 'rtl' }
const STORAGE_KEY = 'glo.locale'

const I18nContext = createContext(null)

function interpolate(template, vars) {
  if (!vars) return template
  return template.replace(/\{(\w+)\}/g, (match, key) => (key in vars ? String(vars[key]) : match))
}

export function I18nProvider({ children }) {
  const [locale, setLocale] = useState(() => localStorage.getItem(STORAGE_KEY) || 'fr')

  useEffect(() => {
    document.documentElement.lang = locale
    document.documentElement.dir = DIRECTIONS[locale]
    localStorage.setItem(STORAGE_KEY, locale)
  }, [locale])

  const value = useMemo(() => {
    const dict = DICTS[locale] ?? DICTS.fr
    const t = (key, vars) => interpolate(dict[key] ?? key, vars)
    return { locale, setLocale, dir: DIRECTIONS[locale], t }
  }, [locale])

  return <I18nContext.Provider value={value}>{children}</I18nContext.Provider>
}

export function useI18n() {
  const ctx = useContext(I18nContext)
  if (!ctx) throw new Error('useI18n must be used within I18nProvider')
  return ctx
}
