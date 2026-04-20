# Fridge — Application de gestion de repas

**Fridge** est une application web de planification de repas et de gestion d'inventaire alimentaire pour les ménages francophones.

**Démo** : [fridge.etienne.ferry.servd16161.odns.fr](http://fridge.etienne.ferry.servd16161.odns.fr/)

---

## Fonctionnalités

- **Recettes** — Parcourir, créer et gérer des recettes avec étapes, ingrédients, restrictions alimentaires (régimes) et photos
- **Planning hebdomadaire** — Planifier ses repas sur 7 jours × 4 créneaux (petit-déjeuner, déjeuner, dîner, dessert)
- **Liste de courses** — Générer automatiquement une liste de courses à partir du planning
- **Stock personnel** — Suivre son inventaire d'ingrédients avec dates de péremption et alertes de stock bas
- **Favoris & Likes** — Sauvegarder et noter ses recettes préférées
- **Foyer** — Gestion de l'espace partagé entre membres d'un foyer *(en développement)*
- **Modération** — Tableau de bord pour la validation des recettes soumises par les utilisateurs

---

## Stack technique

| Couche | Technologie |
|--------|------------|
| Backend | Symfony 8.0 / PHP 8.4 |
| Base de données | PostgreSQL 16 + Doctrine ORM 3.6 |
| Frontend | Twig, Stimulus.js 3, UX Turbo |
| Assets | Asset Mapper + ImportMap |
| Mail | Symfony Mailer + Mailtrap |
| Auth | Symfony Security (voters, email verification, reset password) |
| Tests | PHPUnit 13 + Zenstruck Foundry |

---

## Installation

### Prérequis

- PHP >= 8.4
- Composer
- Docker (pour PostgreSQL) ou une instance PostgreSQL locale
- Symfony CLI (recommandé)

### Démarrage

```bash
# Cloner le projet
git clone <repo-url>
cd Fridge_V.0.1_WebApp

# Installer les dépendances PHP
composer install

# Démarrer la base de données (Docker)
docker compose up -d

# Copier et configurer les variables d'environnement
cp .env .env.local
# Modifier DATABASE_URL et MAILER_DSN dans .env.local

# Créer la base de données et appliquer les migrations
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate

# Charger les fixtures (données de test)
php bin/console doctrine:fixtures:load

# Démarrer le serveur de développement
symfony serve
```

L'application est accessible sur [http://localhost:8000](http://localhost:8000).

---

## Structure du projet

```
src/
├── Controller/       # 18 contrôleurs (recettes, planning, stock, auth...)
├── Entity/           # 16 entités Doctrine
├── Form/             # Formulaires Symfony
├── Repository/       # Requêtes personnalisées
├── Security/         # Authentificateur, voters, vérificateur d'email
├── Service/          # ListeCourseService (génération de listes)
└── DataFixtures/     # Fixtures de test (Foundry)

templates/            # Templates Twig
assets/
├── controllers/      # Contrôleurs Stimulus.js
├── styles/           # CSS
└── *.js              # Scripts (planning, étapes de recette...)
```

---

## Rôles utilisateur

| Rôle | Accès |
|------|-------|
| `ROLE_USER` | Recettes, planning, stock, favoris |
| `ROLE_MODERATOR` | Dashboard de modération des recettes |
| `ROLE_ADMIN` | Accès complet |

---

## Tests

```bash
php bin/phpunit
```
