# Ticket Endpoint

Base URL `/api/v1`

> **Data model:** `ticketCode` is stored on the `tickets` table. A registration links to
> its ticket via `Registrations.ticketId` → `tickets.id`. Tickets are created
> automatically on `POST /join-event` and by the invitation flow (`INVITE-*` codes).

---

## Get Ticket Details

`GET /ticket?code={ticketCode}`

No auth required.

### Query Parameters
- `code` — the registration ticket code (e.g. `TICKET-ABC123`)

### Response Body `200 OK`
```json
{
    "success": true,
    "message": "Ticket details retrieved successfully",
    "data": {
        "ticketCode": "TICKET-ABC123",
        "status": "registered",
        "eventId": 1,
        "event": {
            "id": 1,
            "title": "AI Innovation Summit 2026",
            "eventType": "Conference",
            "category": "General",
            "description": "A university event focused on AI technologies.",
            "location": "Main Auditorium",
            "startDate": "2026-08-15 09:00:00",
            "endDate": "2026-08-15 17:00:00",
            "capacity": 300,
            "ticketPrice": 1500.00,
            "isPaid": true,
            "isPublic": true,
            "waitlistEnabled": true,
            "coverImage": "/uploads/event-covers/cover_12345.jpg",
            "status": "upcoming",
            "customFields": [
                {
                    "name": "T-Shirt Size",
                    "key": "tshirtSize",
                    "type": "text"
                }
            ]
        },
        "organizer": "Kumara Perera",
        "holder": {
            "firstName": "Nimal",
            "lastName": "Perera",
            "email": "nimal@gmail.com"
        }
    }
}
```

`data.event.customFields` holds the event's custom registration field definitions so the ticket can render the attendee form.

### Response Body `400 ERROR`
```json
{
    "success": false,
    "message": "Ticket code is required",
    "data": null
}
```

### Response Body `404 ERROR`
```json
{
    "success": false,
    "message": "Invalid ticket code",
    "data": null
}
```