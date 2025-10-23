## [1.0.0] - 2025-10-20

### Added
- add maker et security-bundle
- Gestion Type intervention sans CSS
- Add login session et register non fonctionnel
- Ajout de node_module dans .gitignore
- Création CRUD intervention
- registration ok mais pas le login
- feat : authentification ok
- dashbord up v1
- Ajout du controller ClientController.php: mise en place du crud en backend et ajout des fichiers templates new, show, delete, edit, index.html.twig et du fichier index.css
- Mise en place formulaire

### Changed
- Modification Intervention.php erreur entre User et user
- Modification Intervention correction
- Mis à jour De Material en Materiel

## [1.0.0] - 2025-10-21

### Added
- Ajout du Controller et du CRUD Matériel
- ajout boxicons
- add style sur /register et /login
- Ajout de Tailwind Form
- Ajout de commentaire + modification du css Tailwind et du backend
- ajout fixture pour tests de données


### Fixed
- fix icons
- fix erreur webpack avec npm install et npm run dev
- fix icons
- fix composer.lock & supp extend base sur login et register
- Fix: suppresion du CSS dans le fichier app.css
- fix erreur css
- fix css nav
- 

### Changed
- Mise à jour submit fomulaire intervention
- Mise à jour form
- Modif gitignore pour fichier composer.lock
- Modif gitignore pour fichier composer.lock
- update style
- update /login
- Modification des champs et ajout du front end pour Materiel
- Mise à jour du gitignore
- Modification du fichier index, new, edit.html.twig et ClientType.php
- Modification du fichier index, new, edit.html.twig et ClientType.php
- Modification autorisation accès page selon l'authentification
- Modification Design type intervention
- Mise à jour Form suite
- Mise à jour
- cacher bar nav sur login & register

## [1.0.0] - 2025-10-22

### Added
- dashbord admin & user
- add bouton logout barre menu
- add tests unitaires
- Create symfony.yml
- Ajout du fichier tests/bootstrap.php manquant pour PHPUnit
- Ajout de la configuration docker
- Ajout du fichier bootstrap.php pour les tests PHPUnit
- Ajout de la compilation des assets Webpack Encore pour les tests
- Ajout documentation de docker
- Ajout commentaire pour la partie Type Intervention
- Ajout d'un nouveau bouton
- Ajout d'une Fixture pour Materiel
- add commentaires et supp home
- code commenté fichier ClientController, ClientRepository, ClientType et Client.php
- Mise en place des commentaires de la partie intervention
- Create generate-changelog.yml
- test sans entrypoint

### Fixed
- Correction du bug d'affichage mobile
- Performance améliorée du chargement
- Fix: Ajout de la crÃ©ation de base de donnÃ©es avant les migrations
- Fix: Utilisation du nom de base de donnÃ©es poolmanager_db
- Fix: Retrait du bootstrap dans phpunit.dist.xml pour compatibilitÃ© Tailwind
- Fix: Ajout de la variable KERNEL_CLASS dans phpunit.dist.xml
- Fix: Ajout de la variable DEFAULT_URI pour les tests
- Fix: Retrait du cache npm (package-lock.json manquant)
- Fix: Utilisation de npm install au lieu de npm ci
- Fix: Ajout de AuthenticatedWebTestCase pour authentifier automatiquement les tests
- Fix: Correction des tests pour rÃ©soudre les erreurs CI/CD 
- Fix: Correction des titres de pages et debug des tests de registration 
- Fix: Correction finale des tests - titres, messages et redirections 
- Fix: Correction finale des 2 derniers tests
- Fix: Configuration PHPUnit pour ignorer les dÃ©prÃ©ciations
- Fix: Migration avec CREATE TABLE IF NOT EXISTS
- Fix: Migration complÃ¨te avec IF NOT EXISTS pour toutes les tables
   Ajout de IF NOT EXISTS pour intervention_materiel, materiel

   Ajout de IF NOT EXISTS pour image, intervention_material, material dans down()

   Ã‰vite tous les conflits de tables existantes
- Fix: Contraintes de clÃ©s Ã©trangÃ¨res avec IF NOT EXISTS
    Ajout de IF NOT EXISTS pour toutes les contraintes FK

    Ã‰vite les erreurs de contraintes dupliquÃ©es

    Migration complÃ¨tement idempotente
- Fix: Syntaxe SQL correcte pour les contraintes conditionnelles
    Remplacement de IF NOT EXISTS par des requÃªtes conditionnelles

    Utilisation de information_schema pour vÃ©rifier l'existence

    Syntaxe MySQL compatible avec PREPARE/EXECUTE
- Fix: VÃ©rifications complÃ¨tes pour toutes les opÃ©rations DROP
    VÃ©rification de l'existence des tables avant DROP FOREIGN KEY

    VÃ©rification de l'existence des tables avant DROP TABLE

    Migration complÃ¨tement robuste et idempotente
- Fix: Correction du problÃ¨me de routage app_home manquant
    Remplacement des rÃ©fÃ©rences Ã  app_home par app_dashboard dans SecurityController
    Remplacement des rÃ©fÃ©rences Ã  app_home par app_dashboard dans RegistrationController
    Correction du test SecurityControllerTest pour s'attendre Ã  une redirection vers /dashboard
    RÃ©solution de l'erreur 'Unable to generate a URL for the named route app_home'
- test: VÃ©rification des tests CI/CD aprÃ¨s corrections
- fix erreur deploy
- fix
- fix npm problem
- fix css et ajout fixtures
- fix boucle infini
- juste build css a voir pour fixture
- fix node
- fix entry
- Check if DATABASE_URL is set and not empty

### Changed
- Mise à jour Champs techicien + Materiel, Form
- Refactor style et suppression barre menu pour les pages /login et /register
- /register uniquement accessible pour l'admin
- Suppression de l'entité et du repository Image
- Modification de l'entité Interventions pour retiter les liens avec Image
- Modification sécurité accès Role admin
- Modification du fichier edit.html.twig
- update style et add bouton add user pour admin
- refactor
- refactor filtrage register user pour les conditions de création d'un user
- Parametrage de ENUM pour Status
- Mise à jour du show.html.twig
- Mise Ã  jour du workflow CI pour utiliser MySQL et PHP 8.2
- Mise à jour show.html.twig suite
- affichage user modifier
- Suppresion des fichier compose.override.yaml et compose.yaml
- UI : Delete lien pour s'incrire sur /login et inversement
- update ui
- Mise à jour index.html.twig
- Correctif bug dans le fichier edit.html.twig
- Mise à jour de docker-compose
- modification du fichier index et show.html.twig
- supp users add et gestion users directement dispo dans la barre menu au dessus de settings
- update entité technicens maintenant en techniciens
- Modification du fichier show.html.twig
- update barre menu
- Update generate-changelog.yml
- Update generate-changelog.yml
- modification dockerfile => Dockerfile
- docker pour deployer manque css build auto

## [1.0.0] - 2025-10-23

### Added
final test toma abandon ensuite

### Changed
Mise à jour status sur le tableau d'affichage des interventions


