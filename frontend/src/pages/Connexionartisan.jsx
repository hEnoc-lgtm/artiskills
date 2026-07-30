import { useState } from "react";

// 1. On ajoute la prop "onRetour" pour recevoir la fonction de navigation depuis App.jsx
export default function ConnexionArtisan({ onRetour }) {
  const [credentials, setCredentials] = useState({ contact: "", codePin: "" });
  const [chargement, setChargement] = useState(false); // Ajouté pour gérer l'état de chargement

  const handleInputChange = (e) => {
    const { name, value } = e.target;
    setCredentials({ ...credentials, [name]: value });
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    setChargement(true);
    
    // TODO: envoyer les informations au backend pour vérification
    console.log("Connexion artisan :", credentials);
    
    // Simulation d'attente (à remplacer par votre vrai fetch plus tard)
    setTimeout(() => {
      setChargement(false);
      // onLoginSuccess(data) sera appelé ici plus tard
    }, 1000);
  };

  return (
    <div className="connexion-container">
      <div className="connexion-card">
        <div className="connexion-header">
          <h2>Connexion Artisan</h2>
          <p>Entrez votre contact et votre code PIN pour accéder à votre espace test.</p>
        </div>

        <form onSubmit={handleSubmit} className="connexion-form">
          <div className="input-group">
            <label htmlFor="contact">Numéro de contact</label>
            <input
              type="tel"
              id="contact"
              name="contact"
              value={credentials.contact}
              onChange={handleInputChange}
              placeholder="Ex: 229XXXXXXXX"
              required
              disabled={chargement}
            />
          </div>

          <div className="input-group">
            <label htmlFor="codePin">Code PIN</label>
            <input
              type="password"
              id="codePin"
              name="codePin"
              value={credentials.codePin}
              onChange={handleInputChange}
              placeholder="••••"
              maxLength={4}
              required
              disabled={chargement}
            />
          </div>

          <button type="submit" className="submit-btn" disabled={chargement}>
            {chargement ? "Vérification..." : "Se connecter"}
          </button>

          {/* 2. NOUVEAU BOUTON DE RETOUR */}
          <button 
            type="button" 
            className="back-btn" 
            onClick={onRetour}
            disabled={chargement}
          >
            ← Retour à l'accueil
          </button>
        </form>
      </div>

      <style>{`
        .connexion-container {
          min-height: 100vh;
          display: flex;
          justify-content: center;
          align-items: center;
          padding: 30px 20px;
          background: linear-gradient(180deg, #f8fafc 0%, #e2e8f0 100%);
          font-family: 'Montserrat', sans-serif;
        }
        .connexion-card {
          width: 100%;
          max-width: 420px;
          background: #ffffff;
          border-radius: 20px;
          box-shadow: 0 24px 60px rgba(15, 23, 42, 0.12);
          padding: 36px 34px;
          border: 1px solid rgba(15, 23, 42, 0.08);
        }
        .connexion-header {
          margin-bottom: 28px;
          text-align: center;
        }
        .connexion-header h2 {
          margin: 0 0 10px;
          font-size: 1.9rem;
          color: #0f172a;
          font-weight: 800;
        }
        .connexion-header p {
          margin: 0;
          color: #475569;
          line-height: 1.6;
        }
        .connexion-form {
          display: grid;
          gap: 20px;
        }
        .input-group {
          display: flex;
          flex-direction: column;
          gap: 8px;
        }
        .input-group label {
          font-size: 0.9rem;
          font-weight: 600;
          color: #334155;
        }
        .input-group input {
          padding: 14px 16px;
          border: 1px solid #cbd5e1;
          border-radius: 10px;
          font-size: 1rem;
          color: #0f172a;
          background: #f8fafc;
          font-family: 'Montserrat', sans-serif;
          transition: all 0.2s ease;
        }
        .input-group input:focus {
          outline: none;
          border-color: #0f172a;
          box-shadow: 0 0 0 4px rgba(15, 23, 42, 0.07);
          background: #ffffff;
        }
        .input-group input:disabled {
          background: #f1f5f9;
          color: #94a3b8;
          cursor: not-allowed;
        }
        .submit-btn {
          width: 100%;
          padding: 14px 18px;
          background: #0f172a;
          color: #ffffff;
          border: none;
          border-radius: 10px;
          font-size: 1rem;
          font-weight: 700;
          cursor: pointer;
          transition: background 0.2s ease, transform 0.2s ease;
          font-family: 'Montserrat', sans-serif;
        }
        .submit-btn:hover:not(:disabled) {
          background: #1e293b;
          transform: translateY(-1px);
        }
        .submit-btn:disabled {
          background: #94a3b8;
          cursor: not-allowed;
          transform: none;
        }

        /* 3. NOUVEAU STYLE POUR LE BOUTON DE RETOUR */
        .back-btn {
          width: 100%;
          padding: 12px 18px;
          background: transparent;
          color: #475569;
          border: 1px solid #cbd5e1;
          border-radius: 10px;
          font-size: 0.95rem;
          font-weight: 600;
          cursor: pointer;
          transition: all 0.2s ease;
          font-family: 'Montserrat', sans-serif;
        }
        .back-btn:hover:not(:disabled) {
          background: #f1f5f9;
          color: #0f172a;
          border-color: #94a3b8;
        }
        .back-btn:disabled {
          color: #94a3b8;
          border-color: #e2e8f0;
          cursor: not-allowed;
        }

        @media (max-width: 600px) {
          .connexion-card {
            padding: 28px 22px;
          }
        }
      `}</style>
    </div>
  );
}