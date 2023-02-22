# Test - API de gestion de stock

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
