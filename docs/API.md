# Documentation API — CLB API

API de consultation en **lecture seule** de la base de données de l'application École La Bonté (`Ecole_La_Bonte_4`).  
Toutes les réponses sont au format **JSON**.

---

## Sommaire

- [Authentification](#authentification)
- [Authentification parent (téléphone + PIN)](#authentification-parent-téléphone--pin)
- [Conventions](#conventions)
- [Codes de réponse](#codes-de-réponse)
- [Abonnements](#abonnements)
- [Absences agents](#absences-agents)
- [Absences élèves](#absences-élèves)
- [Élèves](#élèves)
- [Paiements](#paiements)
- [Tarifications](#tarifications)

---

## Authentification

Les endpoints `/api/*` (sauf login et changement de PIN) exigent une authentification via l'un des deux modes ci-dessous.

### 1. Token de session parent (après login téléphone + PIN)

Utilisez le `token` retourné par `POST /api/auth/login` :

| En-tête | Exemple |
|---------|---------|
| `Authorization` | `Authorization: Bearer {token}` |
| `X-API-KEY` | `X-API-KEY: {token}` |

### 2. Token API technique (intégrations / admin)

| En-tête | Exemple |
|---------|---------|
| `X-API-KEY` | `X-API-KEY: votre-token` |
| `Authorization` | `Authorization: Bearer votre-token` |

Configuré via `API_TOKEN` dans `.env.local`.

**Réponse en cas d'échec (401) :**

```json
{
  "error": "Token d'authentification manquant."
}
```

```json
{
  "error": "Token d'authentification invalide."
}
```

---

## Authentification parent (téléphone + PIN)

Les parents s'authentifient avec le **numéro de téléphone du tuteur** (`parent_eleve.numero_telephone_tuteur`) et un **PIN** (4 à 6 chiffres).

- Si le parent n'a jamais changé son PIN, le **PIN par défaut** (`DEFAULT_PIN` dans `.env`) est accepté.
- Le numéro doit correspondre à un **tuteur actif** existant dans `parent_eleve.numero_telephone_tuteur` (comparaison sur les chiffres uniquement).
- Les PIN et sessions sont stockés dans la **base MySQL** (`parent_pin`, `parent_session`), avec clé étrangère vers `parent_eleve.id`.

| Variable | Description | Défaut |
|----------|-------------|--------|
| `DEFAULT_PIN` | PIN initial pour les parents sans PIN personnalisé | `1234` |
| `API_SESSION_TTL` | Durée de validité du token de session (secondes) | `86400` (24 h) |

### Connexion

```
POST /api/auth/login
```

**Corps JSON :**

```json
{
  "telephone": "+243997031460",
  "pin": "1234"
}
```

**Réponse (200) :**

```json
{
  "token": "a1b2c3...",
  "expires_at": "2026-06-19T16:00:00+00:00",
  "parent": {
    "id": 1007,
    "nomTuteur": "ELIE KAKUDJI",
    "numeroTelephoneTuteur": "+243997031460"
  }
}
```

**Exemple :**

```bash
curl -X POST http://localhost:8000/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"telephone":"+243997031460","pin":"1234"}'
```

Utilisez ensuite le `token` pour les autres endpoints :

```bash
curl -H "Authorization: Bearer a1b2c3..." http://localhost:8000/api/eleves/by-parent/1007
```

### Changer le PIN

```
POST /api/auth/change-pin
```

**Corps JSON :**

```json
{
  "telephone": "+243997031460",
  "current_pin": "1234",
  "new_pin": "5678"
}
```

| Champ | Description |
|-------|-------------|
| `telephone` | Numéro du tuteur |
| `current_pin` | PIN actuel (ou PIN par défaut si jamais modifié) |
| `new_pin` | Nouveau PIN (4 à 6 chiffres) |

**Réponse (200) :**

```json
{
  "message": "PIN modifié avec succès."
}
```

**Exemple :**

```bash
curl -X POST http://localhost:8000/api/auth/change-pin \
  -H "Content-Type: application/json" \
  -d '{"telephone":"+243997031460","current_pin":"1234","new_pin":"5678"}'
```

---


## Conventions

| Élément | Valeur |
|---------|--------|
| URL de base (dev) | `http://localhost:8000` |
| Préfixe API | `/api` |
| Méthodes HTTP | `GET` (consultation) et `POST` (auth) |
| Encodage | UTF-8 |
| Dates | ISO 8601 (`2025-07-09T13:08:34+00:00`) |

### Lancer le serveur local

```bash
php -S localhost:8000 -t public
```

### Exemple de requête

```bash
curl -H "X-API-KEY: votre-token" http://localhost:8000/api/eleves/1868
```

---

## Codes de réponse

| Code | Signification |
|------|---------------|
| `200` | Succès |
| `400` | Paramètre manquant ou invalide |
| `401` | Token API manquant ou invalide |
| `404` | Ressource introuvable |

---

## Abonnements

### Liste des abonnements

```
GET /api/abonnements
```

| Paramètre | Type | Requis | Description |
|-----------|------|--------|-------------|
| `actif` | bool | Non | Si `1` ou `true`, retourne uniquement les abonnements dont la date du jour est entre `datedebut` et `datefin` |

**Exemple :**

```bash
curl -H "X-API-KEY: votre-token" "http://localhost:8000/api/abonnements?actif=1"
```

**Réponse (extrait) :**

```json
[
  {
    "id": 1,
    "cle": "cle-abonnement",
    "datedebut": "2025-01-01T00:00:00+00:00",
    "datefin": "2025-12-31T23:59:59+00:00"
  }
]
```

---

### Détail d'un abonnement

```
GET /api/abonnements/{id}
```

| Paramètre | Type | Description |
|-----------|------|-------------|
| `id` | int | Identifiant de l'abonnement |

---

## Absences agents

Absences du personnel (table `absences`), liées à une affectation agent et un type d'absence.

### Liste des absences agents

```
GET /api/absences
```

| Paramètre | Type | Requis | Description |
|-----------|------|--------|-------------|
| `agentaffectation_id` | int | Non | Filtre par identifiant d'affectation agent |

---

### Détail d'une absence agent

```
GET /api/absences/{id}
```

| Paramètre | Type | Description |
|-----------|------|-------------|
| `id` | int | Identifiant de l'absence |

**Réponse (extrait) :**

```json
{
  "id": 1,
  "agentaffectationId": 12,
  "typeAbsence": {
    "id": 1,
    "libelle": "Maladie"
  },
  "observation": "..."
}
```

---

## Absences élèves

Présences / absences des élèves (table `absence_eleve`).  
Le champ `eleve` contient le **nom** de l'élève (texte), pas son identifiant.

### Liste des absences élèves

```
GET /api/absences-eleves
```

| Paramètre | Type | Requis | Description |
|-----------|------|--------|-------------|
| `eleve` | string | Non | Recherche partielle sur le nom de l'élève |
| `classe` | string | Non | Recherche partielle sur la classe |
| `section` | string | Non | Recherche partielle sur la section |
| `anneescolaire` | string | Non | Année scolaire exacte (ex. `2025/2026`) |
| `statut` | string | Non | Statut exact |
| `date_debut` | date | Non | Date minimale d'absence (ex. `2025-09-01`) |
| `date_fin` | date | Non | Date maximale d'absence |

**Exemple :**

```bash
curl -H "X-API-KEY: votre-token" \
  "http://localhost:8000/api/absences-eleves?anneescolaire=2025/2026&classe=6e"
```

**Réponse (extrait) :**

```json
[
  {
    "id": 1,
    "section": "Primaire",
    "classe": "6e A",
    "eleve": "GIFT BONDO KAKUDJI",
    "statut": "Absent",
    "dateAbsence": "2025-10-15T08:00:00+00:00",
    "anneescolaire": "2025/2026"
  }
]
```

---

## Élèves

### Recherche par téléphone du tuteur

```
GET /api/eleves?numero_telephone_tuteur={telephone}
```

| Paramètre | Type | Requis | Description |
|-----------|------|--------|-------------|
| `numero_telephone_tuteur` | string | **Oui** | Numéro du tuteur (`parent_eleve.numero_telephone_tuteur`). Les caractères non numériques sont ignorés lors de la comparaison. |

**Exemple :**

```bash
curl -H "X-API-KEY: votre-token" \
  "http://localhost:8000/api/eleves?numero_telephone_tuteur=%2B243997031460"
```

---

### Élèves par parent

```
GET /api/eleves/by-parent/{parentId}
```

| Paramètre | Type | Description |
|-----------|------|-------------|
| `parentId` | int | Identifiant du parent (`parent_eleve.id`) |

Retourne les élèves actifs (`supp = 0`) rattachés à ce parent.

---

### Absences d'un élève

```
GET /api/eleves/{eleveId}/absences
```

| Paramètre | Type | Description |
|-----------|------|-------------|
| `eleveId` | int | Identifiant de l'élève |

Recherche dans `absence_eleve` en croisant l'ID élève avec plusieurs formats de nom possibles (prénom, nom, post-nom, matricule).

---

### Détail d'un élève

```
GET /api/eleves/{id}
```

| Paramètre | Type | Description |
|-----------|------|-------------|
| `id` | int | Identifiant de l'élève |

**Réponse (extrait) :**

```json
{
  "id": 1868,
  "parent": {
    "id": 1007,
    "nomTuteur": "ELIE KAKUDJI",
    "numeroTelephoneTuteur": "+243997031460"
  },
  "classeId": 42,
  "nom": "BONDO",
  "postNom": "KAKUDJI",
  "prenom": "GIFT",
  "sexe": "Masculin",
  "matricule": "202512496NU182008",
  "dateNaissance": "2008-03-27",
  "supp": false,
  "familleId": null
}
```

---

## Paiements

### Liste des paiements (filtres combinables)

```
GET /api/paiements
```

| Paramètre | Type | Requis | Description |
|-----------|------|--------|-------------|
| `eleve_id` | int | Non | Filtre par élève |
| `tarification_id` | int | Non | Filtre par tarification |
| `mois` | string | Non | Filtre par mois exact (ex. `July`, `pas de mois`) |

**Exemple :**

```bash
curl -H "X-API-KEY: votre-token" \
  "http://localhost:8000/api/paiements?eleve_id=1868&mois=July"
```

---

### Paiements d'un élève pour une année scolaire

```
GET /api/paiements/eleve/{eleveId}/annee-scolaire/{anneescolaireId}
```

| Paramètre | Type | Description |
|-----------|------|-------------|
| `eleveId` | int | Identifiant de l'élève |
| `anneescolaireId` | int | Identifiant de l'année scolaire (`annee_scolaire.id`) |

Filtre sur le champ `paiement.anneescolaire_id`.

**Exemple :**

```bash
curl -H "X-API-KEY: votre-token" \
  http://localhost:8000/api/paiements/eleve/1868/annee-scolaire/4
```

---

### Paiements d'un élève pour l'année scolaire en cours

```
GET /api/paiements/eleve/{eleveId}/annee-scolaire-courante
```

| Paramètre | Type | Description |
|-----------|------|-------------|
| `eleveId` | int | Identifiant de l'élève |

L'année en cours est déterminée par le statut `En Cours` dans `annee_scolaire`, ou à défaut par les dates `date_debut` / `date_fin` englobant la date du jour.

**Réponse :**

```json
{
  "annee_scolaire": {
    "id": 4,
    "status": "En Cours",
    "anneeScolaire": "2025/2026",
    "dateDebut": "2025-09-01",
    "dateFin": "2026-07-02"
  },
  "paiements": [ ... ]
}
```

---

### Paiements d'un élève par mois

```
GET /api/paiements/eleve/{eleveId}/mois/{mois}
```

| Paramètre | Type | Description |
|-----------|------|-------------|
| `eleveId` | int | Identifiant de l'élève |
| `mois` | string | Valeur exacte du champ `paiement.mois` |

**Exemples :**

```bash
curl -H "X-API-KEY: votre-token" \
  http://localhost:8000/api/paiements/eleve/1868/mois/July

curl -H "X-API-KEY: votre-token" \
  "http://localhost:8000/api/paiements/eleve/1868/mois/pas%20de%20mois"
```

---

### Détail d'un paiement

```
GET /api/paiements/{id}
```

| Paramètre | Type | Description |
|-----------|------|-------------|
| `id` | int | Identifiant du paiement |

**Réponse (extrait) :**

```json
{
  "id": 16570,
  "tarification": {
    "id": 5,
    "nom": "Inscription",
    "devise": "USD"
  },
  "eleve": { "id": 1868, "nom": "BONDO", "prenom": "GIFT" },
  "detail": null,
  "montantUsd": "100.00",
  "montantFc": "0.00",
  "montant": null,
  "datepaiement": "2025-07-09T13:09:36+00:00",
  "numRecu": "1868266095972025",
  "mois": "July",
  "anneescolaireId": 4,
  "typePaiement": null
}
```

---

## Tarifications

### Liste des tarifications

```
GET /api/tarifications
```

| Paramètre | Type | Requis | Description |
|-----------|------|--------|-------------|
| `nom` | string | Non | Recherche partielle sur le nom (ex. `Inscription`) |

---

### Détail d'une tarification

```
GET /api/tarifications/{id}
```

| Paramètre | Type | Description |
|-----------|------|-------------|
| `id` | int | Identifiant de la tarification |

**Réponse (extrait) :**

```json
{
  "id": 5,
  "nom": "Inscription",
  "section": ["Tous"],
  "isPaiementDirect": true,
  "devise": "USD"
}
```

---

## Récapitulatif des endpoints

| Méthode | Endpoint |
|---------|----------|
| `POST` | `/api/auth/login` |
| `POST` | `/api/auth/change-pin` |
| `GET` | `/api/abonnements` |
| `GET` | `/api/abonnements/{id}` |
| `GET` | `/api/absences` |
| `GET` | `/api/absences/{id}` |
| `GET` | `/api/absences-eleves` |
| `GET` | `/api/eleves?numero_telephone_tuteur=` |
| `GET` | `/api/eleves/by-parent/{parentId}` |
| `GET` | `/api/eleves/{eleveId}/absences` |
| `GET` | `/api/eleves/{id}` |
| `GET` | `/api/paiements` |
| `GET` | `/api/paiements/eleve/{eleveId}/annee-scolaire/{anneescolaireId}` |
| `GET` | `/api/paiements/eleve/{eleveId}/annee-scolaire-courante` |
| `GET` | `/api/paiements/eleve/{eleveId}/mois/{mois}` |
| `GET` | `/api/paiements/{id}` |
| `GET` | `/api/tarifications` |
| `GET` | `/api/tarifications/{id}` |

---

## Configuration

| Variable | Description |
|----------|-------------|
| `DATABASE_URL` | Connexion MySQL vers la base externe `Ecole_La_Bonte_4` |
| `API_TOKEN` | Token technique pour intégrations (alternative au login parent) |
| `DEFAULT_PIN` | PIN par défaut des parents (avant première modification) |
| `API_SESSION_TTL` | Durée du token de session parent (secondes) |

Les tables `parent_pin` et `parent_session` sont créées dans la même base MySQL (`Ecole_La_Bonte_4`) :

```bash
php bin/console doctrine:migrations:migrate
```

### Collection Postman

Importer dans Postman :

- `postman/CLB-API.postman_collection.json` — tous les endpoints
- `postman/CLB-API.local.postman_environment.json` — variables locales (optionnel)

1. Définir `apiToken` ou exécuter **Auth > Login** (le token de session est enregistré automatiquement).
2. Lancer les requêtes protégées.

Exemple `.env.local` :

```env
DATABASE_URL="mysql://user:password@host:3306/Ecole_La_Bonte_4?serverVersion=8.0.32&charset=utf8mb4"
API_TOKEN="votre-token-secret"
DEFAULT_PIN="1234"
API_SESSION_TTL=86400
```
