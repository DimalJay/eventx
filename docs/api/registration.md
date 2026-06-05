## Registrations Endpoint

Base Url `/api/v1`<br>

### Create Registration

`POST /registration`<br>

Request Body

```json
{
  "eventId": 1,
  "userId": 5
}
```

---

Response Body `200 OK`

```json
{
  "success": true,
  "message": "Registration Created Successfully",
  "data": {
    "id": 1,
    "eventId": 1,
    "userId": 5,
    "registeredAt": "2026-08-01 10:30:00",
    "status": "registered"
  }
}
```

Response Body `404`<br>

```json
{
  "success": false,
  "message": "Event or User not found",
  "data": null
}
```

---

### Get All Registrations

`GET /registrations`

Response Body

```json
{
  "success": true,
  "message": "Registrations Retrieved Successfully",
  "data": [
    {
      "id": 1,
      "eventId": 1,
      "user": {
        "userId": 5,
        "name": "Kumara"
      },
      "registeredAt": "2026-08-01 10:30:00",
      "status": "registered"
    },
    {
      "id": 2,
      "eventId": 1,
      "user": {
        "userId": 7,
        "name": "Nimal"
      },
      "registeredAt": "2026-08-01 11:00:00",
      "status": "waitlisted"
    }
  ]
}
```

### Get One Registration

`GET /registration`

Response Body

```json
{
  "success": true,
  "message": "Registration Retrieved Successfully",
  "data": {
    "id": 1,
    "eventId": 1,
    "user": {
      "userId": 5,
      "name": "Kumara"
    },
    "registeredAt": "2026-08-01 10:30:00",
    "status": "registered"
  }
}
```

Response Body `404`<br>

```json
{
  "success": false,
  "message": "Registration not found",
  "data": null
}
```

### Update Registration Status

`PUT /registration/status`

Request Body

```json
{
  "id": 1,
  "status": "attended"
}
```

Response Body `200 OK`

```json
{
  "success": true,
  "message": "Registration Status Updated Successfully",
  "data": null
}
```

Response Body `404`<br>

```json
{
  "success": false,
  "message": "Registration not found",
  "data": null
}
```

### Cancel Registration

`DELETE /registration`

Request Body

```json
{
  "id": 1
}
```

Response Body `200 OK`

```json
{
  "success": true,
  "message": "Registration Cancelled Successfully",
  "data": null
}
```

Response Body `404`<br>

```json
{
  "success": false,
  "message": "Registration not found",
  "data": null
}
```
