import { BrowserRouter } from "react-router-dom";
import { useState } from "react";

// Imports des pages (assurez-vous que les noms de fichiers correspondent exactement à ces chemins)
import Accueil from "./pages/Accueil";
import InscriptionArtisan from "./pages/Inscriptionartisan";
import ConfirmationParcours from "./pages/Confirmationparcours";
import Pretest from "./pages/Pretest";
import InstructionTest from "./pages/Instructiontest";
import QuestionnaireTest from "./pages/Questionnairetest";
import ResultatAffectation from "./pages/GestionAffectations"; // Conserve votre nom de fichier actuel
import TableauDeBord from "./pages/TableauDeBord"; // ⚠️ RENOMMÉ : Assurez-vous que votre fichier s'appelle TableauDeBord.jsx
import ConnexionAgentAdmin from "./pages/Connexionagentadmin";

export default function App() {
  // 1. État principal qui pilote l'affichage dynamique (Wizard)
  const [etape, setEtape] = useState("accueil");
  
  // 2. Mémoire de la session de l'artisan en cours
  const [session, setSession] = useState({ 
    idArtisan: null, 
    idTest: null, 
    idMetier: null, 
    npi: "" 
  });
  
  // 3. Mémoire de l'administrateur ou agent connecté
  const [adminConnecte, setAdminConnecte] = useState(null);

  // Fonction utilitaire pour revenir à l'accueil et réinitialiser si nécessaire
  const handleRetourAccueil = () => {
    setEtape("accueil");
    // Optionnel : déconnecter l'admin ou vider la session artisan ici si besoin
    // setAdminConnecte(null);
    // setSession({ idArtisan: null, idTest: null, idMetier: null, npi: "" });
  };

  return (
    <BrowserRouter>
      <div className="app-shell">
        
        {/* EN-TÊTE FIXE AVEC LOGOS INSTITUTIONNELS (Masqué uniquement sur le Dashboard) */}
        {etape !== "dashboard" && (
          <header className="site-header">
            <div className="logo-left">
              <img src="/images/logo-artiskills.png" alt="ArtiSkills" className="brand-logo" />
            </div>
            <div className="header-actions">
              <div className="logo-right">
                <img src="/images/logo-anps.png" alt="ANPS Bénin" className="institution-logo" />
              </div>
            </div>
          </header>
        )}

        {/* CONTAINER DE CONTENU RENDU SELON L'ÉTAPE DU PARCOURS */}
        <main className="main-content">
          
          {/* 1. Écran d'accueil de la plateforme */}
          {etape === "accueil" && (
            <Accueil 
              onNavigateToRegister={() => setEtape("inscription")} 
              onNavigateToAdmin={() => setEtape("admin_login")} 
            />
          )}

          {/* 2. Inscription initiale de l'artisan (NPI, Nom, Prénom, Contact, Sexe) */}
          {etape === "inscription" && (
            <InscriptionArtisan 
              onInscriptionSuccess={(data) => {
                setSession({ 
                  idArtisan: data.idArtisan, 
                  idTest: data.idTest, 
                  npi: data.npi 
                });
                setEtape("confirmation");
              }} 
            />
          )}

          {/* 3. Page de transition de confirmation Oui/Non */}
          {etape === "confirmation" && (
            <ConfirmationParcours 
              infoParcours={session} 
              onAccepteTest={() => setEtape("pretest")} 
              onRefuseTest={handleRetourAccueil} 
            />
          )}

          {/* 4. Saisie géographique et choix du corps de métier (Pretest) */}
          {etape === "pretest" && (
            <Pretest 
              idArtisan={session.idArtisan} 
              onPretestSuccess={(idMetierChoisi) => {
                setSession((prevSession) => ({ ...prevSession, idMetier: idMetierChoisi }));
                setEtape("instructions");
              }} 
            />
          )}

          {/* 5. Consignes et avertissements anti-triche */}
          {etape === "instructions" && (
            <InstructionTest onStartTest={() => setEtape("test")} />
          )}

          {/* 6. Le Questionnaire QCM Évaluation */}
          {etape === "test" && (
            <QuestionnaireTest 
              idTest={session.idTest} 
              idMetier={session.idMetier} 
              onTestTermine={() => setEtape("resultats")} 
            />
          )}

          {/* 7. Écran d'affichage de la note et affectation Haversine */}
          {etape === "resultats" && (
            <ResultatAffectation 
              idTest={session.idTest} 
              idArtisan={session.idArtisan} 
              onRetourAccueil={handleRetourAccueil} 
            />
          )}

           {/* 8. Authentification de l'opérateur ANPS */}
          {etape === "admin_login" && (
            <ConnexionAgentAdmin 
              onLoginSuccess={(adminData) => {
                console.log("🚀 FONCTION DÉCLENCHÉE ! Données reçues :", adminData); // <-- AJOUTEZ CETTE LIGNE
                setAdminConnecte(adminData);
                setEtape("dashboard");
                console.log("🚀 ÉTAPE CHANGÉE EN : dashboard"); // <-- AJOUTEZ CETTE LIGNE
              }}
              onRetour={handleRetourAccueil}
            />
          )}
          {/* 9. Le Tableau de Bord global (Adaptatif selon le rôle : Admin ou Agent) */}
          {etape === "dashboard" && (
            <TableauDeBord 
              userConnecte={adminConnecte} 
              onDeconnexion={handleRetourAccueil} 
            />
          )}

        </main>
      </div>

      {/* Styles globaux de la coquille de l'application */}
      <style>{`
        .app-shell { 
          min-height: 100vh; 
          display: flex; 
          flex-direction: column; 
          background-color: #f8fafc; 
          font-family: 'Montserrat', system-ui, sans-serif; 
        }
        .site-header { 
          display: flex; 
          justify-content: space-between; 
          align-items: center; 
          padding: 14px 5%; 
          background: rgba(255, 255, 255, 0.96); 
          border-bottom: 1px solid rgba(0, 0, 0, 0.08); 
          box-shadow: 0 2px 6px rgba(0, 0, 0, 0.04); 
          z-index: 1000; 
          backdrop-filter: saturate(180%) blur(10px); 
        }
        .logo-left, .logo-right { 
          display: flex; 
          align-items: center; 
          min-width: 140px; 
        }
        .brand-logo { 
          max-height: 50px; 
          width: auto; 
          object-fit: contain; 
        }
        .institution-logo { 
          max-height: 45px; 
          width: auto; 
          object-fit: contain; 
        }
        .main-content { 
          flex: 1 0 auto; 
          display: flex; 
          flex-direction: column; 
        }
        .header-actions { 
          display: flex; 
          align-items: center; 
          gap: 16px; 
        }
      `}</style>
    </BrowserRouter>
  );
}