import { useState, useEffect } from "react";
import { Link } from "react-router-dom";

// Mettez ici les chemins vers vos photos d'artisans au travail
const images = [
  "/images/photo%20fond1.jpeg",
  "/images/photo%20fond2.jpg.jpeg",
  "/images/photo%20fond3.jpg.jpeg",
  "/images/photo%20fond4.jpg.jpeg",
  "/images/photo%20fond7.jpg.jpeg",
  "/images/phtot%20fond5.jpg.jpeg",
];

export default function Accueil() {
  const [index, setIndex] = useState(0);

  useEffect(() => {
    // Gestionnaire du fondu enchaîné (Toutes les 7 secondes)
    const timer = setInterval(() => setIndex((i) => (i + 1) % images.length), 7000);
    return () => clearInterval(timer);
  }, []);

  return (
    <div className="page-container">
      {/* 2. SECTION HERO DYNAMIQUE */}
      <section className="hero-section">
        <Link to="/connexionartisan" className="btn hero-login-btn">Se connecter</Link>
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
            <Link to="/inscriptionartisan" className="btn btn-primary">Inscrivez-vous à un test</Link>
            <button className="btn btn-secondary">Découvrir la plateforme</button>
          </div>
          <div className="social-proof">
            <span className="star-icon">★★★★★</span> 
            <p><strong>+300 artisans</strong> ont déjà validé leurs compétences</p>
          </div>
        </div>
      </section>

      {/* 3. DOUBLE FOOTER (RUBAN NATIONAL + FOOTER ARTISKILLS) — mise en page conforme à l'image */}
      <footer className="site-footer">
        {/* Le ruban tricolore officiel du Bénin */}
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

      {/* Styles CSS Modernisés */}
      <style>{`
        /* Global & Reset basique */
        html, body, #root { height: 100%; margin: 0; padding: 0; }
        .page-container {
          font-family: 'Montserrat', sans-serif;
          color: #1e293b;
          background-color: #f8fafc;
          margin: 0;
          padding: 0;
          min-height: 100vh;
          display: flex;
          flex-direction: column;
        }

        /* 1. Header Styles */
        .site-header {
          position: sticky;
          top: 0;
          left: 0;
          right: 0;
          display: flex;
          justify-content: space-between;
          align-items: center;
          flex-wrap: wrap;
          gap: 16px;
          padding: 16px 5%;
          background: rgba(255, 255, 255, 0.95);
          border-bottom: 1px solid rgba(0, 0, 0, 0.08);
          box-shadow: 0 2px 4px rgba(0, 0, 0, 0.08);
          z-index: 1000;
        }
        .logo-left,
        .logo-right {
          display: flex;
          align-items: center;
          min-width: 140px;
        }
        .brand-logo,
        .institution-logo {
          max-width: 100%;
          height: auto;
          object-fit: contain;
        }
        .brand-logo {
          max-height: 70px;
          margin-right: 12px;
        }
        .institution-logo {
          max-height: 55px;
        }

        /* 2. Hero Section Styles (Arrière-plan dynamique) */
        .hero-section {
          position: relative;
          padding: 120px 5%;
          min-height: 500px;
          display: flex;
          align-items: center;
          overflow: hidden;
          flex: 1 0 auto;
        }
        .hero-carousel {
          position: absolute;
          inset: 0;
          z-index: 0;
        }
        .carousel-img {
          position: absolute;
          inset: 0;
          width: 100%;
          height: 100%;
          object-fit: cover;
          opacity: 0;
          transition: opacity 1.8s ease-in-out;
          pointer-events: none;
          will-change: opacity;
        }
        .carousel-img.active {
          opacity: 1;
        }
        /* Voile assombrissant et coloré pour la lisibilité */
        .hero-overlay {
          position: absolute;
          inset: 0;
          background: linear-gradient(90deg, rgba(15,23,42,0.5) 0%, rgba(255,255,255,0.42) 60%);
          pointer-events: none;
          z-index: 1;
          backdrop-filter: saturate(1.2) brightness(1.05);
        }
        .hero-inner-content {
          position: relative;
          z-index: 2;
          max-width: 780px;
          width: 100%;
        }
        .badge-tag {
          display: inline-block;
          background: #dbeafe;
          color: #1e40af;
          padding: 6px 14px;
          border-radius: 50px;
          font-size: 0.85rem;
          font-weight: 600;
          margin-bottom: 20px;
          text-transform: uppercase;
          letter-spacing: 0.5px;
        }
        .hero-inner-content h1 {
          font-size: 2.8rem;
          font-weight: 800;
          line-height: 1.25;
          color: #0f172a;
          margin: 0 0 20px 0;
        }
        .highlight {
          color: #0284c7; /* Bleu azur moderne */
          position: relative;
        }
        .hero-description {
          font-size: 1.15rem;
          line-height: 1.6;
          color: #475569;
          margin-bottom: 35px;
        }
        .action-buttons {
          display: flex;
          gap: 16px;
          margin-bottom: 30px;
          flex-wrap: wrap;
        }
        .btn {
          min-width: 180px;
          text-align: center;
        }
        .btn {
          padding: 14px 28px;
          font-size: 1rem;
          font-weight: 600;
          border-radius: 8px;
          cursor: pointer;
          transition: all 0.2s ease;
        }
        .btn-primary {
          background: #0f172a;
          color: #ffffff;
          border: none;
          box-shadow: 0 4px 6px -1px rgba(15, 23, 42, 0.3);
        }
        .btn-primary:hover {
          background: #1e293b;
          transform: translateY(-1px);
        }
        .btn-secondary {
          background: #ffffff;
          color: #334155;
          border: 1px solid #cbd5e1;
        }
        .btn-secondary:hover {
          background: #f1f5f9;
          border-color: #94a3b8;
        }
        .social-proof {
          display: flex;
          align-items: center;
          gap: 10px;
        }
        .star-icon {
          color: #eab308; /* Jaune or star */
          font-size: 1.1rem;
          letter-spacing: 2px;
        }
        .social-proof p {
          margin: 0;
          font-size: 0.95rem;
          color: #64748b;
        }

        /* 3. Footer Styles */
        .site-footer {
          background: #091322; /* Bleu nuit profond et premium */
          color: #f1f5f9;
          margin-top: 80px;
          width: 100%;
          margin-left: 0;
          margin-right: 0;
          flex-shrink: 0;
        }
        /* Ruban tricolore officiel étendu sur 100% de largeur */
        .benin-flag-ribbon {
          height: 5px;
          width: 100%;
          background: linear-gradient(to right, #008751 33.33%, #ffeb3b 66.66%, #e81123 100%);
        }
        .footer-content {
          padding: 60px 5% 40px;
          max-width: 1400px;
          margin: 0 auto;
        }
        .footer-grid {
          display: grid;
          grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
          gap: 40px;
          align-items: start;
        }
        .footer-column h3 {
          font-size: 1rem;
          text-transform: uppercase;
          letter-spacing: 1px;
          color: #ffffff;
          margin: 0 0 18px 0;
          font-weight: 800;
        }
        .footer-column ul {
          list-style: none;
          padding: 0;
          margin: 0;
        }
        .footer-column ul li {
          margin-bottom: 12px;
        }
        .footer-column ul li a {
          color: #cbd5e1;
          text-decoration: none;
          font-size: 0.95rem;
          transition: color 0.2s ease;
        }
        .footer-column ul li a:hover {
          color: #ffffff;
        }
        .contact-list li {
          color: #cbd5e1;
          font-size: 0.95rem;
          line-height: 1.4;
        }
        .footer-bottom-bar {
          text-align: center;
          padding: 24px 5%;
          border-top: 1px solid rgba(255,255,255,0.06);
          font-size: 0.95rem;
          color: #94a3b8;
          background: #060d17;
        }

        .footer-bottom-inner {
          max-width: 1200px;
          margin: 0 auto;
          display: flex;
          flex-direction: column;
          align-items: center;
          gap: 12px;
          padding: 10px 0;
        }
        .social-icons { display:flex; gap:14px; }
        .social-icon {
          width:44px; height:44px; display:inline-flex; align-items:center; justify-content:center;
          background:#ffffff; color:#091322; border-radius:8px;
          box-shadow: 0 2px 6px rgba(0,0,0,0.15);
        }
        .copy-line { color:#94a3b8; font-size:0.95rem; }

        /* Bouton 'Se connecter' — global styles (cadre, Montserrat, hover) */
        .hero-login-btn {
          position: absolute;
          top: 12px;
          right: 5%;
          z-index: 1200;
          padding: 10px 18px;
          font-size: 0.98rem;
          font-weight: 700;
          border-radius: 8px;
          background: rgba(255,255,255,0.95);
          color: #0f172a;
          border: 2px solid rgba(15,23,42,0.95);
          font-family: 'Montserrat', sans-serif;
          text-decoration: none;
          display: inline-flex;
          align-items: center;
          justify-content: center;
          box-shadow: 0 6px 12px rgba(15,23,42,0.12);
          transition: transform 0.18s ease, background 0.15s ease, color 0.15s ease;
          cursor: pointer;
        }
        .hero-login-btn:hover {
          transform: translateY(-3px);
          background: #0f172a;
          color: #ffffff;
          border-color: #0f172a;
        }

        /* Responsive basique pour mobiles */
        @media (max-width: 1024px) {
          .footer-grid { grid-template-columns: repeat(2, minmax(180px, 1fr)); gap: 28px; }
        }
        @media (max-width: 768px) {
          .site-header { 
            padding: 15px 4%; 
          }
          .brand-logo { 
            height: 40px;
          }
          .institution-logo { 
            height: 45px; 
          }
          .hero-section { 
            padding: 60px 4%; 
          }
          .hero-inner-content h1 { 
            font-size: 2rem; 
          }
          .hero-overlay { 
            background: rgba(255, 255, 255, 0.92); 
          }
          .footer-grid { grid-template-columns: 1fr; }
          .social-icon { width:40px; height:40px; }
          /* Mobile overrides pour le bouton de connexion */
          .hero-login-btn { right: 4%; top: 10px; padding: 8px 14px; font-size: 0.92rem; }
          .hero-login-btn:hover { transform: translateY(-3px); background: #0f172a; color: #fff; border-color: #0f172a; }
        }
      `}</style>
    </div>
  );
}
