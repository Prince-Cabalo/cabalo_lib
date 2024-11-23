# Library System with JWT

A simple library management system utilizing JSON Web Tokens (JWT) for secure access. This project allows users to manage books and authors while maintaining security with token rotation, ensuring each token is single-use.

## Table of Contents
- [Overview](#overview)
- [Technology Stack](#technology-stack)
- [API Endpoints, Payloads, and Responses](#api-endpoints-payloads-and-responses)
  - [User Management](#user-management)
    - [User Registration](#user-registration)
    - [User Authentication](#user-authentication)
    - [User Update](#user-update)
    - [User Delete](#user-delete)
    - [Forgot Password](#forgot-password)
  - [Book Management](#book-management)
    - [Add Books with Author](#add-books-with-author)
    - [Update Books and Authors](#update-books-and-authors)
    - [View Books with Author](#view-books-with-author)
    - [Remove Books and Authors](#remove-books-and-authors)
- [Usage Instructions](#usage-instructions)

## Overview
- **User Registration**: Create an account to access the system.
- **JWT Authentication**: Authenticate to obtain a JWT for secure interactions with the database.
- **Token Rotation**: Tokens are single-use, refreshed after each action to prevent reuse.
- **User and Book Management**:
  - **Add**: Register new books and authors.
  - **Edit**: Update user details, books, and authors.
  - **Delete**: Remove users, books, and authors.
  - **View**: Retrieve details on books and authors.
  - **Forgot Password**: Recover access with a password reset.

## Technology Stack
- **Backend**: PHP (Slim Framework)
- **Database**: MySQL
- **Authentication**: JSON Web Tokens (JWT) with single-use tokens

## API Endpoints, Payloads, and Responses

### User Management

#### User Registration
- **Endpoint**: `POST /cabalo_lib/public/user/register`
- **Payload**:
  ```json
  {
    "username": "admin",
    "password": "123"
  }
  ```
- **Response**:
  ```json
  {
    "status": "success",
    "data": null
  }
  ```

#### User Authentication
- **Endpoint**: `POST /cabalo_lib/public/user/authe`
- **Payload**:
  ```json
  {
    "username": "admin",
    "password": "123"
  }
  ```
- **Response**:
  ```json
  {
    "status": "success",
    "token": "<generated-token>",
    "data": null
  }
  ```

#### User Update
- **Endpoint**: `PUT /cabalo_lib/public/user/update`
- **Payload**:
  ```json
  {
    "userId": 5,
    "newUsername": "newAdmin",
    "newPassword": "new123"
  }
  ```
- **Response**:
  ```json
  {
    "status": "success",
    "Message": "User information updated successfully",
    "newToken": "<generated-token>"
  }
  ```

#### User Delete
- **Endpoint**: `DELETE /cabalo_lib/public/user/delete`
- **Payload**:
  ```json
  {
    "userId": 5
  }
  ```
- **Response**:
  ```json
  {
    "status": "success",
    "Message": "User deleted successfully",
    "newToken": "<generated-token>"
  }
  ```

#### Forgot Password
- **Endpoint**: `POST /cabalo_lib/public/user/forgot-password`
- **Payload**:
  ```json
  {
    "username": "admin123",
    "email": "admin@example.com"
  }
  ```
- **Response**:
  ```json
  {
    "status": "success",
    "Message": "Password reset instructions have been sent to your email",
    "newToken": "<generated-token>"
  }
  ```

### Book Management

#### Add Books with Author
- **Endpoint**: `POST /cabalo_lib/public/book/add`
- **Payload**:
  ```json
  {
    "bookTitle": "sample bookname",
    "authorName": "sample authorname"
  }
  ```
- **Response**:
  ```json
  {
    "status": "success",
    "Message": "The book has been added to the collection",
    "newToken": "<generated-token>"
  }
  ```

#### Update Books and Authors
- **Endpoint**: `PUT /cabalo_lib/public/book/update`
- **Payload**:
  ```json
  {
    "bookId": 9,
    "newBookTitle": "Updated Bookname",
    "newAuthorName": "Updated AuthorName"
  }
  ```
- **Response**:
  ```json
  {
    "status": "success",
    "Message": "The book has been updated",
    "newToken": "<generated-token>"
  }
  ```

#### View Books with Author
- **Endpoint**: `GET /cabalo_lib/public/book/collection`
- **Payload**:
  ```json
  {
    "collectionId": "16"
  }
  ```
- **Response**:
  ```json
  {
    "status": "success",
    "data": [
      {
        "bookid": 16,
        "book_title": "sample bookname",
        "authorid": 16,
        "author_name": "sample authorname"
      }
    ],
    "newToken": "<generated-token>"
  }
  ```

#### Remove Books and Authors
- **Endpoint**: `DELETE /cabalo_lib/public/book/delete`
- **Payload**:
  ```json
  {
    "collectionId": 16
  }
  ```
- **Response**:
  ```json
  {
    "status": "success",
    "message": "Entry and related book/author deleted successfully.",
    "newToken": "<generated-token>"
  }
  ```

## Usage Instructions
1. Make sure the **`cabalo_lib.sql`** file is imported into your MySQL database. This file is located in the `cabalo_lib/database/cabalo_lib.sql` directory.
2. Register a user using the [User Registration](#user-registration) endpoint, then authenticate with the [User Authentication](#user-authentication) endpoint.
3. For adding, updating, viewing, or deleting entries:
   - Use the **`<generated-token>`** obtained after authentication.
   - In your API client (Postman, Thunderclient, etc.), go to the Headers section.
   - Add **`Authorization`** as a header key and paste the `<generated-token>` in the value field.
4. Customize the payloads to match your requirements and send your requests.
```



