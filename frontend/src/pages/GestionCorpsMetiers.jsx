import { useState, useEffect } from "react";

export default function GestionCorpsMetiers() {
  const [metiers, setMetiers] = useState([]);
  const [chargement, setChargement] = useState(true);
  const [recherche, setRecherche] = useState("");
  
  // États pour la fenêtre modale (Ajout / Modification)
  const [modalOuvert, setModalOuvert] = useState(false);
  const [modeEdition, setModeEdition] = useState(false);
  const [currentMetier, setCurrentMetier] = useState({ code_corpsmetier: "", libelle: "" });

  // 1. READ : Charger la liste depuis CRUD/Read_corpsmetier.php
  const chargerMetiers = () => {
    setChargement(true);
    fetch("http://localhost/Code/backend/CRUD/Read_corpsmetier.php")
      .then((res) => res.json())
      .then((data) => {
        if (data.success) setMetiers(data.data);
        setChargement(false);
      })
      .catch((err) => {
        console.error("Erreur d'API", err);
        setChargement(false);
      });
  };

  useEffect(() => {
    const timeoutId = window.setTimeout(() => {
      chargerMetiers();
    }, 0);

    return () => window.clearTimeout(timeoutId);
  }, []);

  // 2. CREATE & UPDATE : Soumission du formulaire vers le dossier CRUD/
  const handleFormSubmit = (e) => {
    e.preventDefault();
    const url = modeEdition
      ? "http://localhost/Code/backend/CRUD/Update_corpsmetier.php"
      : "http://localhost/Code/backend/CRUD/Create_corpsmetier.php";

    fetch(url, {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify(currentMetier)
    })
      .then((res) => res.json())
      .then((data) => {
        if (data.success) {
          alert(data.message);
          setModalOuvert(false);
          chargerMetiers(); // Actualise le tableau
        } else {
          alert(data.message);
        }
      });
  };

  // 3. DELETE : Suppression via CRUD/Delete_corpsmetier.php
  const handleSupprimer = (code) => {
    if (window.confirm(`Voulez-vous supprimer définitivement le corps de métier [${code}] ?`)) {
      fetch("http://localhost/Code/backend/CRUD/Delete_corpsmetier.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ code_corpsmetier: code })
      })
        .then((res) => res.json())
        .then((data) => {
          alert(data.message);
          if (data.success) chargerMetiers();
        });
    }
  };

  const ouvrirModalAjout = () => {
    setModeEdition(false);
    setCurrentMetier({ code_corpsmetier: "", libelle: "" });
    setModalOuvert(true);
  };

  const ouvrirModalEdition = (metier) => {
    setModeEdition(true);
    setCurrentMetier({ code_corpsmetier: metier.code_corpsmetier, libelle: metier.libelle });
    setModalOuvert(true);
  };

  // Filtrage de la liste en temps réel
  const metiersFiltres = metiers.filter((m) =>
    m.libelle.toLowerCase().includes(recherche.toLowerCase()) ||
    m.code_corpsmetier.toLowerCase().includes(recherche.toLowerCase())
  );

  return (
    <div className="management-container">
      <header className="content-header">
        <div>
          <h2>Référentiel des Corps de Métiers</h2>
          <p className="subtitle">Gestion des secteurs d'activité éligibles au programme national ARCH.</p>
        </div>
        <button className="btn-add" onClick={ouvrirModalAjout}>+ Nouveau Métier</button>
      </header>

      {/* Barre de recherche */}
      <div className="search-box">
        <input 
          type="text" 
          placeholder="🔍 Rechercher un métier par son libellé ou son code..." 
          value={recherche}
          onChange={(e) => setRecherche(e.target.value)}
        />
      </div>

      {/* Tableau des données */}
      <div className="table-card">
        {chargement ? (
          <div className="loader">Chargement des métiers...</div>
        ) : (
          <table className="admin-table">
            <thead>
              <tr>
                <th>Code Unique</th>
                <th>Secteur / Libellé du Métier</th>
                <th style={{ textAnlign: "right" }}>Actions</th>
              </tr>
            </thead>
            <tbody>
              {metiersFiltres.map((m) => (
                <tr key={m.code_corpsmetier}>
                  <td className="code-cell">{m.code_corpsmetier}</td>
                  <td><strong>{m.libelle}</strong></td>
                  <td>
                    <div className="actions-wrapper">
                      <button className="btn-edit" onClick={() => ouvrirModalEdition(m)}>Éditer</button>
                      <button className="btn-delete" onClick={() => handleSupprimer(m.code_corpsmetier)}>Supprimer</button>
                    </div>
                  </td>
                </tr>
              ))}
              {metiersFiltres.length === 0 && (
                <tr><td colSpan="3" className="empty-cell">Aucun corps de métier répertorié.</td></tr>
              )}
            </tbody>
          </table>
        )}
      </div>

      {/* BOÎTE MODALE FORMULAIRE */}
      {modalOuvert && (
        <div className="modal-overlay">
          <div className="modal-card">
            <h3>{modeEdition ? "Modifier le corps de métier" : "Créer un nouveau corps de métier"}</h3>
            <form onSubmit={handleFormSubmit}>
              <div className="form-group">
                <label>Code du Métier (Unique)</label>
                <input 
                  type="text" 
                  placeholder="Ex: MET-MACON" 
                  value={currentMetier.code_corpsmetier}
                  onChange={(e) => setCurrentMetier({ ...currentMetier, code_corpsmetier: e.target.value.toUpperCase() })}
                  required
                  disabled={modeEdition} // Bloqué en édition pour préserver l'intégrité relationnelle
                />
              </div>
              <div className="form-group">
                <label>Libellé complet</label>
                <input 
                  type="text" 
                  placeholder="Ex: Maçonnerie et Construction" 
                  value={currentMetier.libelle}
                  onChange={(e) => setCurrentMetier({ ...currentMetier, libelle: e.target.value })}
                  required
                />
              </div>
              <div className="modal-actions">
                <button type="button" className="btn-cancel" onClick={() => setModalOuvert(false)}>Annuler</button>
                <button type="submit" className="btn-save">Enregistrer</button>
              </div>
            </form>
          </div>
        </div>
      )}

      {/* Styles CSS harmonisés avec la charte d'administration */}
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
        .code-cell { font-family: monospace; font-weight: 700; color: #0f172a; background: #f8fafc; padding: 4px 8px !important; border-radius: 4px; display: inline-block; margin-top: 10px; }
        
        .actions-wrapper { display: flex; gap: 8px; justify-content: flex-end; }
        .btn-edit { background: #f1f5f9; border: 1px solid #cbd5e1; padding: 6px 12px; border-radius: 6px; cursor: pointer; color: #334155; font-size: 0.85rem; font-weight: 500; }
        .btn-delete { background: #fef2f2; border: 1px solid #fca5a5; color: #991b1b; padding: 6px 12px; border-radius: 6px; cursor: pointer; font-size: 0.85rem; }
        .empty-cell { text-align: center; padding: 30px !important; color: #64748b; }
        .loader { padding: 40px; text-align: center; color: #64748b; }

        /* Modal pop-up */
        .modal-overlay { position: fixed; inset: 0; background: rgba(15, 23, 42, 0.3); display: flex; justify-content: center; align-items: center; z-index: 200; }
        .modal-card { background: #fff; width: 100%; max-width: 440px; padding: 28px; border-radius: 12px; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1); }
        .modal-card h3 { margin: 0 0 20px 0; font-size: 1.15rem; font-weight: 700; }
        .form-group { display: flex; flex-direction: column; gap: 6px; margin-bottom: 16px; text-align: left; }
        .form-group label { font-size: 0.85rem; font-weight: 600; color: #475569; }
        .form-group input { padding: 10px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 0.92rem; }
        .form-group input:disabled { background: #f1f5f9; color: #94a3b8; cursor: not-allowed; }
        .modal-actions { display: flex; justify-content: flex-end; gap: 10px; border-top: 1px solid #f1f5f9; padding-top: 14px; margin-top: 20px; }
        .btn-cancel { background: #fff; border: 1px solid #cbd5e1; padding: 8px 14px; border-radius: 6px; cursor: pointer; font-weight: 500; }
        .btn-save { background: #000; color: #fff; border: none; padding: 8px 18px; border-radius: 6px; font-weight: 600; cursor: pointer; }
      `}</style>
    </div>
  );
}
