-- ============================================================
-- Base de données : artiskills
-- Projet : ArtiSkills - Plateforme de gestion des tests de
--          compétence des artisans du programme ARCH de l'ANPS
-- Version 2 : hiérarchie géographique Département > Commune >
--             Arrondissement, et fusion Agent/Admin en Profil
-- ============================================================

CREATE DATABASE IF NOT EXISTS artiskills CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE artiskills;

-- ============================================================
-- 1. DEPARTEMENT
-- ============================================================
CREATE TABLE departement (
    idDepart INT AUTO_INCREMENT PRIMARY KEY,
    nomDepartement VARCHAR(100) NOT NULL
) ENGINE=InnoDB;

-- ============================================================
-- 2. COMMUNE
-- ============================================================
CREATE TABLE commune (
    idCommune INT AUTO_INCREMENT PRIMARY KEY,
    nomCommune VARCHAR(100) NOT NULL,
    idDepart INT NOT NULL,
    FOREIGN KEY (idDepart) REFERENCES departement(idDepart)
        ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB;

-- ============================================================
-- 3. ARRONDISSEMENT (avec coordonnées GPS pour le calcul de proximité)
-- ============================================================
CREATE TABLE arrondissement (
    id_arrondissement INT AUTO_INCREMENT PRIMARY KEY,
    nom_arrondissement VARCHAR(100) NOT NULL,
    longitude FLOAT NOT NULL,
    latitude FLOAT NOT NULL,
    idCommune INT NOT NULL,
    FOREIGN KEY (idCommune) REFERENCES commune(idCommune)
        ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB;

-- ============================================================
-- 4. CORPS_METIER
-- ============================================================
CREATE TABLE corps_metier (
    code VARCHAR(20) PRIMARY KEY,
    libelle VARCHAR(100) NOT NULL
) ENGINE=InnoDB;

-- ============================================================
-- 5. ARTISAN (hérite de Utilisateur)
-- ============================================================
CREATE TABLE artisan (
    id_artisan INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    prenom VARCHAR(100) NOT NULL,
    contact VARCHAR(20) NOT NULL UNIQUE,       -- numéro de téléphone (identifiant de connexion)
    dateCreation DATE NOT NULL DEFAULT (CURRENT_DATE),
    sexe ENUM('Masculin', 'Féminin') NOT NULL,
    nbrAnExp INT NOT NULL,
    codePin VARCHAR(255) NOT NULL,             -- code PIN à 4 chiffres, stocké haché
    code_corpsmetier VARCHAR(20) NOT NULL,
    FOREIGN KEY (code_corpsmetier) REFERENCES corps_metier(code)
        ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB;

-- ============================================================
-- 6. PROFIL (fusion Agent ANPS + Administrateur, distingués par "role")
-- ============================================================
CREATE TABLE profil (
    id_profil INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    prenom VARCHAR(100) NOT NULL,
    contact VARCHAR(20) NOT NULL,
    dateCreation DATE NOT NULL DEFAULT (CURRENT_DATE),
    sexe ENUM('Masculin', 'Féminin') NOT NULL,
    motdepasse VARCHAR(255) NOT NULL,          -- toujours stocké haché (password_hash)
    emailPro VARCHAR(150) NOT NULL UNIQUE,
    service VARCHAR(100) NOT NULL,
    dernierAcces DATETIME NULL,
    role ENUM('agent', 'admin') NOT NULL DEFAULT 'agent'
) ENGINE=InnoDB;

-- ============================================================
-- 7. ADRESSE_ATELIER (0..1 -- Artisan, "travailler à")
-- ============================================================
CREATE TABLE adresse_atelier (
    idAdresse INT AUTO_INCREMENT PRIMARY KEY,
    nom_commune VARCHAR(100) NOT NULL,
    nom_departement VARCHAR(100) NOT NULL,
    nom_arrondissement VARCHAR(100) NOT NULL,
    complement VARCHAR(255),
    id_arrondissement INT NOT NULL,
    id_artisan INT NOT NULL UNIQUE,
    FOREIGN KEY (id_arrondissement) REFERENCES arrondissement(id_arrondissement)
        ON DELETE RESTRICT ON UPDATE CASCADE,
    FOREIGN KEY (id_artisan) REFERENCES artisan(id_artisan)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB;

-- ============================================================
-- 8. RESIDENCE (1 -- 1 Artisan, "résider à")
-- ============================================================
CREATE TABLE residence (
    idAdresse INT AUTO_INCREMENT PRIMARY KEY,
    nom_commune VARCHAR(100) NOT NULL,
    nom_departement VARCHAR(100) NOT NULL,
    nom_arrondissement VARCHAR(100) NOT NULL,
    complement VARCHAR(255),
    id_arrondissement INT NOT NULL,
    id_artisan INT NOT NULL UNIQUE,
    FOREIGN KEY (id_arrondissement) REFERENCES arrondissement(id_arrondissement)
        ON DELETE RESTRICT ON UPDATE CASCADE,
    FOREIGN KEY (id_artisan) REFERENCES artisan(id_artisan)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB;

-- ============================================================
-- 9. CENTRE_FORMATION
-- ============================================================
CREATE TABLE centre_formation (
    idCentre INT AUTO_INCREMENT PRIMARY KEY,
    nomCentre VARCHAR(150) NOT NULL,
    contactCentre VARCHAR(20)
) ENGINE=InnoDB;

-- ============================================================
-- 10. ADRESSE_CENTRE (1 -- 1 CentreFormation, "situer à")
-- ============================================================
CREATE TABLE adresse_centre (
    idAdresse INT AUTO_INCREMENT PRIMARY KEY,
    nom_commune VARCHAR(100) NOT NULL,
    nom_departement VARCHAR(100) NOT NULL,
    nom_arrondissement VARCHAR(100) NOT NULL,
    complement VARCHAR(255),
    id_arrondissement INT NOT NULL,
    idCentre INT NOT NULL UNIQUE,
    FOREIGN KEY (id_arrondissement) REFERENCES arrondissement(id_arrondissement)
        ON DELETE RESTRICT ON UPDATE CASCADE,
    FOREIGN KEY (idCentre) REFERENCES centre_formation(idCentre)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB;

-- ============================================================
-- 11. QUESTION
-- ============================================================
CREATE TABLE question (
    idQuestion INT AUTO_INCREMENT PRIMARY KEY,
    enonce TEXT NOT NULL,
    typeQuestion ENUM('QCM_unique', 'QCM_multiple', 'VraiFaux') NOT NULL,
    code_corpsmetier VARCHAR(20) NOT NULL,
    FOREIGN KEY (code_corpsmetier) REFERENCES corps_metier(code)
        ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB;

-- ============================================================
-- 12. REPONSE (proposition liée à une question)
-- ============================================================
CREATE TABLE reponse (
    idReponse INT AUTO_INCREMENT PRIMARY KEY,
    libelleReponse VARCHAR(255) NOT NULL,
    estCorrecte BOOLEAN NOT NULL DEFAULT FALSE,
    idQuestion INT NOT NULL,
    FOREIGN KEY (idQuestion) REFERENCES question(idQuestion)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB;

-- ============================================================
-- 13. TEST (1 -- 1 Artisan, "passer")
-- ============================================================
CREATE TABLE test (
    idTest INT AUTO_INCREMENT PRIMARY KEY,
    date DATE NOT NULL DEFAULT (CURRENT_DATE),
    statutTest ENUM('en_cours', 'termine', 'expire') NOT NULL DEFAULT 'en_cours',
    score INT DEFAULT NULL,
    heureDebut TIME NOT NULL,
    heureFin TIME DEFAULT NULL,
    statutAffectation ENUM('valide', 'en_attente', 'rejete') DEFAULT NULL,
    id_artisan INT NOT NULL UNIQUE,
    FOREIGN KEY (id_artisan) REFERENCES artisan(id_artisan)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB;

-- ============================================================
-- 14. QUESTION_TEST (classe associative Test <-> Question)
-- ============================================================
CREATE TABLE question_test (
    idQuestiontest INT AUTO_INCREMENT PRIMARY KEY,
    ordre INT NOT NULL,
    reponseDonnee VARCHAR(255) DEFAULT NULL,
    estVerouillee BOOLEAN NOT NULL DEFAULT FALSE,
    estCorrecte BOOLEAN DEFAULT NULL,
    idTest INT NOT NULL,
    idQuestion INT NOT NULL,
    FOREIGN KEY (idTest) REFERENCES test(idTest)
        ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (idQuestion) REFERENCES question(idQuestion)
        ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB;

-- ============================================================
-- 15. AFFECTATION (1 -- 0..1 Test, "générer")
-- ============================================================
CREATE TABLE affectation (
    idAffect INT AUTO_INCREMENT PRIMARY KEY,
    distanceCalculee FLOAT NOT NULL,
    adresseReference VARCHAR(255),
    dateAffectation DATE NOT NULL DEFAULT (CURRENT_DATE),
    statutPlace ENUM('validee', 'liste_attente') NOT NULL,
    idTest INT NOT NULL UNIQUE,
    idCentre INT NOT NULL,
    FOREIGN KEY (idTest) REFERENCES test(idTest)
        ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (idCentre) REFERENCES centre_formation(idCentre)
        ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB;

-- ============================================================
-- 16. OBJECTIF_FORMATION (quota par métier et par session)
-- ============================================================
CREATE TABLE objectif_formation (
    idObjectif INT AUTO_INCREMENT PRIMARY KEY,
    nombrePlaces INT NOT NULL,
    periode VARCHAR(50) NOT NULL,
    code_corpsmetier VARCHAR(20) NOT NULL,
    FOREIGN KEY (code_corpsmetier) REFERENCES corps_metier(code)
        ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB;

-- ============================================================
-- 17. HISTORIQUE_SUPPRESSION (traçabilité des suppressions par un Profil admin)
-- ============================================================
CREATE TABLE historique_suppression (
    idHistorique INT AUTO_INCREMENT PRIMARY KEY,
    enonceSup TEXT NOT NULL,
    reponsesSup TEXT NOT NULL,                 -- JSON ou texte listant les réponses supprimées
    dateSuppression DATE NOT NULL DEFAULT (CURRENT_DATE),
    heureSuppression TIME NOT NULL,
    id_profil INT NOT NULL,
    FOREIGN KEY (id_profil) REFERENCES profil(id_profil)
        ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB;

-- ============================================================
-- INDEX SUPPLEMENTAIRES (performance des recherches fréquentes)
-- ============================================================
CREATE INDEX idx_artisan_metier ON artisan(code_corpsmetier);
CREATE INDEX idx_question_metier ON question(code_corpsmetier);
CREATE INDEX idx_test_statut ON test(statutTest);
CREATE INDEX idx_affectation_statut ON affectation(statutPlace);
CREATE INDEX idx_profil_role ON profil(role);