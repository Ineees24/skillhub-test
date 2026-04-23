
CONTRIBUTING.md — SkillHub 

1. Membres du groupe et rôles

Chaque membre de l’équipe possède un rôle principal :

- Cloud Architect : Izad 

Audit cloud
Architecture technique
Plan budgétaire

- DevOps Engineer : Moustansir

Docker (Dockerfile, docker-compose)
Pipeline CI/CD
Déploiement

- Tech Lead : Ines

Gestion Git
Code review
Coordination globale
Documentation (README, CONTRIBUTING)


2. Stratégie de branches

Le projet suit une organisation Git stricte :

- main :  Production — code stable uniquement. 
- dev : Intégration — accumule les fonctionnalités validées via Pull Requests. 
- feature/nom-feature : développement de fonctionnalités
- hotfix/nom-fix : corrections urgentes

Règles

- Tout développement se fait sur une branche feature/ créée à partir de dev
- Jamais de commit direct sur main ou dev
- Une branche = une fonctionnalité ou une tâche
- Une fois la tâche terminée, ouvrir une Pull Request vers dev

3. Convention de commits (Conventional Commits)

Tous les messages de commit doivent respecter le format suivant :


feat : Nouvelle fonctionnalité 
fix : Correction de bug 
docker : Ajout ou modification des fichiers de conteneurisation 
ci : Modification du pipeline CI/CD 
docs : Documentation 
chore : Maintenance, configuration 
test : Ajout ou modification de tests 

Règles

- Description courte, claire et au présent
- Pas de majuscule au début de la description
- Pas de point à la fin

4. Procédure de Pull Request

- Créer une Pull Request

->Créer une branche : 

  bash
   git checkout -b feature/ma-feature
   
->Développer la fonctionnalité

->Commit avec message clair

->Push :

   bash
   git push origin feature/ma-feature
   
->Créer une Pull Request vers dev

Une Pull Request doit contenir :

Description claire
Objectif de la modification
Tests effectués

->Assigner au moins un reviewer parmi les membres du groupe
->Soumettre la PR


Règles de review

- Une PR doit être approuvée par au moins 1 membre avant d'être mergée
- Le reviewer vérifie : lisibilité du code, respect des conventions, absence de credentials
- Ne pas merger sa propre PR sans review

5. Procédure de résolution de conflits Git

En cas de conflit lors d'un merge ou d'un rebase :

   1. Mettre à jour ta branche avec dev :
        bash
        git checkout dev
        git pull origin dev
        git checkout feature/ma-fonctionnalite
        git merge dev
   
   2. Ouvrir les fichiers en conflit — repérer les marqueurs '<<<<<<<', '=======', '>>>>>>>'
   3. Résoudre manuellement en gardant le bon code
   4. Marquer les conflits comme résolus :
       bash
       git add .
       git commit -m "fix: resolve merge conflict with dev"
   
   5. En cas de doute, contacter le Tech Lead avant de forcer un merge


6. Règles de sécurité

- Ne jamais commiter le fichier .env — il est dans le .gitignore
- Utiliser uniquement .env.example pour documenter les variables requises
- Aucune clé API, mot de passe ou secret en dur dans le code ou les Dockerfiles
- Les credentials du pipeline CI/CD sont stockés dans les GitHub Actions Secrets
- Les images Docker doivent utiliser des tags précis 

7. Environnement de développement

Lancer le projet

bash
 Cloner le dépôt
git clone   https://github.com/Ineees24/skillhub
cd skillhub

Lancer la stack complète
docker compose up --build

Lancer les tests

bash
php bin/phpunit



En cas de problème :
- Utiliser les issues GitHub pour tracker les tâches
- Toute décision importante d'architecture est prise collectivement