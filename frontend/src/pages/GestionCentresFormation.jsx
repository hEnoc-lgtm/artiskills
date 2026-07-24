import { useState, useEffect } from "react";

export default function GestionCentresFormation() {
  const [centres, setCentres] = useState([]);
  const [quartiers, setQuartiers] = useState([]); // Pour alimenter la liste déroulante des localisations
  const [chargement, setChargement] = useState(true);
  const [recherche, setRecherche] = useState("");
  
  // États pour la fenêtre modale (Ajout / Modification)
  const [modalOuvert, setModalOuvert] = useState(false);
  const [modeEdition, setModeEdition] = useState(false);
  const [currentCentre, setCurrentCentre] = useState({ 
    idCentre: "", 
    nomCentre: "", 
    contactCentre: "", 
    id_quartier_centre: "" 
  });

  // 1. READ : Charger les centres et les quartiers disponibles au démarrage
  const chargerDonnees = () => {
    setChargement(true);
    
    // Récupération des centres
    fetch("http://localhost/votre_projet_backend/CRUD/Read_centreformation.php")
      .then((res) => res.json())
      .then((data) => {
        if (data.success) setCentres(data.data);
      })
      .catch((err) => console.error("Erreur de chargement des centres", err));

    // Récupération des quartiers (pour remplir le sélecteur du formulaire)
    fetch("http://localhost/votre_projet_backend/CRUD/Read_quartier_village.php")
      .then((res) => res.json())
      .then((data) => {
        if (data.success) setQuartiers(data.data);
        setChargement(false);
      })
      .catch((err) => {
        console.error("Erreur de chargement des quartiers", err);
        setChargement(false);
      });
  };

  useEffect(() => {
    const timeoutId = window.setTimeout(() => {
      chargerDonnees();
    }, 0);

    return () => window.clearTimeout(timeoutId);
  }, []);

  // 2. CREATE & UPDATE : Envoi des données vers le dossier CRUD/
  const handleFormSubmit = (e) => {
    e.preventDefault();
    const url = modeEdition
      ? "http://localhost/votre_projet_backend/CRUD/Update_centreformation.php"
      : "http://localhost/votre_projet_backend/CRUD/Create_centreformation.php";

    fetch(url, {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify(currentCentre)
    })
      .then((res) => res.json())
      .then((data) => {
        if (data.success) {
          alert(data.message);
          setModalOuvert(false);
          chargerDonnees(); // Actualise le tableau complet
        } else {
          alert(data.message);
        }
      });
  };

  // 3. DELETE : Suppression définitive via le fichier CRUD/
  const handleSupprimer = (id) => {
    if (window.confirm("Voulez-vous vraiment retirer ce centre de formation du réseau ARCH officiel ?")) {
      fetch("http://localhost/votre_projet_backend/CRUD/Delete_centreformation.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ idCentre: id })
      })
        .then((res) => res.json())
        .then((data) => {
          alert(data.message);
          if (data.success) chargerDonnees();
        });
    }
  };

  const ouvrirModalAjout = () => {
    setModeEdition(false);
    setCurrentCentre({ idCentre: "", nomCentre: "", contactCentre: "", id_quartier_centre: "" });
    setModalOuvert(true);
  };

  const ouvrirModalEdition = (centre) => {
    setModeEdition(true);
    setCurrentCentre({ 
      idCentre: centre.idCentre, 
      nomCentre: centre.nomCentre, 
      contactCentre: centre.contactCentre || "", 
      id_quartier_centre: centre.id_quartier_centre || "" 
    });
    setModalOuvert(true);
  };

  // Filtrer la liste des établissements en temps réel
  const centresFiltres = centres.filter((c) =>
    c.nomCentre.toLowerCase().includes(recherche.toLowerCase())
  );

  return (
    <div className="management-container">
      <header className="content-header">
        <div>
          <h2>Registre des Centres de Formation ARCH</h2>
          <p className="subtitle">Gestion des infrastructures d'apprentissage et de renforcement de l'ANPS.</p>
        </div>
        <button className="btn-add" onClick={ouvrirModalAjout}>+ Nouveau Centre</button>
      </header>

      {/* Barre de recherche par mot-clé */}
      <div className="search-box">
        <input 
          type="text" 
          placeholder="🔍 Rechercher un centre de formation par sa dénomination officielle..." 
          value={recherche}
          onChange={(e) => setRecherche(e.target.value)}
        />
      </div>

      {/* Tableau d'affichage */}
      <div className="table-card">
        {chargement ? (
          <div className="loader">Synchronisation des infrastructures ARCH en cours...</div>
        ) : (
          <table className="admin-table">
            <thead>
              <tr>
                <th>ID</th>
                <th>Dénomination du Centre</th>
                <th>Contact Téléphonique</th>
                <th>Localisation (Quartier/Village)</th>
                <th style={{ textAlign: "right" }}>Actions</th>
              </tr>
            </thead>
            <tbody>
              {centresFiltres.map((c) => (
                <tr key={c.idCentre}>
                  <td className="id-cell">{c.idCentre}</td>
                  <td><strong>{c.nomCentre}</strong></td>
                  <td>{c.contactCentre || <span className="no-data">Non renseigné</span>}</td>
                  <td><span className="badge-geo">{c.quartier_centre || "Zone non spécifiée"}</span></td>
                  <td>
                    <div className="actions-wrapper">
                      <button className="btn-edit" onClick={() => ouvrirModalEdition(c)}>Éditer</button>
                      <button className="btn-delete" onClick={() => handleSupprimer(c.idCentre)}>Supprimer</button>
                    </div>
                  </td>
                </tr>
              ))}
              {centresFiltres.length === 0 && (
                <tr><td colSpan="5" className="empty-cell">Aucun établissement enregistré pour le moment.</td></tr>
              )}
            </tbody>
          </table>
        )}
      </div>

      {/* FENÊTRE MODALE POP-UP */}
      {modalOuvert && (
        <div className="modal-overlay">
          <div className="modal-card">
            <h3>{modeEdition ? "Mettre à jour la fiche établissement" : "Ajouter une infrastructure ARCH"}</h3>
            <form onSubmit={handleFormSubmit}>
              <div className="form-group">
                <label>Nom officiel du centre</label>
                <input 
                  type="text" 
                  placeholder="Ex: Centre de Formation Professionnelle de Kandi" 
                  value={currentCentre.nomCentre}
                  onChange={(e) => setCurrentCentre({ ...currentCentre, nomCentre: e.target.value })}
                  required
                />
              </div>
              <div className="form-group">
                <label>Ligne téléphonique de contact</label>
                <input 
                  type="text" 
                  placeholder="Ex: +229 23 11 00 11" 
                  value={currentCentre.contactCentre}
                  onChange={(e) => setCurrentCentre({ ...currentCentre, contactCentre: e.target.value })}
                />
              </div>
              <div className="form-group">
                <label>Quartier ou Village d'implantation</label>
                <select 
                  value={currentCentre.id_quartier_centre}
                  onChange={(e) => setCurrentCentre({ ...currentCentre, id_quartier_centre: e.target.value })}
                  required
                >
                  <option value="">-- Sélectionner l'emplacement exact --</option>
                  {quartiers.map((q) => (
                    <option key={q.id_quartier} value={q.id_quartier}>
                      {q.nom_quartier} (Arrondissement ID: {q.id_arrondissement})
                    </option>
                  ))}
                </select>
              </div>
              <div className="modal-actions">
                <button type="button" className="btn-cancel" onClick={() => setModalOuvert(false)}>Annuler</button>
                <button type="submit" className="btn-save">Enregistrer</button>
              </div>
            </form>
          </div>
        </div>
      )}

      {/* Styles CSS harmonisés */}
      <style>{`
        .management-container { width: 100%; box-sizing: border-box; }
        .content-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; }
        .content-header h2 { font-size: 1.4rem; color: #0f172a; margin: 0 0 4px 0; font-weight: 700; }
        .subtitle { font-size: 0.88rem; color: #64748b; margin: 0; }
        .btn-add { background: #000; color: #fff; border: none; padding: 10px 18px; border-radius: 8px; font-weight: 600; cursor: pointer; }
        
        .search-box { margin-bottom: 24px; }
        .search-box input { width: 100%; padding: 12px 14px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 0.95rem; }
        
        .table-card { background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; overflow: hidden; }
        .admin-table { width: 100%; border-collapse: collapse; text-align: left; }
        .admin-table th { background: #f8fafc; padding: 14px 16px; font-size: 0.82rem; text-transform: uppercase; color: #64748b; font-weight: 700; border-bottom: 1px solid #e2e8f0; }
        .admin-table td { padding: 14px 16px; border-bottom: 1px solid #f1f5f9; font-size: 0.95rem; color: #334155; }
        .id-cell { font-family: monospace; font-weight: 700; color: #64748b; }
        .badge-geo { background: #eff6ff; color: #1e40af; padding: 4px 8px; border-radius: 4px; font-size: 0.82rem; font-weight: 600; }
        .no-data { color: #94a3b8; font-style: italic; font-size: 0.88rem; }
        
        .actions-wrapper { display: flex; gap: 8px; justify-content: flex-end; }
        .btn-edit { background: #f1f5f9; border: 1px solid #cbd5e1; padding: 6px 12px; border-radius: 6px; cursor: pointer; color: #334155; font-size: 0.85rem; }
        .btn-delete { background: #fef2f2; border: 1px solid #fca5a5; color: #991b1b; padding: 6px 12px; border-radius: 6px; cursor: pointer; font-size: 0.85rem; }
        .empty-cell { text-align: center; padding: 30px !important; color: #64748b; }
        .loader { padding: 40px; text-align: center; color: #64748b; }
        .modal-overlay { position: fixed; inset: 0; background: rgba(15, 23, 42, 0.3); display: flex; justify-content: center; align-items: center; z-index: 200; }
        .modal-card { background: #fff; width: 100%; max-width: 460px; padding: 28px; border-radius: 12px; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1); }
        .modal-card h3 { margin: 0 0 20px 0; font-size: 1.15rem; font-weight: 700; }
        .form-group { display: flex; flex-direction: column; gap: 6px; margin-bottom: 16px; text-align: left; }
        .form-group label { font-size: 0.85rem; font-weight: 600; color: #475569; }
        .form-group input, .form-group select { padding: 10px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 0.92rem; background: #fff; width: 100%; box-sizing: border-box; }
        .modal-actions { display: flex; justify-content: flex-end; gap: 10px; border-top: 1px solid #f1f5f9; padding-top: 14px; margin-top: 20px; }
        .btn-cancel { background: #fff; border: 1px solid #cbd5e1; padding: 8px 14px; border-radius: 6px; cursor: pointer; }
        .btn-save { background: #000; color: #fff; border: none; padding: 8px 18px; border-radius: 6px; font-weight: 600; cursor: pointer; }
      `}</style>
    </div>
  );
}