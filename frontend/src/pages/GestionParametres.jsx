import { useState, useEffect, useCallback } from "react";

export default function GestionParametres() {
  // Sous-onglets gérés dans les paramètres
  const [sousOnglet, setSousOnglet] = useState("copies_artisans");
  const [listeData, setListeData] = useState([]);
  const [metiers, setMetiers] = useState([]); // Utile pour charger les métiers dans le formulaire d'objectifs
  const [chargement, setChargement] = useState(false);
  const [modalOuvert, setModalOuvert] = useState(false);

  // Recherche et filtres internes
  const [rechercheNpi, setRechercheNpi] = useState("");
  const [detailsArtisanCopy, setDetailsArtisanCopy] = useState(null); // Stocke les questions de la copie d'un test

  // Formulaire d'objectif de formation aligné sur vos 4 colonnes réelles
  const [formDataObjectif, setFormDataObjectif] = useState({
    nombrePlaces: "",
    periode: "",
    code_corpsmetier: ""
  });

  // Formulaire générique pour les tables géographiques secondaires
  const [formDataGeo, setFormDataGeo] = useState({
    nom: "",
    parentId: ""
  });

  // 1. READ : Chargement dynamique selon le sous-onglet sélectionné
  const chargerDonneesParametres = useCallback(() => {
    setChargement(true);
    setDetailsArtisanCopy(null);

    let scriptPHP;

    switch (sousOnglet) {
      case "copies_artisans": scriptPHP = "Read_resultats_tests.php"; break;
      case "historique": scriptPHP = "Read_historique_inscription.php"; break;
      case "objectifs": scriptPHP = "Read_objectif_formation.php"; break;
      case "artisans": scriptPHP = "Read_artisan.php"; break;
      case "departements": scriptPHP = "Read_departement.php"; break;
      case "communes": scriptPHP = "Read_commune.php"; break;
      case "arrondissements": scriptPHP = "Read_arrondissement.php"; break;
      case "quartiers": scriptPHP = "Read_quartier_village.php"; break;
      default: scriptPHP = "";
    }

    fetch(`http://localhost/votre_projet_backend/CRUD/${scriptPHP}`)
      .then((res) => res.json())
      .then((data) => {
        if (data.success) setListeData(data.data);
        setChargement(false);
      })
      .catch((err) => {
        console.error("Erreur d'extraction", err);
        setChargement(false);
      });
  }, [sousOnglet]);

  // Déclencheur automatique de chargement de sous-onglets
  useEffect(() => {
    const timeoutId = window.setTimeout(() => {
      chargerDonneesParametres();

      // Charger la liste des métiers uniquement si on ouvre le sous-onglet objectifs
      if (sousOnglet === "objectifs") {
        fetch("http://localhost/votre_projet_backend/CRUD/Read_corpsmetier.php")
          .then((res) => res.json())
          .then((data) => { if (data.success) setMetiers(data.data); });
      }
    }, 0);

    return () => window.clearTimeout(timeoutId);
  }, [sousOnglet, chargerDonneesParametres]);

  // 2. FOCUS COPIE : Charger l'historique complet des questions/réponses d'un artisan
  const chargerFicheCopieTest = (idTest, nomArtisan) => {
    setChargement(true);
    fetch(`http://localhost/votre_projet_backend/api/test/charger_questions.php?idTest=${idTest}&idMetier=0`)
      .then((res) => res.json())
      .then((data) => {
        if (data.success) {
          setDetailsArtisanCopy({
            candidat: nomArtisan,
            questionsFormatees: data.questions
          });
        }
        setChargement(false);
      })
      .catch((err) => {
        console.error(err);
        setChargement(false);
      });
  };

  // 3. DELETE : Action de suppression générique dans le registre CRUD/
  const handleSupprimerElement = (idTarget) => {
    if (window.confirm("⚠️ Confirmer la suppression définitive de cet élément de configuration ?")) {
      let scriptPHP;
      let payloadKey = "";

      if (sousOnglet === "departements") { scriptPHP = "Delete_departement.php"; payloadKey = "id_departement"; }
      if (sousOnglet === "communes") { scriptPHP = "Delete_commune.php"; payloadKey = "id_commune"; }
      if (sousOnglet === "arrondissements") { scriptPHP = "Delete_arrondissement.php"; payloadKey = "id_arrondissement"; }
      if (sousOnglet === "quartiers") { scriptPHP = "Delete_quartier_village.php"; payloadKey = "id_quartier"; }
      if (sousOnglet === "objectifs") { scriptPHP = "Delete_objectif_formation.php"; payloadKey = "idObjectif"; }

      fetch(`http://localhost/votre_projet_backend/CRUD/${scriptPHP}`, {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ [payloadKey]: idTarget })
      })
        .then((res) => res.json())
        .then((data) => {
          alert(data.message);
          if (data.success) chargerDonneesParametres();
        });
    }
  };

  // 4. SUBMIT : Formulaire d'ajout Objectif ou Géographie
  const handleFormSubmit = (e) => {
    e.preventDefault();
    let url;
    let corpsData;

    if (sousOnglet === "objectifs") {
      url = "http://localhost/votre_projet_backend/CRUD/Create_objectif_formation.php";
      corpsData = formDataObjectif;
    } else {
      let nomChamp = "";
      let parentChamp = "";
      let scriptPHP;
      if (sousOnglet === "departements") { scriptPHP = "Create_departement.php"; nomChamp = "nom_departement"; }
      if (sousOnglet === "communes") { scriptPHP = "Create_commune.php"; nomChamp = "nom_commune"; parentChamp = "id_departement"; }
      if (sousOnglet === "arrondissements") { scriptPHP = "Create_arrondissement.php"; nomChamp = "nom_arrondissement"; parentChamp = "id_commune"; }
      if (sousOnglet === "quartiers") { scriptPHP = "Create_quartier_village.php"; nomChamp = "nom_quartier"; parentChamp = "id_arrondissement"; }

      url = `http://localhost/votre_projet_backend/CRUD/${scriptPHP}`;
      corpsData = { [nomChamp]: formDataGeo.nom };
      if (parentChamp) corpsData[parentChamp] = formDataGeo.parentId;
    }

    fetch(url, {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify(corpsData)
    })
      .then((res) => res.json())
      .then((data) => {
        alert(data.message);
        if (data.success) {
          setModalOuvert(false);
          chargerDonneesParametres();
        }
      });
  };

  const ouvrirModalAjout = () => {
    setFormDataObjectif({ nombrePlaces: "", periode: "", code_corpsmetier: "" });
    setFormDataGeo({ nom: "", parentId: "" });
    setModalOuvert(true);
  };

  // Filtrage intelligent par NPI
  const donneesFiltrees = listeData.filter((item) => {
    const npiCible = item.npi || item.npi_artisan || "";
    return npiCible.includes(rechercheNpi);
  });

  return (
    <div className="management-container">
      <header className="content-header">
        <div>
          <h2>Configuration & Paramètres Référentiels</h2>
          <p className="subtitle">Audit des fiches d'évaluation, quotas de formation, découpages géographiques et journaux de logs.</p>
        </div>
        {(sousOnglet !== "historique" && sousOnglet !== "copies_artisans" && sousOnglet !== "artisans") && (
          <button className="btn-add" onClick={ouvrirModalAjout}>+ Ajouter un élément</button>
        )}
      </header>

      {/* BARRE DES SOUS-ONGLETS DES PARAMÈTRES */}
      <div className="parametres-nav-tabs">
        <button className={sousOnglet === "copies_artisans" ? "active" : ""} onClick={() => setSousOnglet("copies_artisans")}>📝 Copies des Tests</button>
        <button className={sousOnglet === "historique" ? "active" : ""} onClick={() => setSousOnglet("historique")}>📜 Historique Inscriptions</button>
        <button className={sousOnglet === "objectifs" ? "active" : ""} onClick={() => setSousOnglet("objectifs")}>🎯 Objectifs Formation</button>
        <button className={sousOnglet === "artisans" ? "active" : ""} onClick={() => setSousOnglet("artisans")}>👥 Artisans</button>
        <button className={sousOnglet === "departements" ? "active" : ""} onClick={() => setSousOnglet("departements")}>🌍 Départements</button>
        <button className={sousOnglet === "communes" ? "active" : ""} onClick={() => setSousOnglet("communes")}>🏙️ Communes</button>
        <button className={sousOnglet === "arrondissements" ? "active" : ""} onClick={() => setSousOnglet("arrondissements")}>🏘️ Arrondissements</button>
        <button className={sousOnglet === "quartiers" ? "active" : ""} onClick={() => setSousOnglet("quartiers")}>🏡 Quartiers</button>
      </div>

      {/* Barre de filtre par NPI */}
      {(sousOnglet === "copies_artisans" || sousOnglet === "historique" || sousOnglet === "artisans") && (
        <div className="search-box">
          <input
            type="text"
            placeholder="🔍 Filtrer instantanément cette liste par numéro NPI..."
            value={rechercheNpi}
            onChange={(e) => setRechercheNpi(e.target.value)}
          />
        </div>
      )}

      {/* BLOC PRINCIPAL D'AFFICHAGE */}
      <div className="table-card">
        {chargement ? (
          <div className="loader">Extraction et indexation des tables en cours...</div>
        ) : detailsArtisanCopy ? (

          /* ========================================================
             VUE A : AUDIT CHIRURGICAL D'UNE COPIE DE TEST ARTISAN
             ======================================================== */
          <div className="exam-copy-viewer">
            <div className="copy-viewer-header">
              <h3>Copie d'évaluation : <span>{detailsArtisanCopy.candidat}</span></h3>
              <button className="btn-close-copy" onClick={() => setDetailsArtisanCopy(null)}>➔ Retourner à la liste</button>
            </div>

            <div className="questions-review-stack">
              {detailsArtisanCopy.questionsFormatees.map((q, index) => (
                <div key={index} className="review-question-card">
                  <h4>Question {q.ordre} : {q.libelleQuestion}</h4>
                  <div className="review-options-list">
                    {q.options.map((opt, i) => {
                      const estLaReponseDonnee = q.reponseDonnee === opt.libelleReponse;
                      return (
                        <div key={i} className={`review-option-item ${estLaReponseDonnee ? "chosen" : ""}`}>
                          <span>{estLaReponseDonnee ? "🔹" : "⚪"} {opt.libelleReponse}</span>
                          {estLaReponseDonnee && <span className="chosen-tag">Réponse saisie</span>}
                        </div>
                      );
                    })}
                  </div>
                </div>
              ))}
            </div>
          </div>
        ) : (

          /* ========================================================
             VUE B : TABLEAUX STANDARDS DE CONSULTATION PARAMÈTRES
             ======================================================== */
          <table className="admin-table">
            <thead>
              {sousOnglet === "copies_artisans" && (
                <tr>
                  <th>NPI</th><th>Artisan</th><th>Métier</th><th>Date du test</th><th>Score</th><th></th>
                </tr>
              )}
              {sousOnglet === "historique" && (
                <tr>
                  <th>NPI</th><th>Artisan</th><th>Date d'inscription</th><th>Statut</th>
                </tr>
              )}
              {sousOnglet === "objectifs" && (
                <tr>
                  <th>Corps de métier</th><th>Places à pourvoir</th><th>Période</th><th></th>
                </tr>
              )}
              {sousOnglet === "artisans" && (
                <tr>
                  <th>NPI</th><th>Nom</th><th>Prénom</th><th>Téléphone</th>
                </tr>
              )}
              {sousOnglet === "departements" && (
                <tr><th>ID</th><th>Département</th><th></th></tr>
              )}
              {sousOnglet === "communes" && (
                <tr><th>ID</th><th>Commune</th><th>Département parent</th><th></th></tr>
              )}
              {sousOnglet === "arrondissements" && (
                <tr><th>ID</th><th>Arrondissement</th><th>Commune parente</th><th></th></tr>
              )}
              {sousOnglet === "quartiers" && (
                <tr><th>ID</th><th>Quartier / Village</th><th>Arrondissement parent</th><th></th></tr>
              )}
            </thead>
            <tbody>
              {donneesFiltrees.length === 0 ? (
                <tr>
                  <td className="empty-cell" colSpan={6}>Aucune donnée disponible pour ce sous-onglet.</td>
                </tr>
              ) : (
                donneesFiltrees.map((item, index) => (
                  <tr key={item.id || item.id_test || item.idObjectif || index}>
                    {sousOnglet === "copies_artisans" && (
                      <>
                        <td>{item.npi || item.npi_artisan}</td>
                        <td>{item.nom_artisan || item.nom}</td>
                        <td><span className="badge-metier-tag">{item.libelle_metier || item.metier}</span></td>
                        <td className="date-cell">{item.date_test}</td>
                        <td><span className="badge-enum-style">{item.score}</span></td>
                        <td className="actions-wrapper">
                          <button
                            className="btn-audit-action"
                            onClick={() => chargerFicheCopieTest(item.id_test, item.nom_artisan || item.nom)}
                          >
                            Voir la copie
                          </button>
                        </td>
                      </>
                    )}

                    {sousOnglet === "historique" && (
                      <>
                        <td>{item.npi || item.npi_artisan}</td>
                        <td>{item.nom_artisan || item.nom}</td>
                        <td className="date-cell">{item.date_inscription}</td>
                        <td><span className="badge-enum-style">{item.statut}</span></td>
                      </>
                    )}

                    {sousOnglet === "objectifs" && (
                      <>
                        <td><span className="badge-metier-tag">{item.libelle_metier || item.code_corpsmetier}</span></td>
                        <td>{item.nombrePlaces || item.nombre_places}</td>
                        <td>{item.periode}</td>
                        <td className="actions-wrapper">
                          <button className="btn-delete" onClick={() => handleSupprimerElement(item.idObjectif)}>Supprimer</button>
                        </td>
                      </>
                    )}

                    {sousOnglet === "artisans" && (
                      <>
                        <td className="id-cell">{item.npi || item.npi_artisan}</td>
                        <td>{item.nom}</td>
                        <td>{item.prenom}</td>
                        <td>{item.telephone}</td>
                      </>
                    )}

                    {sousOnglet === "departements" && (
                      <>
                        <td className="id-cell">{item.id_departement}</td>
                        <td>{item.nom_departement}</td>
                        <td className="actions-wrapper">
                          <button className="btn-delete" onClick={() => handleSupprimerElement(item.id_departement)}>Supprimer</button>
                        </td>
                      </>
                    )}

                    {sousOnglet === "communes" && (
                      <>
                        <td className="id-cell">{item.id_commune}</td>
                        <td>{item.nom_commune}</td>
                        <td>{item.id_departement}</td>
                        <td className="actions-wrapper">
                          <button className="btn-delete" onClick={() => handleSupprimerElement(item.id_commune)}>Supprimer</button>
                        </td>
                      </>
                    )}

                    {sousOnglet === "arrondissements" && (
                      <>
                        <td className="id-cell">{item.id_arrondissement}</td>
                        <td>{item.nom_arrondissement}</td>
                        <td>{item.id_commune}</td>
                        <td className="actions-wrapper">
                          <button className="btn-delete" onClick={() => handleSupprimerElement(item.id_arrondissement)}>Supprimer</button>
                        </td>
                      </>
                    )}

                    {sousOnglet === "quartiers" && (
                      <>
                        <td className="id-cell">{item.id_quartier}</td>
                        <td>{item.nom_quartier}</td>
                        <td>{item.id_arrondissement}</td>
                        <td className="actions-wrapper">
                          <button className="btn-delete" onClick={() => handleSupprimerElement(item.id_quartier)}>Supprimer</button>
                        </td>
                      </>
                    )}
                  </tr>
                ))
              )}
            </tbody>
          </table>
        )}
      </div>

      {/* WINDOW MODAL DE CRÉATION ACCORDÉ AUX DROITS ADMIN */}
      {modalOuvert && (
        <div className="modal-overlay">
          <div className="modal-card">
            <form onSubmit={handleFormSubmit}>
              {sousOnglet === "objectifs" ? (

                /* FORMULAIRE UNIQUE POUR LA TABLE OBJECTIF_FORMATION COHÉRENT À VOS 4 COLONNES */
                <>
                  <h3>Fixer un Quota d'Objectif National</h3>

                  <div className="form-group">
                    <label>Nombre de places à pourvoir</label>
                    <input
                      type="number"
                      placeholder="Ex: 150"
                      value={formDataObjectif.nombrePlaces}
                      onChange={(e) => setFormDataObjectif({ ...formDataObjectif, nombrePlaces: e.target.value })}
                      required
                    />
                  </div>

                  <div className="form-group">
                    <label>Période d'évaluation</label>
                    <input
                      type="text"
                      placeholder="Ex: Année 2026 ou 7 derniers jours"
                      value={formDataObjectif.periode}
                      onChange={(e) => setFormDataObjectif({ ...formDataObjectif, periode: e.target.value })}
                      required
                    />
                  </div>

                  <div className="form-group">
                    <label>Corps de métier concerné</label>
                    <select
                      value={formDataObjectif.code_corpsmetier}
                      onChange={(e) => setFormDataObjectif({ ...formDataObjectif, code_corpsmetier: e.target.value })}
                      required
                    >
                      <option value="">-- Choisir le métier --</option>
                      {metiers.map((m) => (
                        <option key={m.code_corpsmetier || m.id} value={m.code_corpsmetier || m.id}>
                          {m.libelle}
                        </option>
                      ))}
                    </select>
                  </div>
                </>
              ) : (

                /* FORMULAIRE UNIQUE POUR LES CRUDS GÉOGRAPHIQUES SECONDAIRES */
                <>
                  <h3>Ajouter au registre territorial</h3>

                  <div className="form-group">
                    <label>Nom ou Libellé de la zone géographique</label>
                    <input
                      type="text"
                      placeholder="Ex: COTONOU ou BORGOU"
                      value={formDataGeo.nom}
                      onChange={(e) => setFormDataGeo({ ...formDataGeo, nom: e.target.value.toUpperCase() })}
                      required
                    />
                  </div>

                  {sousOnglet !== "departements" && (
                    <div className="form-group">
                      <label>Identifiant unique (ID) du parent supérieur rattaché</label>
                      <input
                        type="number"
                        placeholder="Saisir l'ID parent requis..."
                        value={formDataGeo.parentId}
                        onChange={(e) => setFormDataGeo({ ...formDataGeo, parentId: e.target.value })}
                        required
                      />
                    </div>
                  )}
                </>
              )}

              <div className="modal-actions">
                <button type="button" className="btn-cancel" onClick={() => setModalOuvert(false)}>Annuler</button>
                <button type="submit" className="btn-save">Enregistrer</button>
              </div>
            </form>
          </div>
        </div>
      )}

      {/* Squelette CSS épuré */}
      <style>{`
        .management-container { width: 100%; box-sizing: border-box; }
        .content-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; }
        .content-header h2 { font-size: 1.4rem; color: #0f172a; margin: 0; font-weight: 700; }
        .subtitle { font-size: 0.88rem; color: #64748b; margin-top: 4px; }
        .btn-add { background: #000; color: #fff; border: none; padding: 10px 18px; border-radius: 8px; font-weight: 600; cursor: pointer; }
        .parametres-nav-tabs { display: flex; gap: 6px; border-bottom: 1px solid #e2e8f0; padding-bottom: 12px; margin-bottom: 24px; overflow-x: auto; }
        .parametres-nav-tabs button { background: transparent; border: none; padding: 8px 14px; font-size: 0.88rem; font-weight: 600; color: #64748b; cursor: pointer; border-radius: 6px; white-space: nowrap; }
        .parametres-nav-tabs button.active { background: #0f172a; color: #fff; }
        .search-box { margin-bottom: 24px; }
        .search-box input { width: 100%; padding: 12px 14px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 0.95rem; }
        .table-card { background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 10px; overflow-x: auto; }
        .admin-table { width: 100%; border-collapse: collapse; text-align: left; }
        .admin-table th { background: #f8fafc; padding: 14px 16px; font-size: 0.82rem; text-transform: uppercase; color: #64748b; font-weight: 700; border-bottom: 1px solid #e2e8f0; }
        .admin-table td { padding: 14px 16px; border-bottom: 1px solid #f1f5f9; font-size: 0.95rem; color: #334155; }
        .id-cell { font-family: monospace; font-weight: 700; color: #94a3b8; }
        .date-cell { font-variant-numeric: tabular-nums; color: #64748b; }
        .badge-enum-style { background: #f0fdfa; color: #0d9488; padding: 4px 10px; border-radius: 50px; font-size: 0.8rem; font-weight: 600; }
        .badge-metier-tag { background: #eff6ff; color: #1e40af; padding: 2px 6px; border-radius: 4px; font-family: monospace; font-weight: 700; font-size: 0.82rem; }
        .actions-wrapper { display: flex; gap: 8px; justify-content: flex-end; }
        .btn-delete { background: #fef2f2; border: 1px solid #fca5a5; color: #991b1b; padding: 6px 12px; border-radius: 6px; cursor: pointer; font-size: 0.85rem; }
        .btn-audit-action { background: #f1f5f9; border: 1px solid #cbd5e1; padding: 6px 12px; border-radius: 6px; font-size: 0.85rem; font-weight: 600; cursor: pointer; color: #1e293b; }
        .btn-audit-action:disabled { opacity: 0.4; cursor: not-allowed; }
        .exam-copy-viewer { text-align: left; padding: 10px; }
        .copy-viewer-header { display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #e2e8f0; padding-bottom: 16px; margin-bottom: 24px; }
        .copy-viewer-header h3 { margin: 0; font-size: 1.2rem; color: #0f172a; }
        .copy-viewer-header h3 span { color: #2563eb; }
        .btn-close-copy { background: #000; color: #fff; border: none; padding: 8px 16px; border-radius: 6px; font-size: 0.88rem; font-weight: 600; cursor: pointer; }
        .questions-review-stack { display: flex; flex-direction: column; gap: 20px; }
        .review-question-card { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 16px; }
        .review-question-card h4 { margin: 0 0 12px 0; font-size: 1rem; color: #0f172a; }
        .review-options-list { display: flex; flex-direction: column; gap: 8px; }
        .review-option-item { display: flex; justify-content: space-between; align-items: center; padding: 10px 14px; border: 1px solid #cbd5e1; border-radius: 6px; background: #fff; font-size: 0.92rem; }
        .review-option-item.chosen { border-color: #93c5fd; background: #eff6ff; font-weight: 600; color: #1e40af; }
        .chosen-tag { font-size: 0.75rem; background: #bfdbfe; padding: 2px 8px; border-radius: 4px; }
        .modal-overlay { position: fixed; inset: 0; background: rgba(15, 23, 42, 0.3); display: flex; justify-content: center; align-items: center; z-index: 200; }
        .modal-card { background: #fff; width: 100%; max-width: 420px; padding: 28px; border-radius: 12px; box-shadow: 0 20px 25px rgba(0,0,0,0.1); }
        .modal-card h3 { margin: 0 0 20px 0; font-weight: 700; font-size: 1.2rem; }
        .form-group { display: flex; flex-direction: column; gap: 6px; margin-bottom: 16px; text-align: left; }
        .form-group label { font-size: 0.85rem; font-weight: 600; color: #475569; }
        .form-group input, .form-group select { padding: 10px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 0.92rem; background: #fff; width: 100%; box-sizing: border-box; }
        .modal-actions { display: flex; justify-content: flex-end; gap: 10px; border-top: 1px solid #f1f5f9; padding-top: 14px; margin-top: 20px; }
        .btn-cancel { background: #fff; border: 1px solid #cbd5e1; padding: 8px 14px; border-radius: 6px; cursor: pointer; }
        .btn-save { background: #000; color: #fff; border: none; padding: 8px 18px; border-radius: 6px; font-weight: 600; cursor: pointer; }
        .empty-cell { text-align: center; padding: 40px !important; color: #64748b; }
        .loader { padding: 40px; text-align: center; color: #64748b; }
      `}</style>
    </div>
  );
}