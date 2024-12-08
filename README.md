# Library System with JSON WEB TOKEN

This library management system uses JSON Web Tokens (JWT) to provide secure and streamlined access to a book and author database. With each user action, a new single-use token is issued, ensuring maximum security and preventing token reuse. The system supports a full range of management features, allowing users to safely add, update, view, and delete entries from the library catalog.

## Table of Contents
- [Overview](#overview)
- [Technology Stack](#technology-stack)
- [API Endpoints, Payloads, and Responses](#api-endpoints-payloads-and-responses)
  - [User Management](#user-management)
    - [User Registration](#user-registration)
    - [User Authentication](#user-authentication)
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
    "token": "<token-generated>",
    "data": null
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
    "newToken": "<token-generated>"
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
    "newToken": "<token-generated>"
  }
  ```

#### View Books with Author
- **Endpoint**: `GET /cabalo_lib/public/book/collection`
- **Payload**:
  ```json
  {
    "collectionId": "69"
  }
  ```
- **Response**:
  ```json
  {
    "status": "success",
    "data": [
      {
        "bookid": 69,
        "book_title": "sample bookname",
        "authorid": 69,
        "author_name": "sample authorname"
      }
    ],
    "newToken": "<token-generated>"
  }
  ```

#### Remove Books and Authors
- **Endpoint**: `DELETE /cabalo_lib/public/book/delete`
- **Payload**:
  ```json
  {
    "collectionId": 69
  }
  ```
- **Response**:
  ```json
  {
    "status": "success",
    "message": "Entry and related book/author deleted successfully.",
    "newToken": "<token-generated>"
  }
  

## General Tips
1. Always ensure that you have the latest JWT token in the Authorization header for each action.
2. Use Postman or Thunderclient for easier management of headers and tokens.
3. Follow the response instructions if a new token is provided after each action.



