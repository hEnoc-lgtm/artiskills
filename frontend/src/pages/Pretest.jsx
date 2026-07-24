import { useState, useEffect } from "react";

export default function Pretest({ idArtisan, onPretestSuccess }) {
  const [formData, setFormData] = useState({
    code_corpsmetier: "",
    nbrAnExp: "",
    // Résidence
    idDepart_residence: "",
    nomCommune_residence: "", // Ajouté pour aider la recherche OSM
    idArrond_residence: "",
    nomQuartier_residence: "",
    lat_residence: "",
    lon_residence: "",
    // Atelier
    idDepart_atelier: "",
    nomCommune_atelier: "",
    idArrond_atelier: "",
    nomQuartier_atelier: "",
    lat_atelier: "",
    lon_atelier: ""
  });

  const [departements, setDepartements] = useState([]);
  const [communes, setCommunes] = useState([]);
  const [arrondissements, setArrondissements] = useState([]);
  const [corpsMetiers, setCorpsMetiers] = useState([]);
  
  const [chargement, setChargement] = useState(false);
  const [rechercheCoords, setRechercheCoords] = useState(false);
  const [erreur, setErreur] = useState(null);

  // Charger les données initiales
  useEffect(() => {
    fetch("http://localhost/Code/backend/api/pretest/init.php")
      .then((res) => res.json())
      .then((data) => {
        if (data.success) {
          setDepartements(data.departements || []);
          setCorpsMetiers(data.corpsMetiers || []);
        }
      })
      .catch((err) => console.error("Erreur chargement init:", err));
  }, []);

  const loadCommunes = async (idDepart, type) => {
    if (!idDepart) return;
    const res = await fetch(`http://localhost/Code/backend/api/pretest/communes.php?idDepart=${idDepart}`);
    const data = await res.json();
    if (data.success) {
      setCommunes(data.communes || []);
      setFormData(prev => ({ ...prev, [`idCommune_${type}`]: "", [`idArrond_${type}`]: "", [`nomQuartier_${type}`]: "", [`lat_${type}`]: "", [`lon_${type}`]: "" }));
      setArrondissements([]);
    }
  };

  const loadArrondissements = async (idCommune, type) => {
    if (!idCommune) return;
    const res = await fetch(`http://localhost/Code/backend/api/pretest/arrondissements.php?idCommune=${idCommune}`);
    const data = await res.json();
    if (data.success) {
      setArrondissements(data.arrondissements || []);
      setFormData(prev => ({ ...prev, [`idArrond_${type}`]: "", [`nomQuartier_${type}`]: "", [`lat_${type}`]: "", [`lon_${type}`]: "" }));
    }
  };

  const handleInputChange = (e) => {
    const { name, value } = e.target;
    setFormData({ ...formData, [name]: value });

    if (name === "idDepart_residence") loadCommunes(value, "residence");
    else if (name === "idCommune_residence") loadArrondissements(value, "residence");
    else if (name === "idDepart_atelier") loadCommunes(value, "atelier");
    else if (name === "idCommune_atelier") loadArrondissements(value, "atelier");
  };

  // Fonction pour interroger OpenStreetMap (Nominatim)
  const fetchCoordinates = async (type) => {
    const quartier = formData[`nomQuartier_${type}`];
    const commune = formData[`nomCommune_${type}`]; // On utilise la commune pour affiner la recherche
    
    if (!quartier || quartier.length < 3) return;

    setRechercheCoords(true);
    // L'User-Agent est OBLIGATOIRE pour l'API Nominatim
    const headers = { 'User-Agent': 'ArtiSkills/1.0 (henoc@example.com)' }; 
    const query = encodeURIComponent(`${quartier}, ${commune || ''}, Bénin`);
    const url = `https://nominatim.openstreetmap.org/search?format=json&q=${query}&countrycodes=bj&limit=1`;

    try {
      const res = await fetch(url, { headers });
      const data = await res.json();
      
      if (data && data.length > 0) {
        setFormData(prev => ({
          ...prev,
          [`lat_${type}`]: data[0].lat,
          [`lon_${type}`]: data[0].lon
        }));
      } else {
        setErreur(`Impossible de localiser "${quartier}". Veuillez vérifier l'orthographe.`);
      }
    } catch (err) {
      console.error("Erreur OSM:", err);
    } finally {
      setRechercheCoords(false);
    }
  };

  const handleSubmit = (e) => {
    e.preventDefault();
    setErreur(null);

    if (!formData.code_corpsmetier || !formData.idArrond_residence || !formData.idArrond_atelier) {
      setErreur("Veuillez remplir tous les champs obligatoires (Métier, Arrondissement de résidence et d'atelier).");
      return;
    }
    if (!formData.nomQuartier_residence || !formData.nomQuartier_atelier) {
      setErreur("Veuillez saisir le nom de votre quartier/village de résidence et d'atelier.");
      return;
    }

    setChargement(true);

    // On envoie les NOMS et les COORDONNÉES au backend. 
    // Le backend se chargera de vérifier si le quartier existe, sinon il le créera et nous renverra l'ID.
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
          if (onPretestSuccess) onPretestSuccess(formData.code_corpsmetier);
        } else {
          setErreur(data.message || "Une erreur est survenue.");
        }
      })
      .catch((err) => {
        setChargement(false);
        setErreur("Impossible de joindre le serveur.");
      });
  };

  return (
    <div className="pretest-wrapper">
      <div className="pretest-card">
        <div className="pretest-header">
          <h2>Avant de commencer</h2>
          <p>Ces informations déterminent le contenu de votre test et votre centre d'affectation.</p>
        </div>

        {erreur && (
          <div className="error-box">
            <span className="error-icon">⚠️</span>
            <p>{erreur}</p>
          </div>
        )}

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
            <label>Nombre d'années d'expérience</label>
            <input type="number" name="nbrAnExp" value={formData.nbrAnExp} onChange={handleInputChange} placeholder="Ex. 5" min="0" max="50" />
          </div>

          <hr className="divider" />

          {/* RÉSIDENCE */}
          <div className="geo-section">
            <h3><span className="icon">🏠</span> Lieu de Résidence</h3>
            <div className="geo-row">
              <select name="idDepart_residence" value={formData.idDepart_residence} onChange={handleInputChange} required>
                <option value="">Département *</option>
                {departements.map((d) => <option key={d.idDepart} value={d.idDepart}>{d.nomDepartement}</option>)}
              </select>
              <select name="idCommune_residence" value={formData.idCommune_residence} onChange={handleInputChange} required disabled={!formData.idDepart_residence}>
                <option value="">Commune *</option>
                {communes.map((c) => <option key={c.idCommune} value={c.idCommune}>{c.nomCommune}</option>)}
              </select>
            </div>
            <div className="geo-row">
              <select name="idArrond_residence" value={formData.idArrond_residence} onChange={handleInputChange} required disabled={!formData.idCommune_residence}>
                <option value="">Arrondissement *</option>
                {arrondissements.map((a) => <option key={a.id_arrondissement} value={a.id_arrondissement}>{a.nom_arrondissement}</option>)}
              </select>
              {/* CHAMP DE SAISIE POUR LE QUARTIER */}
              <input 
                type="text" 
                name="nomQuartier_residence" 
                value={formData.nomQuartier_residence} 
                onChange={handleInputChange} 
                onBlur={() => fetchCoordinates("residence")} // Déclenche la recherche OSM quand on quitte le champ
                placeholder="Nom du quartier/village *" 
                required 
                disabled={!formData.idArrond_residence}
              />
            </div>
            {rechercheCoords && formData.nomQuartier_residence && <small style={{color: '#64748b', fontSize: '0.8rem'}}>📍 Recherche des coordonnées en cours...</small>}
          </div>

          {/* ATELIER */}
          <div className="geo-section">
            <h3><span className="icon">🔨</span> Lieu de l'Atelier</h3>
            <div className="geo-row">
              <select name="idDepart_atelier" value={formData.idDepart_atelier} onChange={handleInputChange} required>
                <option value="">Département *</option>
                {departements.map((d) => <option key={d.idDepart} value={d.idDepart}>{d.nomDepartement}</option>)}
              </select>
              <select name="idCommune_atelier" value={formData.idCommune_atelier} onChange={handleInputChange} required disabled={!formData.idDepart_atelier}>
                <option value="">Commune *</option>
                {communes.map((c) => <option key={c.idCommune} value={c.idCommune}>{c.nomCommune}</option>)}
              </select>
            </div>
            <div className="geo-row">
              <select name="idArrond_atelier" value={formData.idArrond_atelier} onChange={handleInputChange} required disabled={!formData.idCommune_atelier}>
                <option value="">Arrondissement *</option>
                {arrondissements.map((a) => <option key={a.id_arrondissement} value={a.id_arrondissement}>{a.nom_arrondissement}</option>)}
              </select>
              <input 
                type="text" 
                name="nomQuartier_atelier" 
                value={formData.nomQuartier_atelier} 
                onChange={handleInputChange} 
                onBlur={() => fetchCoordinates("atelier")}
                placeholder="Nom du quartier/village *" 
                required 
                disabled={!formData.idArrond_atelier}
              />
            </div>
          </div>

          <button type="submit" className="btn-continue" disabled={chargement}>
            {chargement ? "Enregistrement..." : "Continuer vers le test"}
          </button>
        </form>
      </div>

      <style>{`
        @import url('https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800&display=swap');
        .pretest-wrapper { display: flex; justify-content: center; align-items: center; min-height: 100vh; background-color: #f8fafc; padding: 20px; font-family: 'Montserrat', sans-serif; }
        .pretest-card { background: #ffffff; border: 1px solid #e2e8f0; border-radius: 16px; width: 100%; max-width: 520px; padding: 40px 32px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05); }
        .pretest-header { margin-bottom: 28px; }
        .pretest-header h2 { font-size: 1.5rem; font-weight: 700; color: #0f172a; margin: 0 0 8px 0; }
        .pretest-header p { font-size: 0.9rem; color: #64748b; margin: 0; line-height: 1.5; }
        .error-box { background-color: #fef2f2; border: 1px solid #fca5a5; padding: 12px 16px; border-radius: 8px; display: flex; align-items: center; gap: 12px; margin-bottom: 20px; }
        .error-box p { margin: 0; font-size: 0.88rem; color: #991b1b; font-weight: 500; }
        .pretest-form { display: flex; flex-direction: column; gap: 20px; }
        .form-group { display: flex; flex-direction: column; gap: 6px; }
        .form-group label { font-size: 0.88rem; font-weight: 600; color: #334155; }
        .form-group select, .form-group input { width: 100%; padding: 12px 14px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 0.95rem; color: #0f172a; background-color: #ffffff; font-family: 'Montserrat', sans-serif; box-sizing: border-box; transition: border-color 0.2s; }
        .form-group select:focus, .form-group input:focus { outline: none; border-color: #000000; }
        .form-group select:disabled, .form-group input:disabled { background-color: #f1f5f9; color: #94a3b8; cursor: not-allowed; }
        .divider { border: none; border-top: 1px solid #e2e8f0; margin: 8px 0; }
        .geo-section { display: flex; flex-direction: column; gap: 12px; }
        .geo-section h3 { font-size: 0.95rem; font-weight: 700; color: #0f172a; margin: 0; display: flex; align-items: center; gap: 8px; }
        .geo-row { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
        .btn-continue { width: 100%; background: #000000; color: #ffffff; border: none; padding: 14px; font-size: 1rem; font-weight: 600; border-radius: 8px; cursor: pointer; font-family: 'Montserrat', sans-serif; transition: background 0.2s; margin-top: 8px; }
        .btn-continue:hover:not(:disabled) { background: #1e293b; }
        .btn-continue:disabled { background: #cbd5e1; color: #94a3b8; cursor: not-allowed; }
      `}</style>
    </div>
  );
}