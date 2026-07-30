import { useState, useEffect } from "react";

export default function GestionComptesAgents() {
  const [profils, setProfils] = useState([]);
  const [chargement, setChargement] = useState(true);
  const [erreur, setErreur] = useState(null);
  const [modeEdition, setModeEdition] = useState(false);
  const [profilSelectionne, setProfilSelectionne] = useState(null);
  const [showModal, setShowModal] = useState(false);
  
  const [formData, setFormData] = useState({
    nom: "",
    prenom: "",
    contact: "",
    sexe: "Masculin",
    emailPro: "",
    service: "",
    role: "agent simple",
    motDePasse: ""
  });

  useEffect(() => {
    chargerProfils();
  }, []);

    const chargerProfils = async () => {
    setChargement(true);
    try {
      const response = await fetch("http://localhost/Code/backend/CRUD/Read_profil.php");
      
      // Vérifier si le serveur a bien répondu (ex: pas de 404 ou 500)
      if (!response.ok) {
        throw new Error(`Erreur HTTP : ${response.status} ${response.statusText}`);
      }

      const data = await response.json();
      
      if (data.success) {
        setProfils(data.data || []);
      } else {
        setErreur(data.message || "Erreur côté serveur PHP");
      }
    } catch (err) {
      // ⚠️ C'EST ICI LA MAGIE : on affiche la VRAIE erreur au lieu du message générique
      console.error("Détails de l'erreur :", err); 
      setErreur("Erreur : " + err.message); 
    } finally {
      setChargement(false);
    }
  };

  const handleInputChange = (e) => {
    const { name, value } = e.target;
    setFormData({ ...formData, [name]: value });
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    setErreur(null);

    if (!formData.nom || !formData.prenom || !formData.emailPro || !formData.contact || !formData.sexe || !formData.role) {
      setErreur("Tous les champs obligatoires doivent être remplis");
      return;
    }

    if (!modeEdition && !formData.motDePasse) {
      setErreur("Le mot de passe est obligatoire pour la création");
      return;
    }

    const url = modeEdition 
      ? "http://localhost/Code/backend/CRUD/Update_profil.php"
      : "http://localhost/Code/backend/CRUD/Create_profil.php";

    const body = modeEdition 
      ? { ...formData, id_profil: profilSelectionne.id_profil }
      : formData;

    try {
      const response = await fetch(url, {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(body)
      });

      const data = await response.json();

      if (data.success) {
        setFormData({
          nom: "",
          prenom: "",
          contact: "",
          sexe: "Masculin",
          emailPro: "",
          service: "",
          role: "agent simple",
          motDePasse: ""
        });
        setModeEdition(false);
        setProfilSelectionne(null);
        setShowModal(false);
        chargerProfils();
        alert(modeEdition ? "Profil modifié avec succès" : "Profil créé avec succès");
      } else {
        setErreur(data.message || "Une erreur est survenue");
      }
    } catch (err) {
      setErreur("Impossible de joindre le serveur");
    }
  };

  const handleEdit = (profil) => {
    setProfilSelectionne(profil);
    setFormData({
      nom: profil.nom || "",
      prenom: profil.prenom || "",
      contact: profil.contact || "",
      sexe: profil.sexe || "Masculin",
      emailPro: profil.emailPro || "",
      service: profil.service || "",
      role: profil.role || "agent simple",
      motDePasse: ""
    });
    setModeEdition(true);
    setShowModal(true);
  };

  const handleDelete = async (id) => {
    if (!window.confirm("Êtes-vous sûr de vouloir supprimer ce profil ?")) {
      return;
    }

    try {
      const response = await fetch("http://localhost/Code/backend/CRUD/Delete_profil.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ id_profil: id })
      });

      const data = await response.json();

      if (data.success) {
        chargerProfils();
        alert("Profil supprimé avec succès");
      } else {
        setErreur(data.message || "Erreur lors de la suppression");
      }
    } catch (err) {
      setErreur("Impossible de joindre le serveur");
    }
  };

  const resetForm = () => {
    setFormData({
      nom: "",
      prenom: "",
      contact: "",
      sexe: "Masculin",
      emailPro: "",
      service: "",
      role: "agent simple",
      motDePasse: ""
    });
    setModeEdition(false);
    setProfilSelectionne(null);
    setShowModal(false);
    setErreur(null);
  };

  return (
    <div className="gestion-profils-container">
      <div className="page-header">
        <h1>Gestion des Comptes Agents</h1>
        <button className="btn-primary" onClick={() => setShowModal(true)}>
          + Ajouter un agent
        </button>
      </div>

      {erreur && (
        <div className="alert alert-error">
          <span>⚠️</span> {erreur}
          <button onClick={() => setErreur(null)}>×</button>
        </div>
      )}

      {chargement ? (
        <div className="loading">Chargement des profils...</div>
      ) : (
        <div className="table-container">
          <table className="data-table">
            <thead>
              <tr>
                <th>Nom</th>
                <th>Prénom</th>
                <th>Email</th>
                <th>Contact</th>
                <th>Sexe</th>
                <th>Service</th>
                <th>Rôle</th>
                <th>Dernier accès</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              {profils.map((profil) => (
                <tr key={profil.id_profil}>
                  <td>{profil.nom}</td>
                  <td>{profil.prenom}</td>
                  <td>{profil.emailPro}</td>
                  <td>{profil.contact}</td>
                  <td>{profil.sexe}</td>
                  <td>{profil.service}</td>
                  <td>
                    <span className={`badge ${profil.role === 'administratueur' ? 'badge-admin' : 'badge-agent'}`}>
                      {profil.role === 'administratueur' ? 'Administrateur' : 'Agent Simple'}
                    </span>
                  </td>
                  <td>{profil.dernierAcces ? new Date(profil.dernierAcces).toLocaleString('fr-FR') : 'Jamais'}</td>
                  <td className="actions">
                    <button className="btn-edit" onClick={() => handleEdit(profil)}>
                      ✏️ Modifier
                    </button>
                    <button className="btn-delete" onClick={() => handleDelete(profil.id_profil)}>
                      🗑️ Supprimer
                    </button>
                  </td>
                </tr>
              ))}
              {profils.length === 0 && (
                <tr>
                  <td colSpan="9" style={{ textAlign: "center", padding: "20px", color: "#64748b" }}>
                    Aucun profil enregistré
                  </td>
                </tr>
              )}
            </tbody>
          </table>
        </div>
      )}

      {showModal && (
        <div className="modal-overlay" onClick={resetForm}>
          <div className="modal-content" onClick={(e) => e.stopPropagation()}>
            <div className="modal-header">
              <h2>{modeEdition ? "Modifier le profil" : "Ajouter un nouvel agent"}</h2>
              <button className="btn-close" onClick={resetForm}>×</button>
            </div>

            <form onSubmit={handleSubmit} className="profil-form">
              <div className="form-row">
                <div className="form-group">
                  <label>Nom *</label>
                  <input type="text" name="nom" value={formData.nom} onChange={handleInputChange} required placeholder="Ex: DOSSOU" />
                </div>
                <div className="form-group">
                  <label>Prénom *</label>
                  <input type="text" name="prenom" value={formData.prenom} onChange={handleInputChange} required placeholder="Ex: Jean" />
                </div>
              </div>

              <div className="form-row">
                <div className="form-group">
                  <label>Email professionnel *</label>
                  <input type="email" name="emailPro" value={formData.emailPro} onChange={handleInputChange} required placeholder="Ex: jean@anps.bj" />
                </div>
                <div className="form-group">
                  <label>Contact *</label>
                  <input type="tel" name="contact" value={formData.contact} onChange={handleInputChange} required placeholder="Ex: 229XXXXXXXX" pattern="[0-9]+" />
                </div>
              </div>

              <div className="form-row">
                <div className="form-group">
                  <label>Sexe *</label>
                  <select name="sexe" value={formData.sexe} onChange={handleInputChange} required>
                    <option value="Masculin">Masculin</option>
                    <option value="Féminin">Féminin</option>
                  </select>
                </div>
                <div className="form-group">
                  <label>Rôle *</label>
                  <select name="role" value={formData.role} onChange={handleInputChange} required>
                    <option value="agent simple">Agent Simple</option>
                    <option value="administratueur">Administrateur</option>
                  </select>
                </div>
              </div>

              <div className="form-group">
                <label>Service</label>
                <input type="text" name="service" value={formData.service} onChange={handleInputChange} placeholder="Ex: Direction ARCH Formation" />
              </div>

              <div className="form-group">
                <label>{modeEdition ? "Nouveau mot de passe (laisser vide pour conserver)" : "Mot de passe *"}</label>
                <input type="password" name="motDePasse" value={formData.motDePasse} onChange={handleInputChange} required={!modeEdition} placeholder="••••••••" />
              </div>

              <div className="modal-footer">
                <button type="button" className="btn-secondary" onClick={resetForm}>Annuler</button>
                <button type="submit" className="btn-primary">{modeEdition ? "Modifier" : "Créer"}</button>
              </div>
            </form>
          </div>
        </div>
      )}

      <style>{`
        .gestion-profils-container { padding: 20px; font-family: 'Montserrat', sans-serif; }
        .page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; }
        .page-header h1 { font-size: 1.8rem; color: #0f172a; margin: 0; }
        .btn-primary { background: #1e3a8a; color: white; border: none; padding: 12px 24px; border-radius: 8px; font-size: 0.95rem; font-weight: 600; cursor: pointer; }
        .btn-primary:hover { background: #172554; }
        .alert { padding: 12px 16px; border-radius: 8px; margin-bottom: 20px; display: flex; align-items: center; gap: 10px; }
        .alert-error { background: #fef2f2; border: 1px solid #fca5a5; color: #991b1b; }
        .alert button { margin-left: auto; background: none; border: none; font-size: 1.2rem; cursor: pointer; }
        .loading { text-align: center; padding: 40px; color: #64748b; }
        .table-container { background: white; border: 1px solid #e2e8f0; border-radius: 12px; overflow: hidden; }
        .data-table { width: 100%; border-collapse: collapse; }
        .data-table th { background: #f8fafc; padding: 14px 16px; text-align: left; font-size: 0.85rem; font-weight: 700; color: #64748b; text-transform: uppercase; border-bottom: 2px solid #e2e8f0; }
        .data-table td { padding: 14px 16px; border-bottom: 1px solid #f1f5f9; font-size: 0.95rem; color: #334155; }
        .data-table tr:hover { background: #f8fafc; }
        .badge { padding: 4px 12px; border-radius: 20px; font-size: 0.8rem; font-weight: 600; }
        .badge-admin { background: #7f1d1d; color: #fecaca; }
        .badge-agent { background: #14532d; color: #bbf7d0; }
        .actions { display: flex; gap: 8px; }
        .btn-edit { background: #f59e0b; color: white; border: none; padding: 6px 12px; border-radius: 6px; font-size: 0.85rem; cursor: pointer; }
        .btn-edit:hover { background: #d97706; }
        .btn-delete { background: #ef4444; color: white; border: none; padding: 6px 12px; border-radius: 6px; font-size: 0.85rem; cursor: pointer; }
        .btn-delete:hover { background: #dc2626; }
        .modal-overlay { position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0, 0, 0, 0.5); display: flex; align-items: center; justify-content: center; z-index: 1000; }
        .modal-content { background: white; border-radius: 12px; width: 90%; max-width: 700px; max-height: 90vh; overflow-y: auto; }
        .modal-header { display: flex; justify-content: space-between; align-items: center; padding: 20px 24px; border-bottom: 1px solid #e2e8f0; }
        .modal-header h2 { margin: 0; font-size: 1.4rem; color: #0f172a; }
        .btn-close { background: none; border: none; font-size: 1.8rem; cursor: pointer; color: #64748b; }
        .profil-form { padding: 24px; }
        .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px; }
        .form-group { display: flex; flex-direction: column; gap: 6px; }
        .form-group label { font-size: 0.9rem; font-weight: 600; color: #475569; }
        .form-group input, .form-group select { padding: 10px 12px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 0.95rem; font-family: 'Montserrat', sans-serif; }
        .form-group input:focus, .form-group select:focus { outline: none; border-color: #1e3a8a; box-shadow: 0 0 0 3px rgba(30, 58, 138, 0.1); }
        .modal-footer { display: flex; justify-content: flex-end; gap: 12px; margin-top: 20px; padding-top: 20px; border-top: 1px solid #e2e8f0; }
        .btn-secondary { background: #f1f5f9; color: #475569; border: none; padding: 10px 20px; border-radius: 6px; font-size: 0.95rem; font-weight: 600; cursor: pointer; }
        .btn-secondary:hover { background: #e2e8f0; }
      `}</style>
    </div>
  );
}