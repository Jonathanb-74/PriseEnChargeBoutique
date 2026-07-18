# Prise en Charge Boutique

Application de gestion des prises en charge d'un atelier de réparation (électronique, informatique…) : suivi des clients, des machines déposées, du cycle de vie de chaque réparation, signatures électroniques, fiches PDF et notifications email au client.

Dépôt : [https://github.com/Jonathanb-74/PriseEnChargeBoutique](https://github.com/Jonathanb-74/PriseEnChargeBoutique)

## Fonctionnalités

- **Clients** : fiches particulier/professionnel, historique des machines et prises en charge, import CSV en masse.
- **Prises en charge** : création guidée (client, machine, panne signalée, photos), statuts personnalisables et réordonnables par glisser-déposer, assignation à un technicien, notes de suivi.
- **Verrouillage automatique** : une prise en charge dont le statut est marqué comme final passe en lecture seule (seul le changement de statut reste possible, pour permettre une réouverture).
- **Signatures électroniques** : pad de signature (plein écran sur mobile) pour le client et l'employé ; les techniciens peuvent pré-enregistrer leur signature dans leur profil pour qu'elle s'applique automatiquement à la création d'une prise en charge.
- **Fiches PDF** : version interne (avec mot de passe machine) et version client (sans), logo et couleur d'accent personnalisables, photos jointes en pleine page.
- **Emails** : modèle par défaut ou personnalisé par type d'email (titre spécifique, signature commune configurable), envoi en file d'attente (queue), choix CC/CCI, historique des envois consultable dans l'administration.
- **Configuration SMTP** entièrement pilotable depuis l'interface d'administration (serveur, port, chiffrement, expéditeur, adresse de réponse), avec envoi d'un email de test.
- **Utilisateurs** : comptes locaux ou connexion Microsoft 365 (Azure AD SSO), rôles Admin/Technicien, gestion complète depuis l'administration (création, modification, réinitialisation du mot de passe et de la 2FA).
- **Authentification à deux facteurs (2FA/MFA) obligatoire** pour tout compte local (les comptes Microsoft 365 sont protégés par Azure AD directement).
- **Tableau de bord** avec prises en charge ouvertes et filtres cohérents sur la liste complète.

## Stack technique

- [Laravel 13](https://laravel.com) (PHP 8.4+)
- [Livewire 3](https://livewire.laravel.com) + [Volt](https://livewire.laravel.com/docs/volt) pour les composants interactifs
- [Tailwind CSS 3](https://tailwindcss.com) + [Vite](https://vitejs.dev)
- MySQL
- [DomPDF](https://github.com/barryvdh/laravel-dompdf) pour la génération des fiches PDF
- [Google2FA](https://github.com/antonioribeiro/google2fa) pour l'authentification à deux facteurs
- [Laravel Socialite](https://laravel.com/docs/socialite) + provider Azure pour la connexion Microsoft 365
- [Pest](https://pestphp.com) pour les tests

## Prérequis

- PHP 8.4 ou supérieur, avec les extensions habituelles de Laravel (mbstring, pdo_mysql, gd ou imagick, zip…)
- Composer
- Node.js 18+ et npm
- MySQL 8+ (ou MariaDB équivalent)
- Un serveur web (Apache/Nginx) ou simplement `php artisan serve` en développement

## Installation

```bash
git clone https://github.com/Jonathanb-74/PriseEnChargeBoutique.git
cd PriseEnChargeBoutique

composer install
npm install

cp .env.example .env
php artisan key:generate
```

Renseignez ensuite dans `.env` au minimum les informations de base de données (`DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`) et l'URL de l'application (`APP_URL`).

```bash
php artisan migrate --seed
php artisan storage:link

npm run build
```

Un compte administrateur de démonstration est créé par le seeder (`admin@boutique.test` / `password`) — **à changer immédiatement en production**, d'autant que la 2FA sera exigée dès la première connexion.

En développement, lancez :

```bash
php artisan serve
npm run dev
```

## Configuration après installation

- **Email sortant (SMTP)** : Paramètres → Email sortant, directement depuis l'interface (aucune variable `.env` à modifier). Par défaut, les emails partent en mode journal (`log`), sans envoi réel.
- **Connexion Microsoft 365** (facultatif) : renseigner `AZURE_CLIENT_ID`, `AZURE_CLIENT_SECRET` et `AZURE_TENANT` dans `.env` pour activer le bouton de connexion SSO.
- **Traitement des emails en file d'attente** : les emails ne partent pas de façon synchrone. Une tâche planifiée doit exécuter `php artisan schedule:run` chaque minute (cron classique en production, Planificateur de tâches Windows en local) — voir la documentation intégrée sur la page "File d'attente" de l'administration pour le détail des commandes selon l'environnement.

## Tests

```bash
php artisan test
# ou directement
vendor/bin/pest
```

La suite de tests utilise SQLite en mémoire, indépendamment de la base de données configurée dans `.env`.

## Licence

Projet privé — usage interne.
