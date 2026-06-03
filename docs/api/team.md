## Team Endpoint

Base Url `/api/v1`<br>

### Create Team

`POST /team`<br>

Request Body

```json
{
  "teamName": "Registration Team",
  "managedBy": 5,
  "createdBy": 2
}
```

Response Body `201 Created`

```json
{
  "success": true,
  "message": "Team Created Successfully",
  "data": {
    "teamID": 1,
    "eventID": 1,
    "teamName": "Registration Team",
    "managedBy": 5,
    "createdBy": 2,
    "createdAt": "2026-06-02 10:30:00"
  }
}
```

Response Body `400 Bad Request`

```json
{
  "success": false,
  "message": "Invalid input data",
  "data": null
}
```

---

### Get All Teams

`GET /team`<br>

Response Body `200 OK`

```json
{
  "success": true,
  "message": "Teams Retrieved Successfully",
  "data": [
    {
      "teamID": 1,
      "eventID": 1,
      "teamName": "Registration Team",
      "managedBy": 5,
      "createdBy": 2,
      "createdAt": "2026-06-02 10:30:00"
    },
    {
      "teamID": 2,
      "eventID": 1,
      "teamName": "Marketing Team",
      "managedBy": 6,
      "createdBy": 2,
      "createdAt": "2026-06-02 11:00:00"
    }
  ]
}
```

Response Body `404 Not Found`

```json
{
  "success": false,
  "message": "No teams found",
  "data": null
}
```

---

### Get Team By ID

`GET /team/{teamID}`<br>

Example

`GET /team/1`

Response Body `200 OK`

```json
{
  "success": true,
  "message": "Team Retrieved Successfully",
  "data": {
    "teamID": 1,
    "eventID": 1,
    "teamName": "Registration Team",
    "managedBy": 5,
    "createdBy": 2,
    "createdAt": "2026-06-02 10:30:00"
  }
}
```

Response Body `404 Not Found`

```json
{
  "success": false,
  "message": "Team not found",
  "data": null
}
```

---

### Get Teams By Event

`GET /team/event/{eventID}`<br>

Example

`GET /team/event/1`

Response Body `200 OK`

```json
{
  "success": true,
  "message": "Event Teams Retrieved Successfully",
  "data": [
    {
      "teamID": 1,
      "eventID": 1,
      "teamName": "Registration Team",
      "managedBy": 5,
      "createdBy": 2,
      "createdAt": "2026-06-02 10:30:00"
    },
    {
      "teamID": 2,
      "eventID": 1,
      "teamName": "Marketing Team",
      "managedBy": 6,
      "createdBy": 2,
      "createdAt": "2026-06-02 11:00:00"
    }
  ]
}
```

Response Body `404 Not Found`

```json
{
  "success": false,
  "message": "No teams found for this event",
  "data": null
}
```

---

### Update Team

`PUT /team/{teamID}`<br>

Example

`PUT /team/1`

Request Body

```json
{
  "teamName": "Updated Registration Team",
  "managedBy": 8
}
```

Response Body `200 OK`

```json
{
  "success": true,
  "message": "Team Updated Successfully",
  "data": {
    "teamID": 1,
    "eventID": 1,
    "teamName": "Updated Registration Team",
    "managedBy": 8,
    "createdBy": 2,
    "createdAt": "2026-06-02 10:30:00"
  }
}
```

Response Body `404 Not Found`

```json
{
  "success": false,
  "message": "Team not found",
  "data": null
}
```

---

### Delete Team

`DELETE /team/{teamID}`<br>

Example

`DELETE /team/1`

Response Body `200 OK`

```json
{
  "success": true,
  "message": "Team Deleted Successfully",
  "data": null
}
```

Response Body `404 Not Found`

```json
{
  "success": false,
  "message": "Team not found",
  "data": null
}
```

---

## Team Object Structure

```json
{
  "teamID": 1,
  "eventID": 1,
  "teamName": "Registration Team",
  "managedBy": 5,
  "createdBy": 2,
  "createdAt": "2026-06-02 10:30:00"
}
```
