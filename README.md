# Test - API de gestion de stock

[![CI workflow](https://github.com/teddy-francfort/test_gestion_stock_api/actions/workflows/ci.yml/badge.svg)](https://github.com/teddy-francfort/test_gestion_stock_api/actions/workflows/ci.yml)

*Développé par Teddy FRANCFORT en Février 2023 dans le cadre d'un processus de recrutement*

- Cette API est développé avec le framework PHP Laravel. 
- Elle a pour objectif de pouvoir être utilisée par une interface front Vue.js
- L'authentification est gérée Laravel Sanctum

Fonctionnalités attendues :
- Système d’authentification (https://laravel.com/docs/9.x/sanctum).
- Ajout/modification de produits (nom, description, quantité, ...).
- Historisation des mouvements de stock : date, quantités, prix (pour les entrées), ...
- Système de notification (mail) quand le stock d’un produit est bas.

## Initialisation du projet

**Initialiser la base de données avec des données tests**

`php artisan migrate:fresh --seed`

**Utilisateur test**

Lorsque la base de données est initialisé avec des données tests,
plusieurs utilisateurs sont générés automatiquement dont un manuellement

identifiant : admin@test.com

mot de passe : password


# Routes de l'API

Les routes pour utiliser l'api sont les suivantes :

GET|HEAD        api/is-auth

GET|HEAD        api/products

POST            api/products

DELETE          api/products/{product}

GET|HEAD        api/products/{product}

PUT|PATCH       api/products/{product}

**Routes de Laravel Breeze utilisé pour la connexion, la déconnexion et la création de compte**

POST            login

POST            logout

POST            register

**Route de sanctum pour récupérer un token CSRF**

GET|HEAD        sanctum/csrf-cookie

