import { useState, useEffect } from "react";
import GestionComptesAgents from "./GestionComptesAgents";
import GestionCorpsMetiers from "./GestionCorpsMetiers";
import GestionCentresFormation from "./GestionCentresFormation";
import GestionBanqueQuestions from "./GestionBanqueQuestions";
import ConsulterResultats from "./ConsulterResultats";
import GestionAffectations from "./GestionAffectations";
import GestionHistoriqueSuppressions from "./GestionHistoriqueSuppressions";
import GestionParametres from "./GestionParametres";

export default function TableauDeBord({ userConnecte, onDeconnexion }) {
  const [ongletActif, setOngletActif] = useState("accueil");
  const [stats, setStats] = useState(null);
  const [chargementStats, setChargementStats] = useState(true);

  const configurationMenu = [
    { id: "accueil", label: "Accueil Dashboard", icon: "🏠", roles: ["administratueur", "agent simple"] },
    { id: "profils", label: "Gérer profils", icon: "👥", roles: ["administratueur"] },
    { id: "metiers", label: "Corps de métiers", icon: "📐", roles: ["administratueur"] },
    { id: "centres", label: "Centres ARCH", icon: "", roles: ["administratueur"] },
    { id: "questions", label: "Banque de questions", icon: "❓", roles: ["administratueur"] },
    { id: "resultats", label: "Consulter résultats", icon: "📊", roles: ["administratueur", "agent simple"] },
    { id: "affectations", label: "Suivi affectations", icon: "📍", roles: ["administratueur", "agent simple"] },
    { id: "suppressions", label: "Historique suppressions", icon: "🔒", roles: ["administratueur"] },
  ];

  const menuVisible = configurationMenu.filter(item => 
    item.roles.includes(userConnecte?.role)
  );

  const arrondirStat = (value, precision = 1) => {
    const multiplier = Math.pow(10, precision || 0);
    return Math.round(value * multiplier) / multiplier;
  };

  useEffect(() => {
    if (ongletActif !== "accueil") return;
    const timeoutId = window.setTimeout(() => {
      setChargementStats(true);
      fetch("http://localhost/Code/backend/CRUD/Read_statistiques_dashboard.php")
        .then((res) => res.json())
        .then((data) => {
          if (data.success) setStats(data);
          setChargementStats(false);
        })
        .catch((err) => {
          console.error("Erreur de synchro statistiques", err);
          setChargementStats(false);
        });
    }, 0);
    return () => window.clearTimeout(timeoutId);
  }, [ongletActif]);

  return (
    <div className="dashboard-wrapper">
      <aside className="dashboard-sidebar">
        <div className="sidebar-brand-box">
          <div className="brand-badge">ARCH</div>
          <h2>ANPS Administration</h2>
        </div>

        <div className="user-profile-summary">
          <div className="avatar-circle">👤</div>
          <div className="user-info-text">
            <h4>{userConnecte?.nom || "Nom Opérateur"}</h4>
            <span className={`user-role-tag ${userConnecte?.role === 'administratueur' ? 'role-admin' : 'role-agent'}`}>
              {userConnecte?.role === 'administratueur' ? 'Administrateur' : 'Agent Simple'}
            </span>
          </div>
        </div>

        <nav className="sidebar-nav-links">
          {menuVisible.map((item) => (
            <button 
              key={item.id}
              className={ongletActif === item.id ? "active" : ""} 
              onClick={() => setOngletActif(item.id)}
            >
              <span>{item.icon}</span> {item.label}
            </button>
          ))}
        </nav>

        <div className="sidebar-footer-anchors">
          {/* ✅ CORRECTION : Paramètres uniquement pour les administrateurs */}
          {userConnecte?.role === 'administratueur' && (
            <button className={`btn-settings-anchor ${ongletActif === "parametres" ? "active" : ""}`} onClick={() => setOngletActif("parametres")}>
              <span>⚙️</span> Paramètres avancés
            </button>
          )}
          <button className="btn-sidebar-logout" onClick={onDeconnexion}>
            🚪 Fermer la session
          </button>
        </div>
      </aside>

      <main className="dashboard-main-content">
        
        {ongletActif === "accueil" && (
          <div className="stats-dashboard-view">
            <header className="view-title-header">
              <h1>Suivi National des Indicateurs ARCH</h1>
              <p>Analyse des performances d'évaluations et de la répartition géospatiale des artisans au Bénin.</p>
            </header>

            {chargementStats ? (
              <div className="stats-loader-info">Calcul des métriques ministérielles...</div>
            ) : (
              <>
                <div className="stats-cards-grid">
                  <div className="metric-card-box">
                    <div className="metric-box-header">
                      <span className="box-icon-style">👥</span>
                      <h4>Opérateurs Habilités</h4>
                    </div>
                    <h2>{stats?.compteurs?.totalAgents || 0}</h2>
                    <p className="card-sub-info">Comptes agents actifs</p>
                  </div>
                  <div className="metric-card-box">
                    <div className="metric-box-header">
                      <span className="box-icon-style">📝</span>
                      <h4>Artisans Évalués</h4>
                    </div>
                    <h2>{stats?.compteurs?.totalTests || 0}</h2>
                    <p className="card-sub-info">Sessions de tests démarrées</p>
                  </div>
                  <div className="metric-card-box">
                    <div className="metric-box-header">
                      <span className="box-icon-style">📈</span>
                      <h4>Taux de Réussite</h4>
                    </div>
                    <h2 className="success-color-txt">{stats?.compteurs?.tauxReussite || "0%"}</h2>
                    <p className="card-sub-info">Moyenne nationale (&gt;= 10/20)</p>
                  </div>
                </div>

                <div className="geo-stats-section-layout">
                  <div className="geo-table-card-wrapper">
                    <h3> Répartition des certifications par Département</h3>
                    <table className="geo-statistics-table">
                      <thead>
                        <tr>
                          <th>Département</th>
                          <th>Total Évaluations</th>
                          <th>Candidats Validés</th>
                        </tr>
                      </thead>
                      <tbody>
                        {stats?.geographie?.map((geo, index) => (
                          <tr key={index}>
                            <td><strong>{geo.departement}</strong></td>
                            <td className="numeric-cell">{geo.total_evaluations}</td>
                            <td className="numeric-cell success-cell-style">
                              {geo.total_valides} <span>({geo.total_evaluations > 0 ? arrondirStat((geo.total_valides / geo.total_evaluations) * 100) : 0}%)</span>
                            </td>
                          </tr>
                        ))}
                        {(!stats?.geographie || stats.geographie.length === 0) && (
                          <tr><td colSpan="3" style={{ textAlign: "center", color: "#64748b", padding: "20px" }}>Aucune donnée géographique enregistrée.</td></tr>
                        )}
                      </tbody>
                    </table>
                  </div>
                </div>
              </>
            )}
          </div>
        )}

        {ongletActif === "profils" && <GestionComptesAgents />}
        {ongletActif === "metiers" && <GestionCorpsMetiers />}
        {ongletActif === "centres" && <GestionCentresFormation />}
        {ongletActif === "questions" && <GestionBanqueQuestions idAdminConnecte={userConnecte?.id_profil || 1} />}
        {ongletActif === "resultats" && <ConsulterResultats />}
        {ongletActif === "affectations" && <GestionAffectations />}
        {ongletActif === "suppressions" && <GestionHistoriqueSuppressions />}
        
        {/* ✅ CORRECTION : Protection de l'accès aux paramètres */}
        {ongletActif === "parametres" && (
          userConnecte?.role === 'administratueur' ? (
            <GestionParametres />
          ) : (
            <div className="access-denied-container">
              <div className="access-denied-box">
                <span className="denied-icon">🔒</span>
                <h2>Accès Refusé</h2>
                <p>Vous n'avez pas les privilèges nécessaires pour accéder à cette section.</p>
                <button onClick={() => setOngletActif("accueil")} className="btn-return-home">
                  Retour à l'accueil
                </button>
              </div>
            </div>
          )
        )}

      </main>

      <style>{`
        .dashboard-wrapper { display: flex; min-height: 100vh; background-color: #f8fafc; font-family: 'Montserrat', sans-serif; box-sizing: border-box; overflow: hidden; }
        .dashboard-sidebar { width: 280px; background: #0f172a; color: #ffffff; padding: 24px 16px; display: flex; flex-direction: column; border-right: 1px solid #1e293b; flex-shrink: 0; box-sizing: border-box; text-align: left; height: 100vh; position: sticky; top: 0; }
        .sidebar-brand-box { display: flex; align-items: center; gap: 10px; margin-bottom: 24px; padding-left: 8px; }
        .brand-badge { background: #2563eb; color: #fff; font-size: 0.7rem; font-weight: 800; padding: 3px 8px; border-radius: 4px; text-transform: uppercase; }
        .sidebar-brand-box h2 { font-size: 1.15rem; font-weight: 700; margin: 0; color: #f8fafc; }
        .user-profile-summary { display: flex; align-items: center; gap: 12px; background: #1e293b; padding: 12px; border-radius: 10px; margin-bottom: 28px; }
        .avatar-circle { width: 36px; height: 36px; background: #334155; border-radius: 50%; display: flex; justify-content: center; align-items: center; font-size: 1.1rem; }
        .user-info-text { display: flex; flex-direction: column; overflow: hidden; }
        .user-info-text h4 { margin: 0; font-size: 0.9rem; font-weight: 600; color: #f1f5f9; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .user-role-tag { font-size: 0.75rem; font-weight: 600; margin-top: 2px; padding: 2px 8px; border-radius: 4px; display: inline-block; width: fit-content; }
        .role-admin { background: #7f1d1d; color: #fecaca; }
        .role-agent { background: #14532d; color: #bbf7d0; }
        .sidebar-nav-links { display: flex; flex-direction: column; gap: 4px; flex: 1; overflow-y: auto; padding-right: 2px; }
        .sidebar-nav-links button { display: flex; align-items: center; gap: 12px; background: transparent; border: none; color: #94a3b8; padding: 12px 14px; border-radius: 8px; font-size: 0.92rem; font-weight: 600; cursor: pointer; text-align: left; transition: all 0.2s; width: 100%; box-sizing: border-box; }
        .sidebar-nav-links button span { font-size: 1.1rem; color: #64748b; }
        .sidebar-nav-links button:hover, .sidebar-nav-links button.active { background: #1e293b; color: #ffffff; }
        .sidebar-nav-links button.active span { color: #38bdf8; }
        .sidebar-footer-anchors { display: flex; flex-direction: column; gap: 8px; border-top: 1px solid #1e293b; padding-top: 16px; margin-top: 20px; }
        .btn-settings-anchor { display: flex; align-items: center; gap: 10px; background: transparent; border: none; color: #94a3b8; font-size: 0.88rem; font-weight: 600; padding: 10px 12px; border-radius: 6px; cursor: pointer; text-align: left; width: 100%; box-sizing: border-box; }
        .btn-settings-anchor:hover, .btn-settings-anchor.active { background: #1e293b; color: #fff; }
        .btn-sidebar-logout { display: flex; align-items: center; gap: 10px; background: #7f1d1d; color: #fecaca; border: none; padding: 10px 12px; border-radius: 6px; font-size: 0.88rem; font-weight: 600; cursor: pointer; justify-content: center; width: 100%; box-sizing: border-box; transition: background 0.2s; }
        .btn-sidebar-logout:hover { background: #991b1b; }
        .dashboard-main-content { flex: 1; padding: 40px; box-sizing: border-box; overflow-y: auto; height: 100vh; display: flex; flex-direction: column; }
        .stats-dashboard-view { display: flex; flex-direction: column; gap: 32px; width: 100%; box-sizing: border-box; }
        .view-title-header { text-align: left; }
        .view-title-header h1 { font-size: 1.6rem; color: #0f172a; margin: 0 0 6px 0; font-weight: 700; }
        .view-title-header p { font-size: 0.92rem; color: #64748b; margin: 0; }
        .stats-cards-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; width: 100%; box-sizing: border-box; }
        .metric-card-box { background: #ffffff; border: 1px solid #e2e8f0; border-radius: 14px; padding: 24px; box-shadow: 0 1px 2px rgba(0,0,0,0.02); text-align: left; display: flex; flex-direction: column; gap: 10px; box-sizing: border-box; }
        .metric-box-header { display: flex; align-items: center; gap: 10px; }
        .box-icon-style { font-size: 1.3rem; }
        .metric-card-box h4 { margin: 0; font-size: 0.88rem; color: #64748b; font-weight: 600; }
        .metric-card-box h2 { margin: 0; font-size: 2.2rem; font-weight: 800; color: #0f172a; line-height: 1; }
        .success-color-txt { color: #16a34a !important; }
        .card-sub-info { margin: 0; font-size: 0.8rem; color: #94a3b8; font-weight: 500; }
        .geo-stats-section-layout { background: #ffffff; border: 1px solid #e2e8f0; border-radius: 14px; padding: 24px; box-shadow: 0 1px 2px rgba(0,0,0,0.02); text-align: left; width: 100%; box-sizing: border-box; }
        .geo-stats-section-layout h3 { margin: 0 0 20px 0; font-size: 1.1rem; font-weight: 700; color: #1e293b; }
        .geo-statistics-table { width: 100%; border-collapse: collapse; }
        .geo-statistics-table th { background: #f8fafc; padding: 12px 16px; font-size: 0.8rem; text-transform: uppercase; color: #64748b; font-weight: 700; border-bottom: 1px solid #e2e8f0; }
        .geo-statistics-table td { padding: 14px 16px; border-bottom: 1px solid #f1f5f9; font-size: 0.95rem; color: #334155; }
        .numeric-cell { font-variant-numeric: tabular-nums; font-weight: 600; }
        .success-cell-style { color: #16a34a; }
        .success-cell-style span { font-size: 0.8rem; color: #94a3b8; font-weight: 500; }
        .stats-loader-info { padding: 50px; text-align: center; color: #64748b; font-weight: 500; }
        
        /* Styles pour le message d'accès refusé */
        .access-denied-container {
          display: flex;
          justify-content: center;
          align-items: center;
          height: 100%;
          width: 100%;
        }
        .access-denied-box {
          background: #ffffff;
          border: 1px solid #fca5a5;
          border-radius: 12px;
          padding: 40px;
          text-align: center;
          max-width: 400px;
          box-shadow: 0 4px 6px rgba(0,0,0,0.05);
        }
        .denied-icon {
          font-size: 3rem;
          display: block;
          margin-bottom: 16px;
        }
        .access-denied-box h2 {
          color: #991b1b;
          margin: 0 0 12px 0;
          font-size: 1.5rem;
        }
        .access-denied-box p {
          color: #475569;
          margin-bottom: 24px;
        }
        .btn-return-home {
          background: #1e3a8a;
          color: white;
          border: none;
          padding: 10px 20px;
          border-radius: 6px;
          cursor: pointer;
          font-weight: 600;
        }
      `}</style>
    </div>
  );
}