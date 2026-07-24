import { useState, useEffect } from "react";

export default function GestionAffectations() {
  const [affectations, setAffectations] = useState([]);
  const [chargement, setChargement] = useState(true);
  const [recherche, setRecherche] = useState("");

  const chargerAffectations = () => {
    setChargement(true);
    fetch("http://localhost/votre_projet_backend/CRUD/Read_affectation.php")
      .then((res) => res.json())
      .then((data) => {
        if (data.success) setAffectations(data.data);
        setChargement(false);
      })
      .catch((err) => {
        console.error("Erreur de liaison", err);
        setChargement(false);
      });
  };

  useEffect(() => {
    const timeoutId = window.setTimeout(() => {
      chargerAffectations();
    }, 0);

    return () => window.clearTimeout(timeoutId);
  }, []);

  // Filtrage en temps réel par NPI ou Nom
  const listeFiltree = affectations.filter((a) =>
    a.npi.toLowerCase().includes(recherche.toLowerCase()) ||
    a.nom.toLowerCase().includes(recherche.toLowerCase())
  );

  return (
    <div className="management-container">
      <header className="content-header">
        <div>
          <h2>Registre National des Affectations ARCH</h2>
          <p className="subtitle">Consultation officielle et suivi en temps réel des orientations des artisans du Bénin.</p>
        </div>
      </header>

      {/* Barre de recherche par NPI */}
      <div className="search-box">
        <input 
          type="text" 
          placeholder="🔍 Rechercher un dossier d'orientation par numéro NPI ou Nom..." 
          value={recherche} 
          onChange={(e) => setRecherche(e.target.value)} 
        />
      </div>

      {/* Tableau d'affichage sécurisé en lecture seule */}
      <div className="table-card">
        {chargement ? (
          <div className="loader">Chargement sécurisé du registre...</div>
        ) : (
          <table className="admin-table">
            <thead>
              <tr>
                <th>Artisan (NPI)</th>
                <th>Note obtenue</th>
                <th>Centre ARCH affecté</th>
                <th>Distance calculée</th>
                <th>Date de décision</th>
              </tr>
            </thead>
            <tbody>
              {listeFiltree.map((a) => (
                <tr key={a.idTest}>
                  <td>
                    <strong>{a.nom}</strong> {a.prenom}
                    <div className="npi-sub-text">NPI: {a.npi}</div>
                  </td>
                  <td>
                    <span className={`badge-score ${a.note_test >= 10 ? "admis" : "ajourne"}`}>
                      {a.note_test} / 20
                    </span>
                  </td>
                  <td><strong>{a.centre_attribue}</strong></td>
                  <td><span className="badge-distance">📍 {a.distanceCalculee} km</span></td>
                  <td>{new Date(a.dateAffectation).toLocaleDateString("fr-FR")}</td>
                </tr>
              ))}
              {listeFiltree.length === 0 && (
                <tr><td colSpan="5" className="empty-cell">Aucun dossier d'affectation enregistré.</td></tr>
              )}
            </tbody>
          </table>
        )}
      </div>

      <style>{`
        .management-container { width: 100%; box-sizing: border-box; }
        .content-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; }
        .content-header h2 { font-size: 1.4rem; color: #0f172a; margin: 0; font-weight: 700; }
        .subtitle { font-size: 0.88rem; color: #64748b; margin-top: 4px; }
        .search-box { margin-bottom: 24px; }
        .search-box input { width: 100%; padding: 12px 14px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 0.95rem; }
        .table-card { background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; overflow: hidden; }
        .admin-table { width: 100%; border-collapse: collapse; text-align: left; }
        .admin-table th { background: #f8fafc; padding: 14px 16px; font-size: 0.82rem; text-transform: uppercase; color: #64748b; font-weight: 700; border-bottom: 1px solid #e2e8f0; }
        .admin-table td { padding: 14px 16px; border-bottom: 1px solid #f1f5f9; font-size: 0.95rem; color: #334155; }
        .npi-sub-text { font-family: monospace; font-size: 0.8rem; color: #64748b; margin-top: 2px; }
        
        .badge-score { padding: 4px 8px; border-radius: 4px; font-size: 0.82rem; font-weight: 700; }
        .badge-score.admis { background: #dcfce7; color: #15803d; }
        .badge-score.ajourne { background: #fef2f2; color: #b91c1c; }
        .badge-distance { background: #f0fdfa; color: #0d9488; padding: 4px 8px; border-radius: 4px; font-size: 0.85rem; font-weight: 600; }
        .empty-cell { text-align: center; padding: 30px !important; color: #64748b; }
        .loader { padding: 40px; text-align: center; color: #64748b; }
      `}</style>
    </div>
  );
}
