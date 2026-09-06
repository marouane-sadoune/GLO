import { Link } from 'react-router-dom'

export function NotFoundPage() {
  return (
    <div className="flex min-h-[50vh] flex-col items-center justify-center gap-3 text-center">
      <h1 className="text-3xl font-bold text-slate-800">404</h1>
      <p className="text-slate-500">Page introuvable.</p>
      <Link to="/" className="text-indigo-600 hover:underline">
        Retour à l'accueil
      </Link>
    </div>
  )
}
