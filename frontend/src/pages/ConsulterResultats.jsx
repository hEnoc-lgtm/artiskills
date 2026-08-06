import { useState, useEffect } from "react";

export default function ConsulterResultats() {
  const [resultats, setResultats] = useState([]);
  const [metiers, setMetiers] = useState([]);
  const [departements, setDepartements] = useState([]);
  const [chargement, setChargement] = useState(true);
  
  // États des filtres synchronisés avec votre image
  const [filtreMetier, setFiltreMetier] = useState("tous");
  const [filtreDepartement, setFiltreDepartement] = useState("tous");
  const [filtreStatut, setFiltreStatut] = useState("tous");
  const [filtrePeriode, setFiltrePeriode] = useState("tous");

  useEffect(() => {
    // 1. Charger les résultats d'évaluation
    fetch("http://localhost/Code/backend/CRUD/Read_resultats_tests.php")
      .then((res) => res.json())
      .then((data) => { if (data.success) setResultats(data.data); });

    // 2. Charger la liste des métiers pour le filtre
    fetch("http://localhost/Code/backend/CRUD/Read_corpsmetier.php")
      .then((res) => res.json())
      .then((data) => { if (data.success) setMetiers(data.data); });

    // 3. Charger la liste des départements pour le filtre
    fetch("http://localhost/Code/backend/CRUD/Read_departement.php")
      .then((res) => res.json())
      .then((data) => {
        if (data.success) setDepartements(data.data);
        setChargement(false);
      });
  }, []);

  // Filtrage combiné des lignes du tableau
  const resultatsFiltrés = resultats.filter((item) => {
    const matchMetier = filtreMetier === "tous" || item.libelle === filtreMetier;
    const matchDept = filtreDepartement === "tous" || item.nomDepartement === filtreDepartement;
    const matchStatut = filtreStatut === "tous" || item.statutTest === filtreStatut;
    
    // Filtre période (Exemple pour les 7 derniers jours)
    let matchPeriode = true;
    if (filtrePeriode === "7_jours" && item.date && item.heureDebut) {
      const dateTest = new Date(`${item.date}T${item.heureDebut}`);
      const ilYaSeptJours = new Date();
      ilYaSeptJours.setDate(ilYaSeptJours.getDate() - 7);
      matchPeriode = dateTest >= ilYaSeptJours;
    }

    return matchMetier && matchDept && matchStatut && matchPeriode;
  });

  return (
    <div className="resultats-panel-wrapper">
      
      {/* SECTION FILTRES SUPÉRIEURS */}
      <div className="filters-header-grid">
        <select value={filtreMetier} onChange={(e) => setFiltreMetier(e.target.value)}>
          <option value="tous">Corps de métier — tous</option>
          {metiers.map((m, i) => <option key={i} value={m.libelle}>{m.libelle}</option>)}
        </select>

        <select value={filtreDepartement} onChange={(e) => setFiltreDepartement(e.target.value)}>
          <option value="tous">Sélectionner un Département</option>
          {departements.map((d, i) => <option key={i} value={d.nomDepartement}>{d.nomDepartement}</option>)}
        </select>

        <select value={filtreStatut} onChange={(e) => setFiltreStatut(e.target.value)}>
          <option value="tous">Tous les statuts</option>
          <option value="Validé">Validé</option>
          <option value="En attente">En attente</option>
          <option value="Expiré">Expiré</option>
        </select>

        <select value={filtrePeriode} onChange={(e) => setFiltrePeriode(e.target.value)}>
          <option value="tous">Toutes les périodes</option>
          <option value="7_jours">7 derniers jours</option>
        </select>
      </div>

      {/* TABLEAU DES RÉSULTATS */}
      <div className="table-ui-card">
        {chargement ? (
          <div className="table-loader-txt">Chargement des données ANPS...</div>
        ) : (
          <table className="mock-data-table">
            <thead>
              <tr>
                <th>Artisan</th>
                <th>Corps de métier</th>
                <th>Département</th>
                <th>Note</th>
                <th>Statut</th>
              </tr>
            </thead>
            <tbody>
              {resultatsFiltrés.map((r) => (
                <tr key={r.idTest}>
                  <td><strong>{r.nom} {r.prenom.charAt(0)}.</strong></td>
                  <td>{r.libelle || "Non renseigné"}</td>
                  <td>{r.nomDepartement || "Inconnu"}</td>
                  <td className="note-cell-style">{r.score !== null ? `${r.score}/10` : "—"}</td>
                  <td>
                    <span className={`status-badge-pill ${r.statutTest.toLowerCase().replace(/\s/g, '_')}`}>
                      {r.statutTest}
                    </span>
                  </td>
                </tr>
              ))}
              {resultatsFiltrés.length === 0 && (
                <tr><td colSpan="5" className="empty-row-info">Aucun artisan ne correspond à cette configuration de filtres.</td></tr>
              )}
            </tbody>
          </table>
        )}
      </div>

      <style>{`
        .resultats-panel-wrapper { width: 100%; box-sizing: border-box; }
        
        .filters-header-grid { display: flex; flex-direction: column; gap: 14px; margin-bottom: 28px; max-width: 100%; }
        .filters-header-grid select { width: 100%; padding: 14px 16px; border: 1px solid #e2e8f0; border-radius: 10px; font-size: 1rem; color: #0f172a; background-color: #ffffff; cursor: pointer; box-shadow: 0 1px 2px rgba(0,0,0,0.02); }
        .filters-header-grid select:focus { outline: none; border-color: #bfdbfe; }

        .table-ui-card { background: #ffffff; border: 1px solid #e2e8f0; border-radius: 14px; overflow: hidden; }
        .mock-data-table { width: 100%; border-collapse: collapse; text-align: left; }
        .mock-data-table th { background: #ffffff; padding: 16px 20px; font-size: 0.9rem; color: #64748b; font-weight: 500; border-bottom: 1px solid #f1f5f9; }
        .mock-data-table td { padding: 18px 20px; border-bottom: 1px solid #f1f5f9; font-size: 0.98rem; color: #0f172a; }
        .note-cell-style { font-weight: 600; color: #1e293b; }
        
        .status-badge-pill { display: inline-block; padding: 6px 16px; border-radius: 6px; font-size: 0.85rem; font-weight: 600; text-align: center; min-width: 90px; }
        .status-badge-pill.validé { background-color: #e8f5e9; color: #2e7d32; }
        .status-badge-pill.en_attente { background-color: #fff3e0; color: #ef6c00; }
        .status-badge-pill.expiré { background-color: #ffebee; color: #c62828; }
        
        .empty-row-info { text-align: center; padding: 40px !important; color: #94a3b8; }
        .table-loader-txt { padding: 50px; text-align: center; color: #64748b; font-weight: 500; }
      `}</style>
    </div>
  );
}