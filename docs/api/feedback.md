# Feedback Endpoint

Base URL `/api/v1`

---

## Submit Feedback

`POST /feedback`

### Request Body
```json
{
    "eventId": 2,
    "participantId": 3,
    "organizationRating": 5,
    "contentRating": 4,
    "experienceRating": 5,
    "comment": "Excellent event with very informative sessions.",
    "sentiment": "Positive"
}
```

### Response Body `200 OK`
```json
{
    "success": true,
    "message": "Feedback submitted successfully.",
    "data": null
}
```

### Response Body `400 ERROR`
```json
{
    "success": false,
    "message": "All fields except comment are required",
    "data": null
}
```

---

## Get Feedbacks by Event

`GET /feedbacks?eventId={eventId}`

### Response Body `200 OK`
```json
{
    "success": true,
    "message": "List of feedbacks for event ID: 2",
    "data": [
        {
            "id": 1,
            "eventId": 2,
            "participantId": 3,
            "participantName": "John Silva",
            "organizationRating": 5,
            "contentRating": 4,
            "experienceRating": 5,
            "comment": "Excellent event with very informative sessions.",
            "sentiment": "Positive",
            "createdAt": "2026-07-01 10:00:00"
        }
    ]
}
```

### Response Body `404 ERROR`
```json
{
    "success": false,
    "message": "No feedback found for this event.",
    "data": null
}
```

---

## Get Single Feedback

`GET /feedback?id={feedbackId}`

### Response Body `200 OK`
```json
{
    "success": true,
    "message": "Feedback details for ID: 1",
    "data": {
        "id": 1,
        "eventId": 2,
        "participantId": 3,
        "organizationRating": 5,
        "contentRating": 4,
        "experienceRating": 5,
        "comment": "Excellent event with very informative sessions.",
        "sentiment": "Positive",
        "createdAt": "2026-07-01 10:00:00"
    }
}
```

### Response Body `404 ERROR`
```json
{
    "success": false,
    "message": "Feedback not found for ID: 1",
    "data": null
}
```
