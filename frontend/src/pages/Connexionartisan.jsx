import { useState } from "react";

export default function ConnexionArtisan() {
  const [credentials, setCredentials] = useState({ contact: "", codePin: "" });

  const handleInputChange = (e) => {
    const { name, value } = e.target;
    setCredentials({ ...credentials, [name]: value });
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    // TODO: envoyer les informations au backend pour vérification
    console.log("Connexion artisan :", credentials);
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
            <label>Numéro de contact</label>
            <input
              type="tel"
              name="contact"
              value={credentials.contact}
              onChange={handleInputChange}
              placeholder="Ex: 01XXXXXXXX"
              required
            />
          </div>

          <div className="input-group">
            <label>Code PIN</label>
            <input
              type="password"
              name="codePin"
              value={credentials.codePin}
              onChange={handleInputChange}
              placeholder="••••"
              maxLength={4}
              required
            />
          </div>

          <button type="submit" className="submit-btn">
            Se connecter
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
        }
        .input-group input:focus {
          outline: none;
          border-color: #0f172a;
          box-shadow: 0 0 0 4px rgba(15, 23, 42, 0.07);
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
        }
        .submit-btn:hover {
          background: #1e293b;
          transform: translateY(-1px);
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
