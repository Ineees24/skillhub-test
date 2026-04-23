import { useState, useMemo, useEffect } from "react";
import { Link, useNavigate } from "react-router-dom";
import Footer from "../components/Footer";
import {
  desinscrireAtelier,
  fetchMesInscriptions,
} from "../services/atelierService";
import { getUser, logout } from "../services/authService";
import { getCategoryVisualByKey } from "../utils/categoryVisuals";
import "./Dashboard.css";

const categories = {
  developpement: "Developpement",
  design: "Design",
  marketing: "Marketing",
  "soft-skills": "Soft Skills",
  "creation-contenu": "Creation de Contenu",
  entrepreneuriat: "Entrepreneuriat",
};

const priceRanges = [
  { value: "0-50", label: "0€ - 50€" },
  { value: "50-100", label: "50€ - 100€" },
  { value: "100-150", label: "100€ - 150€" },
  { value: "150+", label: "Plus de 150€" },
];

const durationOptions = [2, 3, 4, 6, 8];

function mapAteliersToDashboard(rows) {
  return rows.map((formation, index) => {
    const termine = index % 3 === 0;
    const progression = termine ? 100 : 20 + ((index * 13) % 70);

    return {
      id: formation.id,
      titre: formation.titre,
      description: formation.description || "",
      categorie: formation.categorie || "developpement",
      duree: Number(formation.duree || 0),
      prix: Number(formation.prix || 0),
      statut: termine ? "termine" : "en-cours",
      dateInscription: new Date().toISOString().slice(0, 10),
      progression,
    };
  });
}

function matchesPriceRange(prix, range) {
  if (range === "0-50") return prix >= 0 && prix <= 50;
  if (range === "50-100") return prix > 50 && prix <= 100;
  if (range === "100-150") return prix > 100 && prix <= 150;
  if (range === "150+") return prix > 150;

  return false;
}

function matchesSelectedPriceRanges(prix, selectedPrices) {
  return selectedPrices.some((range) => matchesPriceRange(prix, range));
}

function matchesSearch(formation, search) {
  if (!search) return true;

  const normalizedSearch = search.toLowerCase();
  return (
    formation.titre.toLowerCase().includes(normalizedSearch) ||
    formation.description.toLowerCase().includes(normalizedSearch)
  );
}

function matchesFilters(formation, filters) {
  const {
    search,
    selectedCategories,
    selectedDurations,
    selectedPrices,
    selectedStatuts,
  } = filters;

  if (!matchesSearch(formation, search)) {
    return false;
  }

  if (
    selectedCategories.length > 0 &&
    !selectedCategories.includes(formation.categorie)
  ) {
    return false;
  }

  if (
    selectedDurations.length > 0 &&
    !selectedDurations.includes(formation.duree.toString())
  ) {
    return false;
  }

  if (
    selectedPrices.length > 0 &&
    !matchesSelectedPriceRanges(formation.prix, selectedPrices)
  ) {
    return false;
  }

  if (
    selectedStatuts.length > 0 &&
    !selectedStatuts.includes(formation.statut)
  ) {
    return false;
  }

  return true;
}
function removeFormationById(formationsList, id) {
  return formationsList.filter((formation) => formation.id !== id);
}

