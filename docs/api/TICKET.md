# Ticket Endpoint

Base Url `/api/v1`<br>


## Get All Tickets

`GET /ticket`<br>

Response Body `200 OK`

```json
{
  "success": true,
  "message": "Tickets Retrieved Successfully",
  "data": [
    {
      "ticketID": 1001,
      "registrationID": 25,
      "userID": 15,
      "eventID": 3,
      "paymentID": 12
    },
    {
      "ticketID": 1002,
      "registrationID": 26,
      "userID": 18,
      "eventID": 3,
      "paymentID": null
    }
  ]
}
```

Response Body `404 Not Found`

```json
{
  "success": false,
  "message": "No tickets found",
  "data": null
}
```

---

## Get Ticket By ID

`GET /ticket/{ticketID}`<br>

Example

`GET /ticket/1001`

Response Body `200 OK`

```json
{
  "success": true,
  "message": "Ticket Retrieved Successfully",
  "data": {
    "ticketID": 1001,
    "registrationID": 25,
    "userID": 15,
    "eventID": 3,
    "paymentID": 12
  }
}
```

Response Body `404 Not Found`

```json
{
  "success": false,
  "message": "Ticket not found",
  "data": null
}
```

---

## Get Ticket By Registration

`GET /ticket/registration/{registrationID}`<br>

Example

`GET /ticket/registration/25`

Response Body `200 OK`

```json
{
  "success": true,
  "message": "Ticket Retrieved Successfully",
  "data": {
    "ticketID": 1001,
    "registrationID": 25,
    "userID": 15,
    "eventID": 3,
    "paymentID": 12
  }
}
```

Response Body `404 Not Found`

```json
{
  "success": false,
  "message": "Ticket not found",
  "data": null
}
```

---

## Get Tickets By Event

`GET /ticket/event/{eventID}`<br>

Example

`GET /ticket/event/3`

Response Body `200 OK`

```json
{
  "success": true,
  "message": "Event Tickets Retrieved Successfully",
  "data": [
    {
      "ticketID": 1001,
      "registrationID": 25,
      "userID": 15,
      "eventID": 3,
      "paymentID": 12
    },
    {
      "ticketID": 1002,
      "registrationID": 26,
      "userID": 18,
      "eventID": 3,
      "paymentID": null
    }
  ]
}
```

Response Body `404 Not Found`

```json
{
  "success": false,
  "message": "No tickets found for this event",
  "data": null
}
```

---

## Get Tickets By User

`GET /ticket/user/{userID}`<br>

Example

`GET /ticket/user/15`

Response Body `200 OK`

```json
{
  "success": true,
  "message": "User Tickets Retrieved Successfully",
  "data": [
    {
      "ticketID": 1001,
      "registrationID": 25,
      "userID": 15,
      "eventID": 3,
      "paymentID": 12
    }
  ]
}
```

Response Body `404 Not Found`

```json
{
  "success": false,
  "message": "No tickets found",
  "data": null
}
```

---

## Delete Ticket

`DELETE /ticket/{ticketID}`<br>

Example

`DELETE /ticket/1001`

Response Body `200 OK`

```json
{
  "success": true,
  "message": "Ticket Deleted Successfully",
  "data": null
}
```

Response Body `404 Not Found`

```json
{
  "success": false,
  "message": "Ticket not found",
  "data": null
}
```

---

## Ticket Object Structure

```json
{
  "ticketID": 1001,
  "registrationID": 25,
  "userID": 15,
  "eventID": 3,
  "paymentID": 12
}
```
