export function EmptyState({ title, action = null }) {
  return (
    <div className="flex flex-col items-center justify-center gap-3 py-16 text-center text-slate-500">
      <p>{title}</p>
      {action}
    </div>
  )
}
