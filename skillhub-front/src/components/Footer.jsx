import { Link } from "react-router-dom";
import "./Footer.css";

function Footer() {
  return (
    <footer className="footer">
      <div className="container">
        <div className="grid-4">
          <div>
            <div className="logo">SkillHub</div>
            <p>La plateforme d&apos;ateliers courts pour votre reconversion.</p>
          </div>
          <div>
            <h3>Liens</h3>
            <ul>
              <li>
                <Link to="/#comment-ca-marche">Comment ça marche</Link>
              </li>
              <li>
                <Link to="/ateliers">Formations</Link>
              </li>
              <li>
                <Link to="/#temoignages">Témoignages</Link>
              </li>
            </ul>
          </div>
          <div>
            <h3>Légal</h3>
            <ul>
              <li>
                <Link to="/mentions-legales">Mentions légales</Link>
              </li>
              <li>
                <Link to="/rgpd">RGPD</Link>
              </li>
              <li>
                <Link to="/cookies">Cookies</Link>
              </li>
            </ul>
          </div>
          <div>
            <h3>Réseaux</h3>
            <ul>
              <li>
                <a
                  href="https://www.linkedin.com"
                  target="_blank"
                  rel="noreferrer"
                >
                  LinkedIn
                </a>
              </li>
              <li>
                <a href="https://x.com" target="_blank" rel="noreferrer">
                  Twitter
                </a>
              </li>
              <li>
                <a
                  href="https://www.instagram.com"
                  target="_blank"
                  rel="noreferrer"
                >
                  Instagram
                </a>
              </li>
            </ul>
          </div>
        </div>
        <p className="footer-bottom">
          &copy; <span id="year">{new Date().getFullYear()}</span> SkillHub.
          Tous droits réservés.
        </p>
      </div>
    </footer>
  );
}

export default Footer;
