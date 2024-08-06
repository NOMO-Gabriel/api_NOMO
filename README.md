# API_NOMO

A simple API to provide data for products for a single store.

## Overview

`api_NOMO` is a RESTful API built with Symfony that allows for managing products in a single store. It supports operations for handling products, categories, users, and images. 

## Features

- **Product Management**
  - Create, read, update, and delete products
  - Associate products with categories
  - Manage product images

- **Category Management**
  - Create, read, update, and delete categories

- **User Management**
  - User registration, login, and profile management
  - Access roles: USER, EDITOR, ADMIN

- **Image Management**
  - Upload and associate images with products

- **Product Filtering**
  - Filter products by category

## Installation

### Prerequisites

- PHP 8.1 or higher
- Composer
- Symfony CLI
- MySQL or PostgreSQL

### Steps

1. **Clone the repository:**

    github
    ```bash
    https://github.com/NOMO-Gabriel/api_NOMO.git
    ```
    or gitlab
     ```bash
     https://gitlab.com/nomo-gabriel-team/api_nomo.git
    ```
   

2. **Navigate to the project directory:**

    ```bash
    cd api_NOMO
    cd api
    ```

3. **Install dependencies:**

    ```bash
    composer install
    ```

4. **Configure your environment:**

    Rename `.env.example` to `.env` and update database credentials and other configuration settings.

5. **Create the database:**

    ```bash
    php bin/console doctrine:database:create
    ```

6. **Run migrations:**

    ```bash
    php bin/console doctrine:migrations:migrate
    ```

7. **Start the Symfony server:**

    ```bash
    symfony server:start
    ```
    or 
    ```bash
   php -S localhost:8000 -t public
    ```

## API Endpoints

### Products

- `GET /products` : List all products
- `GET /product/{id}` : View a single product
- `GET /products/category/{categoryId}` : List products by category
- `POST /product` : Create a new product
- `PUT /product/{id}` : Update a product
- `DELETE /product/{id}` : Delete a product

### Categories

- `GET /categories` : List all categories
- `GET /category/{id}` : View a single category
- `POST /category` : Create a new category
- `PUT /category/{id}` : Update a category
- `DELETE /category/{id}` : Delete a category

### Users

- `GET /users` : List all users
- `GET /user/{id}` : View a single user
- `POST /register` : Register a new user
- `POST /login` : Authenticate a user
- `PUT /editUser/{id}` : Update user information
- `PUT /editRole/{id}` : Update user roles
- `DELETE /user/{id}` : Delete a user

## Authentication

The API uses JWT (JSON Web Tokens) for authentication. To access protected routes, include the `Authorization` header with the token in your requests.

## Modeling
The API modelingis available at [modeling](/about/1-Modeling/modeling.md).


## Documentation

The API documentation is available at [DOCUMENTATION](/about/DOCUMENTATION/Readme.md).

## Contributing

1. Fork the repository
2. Create a new branch (`git checkout -b feature/your-feature`)
3. Commit your changes (`git commit -am 'Add new feature'`)
4. Push to the branch (`git push origin feature/your-feature`)
5. Create a new Pull Request

## License

This project is licensed under the MIT License - see the [LICENSE](/License.txt) file for details.

## Contact

For any questions or support, please contact [me](mailto:gabriel.nomo@facsciences-uy1.cm).
