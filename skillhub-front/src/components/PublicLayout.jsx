import { Outlet } from 'react-router-dom'
import { AuthModalProvider } from '../contexts/AuthModalContext'

export default function PublicLayout() {
  return (
    <AuthModalProvider>
      <Outlet />
    </AuthModalProvider>
  )
}
