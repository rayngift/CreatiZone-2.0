# CreatiZone — Challenge Hub (version reconstruite)

## Installation (WAMP / XAMPP)
1. Copiez le dossier `creatizone` dans `C:\wamp64\www\`.
2. phpMyAdmin → onglet **Importer** → choisissez `sql/schema.sql`. (Crée une NOUVELLE base `creatizone`; l'ancienne `challenge_hub` n'est pas touchée.)
3. Si votre MySQL a un mot de passe, modifiez `config/database.php`.
4. Ouvrez http://localhost/creatizone/ (PHP 8.1+ requis).

## Structure
- `index.php` accueil · `login.php` connexion/inscription · `discover.php` liste des défis
- `defi.php` détail d'un défi + participations, votes, commentaires
- `participation.php` créer/modifier/supprimer une participation
- `mes_defis.php` CRUD de vos défis · `profile.php` profil et badges · `classement.php`
- `vote.php`, `comment.php` : API JSON (appelées par `assets/app.js`)
- `classes/` modèles (PDO) · `config/database.php` unique connexion · `includes/bootstrap.php` helpers (session, CSRF, uploads)
