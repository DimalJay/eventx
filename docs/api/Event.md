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
eventCategory: "Technology"
registrationDeadline: "2026-08-10"
ticketPrice: 1500.00
isPaid: true
isPublic: true
waitlistEnabled: true
coverImage: [file]
```

### Response Body `200 OK`
```json
{
    "success": true,
    "message": "Event created successfully"
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
    "description": "Updated event description",
    "location": "Conference Hall",
    "startDate": "2026-08-16",
    "endDate": "2026-08-16",
    "capacity": 350,
    "ticketPrice": 2000.00,
    "isPublic": true,
    "isPaid": true,
    "waitlistEnabled": false,
    "agenda": "Updated agenda"
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
            "description": "A university event focused on AI technologies.",
            "location": "Main Auditorium",
            "startDate": "2026-08-15 09:00:00",
            "endDate": "2026-08-15 17:00:00",
            "capacity": 300,
            "ticketPrice": 1500.00,
            "isPaid": true,
            "coverImage": "/uploads/event-covers/cover_12345.jpg"
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
    "eventId": 1
}
```

### Response Body `200 OK`
```json
{
    "success": true,
    "message": "User registered for the event successfully",
    "data": {
        "id": 1,
        "eventId": 1,
        "userId": 5,
        "status": "registered",
        "registeredAt": "2026-08-01 10:30:00"
    }
}
```

### Response Body `400 ERROR`
```json
{
    "success": false,
    "message": "User is already registered for this event"
}
```
