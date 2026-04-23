import { useEffect, useState } from "react";
import PropTypes from "prop-types";
import { Link, useNavigate, useParams } from "react-router-dom";
import SiteHeader from "../components/SiteHeader";
import Footer from "../components/Footer";
import { useAuthModal } from "../contexts/AuthModalContext";
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

export default function FormationDetail() {
  const { id } = useParams();
  const navigate = useNavigate();
  const { openModal, publicUser } = useAuthModal();
  const [detail, setDetail] = useState(null);
  const [loading, setLoading] = useState(true);
  const [apiError, setApiError] = useState("");

  useEffect(() => {
    let alive = true;
    setLoading(true);
    setApiError("");
    setDetail(null);

    fetchAtelierDetail(id)
      .then((data) => {
        if (!alive) return;
        setDetail(data);
      })
      .catch(() => {
        if (!alive) return;
        setDetail(null);
        setApiError("Impossible de charger cette formation depuis le serveur.");
      })
      .finally(() => {
        if (alive) setLoading(false);
      });

    return () => {
      alive = false;
    };
  }, [id]);

  if (!detail) {
    return (
      <>
        <SiteHeader />
        <main className="fd-page">
          <div className="container">
            <div className="fd-empty-state">
              <h1>{loading ? "Chargement…" : "Formation introuvable"}</h1>
              <p>
                {loading
                  ? "Récupération des informations de la formation."
                  : apiError ||
                    "La formation demandée n’existe pas ou n’est plus disponible."}
              </p>
              <button
                type="button"
                className="btn btn-primary"
                onClick={() => navigate("/ateliers")}
              >
                Retour aux ateliers
              </button>
            </div>
          </div>
        </main>
        <Footer />
      </>
    );
  }

  const categoryLabel =
    categories[detail.categorie] || detail.categorie || "Formation";

  return (
    <>
      <SiteHeader />
      <main className="fd-page">
        <div className="container fd-layout">
          <Link to="/ateliers" className="fd-back">
            Retour à la liste
          </Link>

          <header className="fd-hero">
            <span className="fd-badge">{categoryLabel}</span>
            <h1>{detail.titre}</h1>
            <p className="fd-desc">
              {detail.description ||
                "Aucune description fournie pour cette formation."}
            </p>

            <div className="fd-stats">
              <StatItem label="Niveau" value={detail.niveau || "—"} />
              <StatItem label="Formateur" value={detail.formateur || "—"} />
              <StatItem
                label="Apprenants"
                value={String(detail.apprenants ?? 0)}
              />
              <StatItem label="Vues" value={String(detail.vues ?? 0)} />
            </div>
          </header>

          <section
            className="fd-section fd-modules"
            aria-labelledby="fd-programme-title"
          >
            <h2 id="fd-programme-title" className="fd-section-title">
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
            className="fd-section fd-cta"
            aria-label="Suivre la formation"
          >
            {!publicUser ? (
              <>
                <p className="fd-cta-text">
                  Connectez-vous pour accéder au suivi de cette formation et
                  suivre votre progression.
                </p>
                <button
                  type="button"
                  className="btn btn-primary"
                  onClick={() => openModal("connexion")}
                >
                  Suivre la formation
                </button>
              </>
            ) : (
              <>
                <p className="fd-cta-text">
                  Poursuivez votre apprentissage depuis votre espace de suivi.
                </p>
                <button
                  type="button"
                  className="btn btn-primary"
                  onClick={() => navigate(`/apprenant/suivi/${detail.id}`)}
                >
                  Suivre la formation
                </button>
              </>
            )}
          </section>
        </div>
      </main>
      <Footer />
    </>
  );
}
