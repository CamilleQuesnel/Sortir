
   # Sortir

## Présentation
 Sortir.com est une application web réalisée avec Symfony 7 permettant de
 gérer des sorties entre participants. Les utilisateurs peuvent proposer des évè
nements, s'inscrire à ceux existants et suivre les activités de leur campus.
     
  ## Fonctionnalités principales
  - Inscription et authentification des utilisateurs
  - Création et gestion des sorties (dates, durée, lieu…)
  - Filtrage des sorties par campus, période ou état
  - Inscription et désinscription à une sortie
  - Administration des villes, des campus et des utilisateurs
  - Import d'utilisateurs depuis un fichier CSV
  - Gestion des mots de passe oubliés et envoi de notifications par courriel
  - Affichage adapté aux mobiles

 ## Installation
   1. Cloner le dépôt puis placer vous à la racine du projet
      ```bash
      git clone <repository-url>
      cd Sortir
      ```
   2. Installer les dépendances PHP
      ```bash
      composer install
      ```
   3. Copier le fichier `.env` adapté à votre environnement et lancer les conteneurs nécessaires (PostgreSQL et Mailpit)
     ```bash
     docker compose up -d
     ```
  4. Créer la base de données et exécuter les migrations
     ```bash
     php bin/console doctrine:database:create
     php bin/console doctrine:migrations:migrate
     ```
  5. (Optionnel) Charger les données de démonstration
     ```bash
     php bin/console doctrine:fixtures:load
     ```
  6. Démarrer le serveur de développement
     ```bash
     symfony server:start
     ```

  ## Tests
  L'application est livrée avec quelques tests automatisés basés sur PHPUnit. Pour les exécuter :
  ```bash
  ./vendor/bin/phpunit
  ```
