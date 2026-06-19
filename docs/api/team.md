## Team Endpoint

Base Url `/api/v1`<br>

### Add Member to Team

`POST /team`<br>

Request Body

```json
{
  "userId": 15,
  "eventId": 3,
  "role": "MEMBER"
  }
```

Response Body `201 SUCCESS`
```json
{
  "success": true,
  "message": "User added to team successfully.",
  "data": {
    "id": 101,
    "userId": 15,
    "eventId": 3,
    "role": "MEMBER",
    "joinedAt": "2026-06-05 10:30:00"
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

Response Body `400 ERROR`
```json
{
  "success": false,
  "message": "User is already assigned to this team."
}
```
----

### Get Teams By Event

`GET /team/event?id={eventId}`<br>

Response Body `200 OK`

```json
{
  "success": true,
  "message": "Event Teams Retrieved Successfully",
  "data": [
    {
      "id":1,
     "userId": 15,
      "firstName": "John",
      "lastName": "Silva",
      "role": "MEMBER"
    },
    {
      "id": 1,
      "userId": 18,
      "firstName": "Nimal",
      "lastName": "Perera",
      "role": "MEMBER"
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

### Update User Role in Team

`PUT /team`<br>

Request Body

```json
{
  "id": 1,
  "role": "MEMBER"
}
```

Response Body `201 SUCCESS`

```json
{
  "success": true,
  "message": "User Role Updated Successfully",
  "data": {
    "id": 1,
    "eventId": 1,
    "userId": 1,
    "firstName": "Nimal",
    "lastName": "Perera",
    "role": "MEMBER",
    "joinedAt": "2026-06-02 10:30:00"
  }
}
```

Response Body `404 Not Found`

```json
{
  "success": false,
  "message": "Error occured while updating user role",
  "data": null
}
```

---

### Delete User from Team

`DELETE /team`<br>

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
  "message": "User removed from team Successfully",
  "data": null
}
```

Response Body `404 Not Found`

```json
{
  "success": false,
  "message": "Error occured while removing user from team",
  "data": null
}
```

---

## Team Member Types

ORGANIZER
COORDINATOR
MEMBER

