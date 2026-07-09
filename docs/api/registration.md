# Registrations Endpoint

Base URL `/api/v1`

All registration routes require `AuthMiddleware` (requires `auth_token` cookie).

---

## Get All Registrations

`GET /registrations?eventId={eventId}`

### Response Body `200 OK`
```json
{
    "success": true,
    "message": "List of registrations for event ID: 1",
    "data": [
        {
            "id": 1,
            "eventId": 1,
            "user": {
                "userId": 5,
                "name": "Kumara"
            },
            "ticketCode": "TICKET-ABC123",
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
            "ticketCode": "TICKET-DEF456",
            "registeredAt": "2026-08-01 11:00:00",
            "status": "waitlisted"
        }
    ]
}
```

---

## Update Registration Status

`PUT /registration/status`

### Request Body
```json
{
    "id": 1,
    "status": "attended"
}
```

### Response Body `200 OK`
```json
{
    "success": true,
    "message": "Registration status updated successfully",
    "data": null
}
```

### Response Body `404 ERROR`
```json
{
    "success": false,
    "message": "Registration not found"
}
```

---

## Scan Ticket

`POST /registration/scan`

### Request Body
```json
{
    "ticketCode": "TICKET-ABC123"
}
```

### Response Body `200 OK`
```json
{
    "success": true,
    "message": "Ticket Details retrieved successfully",
    "data": {
        "id": 1,
        "eventId": 1,
        "userId": 5,
        "ticketCode": "TICKET-ABC123",
        "status": "registered",
        "registeredAt": "2026-08-01 10:30:00"
    }
}
```

### Response Body `404 ERROR`
```json
{
    "success": false,
    "message": "Invalid ticket code"
}
```
