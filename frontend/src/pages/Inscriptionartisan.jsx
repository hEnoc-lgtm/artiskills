import { useState } from "react";

export default function InscriptionArtisan() {
  const [formData, setFormData] = useState({
    nom: "",
    prenom: "",
    sexe: "",
    contact: "",
    codePin: "",
  });

  const handleInputChange = (e) => {
    const { name, value } = e.target;
    setFormData({ ...formData, [name]: value });
  };

  const handleSubmit = (e) => {
    e.preventDefault();
    console.log("Données prêtes pour le CRUD PHP :", formData);
    // Ici se fera l'appel fetch() vers votre fichier PHP
  };

  return (
    <div className="register-container">
      {/* Bande tricolore officielle du Bénin */}
      <div className="top-stripe" />

      <div className="register-card">
        <div className="register-header">
          <h2>Inscription de l'Artisan</h2>
          <p>Créez votre profil pour planifier votre test d'évaluation.</p>
        </div>

        <form onSubmit={handleSubmit} className="register-form">
          <div className="form-grid">
            
            <div className="input-group">
              <label>Nom</label>
              <input 
                type="text" 
                name="nom" 
                value={formData.nom} 
                onChange={handleInputChange} 
                placeholder="Ex: HOUNDEGNON" 
                required 
              />
            </div>

            <div className="input-group">
              <label>Prénom(s)</label>
              <input 
                type="text" 
                name="prenom" 
                value={formData.prenom} 
                onChange={handleInputChange} 
                placeholder="Ex: Koffi" 
                required 
              />
            </div>

            <div className="input-group">
              <label>Sexe</label>
              <select name="sexe" value={formData.sexe} onChange={handleInputChange} required>
                <option value="">Sélectionnez...</option>
                <option value="M">Masculin</option>
                <option value="F">Féminin</option>
              </select>
            </div>

            <div className="input-group">
              <label>Numéro de Contact (Téléphone)</label>
              <input 
                type="tel" 
                name="contact" 
                value={formData.contact} 
                onChange={handleInputChange} 
                placeholder="Ex: 01XXXXXXXX" 
                required 
              />
            </div>

            <div className="input-group full-width">
              <label>Code PIN de Sécurité (4 chiffres)</label>
              <input 
                type="password" 
                name="codePin" 
                value={formData.codePin} 
                onChange={handleInputChange} 
                placeholder="••••" 
                maxLength={4}
                required 
              />
              <span className="input-help">Ce code secret vous servira à vous connecter plus tard.</span>
            </div>

          </div>

          <button type="submit" className="submit-btn">
            Créer mon compte ➔
          </button>
        </form>
      </div>

      <style>{`
        .register-container {
          min-height: 100vh;
          background: #f1f5f9;
          display: flex;
          flex-direction: column;
          align-items: center;
          padding: 60px 20px;
          font-family: 'Montserrat', sans-serif;
        }
        .top-stripe {
          height: 6px;
          width: 100%;
          max-width: 550px;
          background: linear-gradient(to right, #008751 33.33%, #ffeb3b 33.33% 66.66%, #e81123 66.66%);
          border-radius: 4px 4px 0 0;
        }
        .register-card {
          background: #ffffff;
          width: 100%;
          max-width: 550px;
          padding: 40px;
          box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.05);
          border-radius: 0 0 12px 12px;
        }
        .register-header {
          margin-bottom: 30px;
          border-bottom: 1px solid #e2e8f0;
          padding-bottom: 20px;
        }
        .register-header h2 {
          font-size: 1.6rem;
          color: #0f172a;
          margin: 0 0 6px 0;
          font-weight: 700;
        }
        .register-header p {
          color: #64748b;
          margin: 0;
          font-size: 0.9rem;
        }
        .form-grid {
          display: grid;
          grid-template-columns: repeat(2, 1fr);
          gap: 20px;
          margin-bottom: 30px;
        }
        .input-group {
          display: flex;
          flex-direction: column;
          gap: 6px;
        }
        .input-group.full-width {
          grid-column: span 2;
        }
        .input-group label {
          font-size: 0.85rem;
          font-weight: 600;
          color: #475569;
        }
        .input-group input, .input-group select {
          padding: 12px;
          border: 1px solid #cbd5e1;
          border-radius: 6px;
          font-size: 0.95rem;
          background: #ffffff;
          color: #0f172a;
          transition: all 0.2s ease;
        }
        .input-group input:focus, .input-group select:focus {
          outline: none;
          border-color: #008751;
          box-shadow: 0 0 0 3px rgba(0, 135, 81, 0.15);
        }
        .input-help {
          font-size: 0.8rem;
          color: #94a3b8;
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
        @media (max-width: 640px) {
          .form-grid { grid-template-columns: 1fr; }
          .input-group.full-width { grid-column: span 1; }
        }
      `}</style>
    </div>
  );
}
