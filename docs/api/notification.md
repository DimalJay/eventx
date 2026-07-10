# Notification Endpoint

Base URL `/api/v1`

All notification routes require `AuthMiddleware` (requires `auth_token` cookie).

---

## Get All Notifications

`GET /notifications?page=1&limit=10`

### Query Parameters
| Param | Type | Default | Description |
|-------|------|---------|-------------|
| page | int | 1 | Page number |
| limit | int | 10 | Items per page (max 100) |

### Response Body `200 OK`
```json
{
    "success": true,
    "message": "Notifications retrieved successfully",
    "data": [
        {
            "id": 1,
            "title": "Event Reminder",
            "message": "Your event starts in 1 hour",
            "userId": 1,
            "title": "Event Reminder",
            "message": "Your event starts in 1 hour",
            "status": "unread",
            "type": "reminder",
            "createdAt": "2025-09-08 10:00:00",
            "readAt": null,
            "isRead": false,
            "extras": null
        }
    ],
    "page": 1,
    "limit": 10,
    "total": 1,
    "totalPages": 1
}
```

---

## Mark Notification as Read

`PUT /notification/read`

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
    "message": "Notification marked as read"
}
```

---

## Mark All Notifications as Read

`PUT /notifications/read-all`

### Response Body `200 OK`
```json
{
    "success": true,
    "message": "All notifications marked as read"
}
```
