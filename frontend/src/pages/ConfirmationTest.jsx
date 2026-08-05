import { useEffect } from "react";

export default function ConfirmationTest({ onRetourAccueil }) {
  useEffect(() => {
    // Déconnexion automatique après 5 secondes
    const timer = setTimeout(() => {
      if (onRetourAccueil) onRetourAccueil();
    }, 5000);

    return () => clearTimeout(timer);
  }, [onRetourAccueil]);

  return (
    <div className="confirmation-wrapper">
      <div className="confirmation-card">
        <div className="success-icon">
          <svg width="64" height="64" viewBox="0 0 24 24" fill="none">
            <circle cx="12" cy="12" r="12" fill="#86efac"/>
            <path d="M7 12L10.5 15.5L17 9" stroke="#166534" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"/>
          </svg>
        </div>
        
        <h2 className="confirmation-title">Test soumis avec succès</h2>
        
        <p className="confirmation-text">
          Votre test a bien été enregistré. L'ANPS vous contactera directement 
          pour vous communiquer les suites de votre évaluation.
        </p>
        
        <p className="auto-logout-text">
          Vous allez être déconnecté automatiquement...
        </p>
        
        <button onClick={onRetourAccueil} className="btn-retour">
          Retour à l'accueil
        </button>
      </div>

      <style>{`
        .confirmation-wrapper { 
          display: flex; 
          justify-content: center; 
          align-items: center; 
          min-height: 60vh; 
          padding: 20px; 
          background-color: #f8fafc; 
        }
        .confirmation-card { 
          background: #ffffff; 
          border: 1px solid #e2e8f0; 
          border-radius: 16px; 
          width: 100%; 
          max-width: 480px; 
          padding: 48px 32px; 
          box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05); 
          text-align: center; 
        }
        .success-icon { 
          display: flex; 
          justify-content: center; 
          margin-bottom: 24px; 
        }
        .confirmation-title { 
          font-size: 1.5rem; 
          font-weight: 700; 
          color: #0f172a; 
          margin: 0 0 16px 0; 
        }
        .confirmation-text { 
          font-size: 1rem; 
          color: #475569; 
          line-height: 1.6; 
          margin: 0 0 24px 0; 
        }
        .auto-logout-text { 
          font-size: 0.85rem; 
          color: #94a3b8; 
          margin: 0 0 24px 0; 
          font-style: italic; 
        }
        .btn-retour { 
          width: 100%; 
          background: #0f172a; 
          color: #ffffff; 
          border: none; 
          padding: 14px; 
          font-size: 1rem; 
          font-weight: 600; 
          border-radius: 8px; 
          cursor: pointer; 
          font-family: 'Montserrat', sans-serif; 
          transition: background 0.2s; 
        }
        .btn-retour:hover { 
          background: #1e293b; 
        }
      `}</style>
    </div>
  );
}