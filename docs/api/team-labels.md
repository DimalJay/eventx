# Team Labels Endpoint

Base URL `/api/v1`

Team labels are a single comma-separated label string stored per event (one row per event in the `team_labels` table). All routes require `AuthMiddleware` (requires `auth_token` cookie) and that the caller has team access to the event.

---

## Get Labels

`GET /team-labels?eventId={eventId}`

### Response Body `200 OK`
```json
{
    "success": true,
    "message": "Labels fetched successfully",
    "data": {
        "eventId": 3,
        "labels": "VIP, Speaker, Press"
    }
}
```

Empty labels return `""`:
```json
{
    "success": true,
    "message": "Labels fetched successfully",
    "data": {
        "eventId": 3,
        "labels": ""
    }
}
```

---

## Edit Labels

`PUT /team-labels`

### Request Body
```json
{
    "eventId": 3,
    "labels": "VIP, Speaker, Press"
}
```

### Response Body `200 OK`
```json
{
    "success": true,
    "message": "Labels updated successfully",
    "data": {
        "eventId": 3,
        "labels": "VIP, Speaker, Press"
    }
}
```

### Error Response `400 ERROR`
```json
{
    "success": false,
    "message": "Event ID is required"
}
```

### Error Response `403 ERROR`
```json
{
    "success": false,
    "message": "Unauthorized: You do not have access to this event"
}
```