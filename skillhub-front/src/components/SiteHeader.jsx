import { useState } from 'react'
import { Link, useNavigate } from 'react-router-dom'
import { useAuthModal } from '../contexts/AuthModalContext'
import { clearSession, logout } from '../services/authService'

export default function SiteHeader() {
  const [navOpen, setNavOpen] = useState(false)
  const { openModal, publicUser, refreshPublicSession } = useAuthModal()
  const navigate = useNavigate()

  const displayName = publicUser
    ? [publicUser.prenom, publicUser.nom].filter(Boolean).join(' ').trim() || publicUser.email
    : ''

  const role = String(publicUser?.role || '').toUpperCase()
  const dashboardPath = role === 'FORMATEUR' ? '/formateur' : '/apprenant'

  const handleLogout = async () => {
    try {
      await logout()
    } catch {
      clearSession()
    }
    refreshPublicSession()
    setNavOpen(false)
    navigate('/')
  }

  return (
    <header className="header">
      <div className="container nav-container">
        <div className="logo">SkillHub</div>
        <button
          type="button"
          id="burger-btn"
          className="burger"
          onClick={() => setNavOpen((o) => !o)}
          aria-expanded={navOpen}
          aria-controls="nav"
        >
          ☰
        </button>
        <nav className={`nav${navOpen ? ' open' : ''}`} id="nav">
          <Link to="/" onClick={() => setNavOpen(false)}>
            Accueil
          </Link>
          <Link to="/ateliers" onClick={() => setNavOpen(false)}>
            Nos formations
          </Link>
          <a href="#apropos" onClick={() => setNavOpen(false)}>
            À propos
          </a>
          <a href="#contact" onClick={() => setNavOpen(false)}>
            Contact
          </a>
          <a href="#user" onClick={() => setNavOpen(false)}>
            Utilisateur
          </a>
          {publicUser ? (
            <>
              <button
                type="button"
                className="btn"
                onClick={() => {
                  setNavOpen(false)
                  navigate(dashboardPath)
                }}
                title="Accéder à mon profil"
              >
                {displayName}
              </button>
              <button
                type="button"
                className="btn"
                onClick={() => {
                  handleLogout()
                }}
              >
                Déconnexion
              </button>
            </>
          ) : (
            <button
              type="button"
              className="btn"
              data-modal="connexion"
              onClick={() => {
                openModal('connexion')
                setNavOpen(false)
              }}
            >
              Connexion
            </button>
          )}
        </nav>
      </div>
    </header>
  )
}
