import { useState } from "react";

export default function Connexionagentadmin() {
  const [adminData, setAdminData] = useState({
    identifiant: "",
    motDePasse: "",
  });

  const handleInputChange = (e) => {
    const { name, value } = e.target;
    setAdminData({ ...adminData, [name]: value });
  };

  const handleSubmit = (e) => {
    e.preventDefault();
    console.log("Données Admin prêtes pour l'API PHP :", adminData);
  };

  return (
    <div className="admin-container">
      <div className="top-stripe" />

      <div className="admin-card">
        <div className="admin-header">
          <div className="admin-badge">Espace Sécurisé</div>
          <h2>Portail Administration</h2>
          <p>Réservé aux agents officiels et aux administrateurs du programme.</p>
        </div>

        <form onSubmit={handleSubmit} className="admin-form">
          <div className="form-stack">
            <div className="input-group">
              <label>Identifiant ou Matricule</label>
              <input
                type="text"
                name="identifiant"
                value={adminData.identifiant}
                onChange={handleInputChange}
                placeholder="Ex: AGENT-2026-X"
                required
              />
            </div>

            <div className="input-group">
              <label>Mot de passe</label>
              <input
                type="password"
                name="motDePasse"
                value={adminData.motDePasse}
                onChange={handleInputChange}
                placeholder="••••••••"
                required
              />
            </div>
          </div>

          <button type="submit" className="submit-btn">
            Se connecter au tableau de bord ?
          </button>
        </form>

        <div className="admin-footer">
          <p>Un problème d'accès ? Contactez le support technique de la plateforme.</p>
        </div>
      </div>

      <style>{`
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
        }
        .input-group input:focus {
          outline: none;
          border-color: #1e3a8a;
          box-shadow: 0 0 0 3px rgba(30, 58, 138, 0.15);
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
        }
        .submit-btn:hover {
          background: #172554;
        }
        .admin-footer {
          margin-top: 25px;
          text-align: center;
          font-size: 0.8rem;
          color: #94a3b8;
          line-height: 1.4;
        }
      `}</style>
    </div>
  );
}
