# Users Endpoint

Base URL `/api/v1`

---

## Register User

`POST /auth/register`

### Request Body
```json
{
    "email": "nimal@gmail.com",
    "firstName": "Nimal",
    "lastName": "Perera",
    "password": "12345",
    "loginType": "EMAIL"
}
```

### Response Body `200 OK`
```json
{
    "success": true,
    "message": "Registration successfully",
    "data": null
}
```

### Response Body `400 ERROR`
```json
{
    "success": false,
    "message": "All fields are required",
    "data": null
}
```

---

## User Login

`POST /auth/login`

### Request Body
```json
{
    "email": "nimal@gmail.com",
    "password": "12345"
}
```

### Response Body `200 OK`
```json
{
    "success": true,
    "message": "Login Successful",
    "data": {
        "token": "...",
        "user": {
            "id": 1,
            "email": "nimal@gmail.com",
            "firstName": "Nimal",
            "lastName": "Perera"
        }
    }
}
```

### Response Body `401 ERROR`
```json
{
    "success": false,
    "message": "Invalid email or password"
}
```

---

## User Logout

`POST /auth/logout`

### Response Body `200 OK`
```json
{
    "success": true,
    "message": "User Logged out"
}
```

---

## Google Login

`POST /auth/google-login`

### Request Body
```json
{
    "credential": "google-credential-token"
}
```

### Response Body `200 OK`
```json
{
    "success": true,
    "message": "Login successful",
    "data": {
        "token": "...",
        "user": {
            "id": 1,
            "email": "nimal@gmail.com",
            "firstName": "Nimal",
            "lastName": "Perera"
        }
    }
}
```

### Response Body `400 ERROR`
```json
{
    "success": false,
    "message": "Google authentication failed"
}
```

---

## Create User

`POST /user`

### Request Body
```json
{
    "email": "nimal@gmail.com",
    "firstName": "Nimal",
    "lastName": "Perera",
    "password": "12345"
}
```

### Response Body `200 OK`
```json
{
    "success": true,
    "message": "User created successfully",
    "data": null
}
```

---

## Get All Users

`GET /users`

### Response Body `200 OK`
```json
{
    "success": true,
    "message": "List of users",
    "data": [
        {
            "id": 1,
            "email": "nimal@gmail.com",
            "firstName": "Nimal",
            "lastName": "Perera",
            "loginType": "standard",
            "isVerified": true,
            "profilePicture": null,
            "accountStatus": "active",
            "phoneNumber": null,
            "createdAt": "2026-07-01 10:00:00",
            "updatedAt": "2026-07-01 10:00:00",
            "lastLogin": "2026-07-10 08:00:00"
        }
    ]
}
```

---

## Get Current User

`GET /user`

Auth: `AuthMiddleware` (requires `auth_token` cookie)

### Response Body `200 OK`
```json
{
    "success": true,
    "message": "User details for ID: 1",
    "data": {
        "id": 1,
        "email": "nimal@gmail.com",
        "firstName": "Nimal",
        "lastName": "Perera",
        "loginType": "standard",
        "isVerified": true,
        "profilePicture": null,
        "accountStatus": "active",
        "phoneNumber": null,
        "createdAt": "2026-07-01 10:00:00",
        "updatedAt": "2026-07-01 10:00:00",
        "lastLogin": "2026-07-10 08:00:00"
    }
}
```

---

## Update User Profile

`PUT /user`

Auth: `AuthMiddleware` (requires `auth_token` cookie)

### Request Body
```json
{
    "firstName": "Nimal",
    "lastName": "Fernando",
    "phoneNumber": "0771234567",
    "profilePicture": "https://example.com/avatar.jpg"
}
```

### Response Body `200 OK`
```json
{
    "success": true,
    "message": "User updated successfully",
    "data": {
        "id": 1,
        "email": "nimal@gmail.com",
        "firstName": "Nimal",
        "lastName": "Fernando",
        "loginType": "standard",
        "isVerified": true,
        "profilePicture": "https://example.com/avatar.jpg",
        "accountStatus": "active",
        "phoneNumber": "0771234567",
        "createdAt": "2026-07-01 10:00:00",
        "updatedAt": "2026-07-10 12:00:00",
        "lastLogin": "2026-07-10 08:00:00"
    }
}
```

### Response Body `400 ERROR`
```json
{
    "success": false,
    "message": "No valid fields to update",
    "data": null
}
```