function DashboardApprenant() {
  const navigate = useNavigate();
  const user = getUser();
  const [formations, setFormations] = useState([]);
  const [loading, setLoading] = useState(true);
  const [apiError, setApiError] = useState("");
  const [search, setSearch] = useState("");
  const [selectedCategories, setSelectedCategories] = useState([]);
  const [selectedDurations, setSelectedDurations] = useState([]);
  const [selectedPrices, setSelectedPrices] = useState([]);
  const [selectedStatuts, setSelectedStatuts] = useState([]);

  useEffect(() => {
    let alive = true;

    fetchMesInscriptions()
      .then((data) => {
        if (!alive) return;
        const inscriptions = Array.isArray(data?.inscriptions)
          ? data.inscriptions
          : [];
        setFormations(inscriptions);
        if (inscriptions.length === 0) {
          setApiError("Vous n'etes inscrit a aucune formation pour le moment.");
        }
      })
      .catch(() => {
        if (!alive) return;
        setApiError("Impossible de charger vos inscriptions pour le moment.");
        setFormations([]);
      })
      .finally(() => {
        if (alive) setLoading(false);
      });

    return () => {
      alive = false;
    };
  }, []);

  const filteredFormations = useMemo(() => {
    return formations.filter((formation) =>
      matchesFilters(formation, {
        search,
        selectedCategories,
        selectedDurations,
        selectedPrices,
        selectedStatuts,
      }),
    );
  }, [
    formations,
    search,
    selectedCategories,
    selectedDurations,
    selectedPrices,
    selectedStatuts,
  ]);

  const handleCategoryChange = (categorie) => {
    setSelectedCategories((prev) =>
      prev.includes(categorie)
        ? prev.filter((c) => c !== categorie)
        : [...prev, categorie],
    );
  };

  const handleDurationChange = (duree) => {
    setSelectedDurations((prev) =>
      prev.includes(duree) ? prev.filter((d) => d !== duree) : [...prev, duree],
    );
  };

  const handlePriceChange = (price) => {
    setSelectedPrices((prev) =>
      prev.includes(price) ? prev.filter((p) => p !== price) : [...prev, price],
    );
  };

  const handleStatutChange = (statut) => {
    setSelectedStatuts((prev) =>
      prev.includes(statut)
        ? prev.filter((s) => s !== statut)
        : [...prev, statut],
    );
  };

  const resetFilters = () => {
    setSearch("");
    setSelectedCategories([]);
    setSelectedDurations([]);
    setSelectedPrices([]);
    setSelectedStatuts([]);
  };

  const handleSuivre = (id) => {
    navigate(`/apprenant/suivi/${id}`);
  };

  const handleLogout = async () => {
    try {
      await logout();
      navigate("/");
    } catch (error) {
      console.error("Erreur lors de la deconnexion", error);
    }
  };

  const handleNePlusSuivre = (id) => {
    const ok = globalThis.confirm(
      "Voulez-vous vraiment ne plus suivre cette formation ?",
    );
    if (!ok) return;

    desinscrireAtelier(id)
      .then(() => {
        setFormations(removeFormationById(formations, id));
      })
      .catch((err) => {
        const msg =
          err.response?.data?.message || "Erreur lors de la désinscription.";
        globalThis.alert(msg);
      });
  };

  const stats = {
    total: formations.length,
    enCours: formations.filter((f) => f.statut === "en-cours").length,
    termine: formations.filter((f) => f.statut === "termine").length,
    progressionMoyenne: Math.round(
      formations.reduce((sum, f) => sum + f.progression, 0) /
        (formations.length || 1),
    ),
  };

  return (
    <div className="dashboard">
      <header className="dashboard-header">
        <div className="container">
          <h1 className="dashboard-title">
            <i className="fa-solid fa-user-graduate" aria-hidden="true"></i>{" "}
            Bonjour {user?.prenom} {user?.nom}
          </h1>
          <nav className="dashboard-nav">
            <Link to="/">Accueil</Link>
            <Link to="/apprenant" className="active">
              Mes formations
            </Link>
            <Link to="/ateliers">Catalogue</Link>
            <button onClick={handleLogout} className="btn-reset btn-logout">
              Deconnexion
            </button>
          </nav>
        </div>
      </header>

      <div className="container dashboard-content">
        <div className="dashboard-stats">
          <div className="stat-card">
            <i
              className="fa-solid fa-book-open stat-icon"
              aria-hidden="true"
            ></i>
            <h3>{stats.total}</h3>
            <p>Formations totales</p>
          </div>
          <div className="stat-card">
            <i className="fa-solid fa-play stat-icon" aria-hidden="true"></i>
            <h3>{stats.enCours}</h3>
            <p>En cours</p>
          </div>
          <div className="stat-card">
            <i
              className="fa-solid fa-circle-check stat-icon"
              aria-hidden="true"
            ></i>
            <h3>{stats.termine}</h3>
            <p>Terminées</p>
          </div>
          <div className="stat-card">
            <i
              className="fa-solid fa-chart-line stat-icon"
              aria-hidden="true"
            ></i>
            <h3>{stats.progressionMoyenne} %</h3>
            <p>Progression moyenne</p>
          </div>
        </div>

        <div className="search-section">
          <input
            type="text"
            placeholder="Rechercher une formation..."
            value={search}
            onChange={(e) => setSearch(e.target.value)}
            className="search-input"
          />
        </div>

        <div className="dashboard-layout">
          <aside className="filters-sidebar">
            <h2>Filtres</h2>

            <div className="filter-group">
              <h3>Statut</h3>
              <label>
                <input
                  type="checkbox"
                  checked={selectedStatuts.includes("en-cours")}
                  onChange={() => handleStatutChange("en-cours")}
                />{" "}
                En cours
              </label>
              <label>
                <input
                  type="checkbox"
                  checked={selectedStatuts.includes("termine")}
                  onChange={() => handleStatutChange("termine")}
                />{" "}
                Terminé
              </label>
            </div>

            <div className="filter-group">
              <h3>Catégorie</h3>
              {Object.entries(categories).map(([key, label]) => (
                <label key={key}>
                  <input
                    type="checkbox"
                    checked={selectedCategories.includes(key)}
                    onChange={() => handleCategoryChange(key)}
                  />{" "}
                  {label}
                </label>
              ))}
            </div>

            <div className="filter-group">
              <h3>Durée</h3>
              {durationOptions.map((duree) => (
                <label key={duree}>
                  <input
                    type="checkbox"
                    checked={selectedDurations.includes(duree.toString())}
                    onChange={() => handleDurationChange(duree.toString())}
                  />{" "}
                  {duree} heures
                </label>
              ))}
            </div>

            <div className="filter-group">
              <h3>Prix</h3>
              {priceRanges.map((range) => (
                <label key={range.value}>
                  <input
                    type="checkbox"
                    checked={selectedPrices.includes(range.value)}
                    onChange={() => handlePriceChange(range.value)}
                  />{" "}
                  {range.label}
                </label>
              ))}
            </div>

            <button onClick={resetFilters} className="btn-reset">
              Réinitialiser
            </button>
          </aside>

          <main className="formations-list">
            <div className="results-header">
              <p>
                {filteredFormations.length} formation
                {filteredFormations.length > 1 ? "s" : ""} trouvée
                {filteredFormations.length > 1 ? "s" : ""}
              </p>
              {loading && <p>Chargement des formations...</p>}
              {!loading && apiError && (
                <p style={{ color: "#fca5a5" }}>{apiError}</p>
              )}
            </div>

            {filteredFormations.length === 0 ? (
              <div className="no-results">
                <h3>Aucune formation trouvée</h3>
                <p>Essayez de modifier vos critères de recherche</p>
              </div>
            ) : (
              <div className="formations-grid">
                {filteredFormations.map((formation) => (
                  <div key={formation.id} className="formation-card">
                    <div className="formation-header">
                      <span className={`badge badge-${formation.statut}`}>
                        {formation.statut === "en-cours"
                          ? "En cours"
                          : "Terminé"}
                      </span>
                      <span className="formation-category">
                        <i
                          className={`${getCategoryVisualByKey(formation.categorie).icon} meta-icon`}
                          aria-hidden="true"
                        ></i>{" "}
                        {categories[formation.categorie]}
                      </span>
                    </div>

                    <h3>{formation.titre}</h3>
                    <p className="formation-description">
                      {formation.description}
                    </p>

                    <div className="formation-meta">
                      <span>
                        <i
                          className="fa-solid fa-clock meta-icon"
                          aria-hidden="true"
                        ></i>{" "}
                        {formation.duree}h
                      </span>
                      <span className="formation-price">{formation.prix}€</span>
                    </div>

                    {formation.progression !== undefined && (
                      <div className="progression-bar">
                        <div
                          className="progression-fill"
                          style={{ width: `${formation.progression}%` }}
                        ></div>
                        <span className="progression-text">
                          {formation.progression}% complété
                        </span>
                      </div>
                    )}

                    <p className="formation-date">
                      Inscrit le{" "}
                      {new Date(formation.dateInscription).toLocaleDateString(
                        "fr-FR",
                      )}
                    </p>

                    <div className="formation-actions">
                      <button
                        onClick={() => handleSuivre(formation.id)}
                        className="btn-follow"
                      >
                        Suivre
                      </button>
                      <button
                        onClick={() => handleNePlusSuivre(formation.id)}
                        className="btn-unfollow"
                      >
                        Ne plus suivre
                      </button>
                    </div>
                  </div>
                ))}
              </div>
            )}
          </main>
        </div>
      </div>
      <Footer />
    </div>
  );
}

export default DashboardApprenant;
