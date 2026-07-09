# Tasks Endpoint

Base URL `/api/v1`

All task routes require `AuthMiddleware` (requires `auth_token` cookie).

---

## Create Task

`POST /task`

### Request Body
```json
{
    "title": "This is a sample task title",
    "description": "This is a sample description",
    "eventId": 1,
    "assignedTo": 2,
    "assignedBy": 1,
    "dueDate": "2025-09-08"
}
```

### Response Body `200 OK`
```json
{
    "success": true,
    "message": "Task created succesfully",
    "data": null
}
```

---

## Get All Tasks

`GET /tasks?eventId={eventId}`

### Response Body `200 OK`
```json
{
    "success": true,
    "message": "Tasks retrieved successfully",
    "data": [
        {
            "id": 1,
            "title": "This is a sample task title",
            "description": "This is a sample description",
            "eventId": 1,
            "createdBy": 1,
            "assignedTo": {
                "userId": 2,
                "name": "Kumara"
            },
            "assignedBy": {
                "userId": 1,
                "name": "Nimal"
            },
            "assignedDate": "2025-09-08",
            "dueDate": "2025-09-08",
            "status": "in-progress",
            "createdAt": "2025-09-08",
            "updatedAt": "2025-09-08"
        }
    ]
}
```

---

## Get Single Task

`GET /task?id={taskId}`

### Response Body `200 OK`
```json
{
    "success": true,
    "message": "Task retrieved successfully",
    "data": {
        "id": 1,
        "title": "This is a sample task title",
        "description": "This is a sample description",
        "eventId": 1,
        "createdBy": 1,
        "assignedTo": {
            "userId": 2,
            "name": "Kumara"
        },
        "assignedBy": {
            "userId": 1,
            "name": "Nimal"
        },
        "assignedDate": "2025-09-08",
        "dueDate": "2025-09-08",
        "status": "in-progress",
        "createdAt": "2025-09-08",
        "updatedAt": "2025-09-08"
    }
}
```

### Response Body `404 ERROR`
```json
{
    "success": false,
    "message": "Error retrieving task: Task not found"
}
```

---

## Update Task

`PUT /task`

### Request Body
```json
{
    "id": 1,
    "title": "Updated task title",
    "description": "Updated description",
    "dueDate": "2025-09-10",
    "status": "completed"
}
```

### Response Body `200 OK`
```json
{
    "success": true,
    "message": "Task updated successfully",
    "data": {
        "id": 1,
        "title": "Updated task title",
        "description": "Updated description",
        "dueDate": "2025-09-10",
        "status": "completed"
    }
}
```

---

## Update Task Status

`PUT /task/status`

### Request Body
```json
{
    "id": 1,
    "status": "in-progress"
}
```

### Response Body `200 OK`
```json
{
    "success": true,
    "message": "Task status updated successfully",
    "data": null
}
```

---

## Delete Task

`DELETE /task`

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
    "message": "Task deleted successfully",
    "data": null
}
```

### Response Body `404 ERROR`
```json
{
    "success": false,
    "message": "Error deleting task: Task not found"
}
```
