## Auth Endpoint

Base URL `/api/v1` <br>

### Register User
`POST /auth/register` <br>

Request Body
```json
{
    "email": "nimal@gmail.com",
    "fname": "Nimal", 
    "lname": "Perera",
    "password": "12345",
    "loginType": "EMAIL",
}
```
----

Response Body `200 OK`
```json
{
    "success": true,
    "message": "Registration succesfully",
    "data": null
}
```
---

Response Body `404 ERROR`
```json
{
    "success": false,
    "message": "Registration Unsuccessful",
    "data": null
}
```
---

### User Login
`POST /auth/login` <br>

Request Body
```json
{
    "email": "nimal@gmail.com",
    "password": "12345"
}
```
---
Response Body `200 OK` <br>
```json
{
    "success": true,
    "message": "Login Successful",
    "data": null
}
```
Response Body `404 ERROR`
```json
{
    "success": false,
    "message": "Login Failed",
    "data": null
}
```
---

