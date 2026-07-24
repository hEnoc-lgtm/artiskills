import { useState, useEffect } from "react";

export default function GestionHistoriqueSuppressions() {
  const [logs, setLogs] = useState([]);
  const [chargement, setChargement] = useState(true);
  const [recherche, setRecherche] = useState("");

  const chargerHistorique = () => {
    setChargement(true);
    fetch("http://localhost/Code/backend/CRUD/Read_historique_suppression.php")
      .then((res) => res.json())
      .then((data) => {
        if (data.success) setLogs(data.data);
        setChargement(false);
      })
      .catch((err) => {
        console.error("Erreur de liaison API", err);
        setChargement(false);
      });
  };

  useEffect(() => {
    const timeoutId = window.setTimeout(() => {
      chargerHistorique();
    }, 0);

    return () => window.clearTimeout(timeoutId);
  }, []);

  // Filtrage par mot-clé dans les énoncés supprimés
  const logsFiltres = logs.filter((log) =>
    log.enonceSup.toLowerCase().includes(recherche.toLowerCase())
  );

  return (
    <div className="management-container">
      <header className="content-header">
        <div>
          <h2>Registre d'Audit des Suppressions</h2>
          <p className="subtitle">Boîte noire de l'application : suivi des questions d'évaluation archivées et détruites.</p>
        </div>
      </header>

      {/* Barre de recherche */}
      <div className="search-box">
        <input 
          type="text" 
          placeholder="🔍 Rechercher dans les archives d'énoncés supprimés..." 
          value={recherche} 
          onChange={(e) => setRecherche(e.target.value)} 
        />
      </div>

      {/* Tableau d'affichage de sécurité */}
      <div className="table-card">
        {chargement ? (
          <div className="loader">Extraction sécurisée des archives...</div>
        ) : (
          <table className="admin-table">
            <thead>
              <tr>
                <th>Horodatage</th>
                <th>Énoncé supprimé</th>
                <th>Options de réponses détruites</th>
                <th>Opérateur ANPS</th>
              </tr>
            </thead>
            <tbody>
              {logsFiltres.map((log) => (
                <tr key={log.id_historique_sup}>
                  <td className="timestamp-cell">
                    <div className="date-txt">{new Date(log.dateSuppression).toLocaleDateString("fr-FR")}</div>
                    <div className="time-txt">🕒 {log.heureSuppression}</div>
                  </td>
                  <td className="enonce-cell">
                    <span className="badge-delete-tag">DÉTRUIT</span>
                    <strong>{log.enonceSup}</strong>
                  </td>
                  <td>
                    <div className="reponses-archive-box">
                      {log.reponsesSup}
                    </div>
                  </td>
                  <td>
                    <span className="badge-operator">👤 {log.nom_admin || "Admin System"}</span>
                  </td>
                </tr>
              ))}
              {logsFiltres.length === 0 && (
                <tr><td colSpan="4" className="empty-cell">Aucune trace de suppression dans les archives.</td></tr>
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
        .admin-table td { padding: 14px 16px; border-bottom: 1px solid #f1f5f9; font-size: 0.95rem; color: #334155; vertical-align: top; }
        
        .timestamp-cell { font-variant-numeric: tabular-nums; min-width: 110px; }
        .date-txt { font-weight: 600; color: #1e293b; }
        .time-txt { font-size: 0.8rem; color: #64748b; margin-top: 2px; }
        
        .enonce-cell { max-width: 260px; line-height: 1.4; }
        .badge-delete-tag { display: inline-block; background: #fef2f2; color: #991b1b; border: 1px solid #fca5a5; font-size: 0.7rem; font-weight: 700; padding: 1px 6px; border-radius: 4px; margin-bottom: 6px; }
        
        .reponses-archive-box { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 8px 12px; font-size: 0.85rem; color: #475569; line-height: 1.4; max-width: 300px; word-break: break-word; }
        
        .badge-operator { background: #f1f5f9; color: #334155; padding: 4px 8px; border-radius: 6px; font-size: 0.82rem; font-weight: 600; display: inline-block; }
        .empty-cell { text-align: center; padding: 30px !important; color: #64748b; }
        .loader { padding: 40px; text-align: center; color: #64748b; }
      `}</style>
    </div>
  );
}
