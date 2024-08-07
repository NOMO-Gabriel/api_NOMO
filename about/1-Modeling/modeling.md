# Modeling of API to Manage Products of a Single Store Built with Symfony

## 1. Requirements Analysis

### Main Features:
1. **Product Description**
   - Name
   - Description
   - Price
   - Stock Quantity 
   - Date Added
   - Images

2. **Category**
   - Name
   - Description 

3. **User**
   - Name
   - Email
   - Password
   - Role (e.g., user, editor, administrator)

4. **Product Filtering**
   - Filter products by category

5. **Access Levels**
   - **Read-Only**: View products
   - **Edit Products**: View and edit products
   - **Edit Products and Categories**: View and modify products and categories

## 2. Database Modeling

### Entities and Relationships

1. **Product**
   - `id` (primary key)
   - `name` (string)
   - `description` (text)
   - `price` (float)
   - `quantity` (int)
   - `createdAt` (datetime)
   - `images` : Relation OneToMany with **Image**
   - `mainImage` : Relation ManyToOne with **Image** (specifying the main image)
   - `category` : ManyToOne relationship with **Category**: Each product can have only one category, but each category can be linked with many products.

2. **Category**
   - `id` (primary key)
   - `name` (string)
   - `description` (text)
   - `products` : OneToMany relationship with **Product**: This means each category can be linked with many products, corresponding to the ManyToOne relationship in Product.

3. **User**
   - `id` (primary key)
   - `name` (string)
   - `email` (string, unique)
   - `password` (string, hashed)
   - `role` (string, e.g., ROLE_USER, ROLE_EDITOR, ROLE_ADMIN)
   - `products` : ManyToMany relationship with **Product** for managing favorites and purchased products. Each user can have many products, and each product can be linked with many users.
   - `token` : (String) for API access

4. **Image**
   - `id` (primary key)
   - `url` (string, URL or path)
   - `description` (string, to describe the image)
   - `product` : ManyToOne relationship with **Product**

## 3. Access Management

### Roles
- **ROLE_USER**: Product read access
- **ROLE_EDITOR**: Product read and modification access
- **ROLE_ADMIN**: Product and category read and modification access

### Controllers and Routes
- **ProductController**: CRUD operations for products, filtering by category
- **CategoryController**: CRUD operations for categories
- **UserController**: User management (registration, login, profile modification)

## 4. Authentication System

### User Management
- Registration (creating a new user)
- Login (authentication via email and password)
- Password management (hashing and validation)
- Modification of user access roles

### Security
- Route protection based on roles (read, modification access, etc.)
- Use of JWT for authentication and authorization 

## 5. API Documentation

### Swagger
You can find our API documentation in the directory: [DOCUMENTATION](/ABOUT_PROJECT/DOCUMENTATION/) to facilitate testing and understanding of available endpoints.

### Example Endpoints

   ## Products
- `GET /products` : View all products
- `GET /product/{id}` : View a single product
- `GET /products/category/{categoryId}` : View products filtered by a specific category
- `POST /product` : Create a new product
- `PUT /product/{id}` : Update a product
- `DELETE /product/{id}` : Delete a product

## Categories
- `GET /categories` : View all categories
- `GET /category/{id}` : View a single category
- `POST /category` : Create a category
- `PUT /category/{id}` : Update a category
- `DELETE /category/{id}` : Delete a category

## Users
- `GET /users` : View all users
- `GET /user/{id}` : View a single user
- `POST /register` : Register a new user
- `POST /login` : Authenticate a user
- `PUT /editUser/{id}` : Update user information
- `PUT /editRole/{id}` : Update user roles
- `DELETE /user/{id}` : Delete a user

## Images
- `GET /images` : view all images
- `GET /image/{id}`: view a specific image
- `PUT /image/{id}`: update an image url for a products
- `POST /image/product/{productId}`: add an image for a specific product
- `DELETE /image/{productId}` : delete an image for  product