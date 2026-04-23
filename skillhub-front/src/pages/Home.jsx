import { Link } from "react-router-dom";
import SiteHeader from "../components/SiteHeader";
import Footer from "../components/Footer";
import { useAuthModal } from "../contexts/AuthModalContext";
import "../styles/skillhub.css";

function HomeContent() {
  const { openModal } = useAuthModal();

  return (
    <main id="main">
      <section className="hero">
        <div className="container">
          <h1>Des ateliers courts pour une nouvelle vie pro</h1>
          <p>
            SkillHub connecte les personnes en reconversion avec des formateurs
            passionnés. Apprenez en 2 à 8 heures les compétences dont le marché
            a vraiment besoin.
          </p>
          <div className="hero-btns">
            <button
              type="button"
              className="btn btn-primary"
              data-modal="inscription"
              onClick={() => openModal("inscription")}
            >
              Commencer
            </button>
          </div>
        </div>
      </section>

      <section id="profiles" className="section">
        <div className="container">
          <h2>Choisissez votre parcours</h2>
          <div className="grid-2">
            <div className="card">
              <div className="badge">Apprenant·e</div>
              <h3>Je veux suivre des ateliers</h3>
              <p>
                Accédez à des sessions courtes et ciblées pour tester un métier,
                monter en compétences.
              </p>
              <button
                type="button"
                className="btn btn-primary"
                data-modal="inscription"
                onClick={() => openModal("inscription")}
              >
                Je m&apos;inscris
              </button>
            </div>
            <div className="card card-alt">
              <div className="badge badge-alt">Formateur·rice</div>
              <h3>Je veux créer des ateliers</h3>
              <p>
                Partagez votre expertise, trouvez des apprenants motivés et
                gérez vos sessions.
              </p>
              <button
                type="button"
                className="btn btn-primary"
                data-modal="inscription"
                onClick={() => openModal("inscription")}
              >
                Devenir formateur
              </button>
            </div>
          </div>
        </div>
      </section>

      <section id="comment-ca-marche" className="section section-gray">
        <div className="container">
          <h2>Comment ça marche ?</h2>
          <p className="subtitle">3 étapes simples</p>
          <div className="grid-3">
            <div className="card">
              <div className="icon">1</div>
              <h3>Créez votre profil</h3>
              <p>
                Inscrivez-vous en choisissant d&apos;être apprenant ou
                formateur..
              </p>
            </div>
            <div className="card">
              <div className="icon">2</div>
              <h3>Choisissez votre parcours</h3>
              <p>
                Sélectionnez un atelier si vous êtes apprenant, ou créez une
                formation si vous êtes formateur.
              </p>
            </div>
            <div className="card">
              <div className="icon">3</div>
              <h3>Validez et profitez</h3>
              <p>
                Confirmez votre choix pour commencer immédiatement votre
                expérience sur SkillHub.
              </p>
            </div>
          </div>
        </div>
      </section>

      <section id="formations" className="section">
        <div className="container">
          <h2>Découvrir nos formations</h2>
          <p className="subtitle">
            Des ateliers courts dans tous les domaines clés
          </p>
          <div className="grid-3">
            <div className="card">
              <div className="icon-large">
                <i className="fa-solid fa-code" aria-hidden="true"></i>
              </div>
              <h3>Développement Web</h3>
              <p>
                HTML, CSS, JavaScript, React... Maîtrisez les technologies du
                web.
              </p>
              <div className="tags">
                <span>Front-end</span>
                <span>Back-end</span>
                <span>Full-stack</span>
              </div>
            </div>
            <div className="card">
              <div className="icon-large">
                <i className="fa-solid fa-palette" aria-hidden="true"></i>
              </div>
              <h3>Design & UX</h3>
              <p>
                UI/UX Design, Figma, prototypage... Créez des interfaces
                intuitives.
              </p>
              <div className="tags">
                <span>UI Design</span>
                <span>UX Research</span>
                <span>Prototypage</span>
              </div>
            </div>
            <div className="card">
              <div className="icon-large">
                <i className="fa-solid fa-bullhorn" aria-hidden="true"></i>
              </div>
              <h3>Marketing Digital</h3>
              <p>
                SEO, réseaux sociaux, emailing... Développez votre présence en
                ligne.
              </p>
              <div className="tags">
                <span>SEO</span>
                <span>Social Media</span>
                <span>Content</span>
              </div>
            </div>
            <div className="card">
              <div className="icon-large">
                <i className="fa-solid fa-people-group" aria-hidden="true"></i>
              </div>
              <h3>Soft Skills</h3>
              <p>
                Communication, leadership, négociation... Développez vos
                compétences relationnelles.
              </p>
              <div className="tags">
                <span>Communication</span>
                <span>Leadership</span>
                <span>Projet</span>
              </div>
            </div>
            <div className="card">
              <div className="icon-large">
                <i className="fa-solid fa-rocket" aria-hidden="true"></i>
              </div>
              <h3>Entrepreneuriat</h3>
              <p>Business model, levée de fonds... Lancez votre projet.</p>
              <div className="tags">
                <span>Business Plan</span>
                <span>Financement</span>
                <span>Stratégie</span>
              </div>
            </div>
            <div className="card">
              <div className="icon-large">
                <i className="fa-solid fa-photo-film" aria-hidden="true"></i>
              </div>
              <h3>Création de Contenu</h3>
              <p>
                Vidéo, podcast, écriture... Créez du contenu engageant qui fait
                la différence sur tous les canaux.
              </p>
              <div className="tags">
                <span>Vidéo</span>
                <span>Podcast</span>
                <span>Rédaction</span>
              </div>
            </div>
          </div>
          <div className="center">
            <Link to="/ateliers" className="btn btn-primary">
              Voir nos formations
            </Link>
          </div>
        </div>
      </section>

      <section id="temoignages" className="section section-gray">
        <div className="container">
          <h2>TEMOIGNAGES</h2>
          <div className="grid-3">
            <div className="card">
              <div className="avatar">L</div>
              <h3>Sarah, 29 ans</h3>
              <p className="role">
                Ex-assistante administrative → Future développeuse
              </p>
              <p className="quote">
                « Les ateliers m&apos;ont aidée à découvrir le développement web
                étape par étape, tout en gardant mon travail. Ça m&apos;a donné
                la confiance nécessaire pour me lancer dans ma reconversion. »
              </p>
            </div>
            <div className="card">
              <div className="avatar">S</div>
              <h3>Yanis, 38 ans</h3>
              <p className="role">Coach en communication digitale</p>
              <p className="quote">
                « Grâce à SkillHub, je peux animer des sessions vraiment
                adaptées aux besoins des apprenants, tout en étant libéré des
                tâches administratives. »
              </p>
            </div>
            <div className="card">
              <div className="avatar">J</div>
              <h3>Emma, 27 ans</h3>
              <p className="role">Designer graphique → UI/UX Designer</p>
              <p className="quote">
                « Les ateliers m&apos;ont aidée autant sur le plan humain que
                technique : je me sens enfin à l&apos;aise pour présenter mes
                créations. »
              </p>
            </div>
          </div>
        </div>
      </section>
    </main>
  );
}

export default function Home() {
  return (
    <>
      <SiteHeader />
      <HomeContent />
      <Footer />
    </>
  );
}
