import { useEffect, useState } from "react";

export default function Pretest({ idArtisan, onPretestSuccess }) {
  const [metiers, setMetiers] = useState([]);
  const [metierSelectionne, setMetierSelectionne] = useState("");
  const [chargement, setChargement] = useState(true);

  useEffect(() => {
    fetch("http://localhost/backend/CRUD/Read_corpsmetier.php")
      .then((res) => res.json())
      .then((data) => {
        if (data.success) {
          setMetiers(data.data);
          if (data.data.length > 0) {
            setMetierSelectionne(data.data[0].code_corpsmetier);
          }
        }
        setChargement(false);
      })
      .catch(() => {
        setChargement(false);
      });
  }, []);

  const handleSubmit = (e) => {
    e.preventDefault();
    if (!metierSelectionne) return;
    if (onPretestSuccess) onPretestSuccess(metierSelectionne);
  };

  return (
    <div className="pretest-container">
      <div className="pretest-card">
        <h2>Choix de parcours de test</h2>
        <p>Bonjour artisan {idArtisan || ""}. Choisissez le corps de metier qui correspond a votre profil.</p>

        <form onSubmit={handleSubmit}>
          <label htmlFor="metier">Corps de metier</label>
          <select
            id="metier"
            value={metierSelectionne}
            onChange={(e) => setMetierSelectionne(e.target.value)}
            disabled={chargement}
          >
            {metiers.map((metier) => (
              <option key={metier.code_corpsmetier} value={metier.code_corpsmetier}>
                {metier.libelle}
              </option>
            ))}
          </select>

          <button type="submit">Continuer</button>
        </form>
      </div>
    </div>
  );
}
