## Notification Endpoint

Base Url `/api/v1`

---

### Create Notification
`POST /notification`

Request Body
```json
{
  "notificationTitle": "Event Reminder: Tech Talk 2026",
  "content": "Don't forget! The Tech Talk event will start tomorrow at 10:00 AM at the Auditorium.",
  "eventID": 12,
  "notificationTime": "2026-06-03 09:00:00"
}
```

Response Body `200 Created`

```json
{
  "success": true,
  "message": "Notification Created Successfully",
  "data": {
    "notificationID": 89,
    "notificationTitle": "Event Reminder: Tech Talk 2026",
    "content": "Don't forget! The Tech Talk event will start tomorrow at 10:00 AM at the Auditorium.",
    "eventID": 12,
    "notificationTime": "2026-06-03 09:00:00",
    "createdAt": "2026-06-02 20:15:45"
  }
}
```
### Get All Notifications
`GET /notifications`

Response Body

```JSON
{
  "success": true,
  "message": "Notifications Retrieved Successfully",
  "data": [
    {
      "notificationID": 89,
      "notificationTitle": "Event Reminder: Tech Talk 2026",
      "content": "Don't forget! The Tech Talk event will start tomorrow at 10:00 AM at the Auditorium.",
      "eventID": 12,
      "notificationTime": "2026-06-03 09:00:00",
      "createdAt": "2026-06-02 20:15:45"
    },
    {
      "notificationID": 87,
      "notificationTitle": "Registration Open - Annual Cultural Festival",
      "content": "Registration is now open for the Annual Cultural Festival. Register now!",
      "eventID": 8,
      "notificationTime": "2026-05-28 14:30:00",
      "createdAt": "2026-05-28 10:00:00"
    }
  ]
}
```

### Get Notification by ID
`GET /notification/{notificationID}`

Response Body
```json
{
  "success": true,
  "message": "Notification Retrieved Successfully",
  "data": {
    "notificationID": 89,
    "notificationTitle": "Event Reminder: Tech Talk 2026",
    "content": "Don't forget! The Tech Talk event will start tomorrow at 10:00 AM at the Auditorium.",
    "eventID": 12,
    "notificationTime": "2026-06-03 09:00:00",
    "createdAt": "2026-06-02 20:15:45"
  }
}
```

### Get Notifications by Event
`GET /notification/event/{eventID}`

Response Body
```json
{
  "success": true,
  "message": "Event Notifications Retrieved Successfully",
  "data": [
    {
      "notificationID": 89,
      "notificationTitle": "Event Reminder: Tech Talk 2026",
      "content": "Don't forget! The Tech Talk event will start tomorrow at 10:00 AM at the Auditorium.",
      "eventID": 12,
      "notificationTime": "2026-06-03 09:00:00"
    }
  ]
}
```

### Delete Notification
`DELETE /notification/{notificationID}`

Response Body `200 OK`
```json
{
  "success": true,
  "message": "Notification Deleted Successfully",
  "data": null
}
```