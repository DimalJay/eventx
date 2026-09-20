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
            "id": 0,
            "name": "John Silva",
            "email": "john@gmail.com",
            "role": "ORGANIZER",
            "label": null,
            "isOrganizer": true
        },
        {
            "id": 2,
            "name": "Nimal Perera",
            "email": "nimal@gmail.com",
            "role": "STAFF",
            "label": "Speaker",
            "isOrganizer": false
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
    "role": "STAFF",
    "label": "Speaker"
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

## Update Member Label

`PUT /team-access/label`

### Request Body
```json
{
    "id": 2,
    "label": "VIP"
}
```

Passing an empty `label` clears it (sets to `null`).

### Response Body `200 OK`
```json
{
    "success": true,
    "message": "Team member label updated successfully",
    "data": {
        "label": "VIP"
    }
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
- `STAFF`

## Team Member Labels

Optional free-text label on a team member row (e.g. `"Speaker"`, `"VIP"`, `"Press"`). Set when adding a member via the optional `label` field, or updated any time with `PUT /team-access/label`.
