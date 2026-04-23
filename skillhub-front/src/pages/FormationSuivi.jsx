import { useEffect, useState } from "react";
import PropTypes from "prop-types";
import { Link, useNavigate, useParams } from "react-router-dom";
import { categories } from "../data/ateliersData";
import { fetchAtelierDetail } from "../services/atelierService";
import "../styles/skillhub.css";
import "./FormationDetail.css";

function StatItem({ label, value }) {
  return (
    <div className="fd-stat">
      <span className="fd-stat-label">{label}</span>
      <span className="fd-stat-value">{value}</span>
    </div>
  );
}

StatItem.propTypes = {
  label: PropTypes.string.isRequired,
  value: PropTypes.node.isRequired,
};

export default function FormationSuivi() {
  const { id } = useParams();
  const navigate = useNavigate();
  const [detail, setDetail] = useState(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState("");

  useEffect(() => {
    let alive = true;
    setLoading(true);
    setError("");
    setDetail(null);

    fetchAtelierDetail(id)
      .then((data) => {
        if (alive) setDetail(data);
      })
      .catch(() => {
        if (alive) setError("Impossible de charger la page de suivi.");
      })
      .finally(() => {
        if (alive) setLoading(false);
      });

    return () => {
      alive = false;
    };
  }, [id]);

  if (loading) {
    return (
      <main className="fd-page">
        <div className="container">
          <div className="fd-empty-state">
            <h1>Chargement…</h1>
            <p>Récupération de votre suivi de formation.</p>
          </div>
        </div>
      </main>
    );
  }

  if (!detail || error) {
    return (
      <main className="fd-page">
        <div className="container">
          <div className="fd-empty-state">
            <h1>Suivi indisponible</h1>
            <p>
              {error || "Cette formation est introuvable ou plus accessible."}
            </p>
            <button
              type="button"
              className="btn btn-primary"
              onClick={() => navigate("/apprenant")}
            >
              Retour au tableau de bord
            </button>
          </div>
        </div>
      </main>
    );
  }

  const progression = Math.min(
    100,
    Math.max(0, Number(detail.progression ?? 0)),
  );
  const categoryLabel =
    categories[detail.categorie] || detail.categorie || "Formation";

  return (
    <main className="fd-page">
      <div className="fd-suivi-bar">
        <div className="container">
          <div className="fd-suivi-bar-inner">
            <p className="fd-suivi-title">Suivi de formation</p>
            <nav className="fd-suivi-links" aria-label="Navigation suivi">
              <Link to="/apprenant" className="fd-nav-pill">
                ← Tableau de bord
              </Link>
              <Link to={`/ateliers/${detail.id}`} className="fd-nav-pill">
                Fiche publique
              </Link>
            </nav>
          </div>
        </div>
      </div>

      <div className="container">
        <div className="fd-layout">
          <header className="fd-hero">
            <span className="fd-badge">{categoryLabel}</span>
            <h1>{detail.titre}</h1>
            <p className="fd-desc">
              {detail.description ||
                "Aucune description fournie pour cette formation."}
            </p>
            <div className="fd-stats">
              <StatItem label="Formateur" value={detail.formateur || "—"} />
              <StatItem label="Niveau" value={detail.niveau || "—"} />
              <StatItem
                label="Apprenants"
                value={String(detail.apprenants ?? 0)}
              />
              <StatItem label="Vues" value={String(detail.vues ?? 0)} />
            </div>
          </header>

          <section
            className="fd-section fd-modules"
            aria-labelledby="fd-suivi-modules"
          >
            <h2 id="fd-suivi-modules" className="fd-section-title">
              Programme — modules
            </h2>
            {detail.modules?.length > 0 ? (
              <ul className="fd-module-list">
                {detail.modules.map((moduleName, index) => (
                  <li key={`${moduleName}-${index}`} className="fd-module-item">
                    <span className="fd-module-index">{index + 1}</span>
                    <p className="fd-module-text">{moduleName}</p>
                  </li>
                ))}
              </ul>
            ) : (
              <p className="fd-empty-modules">
                Aucun module n’est encore publié pour cette formation.
              </p>
            )}
          </section>

          <section
            className="fd-section fd-progress-section"
            aria-labelledby="fd-suivi-progress"
          >
            <h2 id="fd-suivi-progress" className="fd-section-title">
              Votre progression
            </h2>
            <div className="fd-progress-head">
              <span className="fd-progress-percent">{progression}%</span>
              <span className="fd-progress-caption">complété</span>
            </div>
            <progress
              className="fd-progress-track"
              value={progression}
              max={100}
              aria-labelledby="fd-suivi-progress"
            >
              {progression}%
            </progress>
          </section>
        </div>
      </div>
    </main>
  );
}
