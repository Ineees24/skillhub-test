# SkillHub - Backend

Plateforme collaborative mettant en relation formateurs et apprenants.
Le backend permet de gerer l'authentification et les operations metier autour des formations.

## Technologies utilisees

- Laravel 12
- JWT Authentication (`tymon/jwt-auth`)
- MySQL
- PHPUnit pour les tests

## Prerequis

- PHP 8.2+
- Composer
- MySQL

## Installation et lancement

Depuis le dossier `skillhub-back` :

```bash
# 1) Installer les dependances
composer install

# 2) Copier l'environnement
cp .env.example .env

# 3) Generer la cle d'application
php artisan key:generate

# 4) Generer la cle JWT
php artisan jwt:secret

# 5) Configurer la base de donnees dans .env
# DB_CONNECTION=mysql
# DB_HOST=127.0.0.1
# DB_PORT=3306
# DB_DATABASE=skillhub
# DB_USERNAME=root
# DB_PASSWORD=

# 6) Lancer les migrations
php artisan migrate

# 7) Demarrer le serveur
php artisan serve
```

Le backend est accessible sur `http://127.0.0.1:8000`.

## Tests

```bash
php artisan test
```

Le fichier `.env.testing` utilise SQLite en memoire afin de ne pas impacter la base principale.

## Authentification

L'API utilise JWT (JSON Web Token).

1. Se connecter via `POST /api/login` pour obtenir un token.
2. Inclure le token dans chaque requete : `Authorization: Bearer <token>`.
3. Le token est stocke cote frontend et ajoute automatiquement aux requetes API.

## Comptes formateur (developpement)

| Email                  | Mot de passe | Role      |
| ---------------------- | ------------ | --------- |
| formateur@test.com     | 123456       | FORMATEUR |
| emma.laurent@gmail.com | 123456       | FORMATEUR |
