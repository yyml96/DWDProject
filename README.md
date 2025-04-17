
# QC

## Installation

Install the application dependencies by running:

```sh
npm install
```

## Development

Start the application in development mode by running:

```sh
npm run dev
```

## Production

Build the application in production mode by running:

```sh
npm run build
```

## DataProvider

The included data provider uses [ra-data-simple-rest](https://github.com/marmelab/react-admin/tree/master/packages/ra-data-simple-rest). It fits REST APIs that follow a simple RESTful convention.

You'll find an `.env` file at the project root that includes a `VITE_API_URL` variable. Set it to the URL of your backend API. By default, the API expects CRUD endpoints for resources like `/users`, `/posts`, etc.


---

# User.php

The `User.php` file defines methods for database operations related to users. Below are the main methods included in this file:

## Constructor

```php
public function __construct()
```

**Function**: Initializes the MongoDB client and selects the user collection.  
**Parameters**: None.  
**Returns**: None.

## getUsers Method

```php
public function getUsers($range)
```

**Function**: Retrieves a list of users based on the provided range.  
**Parameters**:  
- `$range` (array): Contains two integers representing the start and end index of the user records to be retrieved.  
**Returns**: Returns a list of users and sets the Content-Range in the response header.

## getOne Method

```php
public function getOne($id)
```

**Function**: Retrieves a single user based on the user ID.  
**Parameters**:  
- `$id` (string): The ID of the user.  
**Returns**: Returns the information of a single user.

---

# UserController.php

The `UserController.php` file defines controller methods related to users. These methods call the database operation methods defined in `User.php` and handle HTTP requests and responses.

## getUsers Method

```php
public function getUsers($range)
```

**Function**: Calls the `getUsers` method from `User.php` to retrieve a list of users.  
**Parameters**:  
- `$range` (array): Contains two integers representing the start and end index of the user records to be retrieved.  
**Returns**: Outputs the list of users in JSON format.

## getOne Method

```php
public function getOne($id)
```

**Function**: Calls the `getOne` method from `User.php` to retrieve a single user based on the user ID.  
**Parameters**:  
- `$id` (string): The ID of the user.  
**Returns**: Outputs the information of a single user in JSON format.

---

# api.php

The `api.php` file defines the API routes and dispatches requests to the corresponding controller methods.

### Running Logic Summary

- **Request Routing**: The `api.php` file parses the request URI and method, and dispatches the request to the corresponding controller method.
- **Controller Methods**: The `UserController.php` file contains controller methods that handle the request, call the database operation methods in `User.php`, and return the appropriate HTTP response.
- **Database Operations**: The `User.php` file contains methods that interact with the database to perform specific CRUD operations.

