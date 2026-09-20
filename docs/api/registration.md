# Registrations Endpoint

Base URL `/api/v1`

All registration routes require `AuthMiddleware` (requires `auth_token` cookie).

> **Data model:** every registration is backed by a row in the `tickets` table. The
> `Registrations.ticketId` column references `tickets.id`, and the human-readable
> `ticketCode` lives on the `tickets` row. A ticket row is created automatically when
> a user joins (`POST /join-event`) and by the invitation flow (`INVITE-*` codes).
> Registration responses include a joined `ticketCode` for compatibility.

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
            "ticketId": 11,
            "ticketCode": "TICKET-ABC123",
            "registeredAt": "2026-08-01 10:30:00",
            "status": "registered",
            "customFields": {
                "tshirtSize": "L",
                "dietary": "Vegetarian"
            }
        },
        {
            "id": 2,
            "eventId": 1,
            "user": {
                "userId": 7,
                "name": "Nimal"
            },
            "ticketId": 12,
            "ticketCode": "TICKET-DEF456",
            "registeredAt": "2026-08-01 11:00:00",
            "status": "waitlisted",
            "customFields": null
        }
    ]
}
```

---

## Get Attendees (Organizer)

`GET /event/registrations?eventId={eventId}`

Auth: `AuthMiddleware` (requires `auth_token` cookie)

Returns the full attendee rows for an event, including any submitted custom field answers.

### Response Body `200 OK`
```json
{
    "success": true,
    "message": "List of attendees for event ID: 1",
    "data": [
        {
            "id": 1,
            "eventId": 1,
            "userId": 5,
            "ticketId": 11,
            "ticketCode": "TICKET-ABC123",
            "registeredAt": "2026-08-01 10:30:00",
            "status": "registered",
            "firstName": "Nimal",
            "lastName": "Perera",
            "email": "nimal@gmail.com",
            "profilePicture": null,
            "customFields": {
                "tshirtSize": "L",
                "dietary": "Vegetarian"
            }
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
        "ticketId": 11,
        "ticketCode": "TICKET-ABC123",
        "status": "registered",
        "registeredAt": "2026-08-01 10:30:00",
        "customFields": {
            "tshirtSize": "L",
            "dietary": "Vegetarian"
        }
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
