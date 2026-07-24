import { useState } from "react";

export default function Inscriptionartisan({ onInscriptionSuccess }) {
  // États locaux pour gérer les champs du formulaire
  const [formData, setFormData] = useState({
    npi: "",
    nom: "",
    prenom: ""
  });
  
  const [chargement, setChargement] = useState(false);
  const [erreur, setErreur] = useState(null);

  const handleInputChange = (e) => {
    const { name, value } = e.target;
    // Supprimer les espaces pour le NPI
    const cleanValue = name === "npi" ? value.replace(/\s/g, "") : value;
    setFormData({ ...formData, [name]: cleanValue });
  };

  const handleSubmit = (e) => {
    e.preventDefault();
    setChargement(true);
    setErreur(null);

    // Appel API vers votre script PHP d'inscription initiale
    fetch("http://localhost/backend/api/artisan/inscription.php", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify(formData)
    })
      .then((res) => {
        if (!res.ok && res.status !== 403) {
          throw new Error("Une erreur est survenue lors de l'inscription.");
        }
        return res.json();
      })
      .then((data) => {
        setChargement(false);
        if (data.success) {
          // Succès : On transmet les infos (idTest, idArtisan, dejaInscrit) au composant parent
          // pour basculer automatiquement vers l'Étape 2 (ConfirmationParcours.jsx)
          if (onInscriptionSuccess) {
            onInscriptionSuccess(data);
          }
        } else {
          // Cas d'erreur géré par le PHP (Ex: 403 - Test déjà débuté ou soumis)
          setErreur(data.message);
        }
      })
      .catch((err) => {
        setChargement(false);
        setErreur(err.message || "Impossible de joindre le serveur local.");
      });
  };

  return (
    <div className="register-wrapper">
      <div className="register-card">
        
        {/* En-tête de la carte */}
        <div className="register-header">
          <div className="arch-badge">Programme ARCH - ANPS</div>
          <h2>Formulaire d'identification</h2>
          <p>Veuillez renseigner vos informations officielles pour accéder au test d'évaluation.</p>
        </div>

        {/* Affichage des messages d'erreur du serveur */}
        {erreur && (
          <div className="error-box">
            <span className="error-icon">❌</span>
            <p>{erreur}</p>
          </div>
        )}

        {/* Formulaire */}
        <form onSubmit={handleSubmit} className="register-form">
          <div className="form-stack">
            
            <div className="input-group">
              <label htmlFor="npi">Numéro Personnel d'Identification (NPI)</label>
              <input 
                type="text" 
                id="npi"
                name="npi" 
                value={formData.npi} 
                onChange={handleInputChange} 
                placeholder="Ex: 1234567890123" 
                maxLength="20"
                pattern="[0-9]+"
                title="Le NPI contient uniquement des chiffres"
                required 
              />
              <small className="input-help">Le NPI à 13 chiffres figure sur votre carte nationale d'identité biométrique ou votre acte de naissance ANIP.</small>
            </div>

            <div className="input-group">
              <label htmlFor="nom">Nom de famille</label>
              <input 
                type="text" 
                id="nom"
                name="nom" 
                value={formData.nom} 
                onChange={handleInputChange} 
                placeholder="En majuscules (Ex: DUPONT)" 
                required 
              />
            </div>

            <div className="input-group">
              <label htmlFor="prenom">Prénom(s)</label>
              <input 
                type="text" 
                id="prenom"
                name="prenom" 
                value={formData.prenom} 
                onChange={handleInputChange} 
                placeholder="Ex: Jean Koffi" 
                required 
              />
            </div>

          </div>

          <button type="submit" className="btn-submit" disabled={chargement}>
            {chargement ? "Vérification de vos identifiants..." : "Valider mon identification ➔"}
          </button>
        </form>

        <div className="register-footer">
          <p>Vos données sont protégées de manière sécurisée conformément aux réglementations de la Commission Nationale de l'Informatique et des Libertés du Bénin.</p>
        </div>
      </div>

      {/* Feuille de style CSS intégrée respectant strictement la charte épurée de vos écrans */}
      <style>{`
        .register-wrapper {
          display: flex;
          justify-content: center;
          align-items: center;
          min-height: 100vh;
          background-color: #f8fafc;
          padding: 20px;
          font-family: system-ui, -apple-system, sans-serif;
        }
        .register-card {
          background: #ffffff;
          border: 1px solid #e2e8f0;
          border-radius: 16px;
          width: 100%;
          max-width: 480px;
          padding: 40px 32px;
          box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        }
        .register-header {
          text-align: center;
          margin-bottom: 30px;
        }
        .arch-badge {
          display: inline-block;
          background: #eff6ff;
          color: #1e40af;
          font-size: 0.75rem;
          font-weight: 700;
          padding: 4px 12px;
          border-radius: 50px;
          margin-bottom: 12px;
          text-transform: uppercase;
          letter-spacing: 0.5px;
        }
        .register-header h2 {
          font-size: 1.4rem;
          color: #0f172a;
          margin: 0 0 8px 0;
          font-weight: 700;
        }
        .register-header p {
          font-size: 0.9rem;
          color: #64748b;
          margin: 0;
          line-height: 1.4;
        }
        
        /* Style de la boîte d'alerte d'erreur */
        .error-box {
          background-color: #fef2f2;
          border: 1px solid #fca5a5;
          padding: 12px 16px;
          border-radius: 8px;
          display: flex;
          align-items: center;
          gap: 12px;
          margin-bottom: 24px;
          text-align: left;
        }
        .error-icon { font-size: 1.1rem; }
        .error-box p { margin: 0; font-size: 0.88rem; color: #991b1b; font-weight: 500; line-height: 1.4; }

        .form-stack {
          display: flex;
          flex-direction: column;
          gap: 20px;
          margin-bottom: 28px;
        }
        .input-group {
          display: flex;
          flex-direction: column;
          gap: 6px;
          text-align: left;
        }
        .input-group label {
          font-size: 0.88rem;
          font-weight: 600;
          color: #334155;
        }
        .input-group input {
          width: 100%;
          padding: 12px 14px;
          border: 1px solid #cbd5e1;
          border-radius: 8px;
          font-size: 0.95rem;
          color: #0f172a;
          background-color: #ffffff;
          box-sizing: border-box;
          transition: border-color 0.2s;
        }
        .input-group input:focus {
          outline: none;
          border-color: #000000;
        }
        .input-help {
          font-size: 0.78rem;
          color: #64748b;
          line-height: 1.3;
          margin-top: 2px;
        }
        
        .btn-submit {
          width: 100%;
          background: #000000; /* Bouton principal noir homogène */
          color: #ffffff;
          border: none;
          padding: 14px;
          font-size: 1rem;
          font-weight: 600;
          border-radius: 8px;
          cursor: pointer;
          transition: background 0.2s;
        }
        .btn-submit:hover:not(:disabled) {
          background: #1e293b;
        }
        .btn-submit:disabled {
          background: #cbd5e1;
          color: #94a3b8;
          cursor: not-allowed;
        }
        .register-footer {
          margin-top: 28px;
          border-top: 1px solid #f1f5f9;
          padding-top: 16px;
          font-size: 0.76rem;
          color: #94a3b8;
          line-height: 1.4;
          text-align: center;
        }
      `}</style>
    </div>
  );
}
