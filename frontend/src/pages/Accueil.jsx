import { useState, useEffect } from "react";

const images = [
  "/images/photo%20fond1.jpeg",
  "/images/photo%20fond2.jpg.jpeg",
  "/images/photo%20fond3.jpg.jpeg",
  "/images/photo%20fond4.jpg.jpeg",
  "/images/photo%20fond7.jpg.jpeg",
  "/images/phtot%20fond5.jpg.jpeg",
];

export default function Accueil({ onNavigateToAdmin, onNavigateToRegister }) {
  const [index, setIndex] = useState(0);

  useEffect(() => {
    const timer = setInterval(() => setIndex((i) => (i + 1) % images.length), 7000);
    return () => clearInterval(timer);
  }, []);

  return (
    <div className="page-container">
      <section className="hero-section">
        
        {/* BOUTON HAUT DROITE : Réservé aux Agents/Admins */}
        <button 
          onClick={() => {
            console.log("✅ Navigation vers Admin");
            onNavigateToAdmin();
          }} 
          className="btn hero-login-btn"
          title="Accès réservé au personnel de l'ANPS"
        >
          Se connecter
        </button>

        <div className="hero-carousel">
          {images.map((src, i) => (
            <img 
              key={src} 
              src={src} 
              className={`carousel-img ${i === index ? "active" : ""}`} 
              alt="Artisan béninois au travail" 
            />
          ))}
          <div className="hero-overlay" />
        </div>
        
        <div className="hero-inner-content">
          <span className="badge-tag">Programme ARCH</span>
          <h1>
            La plateforme officielle de l'ANPS pour <span className="highlight">évaluer</span>, <span className="highlight">orienter</span> et <span className="highlight">former</span> les artisans du Bénin
          </h1>
          <p className="hero-description">
            Passez votre test de compétence en ligne et intégrez le centre de formation le mieux adapté à votre métier, dans le cadre du programme national ARCH.
          </p>
          
          <div className="action-buttons">
            {/* BOUTON PRINCIPAL : Mène directement à l'inscription */}
            <button 
              onClick={() => {
                console.log("✅ Navigation vers Inscription");
                onNavigateToRegister();
              }} 
              className="btn btn-primary"
            >
              Inscrivez-vous à un test
            </button>
            
            <button className="btn btn-secondary">En savoir plus</button>
          </div>

          <div className="social-proof">
            <span className="star-icon">★★★★★</span> 
            <p><strong>+300 artisans</strong> ont déjà validé leurs compétences</p>
          </div>
        </div>
      </section>

      {/* FOOTER */}
      <footer className="site-footer">
        <div className="benin-flag-ribbon" />
        <div className="footer-content">
          <div className="footer-grid">
            <div className="footer-column">
              <h3>LE PROJET ARCH</h3>
              <ul>
                <li><a href="#presentation">Présentation du projet ARCH</a></li>
                <li><a href="#objectifs">Objectif du projet ARCH</a></li>
                <li><a href="#beneficiaires">Bénéficiaires, Résultats et Impacts</a></li>
                <li><a href="#orientations">Orientations stratégiques</a></li>
              </ul>
            </div>
            <div className="footer-column">
              <h3>L'ANPS</h3>
              <ul>
                <li><a href="#anps">Présentation de l'ANPS</a></li>
                <li><a href="#missions">Missions et attributions</a></li>
                <li><a href="#organisation">Organisation et fonctionnement</a></li>
              </ul>
            </div>
            <div className="footer-column">
              <h3>MÉDIAS</h3>
              <ul>
                <li><a href="#actualites">Actualités</a></li>
                <li><a href="#communiques">Communiqués</a></li>
                <li><a href="#galerie-photos">Galerie Photos</a></li>
                <li><a href="#galerie-videos">Galerie Vidéos</a></li>
              </ul>
            </div>
            <div className="footer-column">
              <h3>DOCUMENTATION</h3>
              <ul>
                <li><a href="#rapports">Rapports</a></li>
                <li><a href="#documents">Documents</a></li>
                <li className="links-title">LIENS UTILES</li>
                <li><a href="#ministere">Ministère des Affaires Sociales et de la Microfinance</a></li>
                <li><a href="#sante">Ministère de la Santé</a></li>
              </ul>
            </div>
            <div className="footer-column contact-col">
              <h3>NOUS CONTACTER</h3>
              <ul className="contact-list">
                <li><strong>Téléphone:</strong><br/>+229 20 21 34 14</li>
                <li><strong>E-mail:</strong><br/>masm.anps@gouv.bj</li>
                <li><strong>Adresse:</strong><br/>Immeuble HOUNDEKON,<br/>Quartier Sainte Rita</li>
                <li className="geoloc"><a href="#geoloc">Géolocaliser l'ANPS →</a></li>
              </ul>
            </div>
          </div>
        </div>
        <div className="footer-bottom-bar">
          <div className="footer-bottom-inner">
            <div className="social-icons" aria-hidden>
              <span className="social-icon" title="Facebook">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M22 12.07C22 6.48 17.52 2 11.93 2S2 6.48 2 12.07c0 4.99 3.66 9.13 8.44 9.93v-7.03H8.07v-2.9h2.37V9.41c0-2.34 1.4-3.63 3.55-3.63 1.03 0 2.11.18 2.11.18v2.32h-1.19c-1.17 0-1.53.73-1.53 1.48v1.78h2.6l-.42 2.9h-2.18V22c4.78-.8 8.44-4.94 8.44-9.93z" fill="currentColor"/></svg>
              </span>
              <span className="social-icon" title="YouTube">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M23.5 6.2s-.23-1.63-.94-2.35C21.29 3 18.96 3 18.96 3h-13.9s-2.33 0-3.6.85C.73 4.57.5 6.2.5 6.2S.27 8 .27 9.77v2.46c0 1.77.23 3.57.23 3.57s.23 1.63.94 2.35c.92.95 2.02.92 2.52 1.02 1.82.28 7.74.28 7.74.28s2.33 0 3.6-.85c.71-.48.94-2.35.94-2.35s.23-1.8.23-3.57V9.77c0-1.77-.23-3.57-.23-3.57zM9.75 15.02V8.98l6.02 3.02-6.02 3.02z" fill="currentColor"/></svg>
              </span>
              <span className="social-icon" title="Flickr">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><circle cx="7" cy="12" r="3" fill="currentColor"/><circle cx="17" cy="12" r="3" fill="currentColor"/></svg>
              </span>
            </div>
            <div className="copy-line">© {new Date().getFullYear()} ANPS | Mentions Légales &nbsp;&nbsp; Administration</div>
          </div>
        </div>
      </footer>

      <style>{`
        html, body, #root { height: 100%; margin: 0; padding: 0; }
        .page-container { font-family: 'Montserrat', sans-serif; color: #1e293b; background-color: #f8fafc; margin: 0; padding: 0; min-height: 100vh; display: flex; flex-direction: column; }
        .hero-section { position: relative; padding: 120px 5%; min-height: 500px; display: flex; align-items: center; overflow: hidden; flex: 1 0 auto; }
        .hero-carousel { position: absolute; inset: 0; z-index: 0; }
        .carousel-img { position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover; opacity: 0; transition: opacity 1.8s ease-in-out; pointer-events: none; will-change: opacity; }
        .carousel-img.active { opacity: 1; }
        .hero-overlay { position: absolute; inset: 0; background: linear-gradient(90deg, rgba(15,23,42,0.5) 0%, rgba(255,255,255,0.42) 60%); pointer-events: none; z-index: 1; backdrop-filter: saturate(1.2) brightness(1.05); }
        .hero-inner-content { position: relative; z-index: 10; max-width: 780px; width: 100%; }
        .badge-tag { display: inline-block; background: #dbeafe; color: #1e40af; padding: 6px 14px; border-radius: 50px; font-size: 0.85rem; font-weight: 600; margin-bottom: 20px; text-transform: uppercase; letter-spacing: 0.5px; }
        .hero-inner-content h1 { font-size: 2.8rem; font-weight: 800; line-height: 1.25; color: #0f172a; margin: 0 0 20px 0; }
        .highlight { color: #0284c7; position: relative; }
        .hero-description { font-size: 1.15rem; line-height: 1.6; color: #475569; margin-bottom: 35px; }
        .action-buttons { display: flex; gap: 16px; margin-bottom: 30px; flex-wrap: wrap; }
        .btn { min-width: 180px; text-align: center; padding: 14px 28px; font-size: 1rem; font-weight: 600; border-radius: 8px; cursor: pointer; transition: all 0.2s ease; border: none; }
        .btn-primary { background: #0f172a; color: #ffffff; box-shadow: 0 4px 6px -1px rgba(15, 23, 42, 0.3); }
        .btn-primary:hover { background: #1e293b; transform: translateY(-1px); }
        .btn-secondary { background: #ffffff; color: #334155; border: 1px solid #cbd5e1 !important; }
        .btn-secondary:hover { background: #f1f5f9; border-color: #94a3b8 !important; }
        .social-proof { display: flex; align-items: center; gap: 10px; }
        .star-icon { color: #eab308; font-size: 1.1rem; letter-spacing: 2px; }
        .social-proof p { margin: 0; font-size: 0.95rem; color: #64748b; }
        .site-footer { background: #091322; color: #f1f5f9; margin-top: 80px; width: 100%; margin-left: 0; margin-right: 0; flex-shrink: 0; }
        .benin-flag-ribbon { height: 5px; width: 100%; background: linear-gradient(to right, #008751 33.33%, #ffeb3b 66.66%, #e81123 100%); }
        .hero-login-btn { position: absolute; top: 12px; right: 5%; z-index: 1200; padding: 10px 18px; font-size: 0.9rem; font-weight: 700; border-radius: 8px; background: rgba(255,255,255,0.95); color: #0f172a; border: 2px solid rgba(15,23,42,0.95); font-family: 'Montserrat', sans-serif; text-decoration: none; display: inline-flex; align-items: center; justify-content: center; box-shadow: 0 6px 12px rgba(15,23,42,0.12); transition: transform 0.18s ease, background 0.15s ease, color 0.15s ease; cursor: pointer; }
        .hero-login-btn:hover { transform: translateY(-3px); background: #0f172a; color: #ffffff; border-color: #0f172a; }
        @media (max-width: 768px) { 
          .hero-inner-content h1 { font-size: 2rem; } 
          .hero-overlay { background: rgba(255, 255, 255, 0.92); } 
          .hero-login-btn { right: 4%; top: 10px; padding: 8px 12px; font-size: 0.85rem; } 
        }
      `}</style>
    </div>
  );
}