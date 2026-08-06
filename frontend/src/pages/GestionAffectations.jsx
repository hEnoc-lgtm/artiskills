import { useState, useEffect } from "react";

export default function GestionAffectations() {
  const [affectations, setAffectations] = useState([]);
  const [departements, setDepartements] = useState([]); // ← maintenant chargé depuis la BDD
  const [chargement, setChargement] = useState(true);
  const [erreur, setErreur] = useState(null);
  const [recherche, setRecherche] = useState("");
  const [filtreStatut, setFiltreStatut] = useState("tous");
  const [filtreDepartement, setFiltreDepartement] = useState("tous");

  useEffect(() => {
    // 1. Charger les affectations
    fetch("http://localhost/Code/backend/CRUD/Read_affectation.php")
      .then((res) => res.json())
      .then((data) => {
        if (data.success) setAffectations(data.data);
        else setErreur(data.message || "Erreur serveur.");
        setChargement(false);
      })
      .catch(() => {
        setErreur("Impossible de contacter le serveur.");
        setChargement(false);
      });

    // 2. Charger TOUS les départements pour la liste déroulante
    fetch("http://localhost/Code/backend/CRUD/Read_departement.php")
      .then((res) => res.json())
      .then((data) => {
        if (data.success) setDepartements(data.data);
      })
      .catch((err) => console.error("Erreur chargement départements :", err));
  }, []);

  // Filtrage combiné
  const dataFiltree = affectations.filter((a) => {
    const txt = recherche.trim().toLowerCase();
    const matchRecherche =
      !txt ||
      (a.nom || "").toLowerCase().includes(txt) ||
      (a.prenom || "").toLowerCase().includes(txt) ||
      (a.npi || "").includes(txt) ||
      (a.nomCentre || "").toLowerCase().includes(txt);
    const matchStatut = filtreStatut === "tous" || a.statutPlace === filtreStatut;
    const matchDept = filtreDepartement === "tous" || a.nomDepartement === filtreDepartement;
    return matchRecherche && matchStatut && matchDept;
  });

  const nbValidees = affectations.filter((a) => a.statutPlace === "validée directement").length;
  const nbAttente = affectations.filter((a) => a.statutPlace === "liste_attente").length;

  return (
    <div className="affect-wrapper">
      <header className="affect-header">
        <h2>Registre National des Affectations ARCH</h2>
        <p className="affect-subtitle">Consultation officielle et suivi en temps réel des orientations des artisans du Bénin.</p>
      </header>

      {/* Compteurs */}
      <div className="affect-stats">
        <div className="stat-card"><span className="stat-num">{affectations.length}</span><span className="stat-label">Affectations</span></div>
        <div className="stat-card"><span className="stat-num ok">{nbValidees}</span><span className="stat-label">Validées directement</span></div>
        <div className="stat-card"><span className="stat-num warn">{nbAttente}</span><span className="stat-label">Liste d'attente</span></div>
      </div>

      {/* Filtres */}
      <div className="affect-filters">
        <input
          type="text"
          placeholder="🔍 Rechercher par NPI, nom ou centre..."
          value={recherche}
          onChange={(e) => setRecherche(e.target.value)}
        />
        <select value={filtreDepartement} onChange={(e) => setFiltreDepartement(e.target.value)}>
          <option value="tous">Tous les départements</option>
          {departements.map((d) => (
            <option key={d.idDepart} value={d.nomDepartement}>{d.nomDepartement}</option>
          ))}
        </select>
        <select value={filtreStatut} onChange={(e) => setFiltreStatut(e.target.value)}>
          <option value="tous">Tous les statuts</option>
          <option value="validée directement">Validée directement</option>
          <option value="liste_attente">Liste d'attente</option>
        </select>
      </div>

      {/* Tableau */}
      <div className="affect-table-card">
        {chargement ? (
          <div className="affect-loader">Chargement des affectations...</div>
        ) : erreur ? (
          <div className="affect-error">{erreur}</div>
        ) : (
          <table className="affect-table">
            <thead>
              <tr>
                <th>Artisan</th>
                <th>Corps de métier</th>
                <th>Score</th>
                <th>Centre affecté</th>
                <th>Département</th>
                <th>Distance</th>
                <th>Statut</th>
                <th>Date</th>
              </tr>
            </thead>
            <tbody>
              {dataFiltree.map((a) => (
                <tr key={a.idAffect}>
                  <td>
                    <strong>{a.nom} {a.prenom}</strong>
                    <span className="npi-small">NPI : {a.npi}</span>
                  </td>
                  <td>{a.nom_metier || "—"}</td>
                  <td className="score-cell">{a.score !== null ? `${a.score}/10` : "—"}</td>
                  <td>
                    <strong>{a.nomCentre}</strong>
                    <span className="npi-small">{a.quartier_centre}{a.nomCommune ? `, ${a.nomCommune}` : ""}</span>
                  </td>
                  <td>{a.nomDepartement || "—"}</td>
                  <td className="dist-cell">{a.distanceCalculee} km</td>
                  <td>
                    <span className={`badge-affect ${a.statutPlace === "validée directement" ? "ok" : "wait"}`}>
                      {a.statutPlace === "validée directement" ? "Validée" : "Liste d'attente"}
                    </span>
                  </td>
                  <td>{a.dateAffectation}</td>
                </tr>
              ))}
              {dataFiltree.length === 0 && (
                <tr><td colSpan="8" className="empty-cell">Aucune affectation ne correspond à cette recherche.</td></tr>
              )}
            </tbody>
          </table>
        )}
      </div>

      <style>{`
        .affect-wrapper { width: 100%; box-sizing: border-box; }
        .affect-header h2 { font-size: 1.4rem; color: #0f172a; margin: 0 0 4px 0; font-weight: 700; }
        .affect-subtitle { font-size: 0.88rem; color: #64748b; margin: 0 0 20px 0; }
        .affect-stats { display: flex; gap: 14px; margin-bottom: 20px; }
        .stat-card { background: #fff; border: 1px solid #e2e8f0; border-radius: 10px; padding: 12px 20px; display: flex; flex-direction: column; min-width: 140px; }
        .stat-num { font-size: 1.4rem; font-weight: 700; color: #0f172a; }
        .stat-num.ok { color: #16a34a; }
        .stat-num.warn { color: #ea580c; }
        .stat-label { font-size: 0.8rem; color: #64748b; }
        .affect-filters { display: flex; gap: 12px; margin-bottom: 20px; }
        .affect-filters input { flex: 1; padding: 12px 14px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 0.95rem; }
        .affect-filters select { padding: 12px 14px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 0.95rem; background: #fff; }
        .affect-table-card { background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; overflow: hidden; }
        .affect-table { width: 100%; border-collapse: collapse; text-align: left; }
        .affect-table th { background: #f8fafc; padding: 13px 16px; font-size: 0.8rem; text-transform: uppercase; color: #64748b; font-weight: 700; border-bottom: 1px solid #e2e8f0; }
        .affect-table td { padding: 13px 16px; border-bottom: 1px solid #f1f5f9; font-size: 0.92rem; color: #334155; vertical-align: top; }
        .npi-small { display: block; font-size: 0.78rem; color: #94a3b8; margin-top: 2px; }
        .score-cell { font-weight: 700; color: #1e293b; }
        .dist-cell { font-weight: 600; color: #1d4ed8; }
        .badge-affect { display: inline-block; padding: 5px 12px; border-radius: 6px; font-size: 0.8rem; font-weight: 600; min-width: 90px; text-align: center; }
        .badge-affect.ok { background: #e8f5e9; color: #2e7d32; }
        .badge-affect.wait { background: #fff3e0; color: #ef6c00; }
        .empty-cell { text-align: center; padding: 35px !important; color: #94a3b8; }
        .affect-loader, .affect-error { padding: 45px; text-align: center; font-weight: 500; }
        .affect-error { color: #b91c1c; }
      `}</style>
    </div>
  );
}