import { BrowserRouter } from "react-router-dom";
import { useState } from "react";

// Imports des pages
import Accueil from "./pages/Accueil";
import InscriptionArtisan from "./pages/Inscriptionartisan";
import ConfirmationParcours from "./pages/Confirmationparcours";
import Pretest from "./pages/Pretest";
import InstructionTest from "./pages/Instructiontest";
import QuestionnaireTest from "./pages/Questionnairetest";
import ResultatAffectation from "./pages/GestionAffectations"; 
import TableauDeBord from "./pages/TableauDeBord"; 
import ConnexionAgentAdmin from "./pages/Connexionagentadmin";
import ConfirmationTest from "./pages/ConfirmationTest";

export default function App() {
  const [etape, setEtape] = useState("accueil");
  
  // État pour conserver les informations de l'artisan tout au long du parcours
  const [session, setSession] = useState({ 
    idArtisan: null, 
    idTest: null, 
    npi: "" 
  });
  
  const [adminConnecte, setAdminConnecte] = useState(null);
  const handleRetourAccueil = () => setEtape("accueil");

  return (
    <BrowserRouter>
      <div className="app-shell">
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

        <main className="main-content">
          {etape === "accueil" && (
            <Accueil 
              onNavigateToRegister={() => setEtape("inscription")} 
              onNavigateToAdmin={() => setEtape("admin_login")} 
            />
          )}

          {etape === "inscription" && (
            <InscriptionArtisan 
              onInscriptionSuccess={(data) => {
                setSession({ idArtisan: data.idArtisan, idTest: data.idTest, npi: data.npi });
                setEtape("confirmation");
              }}
              onRetour={handleRetourAccueil} 
            />
          )}

          {etape === "confirmation" && (
            <ConfirmationParcours 
              infoParcours={session} 
              onAccepteTest={() => setEtape("pretest")} 
              onRefuseTest={handleRetourAccueil} 
            />
          )}

          {etape === "pretest" && (
            <Pretest 
              idArtisan={session.idArtisan} 
              onPretestSuccess={() => {
                // Le pretest a sauvegardé le code_corpsmetier en base via save.php
                setEtape("instructions");
              }} 
            />
          )}

          {etape === "instructions" && (
            <InstructionTest onStartTest={() => setEtape("test")} />
          )}

          {etape === "test" && (
            // ✅ Le PHP récupère le code_corpsmetier tout seul via l'idTest
            <QuestionnaireTest 
              idTest={session.idTest} 
              onTestTermine={() => setEtape("resultats")} 
            />
          )}

          {etape === "resultats" && (
            <ConfirmationTest onRetourAccueil={handleRetourAccueil} />
          )}
          
          {etape === "admin_login" && (
            <ConnexionAgentAdmin 
              onLoginSuccess={(data) => { 
                setAdminConnecte(data); 
                setEtape("dashboard"); 
              }} 
              onRetour={handleRetourAccueil} 
            />
          )}

          {etape === "dashboard" && (
            <TableauDeBord 
              userConnecte={adminConnecte} 
              onDeconnexion={handleRetourAccueil} 
            />
          )}
        </main>
      </div>

      <style>{`
        html, body, #root { height: 100%; margin: 0; padding: 0; }
        .app-shell { min-height: 100vh; display: flex; flex-direction: column; background-color: #f8fafc; font-family: 'Montserrat', system-ui, sans-serif; }
        .site-header { display: flex; justify-content: space-between; align-items: center; padding: 14px 5%; background: rgba(255, 255, 255, 0.96); border-bottom: 1px solid rgba(0, 0, 0, 0.08); box-shadow: 0 2px 6px rgba(0, 0, 0, 0.04); z-index: 1000; position: sticky; top: 0; }
        .logo-left, .logo-right { display: flex; align-items: center; min-width: 140px; }
        .brand-logo { max-height: 50px; width: auto; object-fit: contain; }
        .institution-logo { max-height: 45px; width: auto; object-fit: contain; }
        .main-content { flex: 1 0 auto; display: flex; flex-direction: column; }
        .header-actions { display: flex; align-items: center; gap: 16px; }
      `}</style>
    </BrowserRouter>
  );
}