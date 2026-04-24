# SkillHub - Guide architecture microservices et authentification SSO

SkillHub est une plateforme web collaborative qui met en relation des apprenants en reconversion et des formateurs independants.

Ce document complete `README.md` et decrit la version actuelle basee sur une architecture microservices, avec un service d'authentification dedie.

## 1) Vue d'ensemble de l'architecture

La solution est composee de trois services principaux :

- `skillhub-front` (React) : interface utilisateur.
- `skillhub-back` (Laravel) : API metier (formations, ateliers, etc.).
- `auth-service-spring` (Spring Boot) : service d'authentification SSO.

### Flux general

1. Le front envoie les demandes d'authentification a l'API Laravel.
2. Laravel relaie l'authentification vers le microservice Spring Boot.
3. Le microservice valide les preuves d'authentification forte et retourne un token.
4. Le front envoie ce token dans `Authorization: Bearer <token>` sur les appels suivants.
5. Laravel verifie la validite du token via le microservice d'authentification.

Cette separation permet de decoupler la logique metier de la logique de securite.

## 2) Systeme d'authentification SSO

## Integration du service Spring Boot

- Le service Spring expose des endpoints d'authentification (`register`, `login`, `me`, `logout`, introspection).
- Laravel consomme ces endpoints via un client HTTP dedie (`AuthServiceClient`).
- Un middleware Laravel (`spring.auth`) protege les routes API et valide le token via le service Spring.

## JWT ou token opaque

Dans cette implementation, le token retenu est un **token opaque** (et non un JWT).

Pourquoi token opaque ici :

- pas de donnees sensibles embarquees dans le token cote client ;
- revocation centralisee plus simple (etat gere cote serveur d'auth) ;
- controle total du cycle de vie des sessions par le microservice SSO ;
- aligne avec une architecture ou l'autorite d'auth est unique et centralisee.

Un JWT reste possible, mais le token opaque est plus adapte quand on veut garder une maitrise serveur forte de la session.

## 3) Installation et lancement

## Prerequis

- Docker
- Docker Compose
- Git

## Cloner le projet

```bash
git clone https://github.com/Ineees24/skillhub.git
cd skillhub
```

## Lancer la stack complete

```bash
docker compose up -d --build
```

Services accessibles en local (selon `docker-compose.yml`) :

- Frontend : `http://localhost:5173`
- Backend Laravel : `http://localhost:8000`
- Auth Spring Boot : `http://localhost:8081`

## Arreter la stack

```bash
docker compose down
```

## 4) Outils et chaine DevOps

## Docker / Docker Compose

- conteneurisation de chaque service ;
- environnement reproductible pour developpement et tests ;
- orchestration front + back + auth-service.

## GitHub Actions

- execution automatique du pipeline CI a chaque push / PR ;
- installation des dependances front et back ;
- lancement des tests et generation des rapports de couverture ;
- execution de l'analyse Sonar.

## SonarCloud

- analyse qualite et securite du code ;
- affichage des metriques (issues, duplications, coverage) ;
- quality gate pour verifier le niveau minimal avant validation.

## 5) Notes importantes

- Ce fichier est complementaire a `README.md` (il ne le remplace pas).
- Pour un fonctionnement auth correct, le service `auth-service-spring` doit etre demarre en meme temps que le backend.
- Le front doit transmettre le token dans chaque requete protegee (`Bearer`).

