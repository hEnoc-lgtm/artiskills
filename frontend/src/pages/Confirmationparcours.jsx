import { useState } from "react";

export default function ConfirmationParcours({ infoParcours, onAccepteTest, onRefuseTest }) {
  const [chargement, setChargement] = useState(false);
  
  // Récupération des données transmises par la page d'inscription
  // infoParcours contient : { success: true, dejaInscrit: true/false, message: "...", npi: "..." }
  const { dejaInscrit, message, npi } = infoParcours;

  const handleNonClick = () => {
    setChargement(true);
    
    // Appel API pour enregistrer l'abandon dans l'historique de la BDD
    fetch("http://localhost/Code/backend/api/artisan/annulertest.php", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ npi: npi })
    })
      .then((res) => res.json())
      .then((data) => {
        setChargement(false);
        // Redirection vers l'accueil
        if (onRefuseTest) onRefuseTest();
      })
      .catch((err) => {
        console.error("Erreur lors de l'annulation", err);
        setChargement(false);
        // Sécurité : même en cas de panne réseau, on laisse l'artisan quitter
        if (onRefuseTest) onRefuseTest();
      });
  };

  return (
    <div className="confirm-wrapper">
      <div className="confirm-card">
        
        {/* BANNIÈRE DE NOTIFICATION HISTORIQUE : S'affiche uniquement si dejaInscrit est VRAI */}
        {dejaInscrit && (
          <div className="alert-history-box">
            <span className="alert-icon">🔔</span>
            <div className="alert-text">
              <strong>Notification ArtiSkills :</strong> {message} Vos données d'identification nationales ont été associées à votre historique.
            </div>
          </div>
        )}

        {/* En-tête de validation */}
        <div className="confirm-header">
          <div className="check-badge">ARCH - ANPS BENIN</div>
          <h2>Confirmation de participation</h2>
          <p>Souhaitez-vous démarrer immédiatement votre test d'évaluation des compétences techniques ?</p>
        </div>

        {/* Boîte de rappel des consignes */}
        <div className="info-box-notice">
          <p>💡 <strong>Note importante :</strong> L'évaluation dure 10 minutes maximum. Assurez-vous d'être dans un environnement calme avec une connexion Internet stable.</p>
        </div>

        {/* Les boutons de choix Oui / Non */}
        <div className="confirm-buttons-stack">
          <button 
            className="btn-yes" 
            onClick={onAccepteTest} 
            disabled={chargement}
          >
            Oui, je commence le test maintenant ➔
          </button>
          
          <button 
            className="btn-no" 
            onClick={handleNonClick} 
            disabled={chargement}
          >
            {chargement ? "Annulation en cours..." : "Non, retourner à l'accueil"}
          </button>
        </div>
      </div>

      {/* Style CSS en parfaite harmonie avec la charte épurée de vos écrans */}
      <style>{`
        .confirm-wrapper {
          display: flex;
          justify-content: center;
          align-items: center;
          min-height: 100vh;
          background-color: #f8fafc;
          padding: 20px;
          font-family: system-ui, -apple-system, sans-serif;
        }
        .confirm-card {
          background: #ffffff;
          border: 1px solid #e2e8f0;
          border-radius: 16px;
          width: 100%;
          max-width: 520px;
          padding: 32px;
          box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.05);
        }
        
        /* Style orange de l'alerte d'historique de réinscription */
        .alert-history-box {
          background-color: #fff7ed;
          border: 1px solid #ffedd5;
          border-left: 4px solid #ea580c;
          padding: 16px;
          border-radius: 8px;
          display: flex;
          gap: 12px;
          margin-bottom: 24px;
          text-align: left;
        }
        .alert-icon { font-size: 1.25rem; }
        .alert-text { font-size: 0.9rem; color: #c2410c; line-height: 1.4; }

        .confirm-header { text-align: center; margin-bottom: 24px; }
        .check-badge {
          display: inline-block;
          background: #f0fdf4;
          color: #166534;
          font-size: 0.75rem;
          font-weight: 700;
          padding: 4px 12px;
          border-radius: 50px;
          margin-bottom: 12px;
        }
        .confirm-header h2 { font-size: 1.4rem; color: #0f172a; margin: 0 0 8px 0; font-weight: 700; }
        .confirm-header p { font-size: 0.95rem; color: #64748b; margin: 0; line-height: 1.4; }
        
        .info-box-notice {
          background: #f8fafc;
          border: 1px solid #e2e8f0;
          padding: 14px;
          border-radius: 8px;
          font-size: 0.88rem;
          color: #475569;
          text-align: left;
          margin-bottom: 32px;
          line-height: 1.4;
        }
        .info-box-notice p { margin: 0; }

        .confirm-buttons-stack { display: flex; flex-direction: column; gap: 12px; }
        
        .btn-yes {
          width: 100%;
          background: #000000; /* Bouton principal noir institutionnel */
          color: #ffffff;
          border: none;
          padding: 14px;
          font-size: 0.98rem;
          font-weight: 600;
          border-radius: 8px;
          cursor: pointer;
          transition: background 0.2s;
        }
        .btn-yes:hover:not(:disabled) { background: #1e293b; }
        
        .btn-no {
          width: 100%;
          background: #ffffff;
          color: #64748b;
          border: 1px solid #cbd5e1;
          padding: 14px;
          font-size: 0.95rem;
          font-weight: 600;
          border-radius: 8px;
          cursor: pointer;
          transition: all 0.2s;
        }
        .btn-no:hover:not(:disabled) { background: #f8fafc; color: #0f172a; border-color: #94a3b8; }
        .btn-yes:disabled, .btn-no:disabled { opacity: 0.6; cursor: not-allowed; }
      `}</style>
    </div>
  );
}
