# Events Endpoint

Base URL `/api/v1`

---

## Create Event

`POST /event`

Auth: `AuthMiddleware` (requires `auth_token` cookie)

### Request Body (multipart/form-data)
```
title: "AI Innovation Summit 2026"
eventType: "Conference"
description: "A university event focused on AI technologies."
location: "Main Auditorium"
startDate: "2026-08-15"
endDate: "2026-08-15"
agenda: "Keynote, Workshops, Networking Session"
capacity: 300
category: "Technology"
registrationDeadline: "2026-08-10"
ticketPrice: 1500.00
isPaid: true
isPublic: true
waitlistEnabled: true
coverImage: [file]
customFields: '[{"name":"T-Shirt Size","key":"tshirtSize","type":"text"},{"name":"Dietary Notes","key":"dietary","type":"text"}]'
```

`customFields` is optional. Send it as a JSON-encoded string of the event's custom registration fields. Each field contains:
- `name` — the human-readable label shown to attendees
- `key` — the machine key used as the property name in submitted answers
- `type` — the field type (e.g. `text`)

### Response Body `200 OK`
```json
{
    "success": true,
    "message": "Event created successfully",
    "data": {
        "id": 39,
        "title": "CF E2E 9821",
        "eventType": "physical",
        "category": "General",
        "description": "",
        "location": "Hall A",
        "startDate": "2026-10-01 09:00:00",
        "endDate": "2026-10-01 17:00:00",
        "organizerId": 55,
        "isPublic": 0,
        "capacity": 0,
        "ticketPrice": 0,
        "regDeadline": null,
        "agenda": null,
        "waitlistEnabled": 0,
        "status": "upcoming",
        "customFields": [
            {
                "name": "T-Shirt Size",
                "key": "tshirtSize",
                "type": "text"
            },
            {
                "name": "Dietary Notes",
                "key": "dietary",
                "type": "text"
            }
        ],
        "createdAt": "2026-09-20 19:30:00",
        "updatedAt": "2026-09-20 19:30:00"
    }
}
```

---

## Get All Events (Auth)

`GET /events`

Auth: `AuthMiddleware` (requires `auth_token` cookie)

### Response Body `200 OK`
```json
{
    "success": true,
    "message": "Events Retrieved Successfully",
    "data": [
        {
            "eventID": 1,
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
            ],
            "organizerID": 1,
            "createdAt": "2026-07-01 10:00:00",
            "updatedAt": "2026-07-01 10:00:00"
        }
    ]
}
```

---

## Get Single Event

`GET /event?id={eventId}`

### Response Body `200 OK`
```json
{
    "success": true,
    "message": "Event details retrieved successfully",
    "data": {
        "eventID": 1,
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
        ],
        "organizerID": 1,
        "createdAt": "2026-07-01 10:00:00",
        "updatedAt": "2026-07-01 10:00:00"
    }
}
```

---

## Update Event

`PUT /event`

Auth: `AuthMiddleware` (requires `auth_token` cookie)

### Request Body
```json
{
    "id": 1,
    "title": "AI Innovation Summit 2026 - Updated",
    "category": "Business",
    "description": "Updated event description",
    "location": "Conference Hall",
    "startDate": "2026-08-16",
    "endDate": "2026-08-16",
    "capacity": 350,
    "ticketPrice": 2000.00,
    "isPublic": true,
    "isPaid": true,
    "waitlistEnabled": false,
    "agenda": "Updated agenda",
    "customFields": [
        {
            "name": "T-Shirt Size",
            "key": "tshirtSize",
            "type": "text"
        },
        {
            "name": "Hotel Room",
            "key": "room",
            "type": "text"
        }
    ]
}
```

### Response Body `200 OK`
```json
{
    "success": true,
    "message": "Event updated successfully",
    "data": null
}
```

---

## Discover Public Events

`GET /discover-events`

### Response Body `200 OK`
```json
{
    "success": true,
    "message": "Public events retrieved successfully",
    "data": [
        {
            "eventID": 1,
            "title": "AI Innovation Summit 2026",
            "category": "General",
            "description": "A university event focused on AI technologies.",
            "location": "Main Auditorium",
            "startDate": "2026-08-15 09:00:00",
            "endDate": "2026-08-15 17:00:00",
            "capacity": 300,
            "ticketPrice": 1500.00,
            "isPaid": true,
            "coverImage": "/uploads/event-covers/cover_12345.jpg",
            "customFields": [
                {
                    "name": "T-Shirt Size",
                    "key": "tshirtSize",
                    "type": "text"
                }
            ]
        }
    ]
}
```

---

## Join Event (Public Registration)

`POST /join-event`

### Request Body
```json
{
    "email": "nimal@gmail.com",
    "firstName": "Nimal",
    "lastName": "Perera",
    "eventId": 1,
    "customFields": {
        "tshirtSize": "L",
        "dietary": "Vegetarian"
    }
}
```

`customFields` is optional. Keys must match the `key` values defined on the event's `customFields`, and each value answers that field during registration.

### Response Body `200 OK`
```json
{
    "success": true,
    "message": "User registered for the event successfully",
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

> A ticket row is created in the `tickets` table when a user registers, and the
> registration links to it via `ticketId`. `ticketCode` is returned (joined from the
> `tickets` row) so scanning, email and QR flows keep working unchanged.

### Response Body `400 ERROR`
```json
{
    "success": false,
    "message": "User is already registered for this event"
}
```
