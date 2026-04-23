SkillHub 

SKILLHUB est une plateforme web collaborative qui met en relation des apprenants en reconversion professionnelle et des formateurs independants proposant des formations dans les domaines du numeriques.
SKILLHUB repose sur des ateliers courts sur toutes les competences utiles : développement, design, marketing, soft skills, etc.

Elle met en relation :

- des formateurs, qui créent et gèrent des formations ;
- des apprenants, qui suivent ces formations et visualisent leur progression.
 

Projet réalisé en groupe de 3 :

Cloud Architect
DevOps Engineer
Tech Lead

1. Stack technique

- Front-end : React.js , React Router 
- Back-end  : Laravel (API REST + JWT)
- Base de données : MySQL (données principales) , MongoDB (logs et historisation)
- DevOps : Docker , Docker Compose , CI/CD (GitHub Actions ou GitLab CI)

2. Prérequis

Avant de lancer le projet, il faut s'assurer d'avoir installé :

- Docker & Docker Compose
- Git
- PHP 8.2+ & Composer 

Lancer le projet

 -> Cloner le dépôt

   bash
   git clone https://github.com/Ineees24/skillhub.git
   cd skillhub

 -> Configurer les variables d'environnement

bash
cp .env.example .env


3. Lancer la stack complète avec Docker :

bash
docker compose up --build


L'application sera accessible aux adresses suivantes :

Front-end : http://localhost:3000 
API back-end : http://localhost:8000 
 

4. Arrêter la stack

bash
docker compose down

5. Commande de  tests

Tests back-end (Laravel)

bash
php artisan test


Ou dans le conteneur Docker :

bash
docker compose exec api php artisan test


6. Structure du dépôt


skillhub/
├── frontend/ 
│   ├── Dockerfile
│   ├── src/
│   └── ...
├── backend/                
│   ├── Dockerfile
│   ├── app/
│   ├── routes/
│   └── ...
├── .github/
│   └── workflows/
│       └── ci.yml           Pipeline CI/CD GitHub Actions
├── docker-compose.yml       Orchestration des services
├── .env.example             Variables d'environnement requises
├── .gitignore
├── .dockerignore
├── CONTRIBUTING.md          Guide de contribution
└── README.md

7. Variables d'environnement

Toutes les variables requises sont documentées dans le fichier .env.example.

Les variables principales sont :

DB_CONNECTION 
DB_HOST 
DB_PORT 
DB_DATABASE
DB_USERNAME
DB_PASSWORD
MONGO_URI
JWT_SECRET 
APP_ENV

8. Stratégie de branches

Le projet suit une organisation Git stricte :

- main :  Production — code stable uniquement. 
- dev : Intégration — accumule les fonctionnalités validées via Pull Requests. 
- feature/nom-feature : développement de fonctionnalités
- hotfix/nom-fix : corrections urgentes

Voir CONTRIBUTING.md pour les règles complètes.



