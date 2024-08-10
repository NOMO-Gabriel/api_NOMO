# Documentation
### Illustrations
![landingPage](/about/DOCUMENTATION/images/img1.png)
![landingPage](/about/DOCUMENTATION/images/img2.png)
![landingPage](/about/DOCUMENTATION/images/img3.png)
![landingPage](/about/DOCUMENTATION/images/img4.png)
![landingPage](/about/DOCUMENTATION/images/img5.png)

## API Endpoints

### Documentation

- `GET api/doc ` : Get the documentation of the api and have all information  about any end-point. 

### Products

- `GET /api/products` : List all products
- `GET /api/product/{id}` : View a single product
- `GET /api/products/category/{categoryId}` : List products by category
- `POST /api/product/create` : Create a new product
- `PATCH /api/product/{id}/update` : Update a product
- `DELETE /api/product/{id}/delete` : Delete a product

### Categories

- `GET /api/categories` : List all categories
- `GET /api/category/{id}` : View a single category
- `POST /api/category/create` : Create a new category
- `PATCH /api/category/{id}/update` : Update a category
- `DELETE /api/category/{id}/delete` : Delete a category

### Images

- `GET /api/images` : List all images
- `GET /api/image/{id}` : View a single image
- `POST /api/image/product/{productId}/create` : Create a new image for a product
- `PATCH /api/image/{id}/update` : Update an image
- `DELETE /api/image/{id}/delete` : Delete an image


### Users

- `POST /api/register` : Register a new user
- `POST /api/login_check` : Authenticate a user and obtain a token to use api
- `GET /api/users` : List all users
- `GET /api/user/{id}` : View a single user
- `PATCH /api/user/{id}/update` : Update user information of a normal user by an admin
- `PATCH /api/admin/{id}/update` : Update user information of a normal user and administrator by a super-admin
- `PATCH /api/setRole/user-{userId}/role-{roleItem}/update` : Update user roles of a normal user by a admin
- `PATCH /api/setRole/admin-{adminId}/role-{roleItem}/update` : Update user roles of a normal user or admin by a super-admin
- `DELETE /api/user/{id}/delete` : Delete a normal user by an admin
- `DELETE /api/admin/{id}/delete` : Delete a normal user or admin by a superAdmin

`NB:` you must prefix all route with the server address and the correct port. If you use this project locally, the default server address is `http://localhost:8000` .

# Modeling
for more information about the modeling,see [my modeling](/about/1-Modeling/modeling.md)]
# Project
for more information about this project, see the [Readme](/README.md)
