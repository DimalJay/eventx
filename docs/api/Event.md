# Events Endpoint

Base Url `/api/v1`<br>

---

## Create Event

`POST /event`<br>

### Request Body

```json
{
  "title": "AI Innovation Summit 2026",
  "description": "A university event focused on AI technologies.",
  "venue": "Main Auditorium",
  "eventDate": "2026-08-15",
  "eventTime": "09:00:00",
  "agenda": "Keynote, Workshops, Networking Session",
  "capacity": 300,
  "eventCategory": "Technology",
  "registrationDeadline": "2026-08-10",
  "ticketPrice": 1500.00,
  "isPaid": true,
  "visibility": "public",
  "waitlistEnable": true,
  "eventImage": "event-image.jpg",
  "organizerID": 5
}
```

### Response Body `200 OK`

```json
{
  "success": true,
  "message": "Event Created Successfully",
  "data": {
    "eventID": 1,
    "title": "AI Innovation Summit 2026",
    "description": "A university event focused on AI technologies.",
    "venue": "Main Auditorium",
    "eventDate": "2026-08-15",
    "eventTime": "09:00:00",
    "agenda": "Keynote, Workshops, Networking Session",
    "capacity": 300,
    "eventCategory": "Technology",
    "registrationDeadline": "2026-08-10",
    "ticketPrice": 1500.00,
    "isPaid": true,
    "visibility": "public",
    "waitlistEnable": true,
    "eventImage": "event-image.jpg",
    "organizerID": 5,
    "status": "upcoming",
    "createdAt": "2026-08-01",
    "updatedAt": "2026-08-01"
  }
}
```

### Response Body `404`<br>

Organizer not found

```json
{
  "success": false,
  "message": "Organizer not found",
  "data": null
}
```

---

## Get All Events

`GET /events`

### Response Body

```json
{
  "success": true,
  "message": "Events Retrieved Successfully",
  "data": [
    {
      "eventID": 1,
      "title": "AI Innovation Summit 2026",
      "eventDate": "2026-08-15",
      "eventTime": "09:00:00",
      "venue": "Main Auditorium",
      "capacity": 300,
      "eventCategory": "Technology",
      "isPaid": true,
      "status": "upcoming",
      "visibility": "public"
    },
    {
      "eventID": 2,
      "title": "Career Fair 2026",
      "eventDate": "2026-09-20",
      "eventTime": "10:00:00",
      "venue": "Faculty Ground",
      "capacity": 500,
      "eventCategory": "Career",
      "isPaid": false,
      "status": "upcoming",
      "visibility": "public"
    }
  ]
}
```

---

## Get One Event

`GET /event`

### Response Body

```json
{
  "success": true,
  "message": "Event Retrieved Successfully",
  "data": {
    "eventID": 1,
    "title": "AI Innovation Summit 2026",
    "description": "A university event focused on AI technologies.",
    "venue": "Main Auditorium",
    "eventDate": "2026-08-15",
    "eventTime": "09:00:00",
    "agenda": "Keynote, Workshops, Networking Session",
    "capacity": 300,
    "eventCategory": "Technology",
    "registrationDeadline": "2026-08-10",
    "ticketPrice": 1500.00,
    "isPaid": true,
    "visibility": "public",
    "waitlistEnable": true,
    "eventImage": "event-image.jpg",
    "organizerID": 5,
    "status": "upcoming",
    "createdAt": "2026-08-01",
    "updatedAt": "2026-08-01"
  }
}
```

### Response Body `404`<br>

Event not found

```json
{
  "success": false,
  "message": "Event not found",
  "data": null
}
```

---

## Update Event Details

`PUT /event`

### Request Body

```json
{
  "eventID": 1,
  "title": "AI Innovation Summit 2026 - Updated",
  "description": "Updated event description",
  "venue": "Conference Hall",
  "eventDate": "2026-08-16",
  "eventTime": "10:00:00",
  "agenda": "Updated agenda",
  "capacity": 350,
  "eventCategory": "Technology",
  "registrationDeadline": "2026-08-12",
  "ticketPrice": 2000.00,
  "visibility": "public"
}
```

### Response Body `200 OK`

```json
{
  "success": true,
  "message": "Event Details Updated Successfully",
  "data": null
}
```

### Response Body `404`<br>

Event not found

```json
{
  "success": false,
  "message": "Event not found",
  "data": null
}
```

---

## Delete Event

`DELETE /event`

### Request Body

```json
{
  "eventID": 1
}
```

### Response Body `200 OK`

```json
{
  "success": true,
  "message": "Event Deleted Successfully",
  "data": null
}
```

### Response Body `404`<br>

Event not found

```json
{
  "success": false,
  "message": "Event not found",
  "data": null
}
```

---

## Update Event Status

`PUT /event/status`

### Request Body

```json
{
  "eventID": 1,
  "status": "completed"
}
```

### Response Body `200 OK`

```json
{
  "success": true,
  "message": "Event Status Updated Successfully",
  "data": null
}
```

### Response Body `404`<br>

Event not found

```json
{
  "success": false,
  "message": "Event not found",
  "data": null
}
```

---

## Enable / Disable Waitlist

`PUT /event/waitlist`

### Request Body

```json
{
  "eventID": 1,
  "waitlistEnable": true
}
```

### Response Body `200 OK`

```json
{
  "success": true,
  "message": "Waitlist Setting Updated Successfully",
  "data": null
}
```

### Response Body `404`<br>

Event not found

```json
{
  "success": false,
  "message": "Event not found",
  "data": null
}
```
