# SymReact

Application de gestion de **clients** et de **factures** : API REST **Symfony 7 / API Platform 4**
authentifiée par **JWT**, et front **React 18** (single page, servi par Webpack Encore).

Chaque utilisateur ne voit que ses propres clients et factures ; un rôle `ROLE_ADMIN` voit tout.

## 🔑 Compte de démonstration

Un compte de démo (administrateur) est créé par les fixtures :

| Email                  | Mot de passe |
| ---------------------- | ------------ |
| `demo@symreact.local`  | `password`   |

> Tous les comptes générés par les fixtures utilisent le mot de passe `password`.

L'identifiant de démo est aussi rappelé sur la page d'accueil et la page de connexion de l'application.

## 🆙 Modernisation (montée de version)

Ce projet a été repris d'une version datant de **2019** et entièrement modernisé.

| Composant        | Avant (2019)        | Après               |
| ---------------- | ------------------- | ------------------- |
| PHP              | 7.1                 | **8.4**             |
| Symfony          | 4.2                 | **7.4 (LTS)**       |
| API Platform     | 1.2                 | **4.x**             |
| Doctrine ORM     | 2.x                 | **3.x**             |
| Lexik JWT        | 2.6                 | **3.x**             |
| React            | 16                  | **18**              |
| react-router-dom | 5                   | **6**               |
| axios            | 0.18                | **1.x**             |
| Webpack Encore   | 0.27                | **5.x**             |

Principaux changements appliqués :

- **Annotations → attributs PHP 8** sur toutes les entités, contrôleurs et services
  (Doctrine ORM, Validator, Serializer, API Platform, Routing).
- **API Platform 1 → 4** : opérations `operations: [new Get(), new GetCollection(), ...]`,
  sous-ressource `/api/customers/{customerId}/invoices` réécrite avec `uriTemplate` + `Link`,
  opération custom `POST /api/invoices/{id}/increment`, suppression de `disable_type_enforcement`
  (propriétés désormais typées) et du normalizer `PatchedDateTimeNormalizer` devenu inutile.
  Les listeners Symfony historiques (`KernelEvents::VIEW`) sont réactivés via
  `api_platform.use_symfony_listeners: true` pour conserver les subscribers métier.
- **Sécurité Symfony 7** : `encoders` → `password_hashers`, suppression d'`anonymous`,
  authenticator `jwt: ~` (Lexik 3), `IS_AUTHENTICATED_ANONYMOUSLY` → `PUBLIC_ACCESS`,
  `User` implémente `PasswordAuthenticatedUserInterface` (`getUserIdentifier`).
- **Swiftmailer / sensio-framework-extra / web-server-bundle** supprimés (dépréciés).
- **Front** : `ReactDOM.render` → `createRoot`, migration react-router v5 → v6
  (`Switch`→`Routes`, `Redirect`→`Navigate`, `history`/`match` → hooks `useNavigate`/`useParams`),
  `jwt-decode` v4 (import nommé), lecture des collections API Platform via `member`
  (nouveau format JSON-LD) avec repli sur `hydra:member`.

## 🧰 Stack

- **Backend** : PHP 8.4, Symfony 7.4, API Platform 4, Doctrine ORM 3, Lexik JWT 3, MySQL 8.
- **Frontend** : React 18, react-router 6, axios, Webpack Encore 5.

## 🚀 Lancer le projet en local

Prérequis : PHP 8.4 (ext `pdo_mysql`, `intl`), Composer 2, Node 22, MySQL 8.

```bash
# 1. Dépendances
composer install
npm install

# 2. Base de données — configurer DATABASE_URL dans .env.local, par ex. :
#    DATABASE_URL="mysql://root:root@127.0.0.1:3306/symreact?serverVersion=8.0&charset=utf8mb4"
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate -n

# 3. Clés JWT
php bin/console lexik:jwt:generate-keypair

# 4. Données de démonstration (clients, factures, comptes)
php bin/console doctrine:fixtures:load -n

# 5. Assets front
npm run build        # ou `npm run watch` en développement

# 6. Serveur
symfony serve        # ou : php -S 127.0.0.1:8000 -t public public/index.php
```

L'application est alors disponible sur http://localhost:8000 et la documentation
de l'API sur http://localhost:8000/api/docs.

## 🐳 Déploiement (Docker)

Voir le `Dockerfile` (Apache + mod_php, multi-stage) et `compose.yaml` (app + MySQL) à la racine.
Le conteneur web n'est exposé que sur `127.0.0.1:8082` : le reverse proxy de l'hôte route
`symreact.lzerri-project.fr` (HTTPS Let's Encrypt) vers ce port, sans toucher aux autres projets.

```bash
# 1. Configurer les secrets de production
cp .env.prod.dist .env.prod && nano .env.prod   # APP_SECRET, mots de passe DB, JWT_PASSPHRASE…

# 2. Construire et démarrer (--env-file pour que MySQL et l'app partagent les mêmes identifiants)
docker compose --env-file .env.prod up -d --build
```

Au premier démarrage, l'entrypoint attend la base, génère les clés JWT (volume persistant),
applique les migrations et crée le compte de démo (`app:seed-demo`, idempotent).
Les fixtures Faker (`doctrine:fixtures:load`) restent réservées au développement.

## 📚 Endpoints principaux

| Méthode | URL                                   | Description                          |
| ------- | ------------------------------------- | ------------------------------------ |
| POST    | `/api/login_check`                    | Authentification, renvoie un JWT     |
| POST    | `/api/users`                          | Inscription                          |
| GET     | `/api/customers`                      | Liste des clients de l'utilisateur   |
| GET     | `/api/customers/{id}/invoices`        | Factures d'un client (sous-ressource)|
| POST    | `/api/invoices`                       | Création d'une facture (chrono auto) |
| POST    | `/api/invoices/{id}/increment`        | Incrémente le chrono d'une facture   |
