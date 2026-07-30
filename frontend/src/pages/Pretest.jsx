import { useState, useEffect } from "react";

export default function Pretest({ idArtisan, onPretestSuccess }) {
  const [formData, setFormData] = useState({
    code_corpsmetier: "", // ✅ Nom exact de la BDD
    nbrAnExp: "",
    idDepart_residence: "", idCommune_residence: "", idArrond_residence: "", nomQuartier_residence: "", lat_residence: "", lon_residence: "",
    idDepart_atelier: "", idCommune_atelier: "", idArrond_atelier: "", nomQuartier_atelier: "", lat_atelier: "", lon_atelier: ""
  });

  const [departements, setDepartements] = useState([]);
  const [corpsMetiers, setCorpsMetiers] = useState([]);
  const [communesRes, setCommunesRes] = useState([]);
  const [arrondRes, setArrondRes] = useState([]);
  const [communesAt, setCommunesAt] = useState([]);
  const [arrondAt, setArrondAt] = useState([]);
  const [chargement, setChargement] = useState(false);
  const [erreur, setErreur] = useState(null);

  useEffect(() => {
    fetch("http://localhost/Code/backend/api/pretest/init.php")
      .then((res) => res.json())
      .then((data) => {
        if (data.success) {
          setDepartements(data.departements || []);
          setCorpsMetiers(data.corpsMetiers || []);
        }
      });
  }, []);

  const loadCommunes = async (idDepart, type) => {
    if (!idDepart) return;
    const res = await fetch(`http://localhost/Code/backend/api/pretest/communes.php?idDepart=${idDepart}`);
    const data = await res.json();
    if (data.success) {
      if (type === "residence") { setCommunesRes(data.communes || []); setArrondRes([]); }
      else { setCommunesAt(data.communes || []); setArrondAt([]); }
    }
  };

  const loadArrond = async (idCommune, type) => {
    if (!idCommune) return;
    const res = await fetch(`http://localhost/Code/backend/api/pretest/arrondissements.php?idCommune=${idCommune}`);
    const data = await res.json();
    if (data.success) {
      if (type === "residence") setArrondRes(data.arrondissements || []);
      else setArrondAt(data.arrondissements || []);
    }
  };

  const handleInputChange = (e) => {
    const { name, value } = e.target;
    setFormData({ ...formData, [name]: value });
    if (name === "idDepart_residence") loadCommunes(value, "residence");
    else if (name === "idCommune_residence") loadArrond(value, "residence");
    else if (name === "idDepart_atelier") loadCommunes(value, "atelier");
    else if (name === "idCommune_atelier") loadArrond(value, "atelier");
  };

  const handleSubmit = (e) => {
    e.preventDefault();
    setErreur(null);

    if (!formData.code_corpsmetier || !formData.idArrond_residence || !formData.idArrond_atelier) {
      setErreur("Veuillez remplir tous les champs obligatoires.");
      return;
    }

    setChargement(true);
    fetch("http://localhost/Code/backend/api/pretest/save.php", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({
        idArtisan,
        code_corpsmetier: formData.code_corpsmetier,
        nbrAnExp: formData.nbrAnExp || null,
        id_arrondissement_residence: formData.idArrond_residence,
        nom_quartier_residence: formData.nomQuartier_residence,
        latitude_residence: formData.lat_residence || null,
        longitude_residence: formData.lon_residence || null,
        id_arrondissement_atelier: formData.idArrond_atelier,
        nom_quartier_atelier: formData.nomQuartier_atelier,
        latitude_atelier: formData.lat_atelier || null,
        longitude_atelier: formData.lon_atelier || null
      })
    })
      .then((res) => res.json())
      .then((data) => {
        setChargement(false);
        if (data.success) {
          //  INDICATEUR : On envoie le code exact à App.jsx
          console.log("🚀 PRETEST: Envoi du code_corpsmetier =", formData.code_corpsmetier);
          if (onPretestSuccess) onPretestSuccess(formData.code_corpsmetier);
        } else {
          setErreur(data.message || "Erreur lors de l'enregistrement.");
        }
      })
      .catch(() => {
        setChargement(false);
        setErreur("Impossible de joindre le serveur.");
      });
  };

  return (
    <div className="pretest-wrapper">
      <div className="pretest-card">
        <div className="pretest-header">
          <h2>Avant de commencer</h2>
          <p>Ces informations déterminent le contenu de votre test.</p>
        </div>
        {erreur && <div className="error-box"><span>️</span><p>{erreur}</p></div>}
        <form onSubmit={handleSubmit} className="pretest-form">
          <div className="form-group">
            <label>Corps de métier *</label>
            <select name="code_corpsmetier" value={formData.code_corpsmetier} onChange={handleInputChange} required>
              <option value="">Sélectionner votre métier</option>
              {corpsMetiers.map((m) => (
                <option key={m.code_corpsmetier} value={m.code_corpsmetier}>{m.libelle}</option>
              ))}
            </select>
          </div>
          <div className="form-group">
            <label>Années d'expérience</label>
            <input type="number" name="nbrAnExp" value={formData.nbrAnExp} onChange={handleInputChange} min="0" max="50" />
          </div>
          <hr className="divider" />
          <div className="geo-section">
            <h3>🏠 Résidence</h3>
            <div className="geo-row">
              <select name="idDepart_residence" value={formData.idDepart_residence} onChange={(e) => { handleInputChange(e); loadCommunes(e.target.value, "residence"); }} required>
                <option value="">Département *</option>
                {departements.map((d) => <option key={d.idDepart} value={d.idDepart}>{d.nomDepartement}</option>)}
              </select>
              <select name="idCommune_residence" value={formData.idCommune_residence} onChange={handleInputChange} required disabled={!formData.idDepart_residence}>
                <option value="">Commune *</option>
                {communesRes.map((c) => <option key={c.idCommune} value={c.idCommune}>{c.nomCommune}</option>)}
              </select>
            </div>
            <div className="geo-row">
              <select name="idArrond_residence" value={formData.idArrond_residence} onChange={handleInputChange} required disabled={!formData.idCommune_residence}>
                <option value="">Arrondissement *</option>
                {arrondRes.map((a) => <option key={a.id_arrondissement} value={a.id_arrondissement}>{a.nom_arrondissement}</option>)}
              </select>
              <input type="text" name="nomQuartier_residence" value={formData.nomQuartier_residence} onChange={handleInputChange} placeholder="Quartier *" required disabled={!formData.idArrond_residence} />
            </div>
          </div>
          <div className="geo-section">
            <h3>🔨 Atelier</h3>
            <div className="geo-row">
              <select name="idDepart_atelier" value={formData.idDepart_atelier} onChange={(e) => { handleInputChange(e); loadCommunes(e.target.value, "atelier"); }} required>
                <option value="">Département *</option>
                {departements.map((d) => <option key={d.idDepart} value={d.idDepart}>{d.nomDepartement}</option>)}
              </select>
              <select name="idCommune_atelier" value={formData.idCommune_atelier} onChange={handleInputChange} required disabled={!formData.idDepart_atelier}>
                <option value="">Commune *</option>
                {communesAt.map((c) => <option key={c.idCommune} value={c.idCommune}>{c.nomCommune}</option>)}
              </select>
            </div>
            <div className="geo-row">
              <select name="idArrond_atelier" value={formData.idArrond_atelier} onChange={handleInputChange} required disabled={!formData.idCommune_atelier}>
                <option value="">Arrondissement *</option>
                {/* ✅ CORRECTION DE LA SYNTAXE ICI (parenthèse fermante ajoutée) */}
                {arrondAt.map((a) => <option key={a.id_arrondissement} value={a.id_arrondissement}>{a.nom_arrondissement}</option>)}
              </select>
              <input type="text" name="nomQuartier_atelier" value={formData.nomQuartier_atelier} onChange={handleInputChange} placeholder="Quartier *" required disabled={!formData.idArrond_atelier} />
            </div>
          </div>
          <button type="submit" className="btn-continue" disabled={chargement}>
            {chargement ? "Enregistrement..." : "Continuer vers le test"}
          </button>
        </form>
      </div>
      <style>{`
        .pretest-wrapper { display: flex; justify-content: center; align-items: center; min-height: 100vh; background-color: #f8fafc; padding: 20px; font-family: 'Montserrat', sans-serif; }
        .pretest-card { background: #ffffff; border: 1px solid #e2e8f0; border-radius: 16px; width: 100%; max-width: 520px; padding: 40px 32px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05); }
        .pretest-header { margin-bottom: 28px; }
        .pretest-header h2 { font-size: 1.5rem; font-weight: 700; color: #0f172a; margin: 0 0 8px 0; }
        .pretest-header p { font-size: 0.9rem; color: #64748b; margin: 0; line-height: 1.5; }
        .error-box { background-color: #fef2f2; border: 1px solid #fca5a5; padding: 12px 16px; border-radius: 8px; display: flex; align-items: center; gap: 12px; margin-bottom: 20px; color: #991b1b; }
        .pretest-form { display: flex; flex-direction: column; gap: 20px; }
        .form-group { display: flex; flex-direction: column; gap: 6px; }
        .form-group label { font-size: 0.88rem; font-weight: 600; color: #334155; }
        .form-group select, .form-group input { width: 100%; padding: 12px 14px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 0.95rem; color: #0f172a; background-color: #ffffff; font-family: 'Montserrat', sans-serif; box-sizing: border-box; }
        .form-group select:disabled, .form-group input:disabled { background-color: #f1f5f9; color: #94a3b8; cursor: not-allowed; }
        .divider { border: none; border-top: 1px solid #e2e8f0; margin: 8px 0; }
        .geo-section { display: flex; flex-direction: column; gap: 12px; }
        .geo-section h3 { font-size: 0.95rem; font-weight: 700; color: #0f172a; margin: 0; }
        .geo-row { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
        .btn-continue { width: 100%; background: #0f172a; color: #ffffff; border: none; padding: 14px; font-size: 1rem; font-weight: 600; border-radius: 8px; cursor: pointer; font-family: 'Montserrat', sans-serif; transition: background 0.2s; }
        .btn-continue:hover:not(:disabled) { background: #1e293b; }
        .btn-continue:disabled { background: #cbd5e1; color: #94a3b8; cursor: not-allowed; }
      `}</style>
    </div>
  );
}