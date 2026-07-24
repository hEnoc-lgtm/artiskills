import { useState, useEffect } from "react";

export default function GestionBanqueQuestions({ idAdminConnecte = 1 }) {
  const [questions, setQuestions] = useState([]);
  const [metiers, setMetiers] = useState([]);
  const [chargement, setChargement] = useState(true);
  const [recherche, setRecherche] = useState("");
  const [modalOuvert, setModalOuvert] = useState(false);
  const [modeEdition, setModeEdition] = useState(false);

  const [currentQuestion, setCurrentQuestion] = useState({
    idQuestion: "",
    enonce: "",
    typeQuestion: "QCM_unique",
    code_corpsmetier: "",
    options: [
      { libelleReponse: "", estCorrecte: 1 },
      { libelleReponse: "", estCorrecte: 0 },
      { libelleReponse: "", estCorrecte: 0 }
    ]
  });

  const chargerDonnees = () => {
    setChargement(true);
    fetch("http://localhost/votre_projet_backend/CRUD/Read_question.php")
      .then((res) => res.json())
      .then((data) => { if (data.success) setQuestions(data.data); });

    fetch("http://localhost/votre_projet_backend/CRUD/Read_corpsmetier.php")
      .then((res) => res.json())
      .then((data) => {
        if (data.success) setMetiers(data.data);
        setChargement(false);
      });
  };

  useEffect(() => {
    const timeoutId = window.setTimeout(() => {
      chargerDonnees();
    }, 0);

    return () => window.clearTimeout(timeoutId);
  }, []);

  const handleOptionTextChange = (index, value) => {
    const nouvellesOptions = [...currentQuestion.options];
    nouvellesOptions[index].libelleReponse = value;
    setCurrentQuestion({ ...currentQuestion, options: nouvellesOptions });
  };

  const handleRadioCorrectChange = (index) => {
    const nouvellesOptions = currentQuestion.options.map((opt, i) => ({
      ...opt,
      estCorrecte: i === index ? 1 : 0
    }));
    setCurrentQuestion({ ...currentQuestion, options: nouvellesOptions });
  };

  const ajouterLigneOption = () => {
    setCurrentQuestion({
      ...currentQuestion,
      options: [...currentQuestion.options, { libelleReponse: "", estCorrecte: 0 }]
    });
  };

  const ouvrirModalAjout = () => {
    setModeEdition(false);
    setCurrentQuestion({
      idQuestion: "", enonce: "", typeQuestion: "QCM_unique", code_corpsmetier: "",
      options: [{ libelleReponse: "", estCorrecte: 1 }, { libelleReponse: "", estCorrecte: 0 }, { libelleReponse: "", estCorrecte: 0 }]
    });
    setModalOuvert(true);
  };

  const ouvrirModalEdition = (q) => {
    setModeEdition(true);
    setCurrentQuestion({
      idQuestion: q.idQuestion,
      enonce: q.enonce,
      typeQuestion: q.typeQuestion,
      code_corpsmetier: q.code_corpsmetier,
      options: q.options && q.options.length > 0 ? q.options : [{ libelleReponse: "", estCorrecte: 1 }]
    });
    setModalOuvert(true);
  };

  const handleFormSubmit = (e) => {
    e.preventDefault();
    const url = modeEdition 
      ? "http://localhost/votre_projet_backend/CRUD/Update_question.php"
      : "http://localhost/votre_projet_backend/CRUD/Create_question.php";

    fetch(url, {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify(currentQuestion)
    })
      .then((res) => res.json())
      .then((data) => {
        if (data.success) {
          alert(data.message);
          setModalOuvert(false);
          chargerDonnees();
        } else {
          alert(data.message);
        }
      });
  };

  const handleSupprimer = (id) => {
    if (window.confirm("⚠️ Attention : Supprimer cette question va l'archiver de manière définitive dans l'historique de suppression nationale ANPS. Confirmer ?")) {
      fetch("http://localhost/votre_projet_backend/CRUD/Delete_question.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ idQuestion: id, id_admin: idAdminConnecte }) // Transmission de l'id de l'admin pour la traçabilité
      })
        .then((res) => res.json())
        .then((data) => {
          alert(data.message);
          if (data.success) chargerDonnees();
        });
    }
  };

  return (
    <div className="management-container">
      <header className="content-header">
        <div>
          <h2>Banque de Questions & Réponses QCM</h2>
          <p className="subtitle">Gestion centralisée des énoncés de tests théoriques ARCH avec traçabilité des suppressions.</p>
        </div>
        <button className="btn-add" onClick={ouvrirModalAjout}>+ Nouvelle Question</button>
      </header>

      <div className="search-box">
        <input type="text" placeholder="🔍 Filtrer les questions par mot-clé..." value={recherche} onChange={(e) => setRecherche(e.target.value)} />
      </div>

      <div className="table-card">
        {chargement ? (
          <div className="loader">Chargement du référentiel d'évaluation...</div>
        ) : (
          <table className="admin-table">
            <thead>
              <tr>
                <th>ID</th>
                <th>Énoncé de l'évaluation</th>
                <th>Options configurées (✔️ = Réponse juste)</th>
                <th>Secteur Métier</th>
                <th style={{ textAlign: "right" }}>Actions</th>
              </tr>
            </thead>
            <tbody>
              {questions.filter(q => q.enonce.toLowerCase().includes(recherche.toLowerCase())).map((q) => (
                <tr key={q.idQuestion}>
                  <td className="id-cell">{q.idQuestion}</td>
                  <td style={{ maxWidth: "240px" }}><strong>{q.enonce}</strong></td>
                  <td>
                    <ul className="mini-options-list">
                      {q.options?.map((opt, i) => (
                        <li key={i} className={opt.estCorrecte == 1 ? "correct-opt-item" : ""}>
                          {opt.estCorrecte == 1 ? "✔️ " : "⚪ "} {opt.libelleReponse}
                        </li>
                      ))}
                    </ul>
                  </td>
                  <td><span className="badge-metier">{q.nom_metier || q.code_corpsmetier}</span></td>
                  <td>
                    <div className="actions-wrapper">
                      <button className="btn-edit" onClick={() => ouvrirModalEdition(q)}>Éditer</button>
                      <button className="btn-delete" onClick={() => handleSupprimer(q.idQuestion)}>Supprimer</button>
                    </div>
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
        )}
      </div>

      {/* MODAL UNIQUE AJOUT & MODIFICATION */}
      {modalOuvert && (
        <div className="modal-overlay">
          <div className="modal-card macro">
            <h3>{modeEdition ? "✏️ Modifier la Question & Réponses" : "➕ Nouveau Questionnaire Évaluation"}</h3>
            <form onSubmit={handleFormSubmit}>
              
              <div className="form-group">
                <label>Énoncé de la question</label>
                <input type="text" placeholder="Saisir l'intitulé..." value={currentQuestion.enonce} onChange={(e) => setCurrentQuestion({ ...currentQuestion, enonce: e.target.value })} required />
              </div>

              <div className="grid-2">
                <div className="form-group">
                  <label>Corps de métier visé</label>
                  <select value={currentQuestion.code_corpsmetier} onChange={(e) => setCurrentQuestion({ ...currentQuestion, code_corpsmetier: e.target.value })} required>
                    <option value="">-- Choisir le secteur --</option>
                    {metiers.map(m => <option key={m.code_corpsmetier} value={m.code_corpsmetier}>{m.libelle}</option>)}
                  </select>
                </div>
                <div className="form-group">
                  <label>Format</label>
                  <select value={currentQuestion.typeQuestion} onChange={(e) => setCurrentQuestion({ ...currentQuestion, typeQuestion: e.target.value })}>
                    <option value="QCM_unique">Choix Unique</option>
                    <option value="QCM_multiple">Choix Multiples</option>
                    <option value="VraiFaux">Vrai / Faux</option>
                  </select>
                </div>
              </div>

              <div className="qcm-options-builder">
                <div className="builder-header">
                  <label>Options de réponses du QCM</label>
                  <button type="button" className="btn-add-line" onClick={ajouterLigneOption}>+ Ajouter un choix</button>
                </div>

                {currentQuestion.options.map((opt, index) => (
                  <div key={index} className="option-input-row">
                    <input 
                      type="radio" 
                      name="correct_answer_radio" 
                      checked={opt.estCorrecte == 1} 
                      onChange={() => handleRadioCorrectChange(index)} 
                    />
                    <input 
                      type="text" 
                      placeholder={`Option n°${index + 1}`}
                      value={opt.libelleReponse} 
                      onChange={(e) => handleOptionTextChange(index, e.target.value)}
                      required 
                    />
                    <span className="row-status-info">{opt.estCorrecte == 1 ? "VRAI" : "FAUX"}</span>
                  </div>
                ))}
              </div>

              <div className="modal-actions">
                <button type="button" className="btn-cancel" onClick={() => setModalOuvert(false)}>Annuler</button>
                <button type="submit" className="btn-save">{modeEdition ? "Mettre à jour" : "Enregistrer tout"}</button>
              </div>
            </form>
          </div>
        </div>
      )}

    {/* Styles CSS */}
<style>{`
  .management-container { width: 100%; box-sizing: border-box; }
  .content-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; }
  .content-header h2 { font-size: 1.4rem; color: #0f172a; margin: 0; font-weight: 700; }
  .subtitle { font-size: 0.88rem; color: #64748b; }
  .btn-add { background: #000; color: #fff; border: none; padding: 10px 18px; border-radius: 8px; font-weight: 600; cursor: pointer; }
  .search-box { margin-bottom: 24px; }
  .search-box input { width: 100%; padding: 12px 14px; border: 1px solid #cbd5e1; border-radius: 8px; }
  .table-card { background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; overflow: hidden; }
  .admin-table { width: 100%; border-collapse: collapse; text-align: left; }
  .admin-table th { background: #f8fafc; padding: 14px 16px; font-size: 0.82rem; text-transform: uppercase; color: #64748b; font-weight: 700; }
  .admin-table td { padding: 14px 16px; border-bottom: 1px solid #f1f5f9; font-size: 0.95rem; color: #334155; }
  .id-cell { font-family: monospace; font-weight: 700; color: #94a3b8; }
  .mini-options-list { list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: 4px; }
  .mini-options-list li { font-size: 0.88rem; color: #475569; }
  .correct-opt-item { color: #16a34a !important; font-weight: 700; }
  .badge-metier { background: #fff7ed; color: #c2410c; padding: 4px 8px; border-radius: 4px; font-size: 0.82rem; font-weight: 600; }
  .actions-wrapper { display: flex; gap: 8px; justify-content: flex-end; }
  .btn-edit { background: #f1f5f9; border: 1px solid #cbd5e1; padding: 6px 12px; border-radius: 6px; cursor: pointer; color: #334155; font-size: 0.85rem; }
  .btn-delete { background: #fef2f2; border: 1px solid #fca5a5; color: #991b1b; padding: 6px 12px; border-radius: 6px; cursor: pointer; font-size: 0.85rem; }
  .qcm-options-builder { margin-top: 20px; background: #f8fafc; padding: 16px; border-radius: 8px; border: 1px solid #e2e8f0; }
  .builder-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px; }
  .builder-header label { font-size: 0.85rem; font-weight: 700; color: #1e293b; }
  .btn-add-line { background: transparent; border: 1px dashed #cbd5e1; color: #2563eb; padding: 4px 10px; border-radius: 4px; font-size: 0.8rem; font-weight: 600; cursor: pointer; }
  .option-input-row { display: flex; align-items: center; gap: 10px; margin-bottom: 8px; }
  .option-input-row input[type="radio"] { width: 18px; height: 18px; accent-color: #16a34a; cursor: pointer; }
  .option-input-row input[type="text"] { flex: 1; padding: 8px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 0.9rem; }
  .row-status-info { font-size: 0.75rem; font-weight: 700; width: 40px; text-align: right; }
  .modal-overlay { position: fixed; inset: 0; background: rgba(15, 23, 42, 0.3); display: flex; justify-content: center; align-items: center; z-index: 200; }
  .modal-card { background: #fff; width: 100%; max-width: 540px; padding: 24px; border-radius: 12px; box-shadow: 0 18px 30px rgba(0, 0, 0, 0.12); }
  .modal-card.macro { background: #fff; width: 100%; max-width: 540px; padding: 28px; border-radius: 12px; box-shadow: 0 20px 25px rgba(0,0,0,0.1); }
  .modal-card h3 { margin: 0 0 16px 0; font-size: 1.1rem; font-weight: 700; }
  .form-group { display: flex; flex-direction: column; gap: 6px; margin-bottom: 14px; text-align: left; }
  .form-group label { font-size: 0.85rem; font-weight: 600; color: #475569; }
  .form-group input, .form-group select { width: 100%; padding: 10px 12px; border: 1px solid #cbd5e1; border-radius: 6px; box-sizing: border-box; font-size: 0.92rem; background: #fff; }
  .grid-2 { display: grid; grid-template-columns: 2fr 1fr; gap: 12px; }
  .modal-actions { display: flex; justify-content: flex-end; gap: 10px; border-top: 1px solid #f1f5f9; padding-top: 14px; margin-top: 20px; }
  .btn-cancel { background: #fff; border: 1px solid #cbd5e1; padding: 8px 14px; border-radius: 6px; cursor: pointer; }
  .btn-save { background: #000; color: #fff; border: none; padding: 8px 18px; border-radius: 6px; font-weight: 600; cursor: pointer; }
  .loader { padding: 40px; text-align: center; color: #64748b; }
`}</style>
    </div>
  );
}