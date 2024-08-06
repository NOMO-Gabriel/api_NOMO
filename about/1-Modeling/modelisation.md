# Modélisation de l'API pour la Gestion des Produits avec Symfony

## 1. Analyse des Exigences

### Fonctionnalités Principales :
1. **Description des Produits**
   - Nom
   - Description
   - Prix
   - Quantité en stock
   - Date d'ajout
   - Images

2. **Catégories**
   - Nom
   - Description 

3. **Utilisateurs**
   - Nom
   - Email
   - Mot de passe
   - Rôle (par exemple, utilisateur, éditeur, administrateur)
   - Clé d'accès

4. **Filtrage des Produits**
   - Filtrer les produits par catégorie

5. **Niveaux d'Accès**
   - **Lecture unique** : Accès en lecture aux produits
   - **Modification des produits** : Accès en lecture et modification des produits
   - **Modification des produits et des catégories** : Accès en lecture, modification des produits et gestion des catégories

## 2. Modélisation de la Base de Données

### Entités et Relations

1. **Produit**
   - `id` (clé primaire)
   - `nom` (string)
   - `description` (text)
   - `prix` (float)
   - `quantite` (int)
   - `dateAjout` (datetime)
   - `categorie` : Relation ManyToOne avec **Catégorie** : ce qui signifie qu'un produit possède une seule catégorie, mais une catégorie peut être associée à plusieurs produits
   - `images` : Relation OneToMany avec **Image**
   - `mainImage` : Relation ManyToOne avec **Image** (préciser qu'il s'agit de l'image principale)

2. **Catégorie**
   - `id` (clé primaire)
   - `nom` (string)
   - `description` (text)
   - `produits` : Relation OneToMany avec **Produit** : c'est la correspondance de la relation ManyToOne chez les produits

3. **Utilisateur**
   - `id` (clé primaire)
   - `nom` (string)
   - `email` (string, unique)
   - `motDePasse` (string, hashé)
   - `role` (string, par exemple ROLE_USER, ROLE_EDITOR, ROLE_ADMIN)
   - `produits` : Relation ManyToMany avec **Produit** pour gérer les favoris ou les produits achetés. Cette relation signifie qu'un utilisateur est lié à plusieurs produits et qu'un produit peut également être lié à plusieurs utilisateurs.
   - `token` : (String) qui sert de clé d'accès à l'API 

4. **Image**
   - `id` (clé primaire)
   - `url` (string, URL ou chemin vers l'image)
   - `description` (string, pour décrire l'image)
   - `produit` : ManyToOne avec **Produit**

## 3. Gestion des Accès

### Rôles
- **ROLE_USER** : Accès en lecture aux produits
- **ROLE_EDITOR** : Accès en lecture et modification des produits
- **ROLE_ADMIN** : Accès en lecture et modification des produits et des catégories

### Contrôleurs et Routes
- **ProductController** : Opérations CRUD pour les produits, filtrage par catégorie
- **CategoryController** : Opérations CRUD pour les catégories
- **UserController** : Gestion des utilisateurs (inscription, connexion, modification du profil)

## 4. Système d'Authentification

### Gestion des Utilisateurs
- Inscription (création d'un nouvel utilisateur)
- Connexion (authentification via email et mot de passe)
- Gestion des mots de passe (hashage et validation)
- Modification des droits d'accès d'un utilisateur

### Sécurité
- Protection des routes selon les rôles (accès en lecture, modification, etc.)
- Utilisation de JWT pour l'authentification et l'autorisation 

## 5. Documentation de l'API

### Swagger
- Une documentation pour notre API est fournie dans le dossier [DOCUMENTATION](/ABOUT_PROJECT/DOCUMENTATION/) afin de faciliter les tests et la compréhension des endpoints disponibles.

### Exemples d'Endpoints

   ## Produits
- `GET /products` : Liste des produits 
- `GET /product/{id}` : Détails d'un produit spécifique
- `GET /products/category/{categoryId}` : Liste des produits avec filtre de la catégorie
- `POST /product` : Création d'un nouveau produit
- `PUT /product/{id}` : Mise à jour d'un produit
- `DELETE /product/{id}` : Suppression d'un produit

## Catégories
- `GET /categories` : Liste des catégories
- `GET /category/{id}` : Détails d'une catégorie spécifique
- `POST /category` : Création d'une catégorie
- `PUT /category/{id}` : Mise à jour d'une catégorie
- `DELETE /category/{id}` : Suppression d'une catégorie

## Utilisateurs
- `GET /users` : Liste des utilisateurs
- `GET /user/{id}` : Détails d'un seul utilisateur
- `POST /register` : Inscription d'un nouvel utilisateur
- `POST /login` : Connexion d'un utilisateur
- `PUT /editUser/{id}` : Modification des informations d'un utilisateur
- `PUT /editRole/{id}` : Mise à jour des autorisations d'un utilisateur spécifique
- `DELETE /user/{id}` : Suppression d'un utilisateur
