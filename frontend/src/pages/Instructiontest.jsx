import { useState } from "react";

export default function InstructionsTest({ onStartTest }) {
  // État pour la case à cocher
  const [accepteConditions, setAccepteConditions] = useState(false);

  const handleCheckboxChange = (e) => {
    setAccepteConditions(e.target.checked);
  };

  const handleStart = (e) => {
    e.preventDefault();
    if (accepteConditions && onStartTest) {
      onStartTest(); // Déclenche le passage au questionnaire de test
    }
  };

  return (
    <div className="instructions-wrapper">
      <div className="instructions-card">
        
        {/* Icône d'avertissement jaune centrée */}
        <div className="warning-icon-box">
          <div className="warning-circle">
            <span className="warning-exclamation">⚠️</span>
          </div>
        </div>

        <h2 className="instructions-title">Avant de commencer votre test</h2>

        {/* Liste des règles du test avec icônes */}
        <div className="rules-list">
          <div className="rule-item">
            <span className="rule-icon">🔄</span>
            <p>Vous ne disposez que d'une <strong>seule tentative</strong>.</p>
          </div>

          <div className="rule-item">
            <span className="rule-icon">🕒</span>
            <p>Le test est <strong>chronométré</strong> et sera soumis automatiquement à la fin du temps imparti.</p>
          </div>

          <div className="rule-item">
            <span className="rule-icon">🔒</span>
            <p>Chaque réponse donnée est <strong>définitive</strong> et non modifiable.</p>
          </div>

          <div className="rule-item">
            <span className="rule-icon">🚫</span>
            <p>Quitter l'application ne vous permettra pas de revoir les questions restantes.</p>
          </div>
        </div>

        {/* Case à cocher de validation */}
        <div className="checkbox-container">
          <input 
            type="checkbox" 
            id="conditions" 
            checked={accepteConditions} 
            onChange={handleCheckboxChange} 
          />
          <label htmlFor="conditions">J'ai lu et compris les conditions du test</label>
        </div>

        {/* Bouton d'action dynamique */}
        <button 
          onClick={handleStart} 
          className="btn-start" 
          disabled={!accepteConditions}
        >
          Commencer le test
        </button>

      </div>

      {/* Style CSS en stricte concordance avec vos écrans précédents */}
      <style>{`
        .instructions-wrapper {
          display: flex;
          justify-content: center;
          align-items: center;
          min-height: 100vh;
          background-color: #f8fafc;
          padding: 20px;
          font-family: system-ui, -apple-system, sans-serif;
        }
        .instructions-card {
          background: #ffffff;
          border: 1px solid #e2e8f0;
          border-radius: 16px;
          width: 100%;
          max-width: 540px;
          padding: 40px 32px;
          box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
          text-align: center;
        }
        
        /* Cerveau du visuel : L'icône d'avertissement */
        .warning-icon-box {
          display: flex;
          justify-content: center;
          margin-bottom: 24px;
        }
        .warning-circle {
          background-color: #fef3c7; /* Fond ambré/jaune très clair */
          width: 72px;
          height: 72px;
          border-radius: 50%;
          display: flex;
          justify-content: center;
          align-items: center;
        }
        .warning-exclamation {
          font-size: 2rem;
        }

        .instructions-title {
          font-size: 1.3rem;
          font-weight: 700;
          color: #0f172a;
          margin: 0 0 28px 0;
        }

        /* Alignement de la liste de blocage */
        .rules-list {
          display: flex;
          flex-direction: column;
          gap: 18px;
          text-align: left;
          margin-bottom: 32px;
          padding: 0 8px;
        }
        .rule-item {
          display: flex;
          align-items: flex-start;
          gap: 14px;
        }
        .rule-icon {
          font-size: 1.15rem;
          margin-top: 2px;
          color: #64748b;
          user-select: none;
        }
        .rule-item p {
          margin: 0;
          font-size: 0.95rem;
          color: #334155;
          line-height: 1.5;
        }

        /* Case à cocher */
        .checkbox-container {
          display: flex;
          align-items: center;
          gap: 10px;
          text-align: left;
          padding: 0 8px;
          margin-bottom: 24px;
          user-select: none;
        }
        .checkbox-container input[type="checkbox"] {
          width: 18px;
          height: 18px;
          accent-color: #000000; /* Coche noire élégante */
          cursor: pointer;
        }
        .checkbox-container label {
          font-size: 0.92rem;
          color: #1e293b;
          font-weight: 500;
          cursor: pointer;
        }

        /* Bouton d'action */
        .btn-start {
          width: 100%;
          background: #000000;
          color: #ffffff;
          border: none;
          padding: 14px;
          font-size: 1rem;
          font-weight: 600;
          border-radius: 8px;
          cursor: pointer;
          transition: all 0.2s ease;
        }
        .btn-start:hover:not(:disabled) {
          background: #1e293b;
        }
        .btn-start:disabled {
          background: #cbd5e1;
          color: #94a3b8;
          cursor: not-allowed;
        }
      `}</style>
    </div>
  );
}
