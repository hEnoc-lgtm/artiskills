import { useState, useEffect, useCallback, useRef } from "react";

export default function QuestionnaireTest({ idTest, onTestTermine }) {
  const [questions, setQuestions] = useState([]);
  const [indexCourant, setIndexCourant] = useState(0);
  const [selection, setSelection] = useState([]); // tableau de {id, libelle}
  const [tempsRestant, setTempsRestant] = useState(null); 
  const [chargement, setChargement] = useState(true);
  const [erreurApi, setErreurApi] = useState(null);
  
  const aDejaCharge = useRef(false);

  useEffect(() => {
    if (aDejaCharge.current || questions.length > 0) {
      return;
    }

    console.log("🔍 DÉBUT CHARGEMENT TEST");
    console.log("🔹 idTest reçu :", idTest);

    if (!idTest) {
      console.error("❌ ERREUR : idTest est manquant !");
      setErreurApi("Données de session manquantes. Veuillez recommencer le test.");
      setChargement(false);
      return;
    }

    aDejaCharge.current = true;
    const url = `http://localhost/Code/backend/api/test/chargerQuestions.php?idTest=${idTest}`;
    console.log("🌐 URL appelée :", url);

    fetch(url)
      .then((res) => res.json())
      .then((data) => {
        console.log("📦 RÉPONSE DU SERVEUR :", data);
        if (data.success) {
          setQuestions(data.questions);
          setTempsRestant(data.tempsRestant);
          
          if (data.questions.length === 0) {
            setErreurApi("Aucune question trouvée en base de données pour ce métier.");
          }
        } else {
          console.error("❌ Le serveur a répondu success:false :", data.message);
          setErreurApi(data.message || "Erreur inconnue du serveur.");
        }
        setChargement(false);
      })
      .catch((err) => {
        console.error("❌ ERREUR RÉSEAU :", err);
        setErreurApi("Impossible de contacter le serveur backend. Vérifiez que XAMPP est lancé.");
        setChargement(false);
      });
  }, [idTest]);

  // Fonction pour finaliser le test côté serveur
  const terminerTest = useCallback(() => {
    fetch(`http://localhost/Code/backend/api/test/terminer_test.php?idTest=${idTest}`, {
      method: "POST",
    })
      .then((res) => res.json())
      .then((data) => {
        if (data.success) {
          console.log("✅ Test terminé avec succès. Durée :", data.duree);
        }
      })
      .catch((err) => console.error("Erreur lors de la finalisation du test", err))
      .finally(() => {
        if (onTestTermine) onTestTermine();
      });
  }, [idTest, onTestTermine]);

  const handleFinChronoAutomatically = useCallback(() => {
    alert("Temps écoulé ! Votre test est clôturé.");
    terminerTest();
  }, [terminerTest]);

  useEffect(() => {
    if (chargement || tempsRestant === null || questions.length === 0) return;

    if (tempsRestant <= 0) {
      handleFinChronoAutomatically();
      return;
    }

    const timer = setInterval(() => setTempsRestant((t) => t - 1), 1000);
    return () => clearInterval(timer);
  }, [tempsRestant, chargement, questions, handleFinChronoAutomatically]);

  const formaterChrono = () => {
    if (tempsRestant === null) return "00:00";
    const min = Math.floor(tempsRestant / 60).toString().padStart(2, "0");
    const sec = (tempsRestant % 60).toString().padStart(2, "0");
    return `${min}:${sec}`;
  };

  const handleSuivant = () => {
    const qCourante = questions[indexCourant];

    if (qCourante.estVerouillee === 1) {
      passerAQuestionSuivante();
      return;
    }

    const estMultiple = qCourante.typeQuestion === "QCM_multiple";

    // Selon le type : tableau (multiple) ou valeur seule (unique)
    const payload = {
      idTest: idTest,
      idQuestion: qCourante.idQuestion,
      idReponse: estMultiple ? selection.map((r) => r.id) : selection[0].id,
      libelleReponse: estMultiple ? selection.map((r) => r.libelle) : selection[0].libelle,
    };

    fetch("http://localhost/Code/backend/api/test/sauvegardereponse.php", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify(payload),
    })
      .then((res) => res.json())
      .then((data) => {
        if (data.success) {
          const questionsMaj = [...questions];
          questionsMaj[indexCourant].estVerouillee = 1;
          questionsMaj[indexCourant].reponseDonnee = Array.isArray(payload.libelleReponse)
            ? payload.libelleReponse.join(", ")
            : payload.libelleReponse;
          setQuestions(questionsMaj);
          passerAQuestionSuivante();
        } else {
          alert(data.message || "Erreur de traitement.");
        }
      })
      .catch((err) => console.error("Erreur réseau", err));
  };

  const passerAQuestionSuivante = () => {
    if (indexCourant < questions.length - 1) {
      setIndexCourant(indexCourant + 1);
      setSelection([]);
    } else {
      // C'est la dernière question, on termine le test proprement
      terminerTest();
    }
  };

  if (chargement) return <div className="test-loader">Génération sécurisée du test et synchronisation du chronomètre...</div>;
  
  if (erreurApi || questions.length === 0) {
    return (
      <div className="test-loader" style={{color: '#991b1b'}}>
        <h3>❌ Erreur de chargement</h3>
        <p>{erreurApi || "Aucune question disponible."}</p>
        <p style={{fontSize: '0.9rem', color: '#64748b'}}>Ouvrez la console du navigateur (F12) pour voir les détails.</p>
      </div>
    );
  }

  const qActuelle = questions[indexCourant];
  const estVerrouillee = qActuelle.estVerouillee === 1;
  const estMultiple = qActuelle.typeQuestion === "QCM_multiple";
  const reponsesVerrouillees = estVerrouillee && qActuelle.reponseDonnee ? qActuelle.reponseDonnee.split(", ") : [];

  // Clic sur une option : toggle si multiple, remplacement si unique
  const gererClicOption = (opt) => {
    if (estVerrouillee) return;
    if (estMultiple) {
      setSelection((prev) =>
        prev.some((r) => r.id === opt.idReponse)
          ? prev.filter((r) => r.id !== opt.idReponse)
          : [...prev, { id: opt.idReponse, libelle: opt.enonce }]
      );
    } else {
      setSelection([{ id: opt.idReponse, libelle: opt.enonce }]);
    }
  };

  return (
    <div className="test-wrapper">
      <div className="test-card">
        <div className="test-header">
          <span className="brand-name">ArtiSkills</span>
          <span className="question-count">Question {indexCourant + 1} / {questions.length}</span>
          <span className="test-timer">🕒 {formaterChrono()}</span>
        </div>

        <h3 className="question-text">{qActuelle.enonce}</h3>

        {estMultiple && (
          <p className="txt-multi-hint">☑️ Plusieurs réponses possibles — cochez toutes les bonnes réponses.</p>
        )}

        <div className="options-stack">
          {qActuelle.options.map((opt) => {
            const estSelectionnee = selection.some((r) => r.id === opt.idReponse) || reponsesVerrouillees.includes(opt.enonce);
            
            return (
              <div 
                key={opt.idReponse} 
                className={`option-card ${estSelectionnee ? "selected" : ""} ${estVerrouillee ? "grisee" : ""}`}
                onClick={() => gererClicOption(opt)}
              >
                <div className={`radio-circle ${estMultiple ? "carre" : ""} ${estSelectionnee ? "checked" : ""}`}>
                  {estMultiple && estSelectionnee ? "✓" : ""}
                </div>
                <span className="option-label">{opt.enonce}</span>
              </div>
            );
          })}
        </div>

        {estVerrouillee && (
          <p className="txt-lock-notice">🔒 Cette réponse est définitive. Vous l'avez validée lors de votre session précédente.</p>
        )}

        <div className="button-actions">
          <button 
            className="btn-prev" 
            disabled={indexCourant === 0} 
            onClick={() => { setIndexCourant(indexCourant - 1); setSelection([]); }}
          >
            Précédent
          </button>
          <button 
            className="btn-next" 
            disabled={selection.length === 0 && !estVerrouillee} 
            onClick={handleSuivant}
          >
            {indexCourant === questions.length - 1 ? "Terminer le test" : "Valider et suivant"}
          </button>
        </div>
      </div>

      <div className="progression-bar">
        {questions.map((q, i) => (
          <div key={i} className={`step-dot ${i === indexCourant ? "active" : q.estVerouillee === 1 ? "completed" : "locked"}`}>
            {q.estVerouillee === 1 ? "✔️" : i + 1}
          </div>
        ))}
      </div>

      <style>{`
        .test-wrapper { display: flex; flex-direction: column; justify-content: center; align-items: center; min-height: 100vh; background: #f8fafc; font-family: 'Montserrat', system-ui, sans-serif; padding: 20px; }
        .test-card { background: #ffffff; border: 1px solid #e2e8f0; border-radius: 16px; width: 100%; max-width: 580px; padding: 32px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); }
        .test-header { display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #f1f5f9; padding-bottom: 16px; margin-bottom: 24px; }
        .brand-name { font-weight: 700; color: #0f172a; } 
        .test-timer { color: #991b1b; font-weight: 600; font-variant-numeric: tabular-nums; }
        .question-text { font-size: 1.2rem; font-weight: 600; color: #0f172a; margin-bottom: 20px; line-height: 1.4; text-align: left; }
        .txt-multi-hint { color: #1d4ed8; font-size: 0.88rem; margin: 0 0 16px 0; text-align: left; font-weight: 600; }
        .options-stack { display: flex; flex-direction: column; gap: 12px; margin-bottom: 32px; }
        .option-card { display: flex; align-items: center; gap: 14px; padding: 16px; border: 1px solid #cbd5e1; border-radius: 10px; cursor: pointer; transition: all 0.2s; background: #ffffff; text-align: left; user-select: none; }
        .option-card:hover:not(.grisee) { border-color: #94a3b8; background-color: #f8fafc; }
        .option-card.selected { border-color: #93c5fd; background: #eff6ff; }
        .option-card.grisee { background: #f1f5f9; color: #94a3b8; border-color: #cbd5e1; cursor: not-allowed; opacity: 0.75; }
        .radio-circle { width: 20px; height: 20px; border: 2px solid #cbd5e1; border-radius: 50%; box-sizing: border-box; flex-shrink: 0; display: flex; justify-content: center; align-items: center; color: #ffffff; font-size: 13px; font-weight: 700; }
        .radio-circle.carre { border-radius: 5px; }
        .radio-circle.checked { border-color: #2563eb; background: #2563eb; }
        .txt-lock-notice { color: #64748b; font-size: 0.85rem; font-style: italic; margin: 0 0 20px 0; text-align: left; }
        .button-actions { display: flex; gap: 16px; }
        .btn-prev { flex: 1; background: #ffffff; border: 1px solid #cbd5e1; padding: 14px; border-radius: 8px; font-weight: 600; cursor: pointer; color: #334155; }
        .btn-prev:disabled { opacity: 0.4; cursor: not-allowed; }
        .btn-next { flex: 1; background: #000000; color: #ffffff; border: none; padding: 14px; border-radius: 8px; font-weight: 600; cursor: pointer; }
        .btn-next:disabled { background: #f1f5f9; color: #94a3b8; cursor: not-allowed; }
        .progression-bar { display: flex; gap: 8px; margin-top: 24px; }
        .step-dot { width: 32px; height: 32px; border-radius: 50%; display: flex; justify-content: center; align-items: center; font-size: 0.85rem; font-weight: 600; background: #e2e8f0; color: #64748b; }
        .step-dot.active { background: #bfdbfe; color: #1e3a8a; }
        .step-dot.completed { background: #dcfce7; color: #14532d; font-size: 0.75rem; }
        .test-loader { font-family: 'Montserrat', sans-serif; color: #475569; font-size: 1.1rem; text-align: center; margin-top: 35vh; display: flex; flex-direction: column; align-items: center; gap: 16px; }
      `}</style>
    </div>
  );
}