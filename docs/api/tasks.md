## Tasks Endpoint

Base Url `/api/v1`<br>

### Create Task
`POST /task`<br>

Request Body
```json
{
  "title": "This is a sample task title",
  "description" : "This is a sample description",
  "eventId" : 1,
  "assignedTo" : 1,
  "assignedBy" : 1,
  "assigdate" : "2025-09-08",
  "duedate" : "2025-09-08"
}
```

Response Body `200 OK`
```json
{
  "success": true,
  "message": "Task Created Successfully",
  "data": {
    "id": 1,
    "title": "This is a sample task title",
    "description" : "This is a sample description",
    "eventId" : 1,
    "createdBy" : 1,
    "assignedTo" : 1,
    "assignedBy" : 1,
    "assignedDate" : "2025-09-08",
    "dueDate" : "2025-09-08",
    "updatedAt": "2025-09-08",
    "createdAt": "2025-09-08",
  }
}
```
Response Body `404`<br>
Task not found
```json
{
  "success": false,
  "message": "Task not found",
  "data": null
}
```

----------------------

### Get all tasks
`GET /tasks`

Response Body
```json
{
  "success": true,
  "message": "Tasks Retrieved Successfully",
  "data": [
    {
      "id" : 1,
      "title": "This is a sample task title",
      "description" : "This is a sample description",
      "eventId" : 1,
      "createdBy" : 1,
      "assignedTo" : {
        "userId": 2,
        "name": "Kumara"
      },
      "assignedBy" : {
        "userId": 2,
        "name": "Kumara"
      },
      "assignedDate" : "2025-09-08",
      "dueDate" : "2025-09-08",
      "updatedAt" : "2025-09-08",
      "status" : "in-progress"
    },
    {
      "id" : 2,
      "title": "This is a sample task title",
      "description" : "This is a sample description",
      "eventId" : 1,
      "createdBy" : 1,
      "assignedTo" : {
        "userId": 2,
        "name": "Kumara"
      },
      "assignedBy" : {
        "userId": 2,
        "userName": "Kumara"
      },
      "assigdate" : "2025-09-08",
      "duedate" : "2025-09-08",
      "updatedAt" : "2025-09-08",
      "status" : "in-progress"
    },
    {
      "id" : 3,
      "title": "This is a sample task title",
      "description" : "This is a sample description",
      "eventId" : 1,
      "createdBy" : 1,
      "assignedTo" : {
        "userId": 2,
        "name": "Kumara"
      },
      "assignedBy" : {
        "userId": 2,
        "userName": "Kumara"
      },
      "assignedDate" : "2025-09-08",
      "dueDate" : "2025-09-08",
      "updatedAt" : "2025-09-08",
      "status" : "in-progress"
    }
  ]
}
```
--------------------

### Get one task
`GET /task`

Response Body
```json
{
  "success": true,
  "message": "Task Retrieved Successfully",
  "data": {
    "id" : 1,
    "title": "This is a sample task title",    
    "description" : "This is a sample description",
    "eventId" : 1,
    "createdBy" : 1,
    "assignedTo" : {
      "userId": 2,
      "name": "Kumara"
    },
    "assignedBy" : {
      "userId": 2,
      "name": "Kumara"
    },
    "assignedDate" : "2025-09-08",
    "dueDate" : "2025-09-08",
    "updatedAt" : "2025-09-08",
    "status" : "in-progress"
  }
}
```
---------------------

### Delete task
`DELETE /tasks`

Request Body
```json
{
  "id": 1
}
```
Response Body `200 OK`
```json
{
  "success": true,
  "message": "Delete Successfully",
  "data": null
}
```
Response Body `404`<br>
Task not found
```json
{
  "success": false,
  "message": "Task not found",
  "data": null
}
```
----------------

### Update task details
`PUT /task`

Request Body
```json
{
  "id" : 1,
  "title": "This is a sample task title",
  "description" : "This is a sample description",
  "dueDate" : "2025-09-08",
  "status" : "in-progress"
}
```
Response Body `200 OK`
```json
{
  "success": true,
  "message": "Task Details Updated Successfully",
  "data": null
}
```

----------------

### Update task status
`PUT /task`

Request Body
```json
{
  "taskID" : 1,
  "status" : "in-progress"
}
```
Response Body `200 OK`
```json
{
  "success": true,
  "message": "Task Status Updated Successfully",
  "data": null
}
```
-------------
### Reassign task
`PUT /tasks`

Request Body
```json
{
  "taskID" : 1,
  "newAssignedId" : 1,
  "reason" : "Current member unavailable"
}
```
Response Body `200 OK`
```json
{
  "success": true,
  "message": "Task Reassigned Successfully",
  "data": null
}
```
-------------
### Extend task deadline
`PUT /tasks`

Request Body
```json
{
  "taskID" : 1,
  "newDueDate" : "2025-09-08",
  "reason" : "Deadline Extended"
}
```
Response Body `200 OK`
```json
{
  "success": true,
  "message": "Deadline Extended Successfully",
  "data": null
}
```
