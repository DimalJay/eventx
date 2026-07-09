# Team Access Endpoint

Base URL `/api/v1`

All team access routes require `AuthMiddleware` (requires `auth_token` cookie).

---

## Get Team Members

`GET /team-access?eventId={eventId}`

### Response Body `200 OK`
```json
{
    "success": true,
    "message": "Team members fetched successfully",
    "data": [
        {
            "id": 1,
            "name": "John Silva",
            "email": "john@gmail.com",
            "role": "Organizer"
        },
        {
            "id": 2,
            "name": "Nimal Perera",
            "email": "nimal@gmail.com",
            "role": "Member"
        }
    ]
}
```

---

## Add Team Member

`POST /team-access`

### Request Body
```json
{
    "email": "nimal@gmail.com",
    "eventId": 3,
    "role": "MEMBER"
}
```

### Response Body `200 OK`
```json
{
    "success": true,
    "message": "Member added to the team successfully",
    "data": null
}
```

### Response Body `400 ERROR`
```json
{
    "success": false,
    "message": "Missing required fields"
}
```

---

## Update Member Role

`PUT /team-access`

### Request Body
```json
{
    "id": 1,
    "role": "COORDINATOR"
}
```

### Response Body `200 OK`
```json
{
    "success": true,
    "message": "Team member updated successfully",
    "data": null
}
```

---

## Remove Team Member

`DELETE /team-access`

### Request Body
```json
{
    "id": 1
}
```

### Response Body `200 OK`
```json
{
    "success": true,
    "message": "Member removed from the team successfully",
    "data": null
}
```

---

## Team Roles

- `ORGANIZER`
- `COORDINATOR`
- `MEMBER`
