SkillHub 

SKILLHUB est une plateforme web collaborative qui met en relation des apprenants en reconversion professionnelle et des formateurs independants proposant des formations dans les domaines du numeriques.
SKILLHUB repose sur des ateliers courts sur toutes les competences utiles : développement, design, marketing, soft skills, etc.

Elle met en relation :

- des formateurs, qui créent et gèrent des formations ;
- des apprenants, qui suivent ces formations et visualisent leur progression.

 La solution est composee de : 
- skillhub-back (Laravel) : API metier (formations, ateliers, etc.).
- auth-service-spring (Spring Boot) : service d'authentification SSO.

Stack technique

- Back-end  : Laravel (API REST + JWT)
 auth-service-spring : (Spring Boot)
- Base de données : MySQL (données principales) , MongoDB (logs et historisation)
- DevOps : Docker , Docker Compose , CI/CD (GitHub Actions ou GitLab CI)


Le système d’authentification de SKILLHUB repose sur un modèle de type SSO géré par un microservice Spring Boot. 

Lorsqu’un utilisateur tente de se connecter ou de s’inscrire, la requête est d’abord envoyée au back-end Laravel. Celui-ci ne traite pas directement l’authentification, mais agit comme un intermédiaire en relayant la demande vers le service Spring Boot à travers un client HTTP spécifique.

Le service Spring Boot expose plusieurs endpoints dédiés à l’authentification, comme la création de compte, la connexion, la récupération des informations utilisateur. C’est lui qui valide les identifiants et qui décide si l’accès doit être accordé.

Une fois l’utilisateur authentifié, le service Spring Boot génère un token d’accès. Cela signifie qu’il ne contient aucune information exploitable côté client. Toutes les données liées à la session restent stockées côté serveur, dans le service d’authentification.

Après réception du token, le front-end le stocke et l’envoie dans chaque requête suivante via l’en-tête Authorization sous la forme 'Bearer <token>'. À chaque appel protégé, le back-end Laravel intercepte la requête grâce à un middleware dédié. Ce middleware contacte alors le service Spring Boot pour vérifier la validité du token.

Si le token est valide, la requête est autorisée et Laravel exécute la logique métier correspondante. Dans le cas contraire, l’accès est refusé. 


Limite d'inscriptions simultanées  Règle métier

Désormais, un apprenant ne peut pas s'inscrire à plus de 5 formations dont le statut est "en-cours" en même temps. Si cette limite est atteinte, l'API retourne une réponse HTTP 400 avec un message explicite invitant l'apprenant à terminer ou se désinscrire d'une formation avant d'en rejoindre une nouvelle. 


Prérequis

Avant de lancer le projet, il faut s'assurer d'avoir installé :

- Docker & Docker Compose
- Git
- PHP 8.2+ & Composer 

Lancer le projet

 -> Cloner le dépôt

   bash
   git clone 
   cd skillhub

 -> Configurer les variables d'environnement

bash
cp .env.example .env


 Lancer la stack complète avec Docker :

bash
docker compose up --build


L'application sera accessible aux adresses suivantes :

Front-end : http://localhost:3000 
API back-end : http://localhost:8000 
Auth Spring Boot : http://localhost:8081
 

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



SKILLHUB utilise Docker et Docker Compose pour conteneuriser chaque service (front-end, back-end et service d’authentification) et garantir un environnement de développement et de test identique pour tous. Docker Compose permet de lancer et orchestrer facilement l’ensemble de l’application avec une seule commande.

GitHub Actions déclenche automatiquement un pipeline à chaque push ou Pull Request. Ce pipeline installe les dépendances, exécute les tests et lance l’analyse du code.

SonarCloud est utilisé pour analyser la qualité et la sécurité du code. Il fournit des métriques comme les bugs, la duplication et la couverture de tests, et applique un quality gate pour s’assurer qu’un niveau minimal de qualité est respecté avant validation.


 Variables d'environnement

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
APP_MASTER_KEY
DB_URL
SSO_TIMESTAMP_TOLERANCE
SSO_NONCE_TTL
SSO_TOKEN_TTL



