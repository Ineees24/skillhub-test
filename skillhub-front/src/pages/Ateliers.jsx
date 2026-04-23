import { useEffect, useMemo, useState } from "react";
import { Link } from "react-router-dom";
import SiteHeader from "../components/SiteHeader";
import Footer from "../components/Footer";
import { useAuthModal } from "../contexts/AuthModalContext";
import { ateliers as ateliersList, categories } from "../data/ateliersData";
import {
  fetchAteliers,
  fetchMesInscriptions,
  inscrireAtelier,
} from "../services/atelierService";
import { getCategoryVisualByKey } from "../utils/categoryVisuals";
import "../styles/skillhub.css";
import "./Ateliers.css";

function matchesPrix(prix, range) {
  if (range === "0-50") return prix >= 0 && prix <= 50;
  if (range === "50-100") return prix > 50 && prix <= 100;
  if (range === "100-150") return prix > 100 && prix <= 150;
  if (range === "150+") return prix > 150;
  return false;
}

export default function Ateliers() {
  const { openModal, publicUser } = useAuthModal();
  const [ateliers, setAteliers] = useState(ateliersList);
  const [loading, setLoading] = useState(true);
  const [inscriptionLoadingId, setInscriptionLoadingId] = useState(null);
  const [inscritsIds, setInscritsIds] = useState([]);
  const [apiError, setApiError] = useState("");
  const [search, setSearch] = useState("");
  const [categorie, setCategorie] = useState([]);
  const [duree, setDuree] = useState([]);
  const [prix, setPrix] = useState([]);

  useEffect(() => {
    let alive = true;
    setLoading(true);
    setApiError("");

    fetchAteliers()
      .then((data) => {
        if (!alive) return;
        const list = Array.isArray(data?.liste_atelier)
          ? data.liste_atelier
          : [];
        if (list.length > 0) {
          setAteliers(list);
        } else {
          setApiError("Aucune formation récupérée depuis le backend.");
        }
      })
      .catch(() => {
        if (!alive) return;
        setApiError("Backend indisponible, affichage des données locales.");
      })
      .finally(() => {
        if (alive) setLoading(false);
      });

    return () => {
      alive = false;
    };
  }, []);

  useEffect(() => {
    const role = String(publicUser?.role || "").toUpperCase();
    if (role !== "APPRENANT") {
      setInscritsIds([]);
      return;
    }

    fetchMesInscriptions()
      .then((data) => {
        const rows = Array.isArray(data?.inscriptions) ? data.inscriptions : [];
        setInscritsIds(rows.map((r) => Number(r.id)));
      })
      .catch(() => {
        setInscritsIds([]);
      });
  }, [publicUser]);

  const toggleValue = (value, setFn) => {
    setFn((prev) =>
      prev.includes(value) ? prev.filter((v) => v !== value) : [...prev, value],
    );
  };

  const filtered = useMemo(() => {
    let list = [...ateliers];

    if (search.trim()) {
      const q = search.toLowerCase();
      list = list.filter(
        (a) =>
          a.titre.toLowerCase().includes(q) ||
          a.description.toLowerCase().includes(q) ||
          a.formateur.toLowerCase().includes(q),
      );
    }

    if (categorie.length > 0) {
      list = list.filter((a) => categorie.includes(a.categorie));
    }

    if (duree.length > 0) {
      list = list.filter((a) => duree.includes(String(a.duree)));
    }

    if (prix.length > 0) {
      list = list.filter((a) => prix.some((r) => matchesPrix(a.prix, r)));
    }

    return list;
  }, [ateliers, search, categorie, duree, prix]);

  const resultsText = useMemo(() => {
    const n = filtered.length;
    if (n === 0) return "0 atelier disponible";
    return `${n} atelier${n > 1 ? "s" : ""} disponible${n > 1 ? "s" : ""}`;
  }, [filtered.length]);

  const resetFilters = () => {
    setSearch("");
    setCategorie([]);
    setDuree([]);
    setPrix([]);
  };

  const handleInscription = async (atelierId) => {
    if (!publicUser) {
      openModal("connexion");
      return;
    }

    const role = String(publicUser.role || "").toUpperCase();
    if (role !== "APPRENANT") {
      alert("Cette action est reservee aux apprenants.");
      return;
    }

    setInscriptionLoadingId(atelierId);
    try {
      const data = await inscrireAtelier(atelierId);
      alert(data?.message || "Inscription effectuee avec succes.");
      setInscritsIds((prev) =>
        prev.includes(atelierId) ? prev : [...prev, atelierId],
      );
      setAteliers((prev) =>
        prev.map((a) =>
          a.id === atelierId
            ? { ...a, apprenants: Number(a.apprenants || 0) + 1 }
            : a,
        ),
      );
    } catch (err) {
      const msg =
        err.response?.data?.message || "Erreur lors de l inscription.";
      alert(msg);
    } finally {
      setInscriptionLoadingId(null);
    }
  };

  return (
    <>
      <SiteHeader />
      <main>
        <section className="hero-search">
          <div className="container">
            <h1>Tous nos ateliers</h1>
            <p>
              Découvrez nos formations courtes et pratiques pour booster vos
              compétences
            </p>
            <div className="search-box">
              <input
                type="text"
                id="search-input"
                placeholder="Rechercher un atelier, une compétence, un formateur..."
                value={search}
                onChange={(e) => setSearch(e.target.value)}
              />
              <button type="button" className="btn btn-primary">
                Rechercher
              </button>
            </div>
          </div>
        </section>

        <section className="ateliers-section">
          <div className="container">
            <div className="ateliers-layout">
              <aside className="filters">
                <h2>Filtres</h2>
                <div className="filter-group">
                  <h3>Catégorie</h3>
                  {[
                    ["developpement", "Développement Web"],
                    ["design", "Design & UX"],
                    ["marketing", "Marketing Digital"],
                    ["soft-skills", "Soft Skills"],
                    ["data", "Data "],
                    ["creation-contenu", "Création de Contenu"],
                    ["entrepreneuriat", "Entrepreneuriat"],
                  ].map(([value, label]) => (
                    <label key={value}>
                      <input
                        type="checkbox"
                        name="categorie"
                        value={value}
                        checked={categorie.includes(value)}
                        onChange={() => toggleValue(value, setCategorie)}
                      />{" "}
                      {label}
                    </label>
                  ))}
                </div>

                <div className="filter-group">
                  <h3>Durée</h3>
                  {["2", "3", "4", "6", "8"].map((h) => (
                    <label key={h}>
                      <input
                        type="checkbox"
                        name="duree"
                        value={h}
                        checked={duree.includes(h)}
                        onChange={() => toggleValue(h, setDuree)}
                      />{" "}
                      {h} heures
                    </label>
                  ))}
                </div>

                <div className="filter-group">
                  <h3>Prix</h3>
                  {[
                    ["0-50", "0€ - 50€"],
                    ["50-100", "50€ - 100€"],
                    ["100-150", "100€ - 150€"],
                    ["150+", "Plus de 150€"],
                  ].map(([value, label]) => (
                    <label key={value}>
                      <input
                        type="checkbox"
                        name="prix"
                        value={value}
                        checked={prix.includes(value)}
                        onChange={() => toggleValue(value, setPrix)}
                      />{" "}
                      {label}
                    </label>
                  ))}
                </div>

                <button
                  type="button"
                  className="btn btn-full"
                  id="reset-filters"
                  onClick={resetFilters}
                >
                  Réinitialiser
                </button>
              </aside>

              <div className="ateliers-content">
                <div className="ateliers-header">
                  <p id="results-count">{resultsText}</p>
                  {loading && <p>Chargement des ateliers...</p>}
                  {!loading && apiError && (
                    <p style={{ color: "#fca5a5" }}>{apiError}</p>
                  )}
                </div>
                <div className="ateliers-grid" id="ateliers-grid">
                  {filtered.length === 0 ? (
                    <div className="no-results">
                      <h3>Aucun atelier trouvé</h3>
                      <p>
                        Essayez de modifier vos critères de recherche ou vos
                        filtres.
                      </p>
                    </div>
                  ) : (
                    filtered.map((atelier) => (
                      <div key={atelier.id} className="atelier-card">
                        <div className="atelier-category">
                          <i
                            className={`${getCategoryVisualByKey(atelier.categorie).icon} meta-icon`}
                            aria-hidden="true"
                          ></i>
                          {categories[atelier.categorie]}
                        </div>
                        <h3>{atelier.titre}</h3>
                        <p>{atelier.description}</p>
                        <div className="atelier-meta">
                          <span>
                            <i
                              className="fa-solid fa-clock meta-icon"
                              aria-hidden="true"
                            ></i>
                            {atelier.duree}h
                          </span>
                          <span>
                            <i
                              className="fa-solid fa-user-tie meta-icon"
                              aria-hidden="true"
                            ></i>
                            {atelier.formateur}
                          </span>
                          <span>
                            <i
                              className="fa-solid fa-signal meta-icon"
                              aria-hidden="true"
                            ></i>
                            {atelier.niveau}
                          </span>
                          <span>
                            <i
                              className="fa-solid fa-user-graduate meta-icon"
                              aria-hidden="true"
                            ></i>
                            {atelier.apprenants} apprenants
                          </span>
                          <span>
                            <i
                              className="fa-solid fa-eye meta-icon"
                              aria-hidden="true"
                            ></i>
                            {atelier.vues} vues
                          </span>
                        </div>
                        <div className="atelier-footer">
                          <div className="atelier-actions">
                            <Link
                              to={`/ateliers/${atelier.id}`}
                              className="btn atelier-btn atelier-btn-secondary"
                            >
                              Voir détails
                            </Link>
                            {inscritsIds.includes(Number(atelier.id)) ? (
                              <button
                                type="button"
                                className="btn atelier-btn"
                                disabled
                              >
                                Déjà inscrit
                              </button>
                            ) : (
                              <button
                                type="button"
                                className="btn btn-primary atelier-btn"
                                data-modal="inscription"
                                onClick={() => handleInscription(atelier.id)}
                                disabled={inscriptionLoadingId === atelier.id}
                              >
                                {inscriptionLoadingId === atelier.id
                                  ? "Inscription..."
                                  : "S'inscrire"}
                              </button>
                            )}
                          </div>
                        </div>
                      </div>
                    ))
                  )}
                </div>
              </div>
            </div>
          </div>
        </section>
      </main>
      <Footer />
    </>
  );
}
