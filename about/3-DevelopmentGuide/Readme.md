# Project Development Steps for API Management

## Overview
This document outlines the steps and commits necessary to develop the API for managing products in a single store using Symfony.
# Steps to Build the API for Product Management

## 1. Planning and Analysis

### Commit 1: Define Requirements
- Clarify main features (product management, categories, users, images).
- Establish access levels and roles.

### Commit 2: Create Project Plan
- Develop a roadmap with key milestones.
- Define priorities and required resources.

## 2. Setup Development Environment

### Commit 3: Install Required Tools
- PHP 8.1 or higher
- Composer
- MySQL or PostgreSQL

### Commit 4: Initialize Symfony Project
- Create a new Symfony project:
  ```bash
  composer create-project symfony/skeleton api
- Install Web dependencies
    ```bash
        cd api
        composer require webapp
### Commit 5: Configure Environment

    Copy .env.example to .env and configure environment variables (database, API keys, etc.).

# 3. Database Design
### Commit 6: Create Entities

    Define entities Product, Category, User, Image:

            bash

        php bin/console make:entity Product
        php bin/console make:entity Category
        php bin/console make:entity User
        php bin/console make:entity Image


### Commit 7: Define Relationships

    Configure relationships between entities (ManyToOne, OneToMany, ManyToMany) in entity files.

### Commit 8: Create Database and Migrate

        Create the database and apply migrations:

        bash

    php bin/console doctrine:database:create
    php bin/console doctrine:migrations:migrate

# 4. Develop Features

### Commit 9: Create Controllers

        Generate controllers for CRUD operations:

        bash

    php bin/console make:controller ProductController
    php bin/console make:controller CategoryController
    php bin/console make:controller UserController
### Commit 10: Define Routes

    Define routes for each feature in routing configuration or controller annotations.
### Commit 11: Implement Business Logic

    Add logic for managing products, categories, users, and images in controllers and services.
### Commit 12: Configure Authentication and Authorization

    Set up JWT for authentication.
    Configure roles and permissions in security.yaml.

# 5. Testing
### Commit 13: Write Unit Tests

    Write PHPUnit tests for services and controllers.
### Commit 14: Conduct Functional Testing

    Test API endpoints using tools like Postman, insomnia or Swagger.
### Commit 15: Fix Bugs

    Identify and resolve issues found during testing.
# 6. Documentation
### Commit 16: Document API

    Generate interactive API documentation with Swagger.
    Update README with relevant information.
### Commit 17: Prepare Developer Guides

    Include instructions for configuration, installation, deployment and usage of the API.