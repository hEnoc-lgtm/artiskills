import { useState, useEffect } from "react";

export default function GestionComptesAgents() {
  const [comptes, setComptes] = useState([]);
  const [chargement, setChargement] = useState(true);
  const [modalOuvert, setModalOuvert] = useState(false);
  const [modeEdition, setModeEdition] = useState(false);

  const [formData, setFormData] = useState({
    id_profil: "",
    nom: "",
    prenom: "",
    contact: "",
    sexe: "Masculin",
    motdepasse: "",
    emailPro: "",
    service: "",
    role: "agent simple"
  });

  const chargerComptes = () => {
    setChargement(true);
    fetch("http://localhost/votre_projet_backend/CRUD/Read_profil.php")
      .then((res) => res.json())
      .then((data) => {
        if (data.success) setComptes(data.data);
        setChargement(false);
      })
      .catch(() => {
        setChargement(false);
      });
  };

  useEffect(() => {
    const timeoutId = window.setTimeout(() => {
      chargerComptes();
    }, 0);

    return () => window.clearTimeout(timeoutId);
  }, []);

  const reinitialiserFormulaire = () => {
    setModeEdition(false);
    setFormData({
      id_profil: "",
      nom: "",
      prenom: "",
      contact: "",
      sexe: "Masculin",
      motdepasse: "",
      emailPro: "",
      service: "",
      role: "agent simple"
    });
    setModalOuvert(true);
  };

  const handleFormSubmit = (e) => {
    e.preventDefault();
    const url = modeEdition
      ? "http://localhost/votre_projet_backend/CRUD/Update_profil.php"
      : "http://localhost/votre_projet_backend/CRUD/Create_profil.php";

    fetch(url, {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify(formData)
    })
      .then((res) => res.json())
      .then((data) => {
        alert(data.message);
        if (data.success) {
          setModalOuvert(false);
          chargerComptes();
        }
      });
  };

  const handleSupprimer = (id) => {
    if (window.confirm("⚠️ Révoquer définitivement les accès de ce profil à l'application ANPS ?")) {
      fetch("http://localhost/votre_projet_backend/CRUD/Delete_profil.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ id_profil: id })
      })
        .then((res) => res.json())
        .then((data) => {
          alert(data.message);
          if (data.success) chargerComptes();
        });
    }
  };

  const ouvrirModalEdition = (id) => {
    setModeEdition(true);
    fetch(`http://localhost/votre_projet_backend/CRUD/Read_profil.php?id=${id}`)
      .then((res) => res.json())
      .then((data) => {
        if (data.success) {
          setFormData({ ...data.data, motdepasse: "PROTECTED" });
          setModalOuvert(true);
        }
      });
  };

  return (
    <div className="management-container">
      <header className="content-header">
        <div>
          <h2>Gérer profils</h2>
          <p className="subtitle">Administration des habilitations d'accès pour les agents simples et les administrateurs de l'ANPS.</p>
        </div>
        <button className="btn-add" onClick={reinitialiserFormulaire}>
          + Nouveau Profil
        </button>
      </header>

      <div className="table-card">
        {chargement ? (
          <div className="loader">Extraction de l'annuaire des profils...</div>
        ) : (
          <table className="admin-table">
            <thead>
              <tr>
                <th>ID</th>
                <th>Opérateur</th>
                <th>E-mail Pro</th>
                <th>Service Affecté</th>
                <th>Rôle (ENUM)</th>
                <th style={{ textAlign: "right" }}>Actions</th>
              </tr>
            </thead>
            <tbody>
              {comptes.map((c) => (
                <tr key={c.id_profil}>
                  <td className="id-cell">{c.id_profil}</td>
                  <td>
                    <strong>{c.nom}</strong> {c.prenom}
                  </td>
                  <td>{c.emailPro}</td>
                  <td>
                    <span className="service-tag">{c.service}</span>
                  </td>
                  <td>
                    <span className={`role-badge ${c.role === "administratueur" ? "admin" : "simple"}`}>
                      {c.role}
                    </span>
                  </td>
                  <td>
                    <div className="actions-wrapper">
                      <button className="btn-edit" onClick={() => ouvrirModalEdition(c.id_profil)}>
                        Éditer
                      </button>
                      <button className="btn-delete" onClick={() => handleSupprimer(c.id_profil)}>
                        Révoquer
                      </button>
                    </div>
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
        )}
      </div>

      {modalOuvert && (
        <div className="modal-overlay">
          <div className="modal-card macro">
            <h3>{modeEdition ? "Modifier le profil" : "Créer un nouveau profil ANPS"}</h3>
            <form onSubmit={handleFormSubmit}>
              <div className="modal-grid-2">
                <div className="form-group">
                  <label>Nom de famille</label>
                  <input type="text" value={formData.nom} onChange={(e) => setFormData({ ...formData, nom: e.target.value })} required />
                </div>
                <div className="form-group">
                  <label>Prénom(s)</label>
                  <input type="text" value={formData.prenom} onChange={(e) => setFormData({ ...formData, prenom: e.target.value })} required />
                </div>
                <div className="form-group">
                  <label>Téléphone Professionnel</label>
                  <input type="text" value={formData.contact} onChange={(e) => setFormData({ ...formData, contact: e.target.value })} required />
                </div>
                <div className="form-group">
                  <label>E-mail Professionnel</label>
                  <input type="email" value={formData.emailPro} onChange={(e) => setFormData({ ...formData, emailPro: e.target.value })} required disabled={modeEdition} />
                </div>
                <div className="form-group">
                  <label>Sexe</label>
                  <select value={formData.sexe} onChange={(e) => setFormData({ ...formData, sexe: e.target.value })}>
                    <option value="Masculin">Masculin</option>
                    <option value="Féminin">Féminin</option>
                  </select>
                </div>
                <div className="form-group">
                  <label>Service de rattachement</label>
                  <input type="text" placeholder="Ex: Informatique, Suivi ARCH" value={formData.service} onChange={(e) => setFormData({ ...formData, service: e.target.value })} required />
                </div>
                <div className="form-group">
                  <label>Rôle Système (ENUM)</label>
                  <select value={formData.role} onChange={(e) => setFormData({ ...formData, role: e.target.value })}>
                    <option value="agent simple">agent simple</option>
                    <option value="administratueur">administratueur</option>
                  </select>
                </div>
                {!modeEdition && (
                  <div className="form-group">
                    <label>Mot de passe secret</label>
                    <input type="password" value={formData.motdepasse} onChange={(e) => setFormData({ ...formData, motdepasse: e.target.value })} required />
                  </div>
                )}
              </div>

              <div className="modal-actions">
                <button type="button" className="btn-cancel" onClick={() => setModalOuvert(false)}>
                  Annuler
                </button>
                <button type="submit" className="btn-save">
                  {modeEdition ? "Mettre à jour" : "Valider le profil"}
                </button>
              </div>
            </form>
          </div>
        </div>
      )}

      <style>{`
        .management-container { width: 100%; box-sizing: border-box; }
        .content-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; }
        .content-header h2 { font-size: 1.4rem; color: #0f172a; margin: 0; font-weight: 700; }
        .subtitle { font-size: 0.88rem; color: #64748b; margin-top: 4px; }
        .btn-add { background: #000; color: #fff; border: none; padding: 10px 18px; border-radius: 8px; font-weight: 600; cursor: pointer; }
        .table-card { background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; overflow: hidden; }
        .admin-table { width: 100%; border-collapse: collapse; text-align: left; }
        .admin-table th { background: #f8fafc; padding: 14px 16px; font-size: 0.82rem; text-transform: uppercase; color: #64748b; font-weight: 700; border-bottom: 1px solid #e2e8f0; }
        .admin-table td { padding: 14px 16px; border-bottom: 1px solid #f1f5f9; font-size: 0.95rem; color: #334155; }
        .id-cell { font-family: monospace; font-weight: 700; color: #94a3b8; }
        .service-tag { background: #f1f5f9; color: #475569; padding: 2px 6px; border-radius: 4px; font-size: 0.85rem; font-weight: 500; }
        .role-badge { padding: 4px 10px; border-radius: 50px; font-size: 0.8rem; font-weight: 600; }
        .role-badge.admin { background: #fef2f2; color: #991b1b; border: 1px solid #fca5a5; }
        .role-badge.simple { background: #eff6ff; color: #1e40af; border: 1px solid #bfdbfe; }
        .actions-wrapper { display: flex; gap: 8px; justify-content: flex-end; }
        .btn-edit { background: #f1f5f9; border: 1px solid #cbd5e1; padding: 6px 12px; border-radius: 6px; cursor: pointer; color: #334155; font-size: 0.85rem; }
        .btn-delete { background: #fef2f2; border: 1px solid #fca5a5; color: #991b1b; padding: 6px 12px; border-radius: 6px; cursor: pointer; font-size: 0.85rem; }
        .modal-overlay { position: fixed; inset: 0; background: rgba(15, 23, 42, 0.3); display: flex; justify-content: center; align-items: center; z-index: 200; }
        .modal-card.macro { background: #fff; width: 100%; max-width: 560px; padding: 28px; border-radius: 12px; box-shadow: 0 20px 25px rgba(0, 0, 0, 0.1); }
        .modal-card h3 { margin: 0 0 20px 0; font-weight: 700; }
        .modal-grid-2 { display: grid; grid-template-columns: repeat(2, 1fr); gap: 14px; text-align: left; }
        .form-group { display: flex; flex-direction: column; gap: 6px; }
        .form-group label { font-size: 0.85rem; font-weight: 600; color: #475569; }
        .form-group input, .form-group select { padding: 10px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 0.92rem; background: #fff; width: 100%; box-sizing: border-box; }
        .modal-actions { display: flex; justify-content: flex-end; gap: 10px; border-top: 1px solid #f1f5f9; padding-top: 14px; margin-top: 20px; }
        .btn-cancel { background: #fff; border: 1px solid #cbd5e1; padding: 8px 14px; border-radius: 6px; cursor: pointer; }
        .btn-save { background: #000; color: #fff; border: none; padding: 8px 18px; border-radius: 6px; font-weight: 600; cursor: pointer; }
        .loader { padding: 40px; text-align: center; color: #64748b; }
      `}</style>
    </div>
  );
}
