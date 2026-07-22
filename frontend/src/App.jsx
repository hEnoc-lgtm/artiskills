import { BrowserRouter, Routes, Route, Link } from "react-router-dom";
import Accueil from "./pages/Accueil";
import InscriptionArtisan from "./pages/Inscriptionartisan";
import ConnexionArtisan from "./pages/Connexionartisan";

function App() {
  return (
    <BrowserRouter>
      <div className="app-shell">
        <header className="site-header">
          <div className="logo-left">
            <img src="/images/logo-artiskills.png" alt="ArtiSkills" className="brand-logo" />
          </div>

          <div className="header-actions">
            <div className="logo-right">
              <img src="/images/logo-anps.png" alt="ANPS Bénin" className="institution-logo" />
            </div>
          </div>
        </header>

        <main className="main-content">
          <Routes>
            <Route path="/" element={<Accueil />} />
            <Route path="/inscriptionartisan" element={<InscriptionArtisan />} />
            <Route path="/connexionartisan" element={<ConnexionArtisan />} />
          </Routes>
        </main>
      </div>
      <style>{`
        .app-shell {
          min-height: 100vh;
          display: flex;
          flex-direction: column;
        }
        .site-header {
          position: sticky;
          top: 0;
          left: 0;
          right: 0;
          display: flex;
          justify-content: space-between;
          align-items: center;
          padding: 14px 5%;
          background: rgba(255, 255, 255, 0.96);
          border-bottom: 1px solid rgba(0, 0, 0, 0.08);
          box-shadow: 0 2px 6px rgba(0, 0, 0, 0.08);
          z-index: 1000;
          backdrop-filter: saturate(180%) blur(10px);
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
        }
        .institution-logo {
          max-height: 55px;
        }
        .main-content {
          flex: 1 0 auto;
        }
        .header-actions { display:flex; align-items:center; gap:16px; }
        .btn {
          font-family: inherit;
          padding: 10px 18px;
          border-radius: 8px;
          font-weight: 600;
          text-decoration: none;
          display: inline-flex;
          align-items: center;
          justify-content: center;
          cursor: pointer;
        }
        .header-login-btn {
          background: #0f172a;
          color: #fff;
          border: none;
          box-shadow: 0 4px 6px -1px rgba(15, 23, 42, 0.2);
        }
        .header-login-btn:hover { background: #1e293b; transform: translateY(-1px); }
      `}</style>
    </BrowserRouter>
  );
}

export default App;
