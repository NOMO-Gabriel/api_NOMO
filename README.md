# API_NOMO

A simple API to provide data for products for a single store.

## Project Status
The development of the API for managing products (`API_NOMO`) is finished.
Please start by reading this `README` for an overview of the project and its objectives. For a detailed list of development steps and associated commits, refer to the [Development Steps](/about/2-DevelopmentGuide/Readme.md). This guide provides a comprehensive outline of the development process, including feature implementation and testing.
To have more information about modeling of the project, see my [modeling](/about/1-Modeling/modeling.md)


## Overview

`api_NOMO` is a REST API built with Symfony that allows for managing products in a single store. It supports operations for handling products, categories, users, and images. 

## Features

- **Product Management**
  - Create, read, update, and delete products
  - Associate products with categories
  - Manage product images

- **Category Management**
  - Create, read, update, and delete categories

- **User Management**
  - User registration, login, and profile management
  - Access roles: USER, EDIT,GRANT_EDIT, ADMIN and SUPER_ADMIN

- **Image Management**
  - Upload and associate images with products

- **Product Filtering**
  - Filter products by category

## Installation

### Prerequisites

- PHP 8.1 or higher
- Composer
- Symfony CLI
- MySQL, PostgresSQL or another Database Management System (DBMS)

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

Update`.env` file and put  the database credentials and other configuration settings. Make sure that your database is properly configured. By default, PostgresSQL is configured.My configuration is default configuration to use Maria db with phpMyAdmin. Comment out this line:

      ```dotenv
      # DATABASE_URL="postgresql://app:!ChangeMe!@127.0.0.1:5432/app?serverVersion=16&charset=utf8"
and

    ````dotenv
    # 
    DATABASE_URL="mysql://root:@127.0.0.1:3306/api_product?serverVersion=10.4.28-MariaDB&charset=utf8mb4"
  
and uncomment the line related to your database. Configure the password, username, and database name, and make sure you specify the correct version of your database server. For example:

- Database: MariaDB
- Name: myDatabaseName
- Username: me
- Password: mypassword
- Server version: 10.4.28

Here is an example configuration:

     DATABASE_URL="mysql://me:mypassword@127.0.0.1:3306/myDatabaseName?serverVersion=10.4.28-MariaDB&charset=utf8mb4"




5. **Create the database:**

    ```bash
    php bin/console doctrine:database:create
    ```

6. **Run migrations:**

    ```bash
    php bin/console doctrine:migrations:migrate
    ```
7. fill database with fixtures:

        php bin/console doctrine:fixtures:load 
this command will delete all data in database and generate new data.

If you need only to add data, make:

        php bin/console doctrine:fixtures:load --append

8. **Start the Symfony server:**

    ```bash
    symfony server:start
    ```
    or 
    ```bash
   php -S localhost:8000 -t public
    ```
## Authentication

The API uses JWT (JSON Web Tokens) for authentication. To access protected routes, include the `Authorization Bearer` header with the token in your requests.

## Modeling
The API modeling is available at [modeling](/about/1-Modeling/modeling.md).


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
