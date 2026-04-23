import { useEffect, useRef, useState } from "react";
import { useLocation, useNavigate } from "react-router-dom";
import PropTypes from "prop-types";
import { login, register } from "../services/authService";

function setFieldBorder(field, valid) {
  if (!field) return;
  field.style.borderColor = valid ? "#374151" : "#ef4444";
}

function apiErrorMessage(err) {
  const d = err.response?.data;
  if (!d) return err.message || "Erreur réseau";
  if (typeof d.error === "string") return d.error;
  if (d.errors) {
    const first = Object.values(d.errors)[0];
    return Array.isArray(first) ? first[0] : String(first);
  }
  return "Erreur";
}

function splitNomPrenom(fullName) {
  const t = fullName.trim();
  if (!t) return { nom: "", prenom: "" };
  const parts = t.split(/\s+/);
  if (parts.length === 1) return { nom: parts[0], prenom: "-" };
  return { nom: parts[0], prenom: parts.slice(1).join(" ") };
}

export default function AuthModals({
  modal,
  modalData,
  onClose,
  openModal,
  refreshPublicSession,
}) {
  const formConnexionRef = useRef(null);
  const formInscriptionRef = useRef(null);

  const location = useLocation();
  const navigate = useNavigate();

  const [connError, setConnError] = useState("");
  const [connLoading, setConnLoading] = useState(false);
  const [connEmail, setConnEmail] = useState("");

  const [regError, setRegError] = useState("");
  const [regLoading, setRegLoading] = useState(false);

  const inscriptionSubtitle =
    location.pathname === "/ateliers"
      ? "Créez votre compte en moins de 2 minutes."
      : "Créez votre compte.";

  useEffect(() => {
    const onKey = (e) => {
      if (e.key === "Escape") onClose();
    };
    if (modal) document.addEventListener("keydown", onKey);
    return () => document.removeEventListener("keydown", onKey);
  }, [modal, onClose]);

  useEffect(() => {
    if (modal) {
      setConnError("");
      setRegError("");
    }
  }, [modal]);

  useEffect(() => {
    if (modal === "connexion") {
      setConnEmail(modalData?.prefillEmail ?? "");
    }
  }, [modal, modalData]);

  const redirectAfterPublicAuth = () => {
    onClose();
    refreshPublicSession();
    navigate("/");
  };

  return (
    <>
      <dialog
        className={`modal${modal === "connexion" ? " active" : ""}`}
        id="modal-connexion"
        aria-modal="true"
      >
        <button
          type="button"
          className="modal-backdrop-close"
          onClick={onClose}
          aria-label="Fermer la fenetre de connexion"
        />
        <div className="modal-content">
          <button
            type="button"
            className="close"
            id="close-connexion"
            onClick={onClose}
            aria-label="Fermer"
          >
            ×
          </button>

          <h2>Connexion</h2>
          <p>Connectez-vous à votre compte SkillHub</p>

          {connError && (
            <p
              style={{
                color: "#f87171",
                marginBottom: "12px",
                fontSize: "0.9rem",
              }}
            >
              {connError}
            </p>
          )}

          <form
            id="form-connexion"
            ref={formConnexionRef}
            onSubmit={async (e) => {
              e.preventDefault();
              setConnError("");
              const form = formConnexionRef.current;
              if (!form) return;
              const required = form.querySelectorAll("[required]");
              let valid = true;
              required.forEach((field) => {
                if (field.value) {
                  setFieldBorder(field, true);
                } else {
                  setFieldBorder(field, false);
                  valid = false;
                }
              });
              if (!valid) return;
              const email = connEmail.trim();
              const password =
                form.querySelector("#login-password")?.value ?? "";
              setConnLoading(true);
              try {
                await login(email, password);
                form.reset();
                redirectAfterPublicAuth();
              } catch (err) {
                setConnError(apiErrorMessage(err));
              } finally {
                setConnLoading(false);
              }
            }}
          >
            <div>
              <label htmlFor="login-email">Email</label>
              <input
                id="login-email"
                type="email"
                required
                placeholder="votre@email.com"
                value={connEmail}
                onChange={(e) => setConnEmail(e.target.value)}
                disabled={connLoading}
              />
            </div>

            <div>
              <label htmlFor="login-password">Mot de passe</label>
              <input
                id="login-password"
                type="password"
                required
                placeholder="••••••••"
                disabled={connLoading}
              />
            </div>

            <div>
              <label>
                <input type="checkbox" disabled={connLoading} /> Se souvenir de
                moi
              </label>
            </div>

            <button
              type="submit"
              className="btn btn-primary"
              disabled={connLoading}
            >
              {connLoading ? "Connexion…" : "Se connecter"}
            </button>

            <p
              style={{
                textAlign: "center",
                marginTop: "15px",
                fontSize: "0.9rem",
              }}
            >
              Pas encore de compte ?{" "}
              <button
                type="button"
                id="switch-to-signup"
                style={{
                  color: "#6366f1",
                  background: "none",
                  border: "none",
                  cursor: "pointer",
                  textDecoration: "underline",
                  fontSize: "inherit",
                }}
                onClick={() => openModal("inscription")}
              >
                S&apos;inscrire
              </button>
            </p>
          </form>
        </div>
      </dialog>

      <dialog
        className={`modal${modal === "inscription" ? " active" : ""}`}
        id="modal-inscription"
        aria-modal="true"
      >
        <button
          type="button"
          className="modal-backdrop-close"
          onClick={onClose}
          aria-label="Fermer la fenetre d'inscription"
        />
        <div className="modal-content">
          <button
            type="button"
            className="close"
            id="close-inscription"
            onClick={onClose}
            aria-label="Fermer"
          >
            ×
          </button>

          <h2>Rejoindre SkillHub</h2>
          <p id="inscription-subtitle">{inscriptionSubtitle}</p>

          {regError && (
            <p
              style={{
                color: "#f87171",
                marginBottom: "12px",
                fontSize: "0.9rem",
              }}
            >
              {regError}
            </p>
          )}

          <form
            id="form-inscription"
            ref={formInscriptionRef}
            onSubmit={async (e) => {
              e.preventDefault();
              setRegError("");
              const form = formInscriptionRef.current;
              if (!form) return;
              const required = form.querySelectorAll("[required]");
              let valid = true;
              required.forEach((field) => {
                const isCheckbox = field.type === "checkbox";
                const ok = isCheckbox ? field.checked : field.value;
                if (ok) {
                  setFieldBorder(field, true);
                } else {
                  setFieldBorder(field, false);
                  valid = false;
                }
              });
              if (!valid) return;
              const role = form.querySelector("#signup-role")?.value ?? "";
              const fullName =
                form.querySelector("#signup-name")?.value?.trim() ?? "";
              const email =
                form.querySelector("#signup-email")?.value?.trim() ?? "";
              const password =
                form.querySelector("#signup-password")?.value ?? "";
              const { nom, prenom } = splitNomPrenom(fullName);
              if (!nom) {
                setRegError("Indiquez au moins un nom.");
                return;
              }
              setRegLoading(true);
              try {
                await register({ nom, prenom, email, password, role });
                form.reset();
                openModal("connexion", { prefillEmail: email });
              } catch (err) {
                setRegError(apiErrorMessage(err));
              } finally {
                setRegLoading(false);
              }
            }}
          >
            <div>
              <label htmlFor="signup-role">Je suis :</label>
              <select
                id="signup-role"
                required
                defaultValue=""
                disabled={regLoading}
              >
                <option value="">Choisissez...</option>
                <option value="APPRENANT">Apprenant·e</option>
                <option value="FORMATEUR">Formateur·rice</option>
              </select>
            </div>

            <div>
              <label htmlFor="signup-name">Nom et prénom</label>
              <input
                id="signup-name"
                type="text"
                required
                disabled={regLoading}
              />
            </div>

            <div>
              <label htmlFor="signup-email">Email</label>
              <input
                id="signup-email"
                type="email"
                required
                disabled={regLoading}
              />
            </div>

            <div>
              <label htmlFor="signup-password">Mot de passe</label>
              <input
                id="signup-password"
                type="password"
                required
                minLength={6}
                placeholder="Minimum 6 caractères"
                disabled={regLoading}
              />
            </div>

            <div>
              <label htmlFor="signup-objectif">Objectif</label>
              <textarea
                id="signup-objectif"
                rows={3}
                placeholder="Ex : Devenir développeur en 12 mois"
                disabled={regLoading}
              />
            </div>

            <div>
              <span>Format :</span>
              <label>
                <input
                  type="radio"
                  name="format"
                  value="visio"
                  disabled={regLoading}
                />{" "}
                Visio
              </label>
              <label>
                <input
                  type="radio"
                  name="format"
                  value="presentiel"
                  disabled={regLoading}
                />{" "}
                Présentiel
              </label>
              <label>
                <input
                  type="radio"
                  name="format"
                  value="both"
                  disabled={regLoading}
                />{" "}
                Peu importe
              </label>
            </div>

            <div>
              <label>
                <input type="checkbox" required disabled={regLoading} />{" "}
                J&apos;accepte la politique RGPD
              </label>
            </div>

            <button
              type="submit"
              className="btn btn-primary"
              disabled={regLoading}
            >
              {regLoading ? "Création…" : "Créer mon compte"}
            </button>

            <p
              style={{
                textAlign: "center",
                marginTop: "15px",
                fontSize: "0.9rem",
              }}
            >
              Déjà un compte ?{" "}
              <button
                type="button"
                id="switch-to-login"
                style={{
                  color: "#6366f1",
                  background: "none",
                  border: "none",
                  cursor: "pointer",
                  textDecoration: "underline",
                  fontSize: "inherit",
                }}
                onClick={() =>
                  openModal("connexion", {
                    prefillEmail:
                      formInscriptionRef.current
                        ?.querySelector("#signup-email")
                        ?.value?.trim() ?? "",
                  })
                }
              >
                Se connecter
              </button>
            </p>
          </form>
        </div>
      </dialog>
    </>
  );
}

AuthModals.propTypes = {
  modal: PropTypes.string,
  modalData: PropTypes.shape({
    prefillEmail: PropTypes.string,
  }),
  onClose: PropTypes.func.isRequired,
  openModal: PropTypes.func.isRequired,
  refreshPublicSession: PropTypes.func.isRequired,
};

AuthModals.defaultProps = {
  modal: null,
  modalData: null,
};
