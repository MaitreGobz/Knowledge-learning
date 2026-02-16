# Knowledge Learning

Plateforme e-learning permettant l’achat de cursus et de leçons en ligne, la validation de parcours de formation et l’obtention de certifications.

Le projet a été réalisé dans le cadre de ma formation de développeur web et web mobile.

## 🧱 Stack technique

- **Backend** : Symfony (API REST)
- **Frontend** : Angular
- **Base de données** : MySQL
- **Paiement** : Stripe (mode sandbox)
- **Emails** : Mailtrap (activation de compte)
- **Authentification** : Symfony Security
- **Tests** : PHPUnit (backend)

## 📁 Structure du projet

```txt
knowledge-learning/
├── backend/        # API Symfony
├── docs/           # Documents
├── frontend/       # Application Angular
└── README.md
```

## ⚙️ Pré-requis

- PHP 8.2+
- Composer 2.x
- Node.js 20+
- NPM
- Angular CLI
- Symfony CLI
- MySQL (dev) / Postgres (demo)
- Compte Stripe (mode test)
- Compte Mailtrap

## 🚀 Installation

1. Cloner le dépôt

```bash
git clone https://github.com/MaitreGobz/Knowledge-learning
cd knowledge-learning
```

2. Instalation du backend (Symfony)

```bash
cd back-end
composer install
```

Configuration de l’environnement

Créer un fichier .env.local :

```bash
APP_ENV=dev
DATABASE_URL="mysql://user:password@127.0.0.1:3306/knowledge_learning?serverVersion=8.0"
MAILER_DSN=smtp://user:password@smtp.mailtrap.io:2525
STRIPE_SECRET_KEY=sk_test_xxxxxxxxxxxxx
STRIPE_PUBLIC_KEY=pk_test_xxxxxxxxxxxxx
```

Créer un fichier .env.test :

```bash
APP_ENV=test
DATABASE_URL="mysql://user:password@127.0.0.1:3306/knowledge_learning?serverVersion=8.0"
MAILER_DSN=smtp://user:password@smtp.mailtrap.io:2525
STRIPE_SECRET_KEY=sk_test_xxxxxxxxxxxxx
STRIPE_PUBLIC_KEY=pk_test_xxxxxxxxxxxxx
```

⚠️ La base de données de test doit être différente de celle du développement.

Base de données :

```bash
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
```

3. Installation du frontend (Angular)

```bash
cd front-end
npm install
```

▶️ Lancement du projet:

Backend:

```bash
cd backend
symfony serve
```

API accessible par défaut sur :
[https://127.0.0.1:8000](https://127.0.0.1:8000)

Frontend :

```bash
cd frontend
ng serve
```

Application accessible sur :
[http://127.0.0.1:4200](http://127.0.0.1:4200)

## Comptes de démonstration

Les comptes sont déjà créer mais il peuvent l'être via les commande :

- Administrateur:

```bash
php bin/console app:create-user admin@test.com Admin123! ROLE_ADMIN
```

- Utilisateur classique :

```bash
php bin/console app:create-user user@test.com User123! ROLE_USER
```

## Documentation API

La documentation de l'API est générée via OpenAPI (Swagger)
Une fois le serveur lancé, elle est accessible à l’adresse :
[https://127.0.0.1:8000/api/doc](https://127.0.0.1:8000/api/doc)

## 💳 Paiement Stripe (Sandbox)

### Le projet utilise Stripe en mode test.

Cartes de test Stripe :

Paiement accepté : 4242 4242 4242 4242

Date : n’importe quelle date future

CVC : n’importe quel code

### Webhook Stripe (développement)

Le projet utilise les webhooks Stripe pour valider définitivement les paiements.

En environnement local, Stripe CLI est utilisé pour rediriger les événements Stripe vers l’API.

Lancer le webhook en local:

```bash
stripe listen --forward-to http://127.0.0.1:8000/api/stripe/webhook
```

Stripe CLI fournit alors un secret de webhook (whsec\_...) à renseigner dans le fichier .env.local :

```bash
STRIPE_WEBHOOK_SECRET=whsec_XXXXXXXXXXXXXXXXX
```

Fonctionnement

Après un paiement Stripe réussi (checkout.session.completed) :

l’API reçoit l’événement via le webhook

l’achat est enregistré en base de données

les droits d’accès au contenu (leçon ou cursus) sont attribués

Le webhook est public mais sécurisé par la signature Stripe.

## 🧪 Tests

### Backend (PHPUnit)

```bash
cd backend
php bin/phpunit
```

Les tests couvrent notamment :

- Inscription utilisateur
- Activation de compte
- Authentification
- Paiement (avec mocks)
- Sécurité des accès

Les tests unitaires sont principalement réalisés côté backend (Symfony).
Le frontend Angular n’intègre pas de tests unitaires dédiés, l’accent ayant été mis sur l’architecture et la fiabilité de l’API.

## 🔒 Sécurité

- Hashage des mots de passe (PasswordHasher Symfony)
- Protection CSRF sur les formulaires sensibles
- Gestion des rôles (USER / ADMIN)
- Contrôle d’accès aux ressources (leçons, achats, backoffice)
- Validation des données côté backend

## 📚 Fonctionnalités principales

- Inscription utilisateur avec activation par email
- Connexion sécurisée
- Catalogue de thèmes, cursus et leçons
- Achat de leçons ou de cursus
- Validation des leçons
- Certification automatique après validation complète d’un cursus
- Backoffice administrateur (accessible uniquement avec ROLE_ADMIN):
  Les requêtes modifiantes nécessitent un CSRF header
  Possibilité de gestion des utilisateur et des du contenu (leçons)

Les fichiers vidéo (.mp4) ne sont pas versionnés pour éviter d’alourdir le dépôt.
Ils doivent être placés localement dans `src/assets/videos/`.

## Auteur

Projet réalisé par Lucas Nayet
Formation : Développeur Web et Web Mobile
Année : 2025
