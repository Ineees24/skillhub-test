# Skillhub Auth Service (Spring Boot)

Microservice d'authentification forte inspire du TP4:
- chiffrement des mots de passe avec `APP_MASTER_KEY` (AES-GCM),
- login HMAC + nonce + timestamp,
- token opaque serveur (pas JWT),
- introspection pour les autres services.

## Endpoints

- `POST /api/auth/register`
- `POST /api/auth/login`
- `POST /api/auth/introspect`
- `GET /api/auth/me`
- `POST /api/auth/logout`

## Login SSO

Le client envoie:
- `email`
- `nonce`
- `timestamp` (epoch seconds)
- `hmac`

Calcul:
`message = email + ":" + nonce + ":" + timestamp`
`hmac = HMAC_SHA256(password_plain, message)`

## Variables d'environnement

- `APP_MASTER_KEY` (obligatoire)
- `DB_URL`
- `DB_USER`
- `DB_PASSWORD`
- `SSO_TIMESTAMP_TOLERANCE` (default 60)
- `SSO_NONCE_TTL` (default 120)
- `SSO_TOKEN_TTL` (default 900)
