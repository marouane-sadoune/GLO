import { useI18n } from '../../i18n/I18nContext'
import { LoadingBlock } from './Spinner'
import { EmptyState } from './EmptyState'

/**
 * @param {{key: string, header: string, render?: (row: any) => any, className?: string}[]} columns
 */
export function Table({ columns, rows, isLoading, onRowClick, rowKey = 'id' }) {
  const { t } = useI18n()

  if (isLoading) return <LoadingBlock label={t('common.loading')} />
  if (!rows || rows.length === 0) return <EmptyState title={t('common.noResults')} />

  return (
    <div className="overflow-x-auto rounded-lg ring-1 ring-slate-200">
      <table className="min-w-full divide-y divide-slate-200 text-sm">
        <thead className="bg-slate-50">
          <tr>
            {columns.map((col) => (
              <th key={col.key} className="px-4 py-3 text-start font-medium text-slate-500">
                {col.header}
              </th>
            ))}
          </tr>
        </thead>
        <tbody className="divide-y divide-slate-100 bg-white">
          {rows.map((row) => (
            <tr
              key={row[rowKey]}
              onClick={onRowClick ? () => onRowClick(row) : undefined}
              className={onRowClick ? 'cursor-pointer hover:bg-slate-50' : ''}
            >
              {columns.map((col) => (
                <td key={col.key} className={`px-4 py-3 text-slate-700 ${col.className ?? ''}`}>
                  {col.render ? col.render(row) : row[col.key]}
                </td>
              ))}
            </tr>
          ))}
        </tbody>
      </table>
    </div>
  )
}
