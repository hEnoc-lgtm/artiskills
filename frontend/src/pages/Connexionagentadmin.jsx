import { useState } from "react";

export default function ConnexionAgentAdmin({ onLoginSuccess, onRetour }) {
  const [adminData, setAdminData] = useState({
    identifiant: "",
    motDePasse: "",
  });

  const [chargement, setChargement] = useState(false);
  const [erreur, setErreur] = useState(null);

  const handleInputChange = (e) => {
    const { name, value } = e.target;
    setAdminData({ ...adminData, [name]: value });
    if (erreur) setErreur(null);
  };

  const handleSubmit = (e) => {
    e.preventDefault();
    setChargement(true);
    setErreur(null);

    // APPEL RÉEL À L'API PHP DE CONNEXION
    fetch("http://localhost/Code/backend/api/auth/login.php", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify(adminData)
    })
      .then(async (res) => {
        const data = await res.json();
        if (!res.ok || !data.success) {
          throw new Error(data.message || "Erreur de connexion");
        }
        return data.data; // On retourne l'objet contenant id_profil, nom, role
      })
      .then((userData) => {
        setChargement(false);
        // On transmet les données réelles de la BDD à App.jsx pour changer d'étape
        if (onLoginSuccess) {
          onLoginSuccess(userData);
        }
      })
      .catch((err) => {
        setChargement(false);
        setErreur(err.message || "Impossible de joindre le serveur.");
      });
  };

  return (
    <div className="admin-container">
      <div className="top-stripe" />

      <div className="admin-card">
        <div className="admin-header">
          <div className="admin-badge">Espace Sécurisé</div>
          <h2>Portail Administration</h2>
          <p>Réservé aux agents officiels et aux administrateurs du programme ARCH.</p>
        </div>

        {erreur && (
          <div className="error-box">
            <span className="error-icon">⚠️</span>
            <p>{erreur}</p>
          </div>
        )}

        <form onSubmit={handleSubmit} className="admin-form">
          <div className="form-stack">
            <div className="input-group">
              <label htmlFor="identifiant">Identifiant ou Matricule</label>
              <input
                type="text"
                id="identifiant"
                name="identifiant"
                value={adminData.identifiant}
                onChange={handleInputChange}
                placeholder="Ex: email@anps.bj ou 229XXXXXXXX"
                required
                disabled={chargement}
              />
            </div>

            <div className="input-group">
              <label htmlFor="motDePasse">Mot de passe</label>
              <input
                type="password"
                id="motDePasse"
                name="motDePasse"
                value={adminData.motDePasse}
                onChange={handleInputChange}
                placeholder="••••••••"
                required
                disabled={chargement}
              />
            </div>
          </div>

          <button type="submit" className="submit-btn" disabled={chargement}>
            {chargement ? "Connexion en cours..." : "Se connecter au tableau de bord"}
          </button>
        </form>

        <div className="admin-footer">
          <p>
            <button 
              type="button" 
              onClick={onRetour} 
              className="back-link"
              disabled={chargement}
            >
              ← Retour à l'accueil
            </button>
            <span className="separator">|</span>
            <span>Un problème d'accès ? Contactez le support technique.</span>
          </p>
        </div>
      </div>

      <style>{`
        @import url('https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800&display=swap');

        .admin-container {
          min-height: 100vh;
          background: #0f172a;
          display: flex;
          flex-direction: column;
          align-items: center;
          justify-content: center;
          padding: 20px;
          font-family: 'Montserrat', sans-serif;
        }
        .top-stripe {
          height: 6px;
          width: 100%;
          max-width: 440px;
          background: linear-gradient(to right, #008751 33.33%, #ffeb3b 33.33% 66.66%, #e81123 66.66%);
          border-radius: 6px 6px 0 0;
        }
        .admin-card {
          background: #ffffff;
          width: 100%;
          max-width: 440px;
          padding: 40px;
          box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.3);
          border-radius: 0 0 12px 12px;
        }
        .admin-header {
          margin-bottom: 30px;
          border-bottom: 1px solid #f1f5f9;
          padding-bottom: 20px;
          text-align: center;
        }
        .admin-badge {
          display: inline-block;
          background: #fef2f2;
          color: #991b1b;
          font-size: 0.75rem;
          font-weight: 700;
          padding: 4px 12px;
          border-radius: 50px;
          margin-bottom: 12px;
          border: 1px solid #fca5a5;
          text-transform: uppercase;
          letter-spacing: 0.5px;
        }
        .admin-header h2 {
          font-size: 1.5rem;
          color: #1e293b;
          margin: 0 0 8px 0;
          font-weight: 700;
        }
        .admin-header p {
          color: #64748b;
          margin: 0;
          font-size: 0.88rem;
          line-height: 1.4;
        }
        .error-box {
          background-color: #fef2f2;
          border: 1px solid #fca5a5;
          padding: 12px 16px;
          border-radius: 8px;
          display: flex;
          align-items: center;
          gap: 12px;
          margin-bottom: 24px;
        }
        .error-box p { 
          margin: 0; 
          font-size: 0.88rem; 
          color: #991b1b; 
          font-weight: 500; 
        }
        .form-stack {
          display: flex;
          flex-direction: column;
          gap: 20px;
          margin-bottom: 25px;
        }
        .input-group {
          display: flex;
          flex-direction: column;
          gap: 6px;
        }
        .input-group label {
          font-size: 0.85rem;
          font-weight: 600;
          color: #475569;
        }
        .input-group input {
          padding: 12px;
          border: 1px solid #cbd5e1;
          border-radius: 6px;
          font-size: 0.95rem;
          background: #ffffff;
          color: #0f172a;
          transition: all 0.2s ease;
          font-family: 'Montserrat', sans-serif;
        }
        .input-group input:focus {
          outline: none;
          border-color: #1e3a8a;
          box-shadow: 0 0 0 3px rgba(30, 58, 138, 0.15);
        }
        .input-group input:disabled {
          background-color: #f1f5f9;
          cursor: not-allowed;
        }
        .submit-btn {
          width: 100%;
          background: #1e3a8a;
          color: #ffffff;
          border: none;
          padding: 14px;
          font-size: 0.95rem;
          font-weight: 600;
          border-radius: 8px;
          cursor: pointer;
          transition: background 0.2s ease;
          font-family: 'Montserrat', sans-serif;
        }
        .submit-btn:hover:not(:disabled) {
          background: #172554;
        }
        .submit-btn:disabled {
          background: #94a3b8;
          cursor: not-allowed;
        }
        .admin-footer {
          margin-top: 25px;
          text-align: center;
          font-size: 0.8rem;
          color: #94a3b8;
          line-height: 1.4;
        }
        .back-link {
          background: none;
          border: none;
          color: #1e3a8a;
          cursor: pointer;
          text-decoration: underline;
          font-size: 0.8rem;
          font-family: 'Montserrat', sans-serif;
          padding: 0;
        }
        .back-link:hover:not(:disabled) {
          color: #172554;
        }
        .back-link:disabled {
          color: #94a3b8;
          cursor: not-allowed;
        }
        .separator {
          margin: 0 8px;
          color: #cbd5e1;
        }
      `}</style>
    </div>
  );
}