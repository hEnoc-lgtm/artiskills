import { useState } from "react";

export default function ConnexionArtisan() {
  const [loginData, setLoginData] = useState({
    contact: "",
    codePin: "",
  });

  const handleInputChange = (e) => {
    const { name, value } = e.target;
    setLoginData({ ...loginData, [name]: value });
  };

  const handleSubmit = (e) => {
    e.preventDefault();
    console.log("Données envoyées pour vérification PHP :", loginData);
    // Ici se fera l'appel fetch() vers votre fichier PHP de traitement de connexion
  };

  return (
    <div className="login-container">
      {/* Bande tricolore officielle du Bénin */}
      <div className="top-stripe" />

      <div className="login-card">
        <div className="login-header">
          <h2>Espace Artisan</h2>
          <p>Connectez-vous pour passer votre test.</p>
        </div>

        <form onSubmit={handleSubmit} className="login-form">
          <div className="form-stack">
            
            <div className="input-group">
              <label>Numéro de Contact (Téléphone)</label>
              <input 
                type="tel" 
                name="contact" 
                value={loginData.contact} 
                onChange={handleInputChange} 
                placeholder="Ex: 01XXXXXXXX" 
                required 
              />
            </div>

            <div className="input-group">
              <label>Code PIN secret (4 chiffres)</label>
              <input 
                type="password" 
                name="codePin" 
                value={loginData.codePin} 
                onChange={handleInputChange} 
                placeholder="••••" 
                maxLength={4}
                required 
              />
            </div>

          </div>

          <button type="submit" className="submit-btn">
            Se connecter ➔
          </button>

          <div className="login-footer">
            <p>Pas encore inscrit ? <a href="#inscription">Créer un compte</a></p>
          </div>
        </form>
      </div>

      <style>{`
        .login-container {
          min-height: 100vh;
          background: #f1f5f9;
          display: flex;
          flex-direction: column;
          align-items: center;
          padding: 80px 20px;
          font-family: 'Montserrat', sans-serif;
        }
        .top-stripe {
          height: 6px;
          width: 100%;
          max-width: 450px;
          background: linear-gradient(to right, #008751 33.33%, #ffeb3b 33.33% 66.66%, #e81123 66.66%);
          border-radius: 4px 4px 0 0;
        }
        .login-card {
          background: #ffffff;
          width: 100%;
          max-width: 450px;
          padding: 40px;
          box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.05);
          border-radius: 0 0 12px 12px;
        }
        .login-header {
          margin-bottom: 30px;
          border-bottom: 1px solid #e2e8f0;
          padding-bottom: 20px;
          text-align: center;
        }
        .login-header h2 {
          font-size: 1.6rem;
          color: #0f172a;
          margin: 0 0 6px 0;
          font-weight: 700;
        }
        .login-header p {
          color: #64748b;
          margin: 0;
          font-size: 0.9rem;
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
          border-color: #008751;
          box-shadow: 0 0 0 3px rgba(0, 135, 81, 0.15);
        }
        .submit-btn {
          width: 100%;
          background: #0f172a;
          color: #ffffff;
          border: none;
          padding: 14px;
          font-size: 1rem;
          font-weight: 600;
          border-radius: 8px;
          cursor: pointer;
          transition: background 0.2s ease;
        }
        .submit-btn:hover {
          background: #1e293b;
        }
        .login-footer {
          margin-top: 20px;
          text-align: center;
          font-size: 0.85rem;
        }
        .login-footer p {
          color: #64748b;
          margin: 0;
        }
        .login-footer a {
          color: #008751;
          text-decoration: none;
          font-weight: 600;
        }
        .login-footer a:hover {
          text-decoration: underline;
        }
      `}</style>
    </div>
  );
}
