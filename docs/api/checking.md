## CheckIn Endpoint

Base Url `/api/v1`

### Check Attendance (QR / Manual Verification)
`POST /checking`

**Request Body**
```json
{
  "registerID": 45,
  "eventID": 12,
  "userID": 78,
  "verifyMethod": "QR"
}
```

**Response Body** 
200 OK<br>
Successful Check-in

```json
{
  "success": true,
  "message": "Attendance Checked Successfully",
  "data": {
    "registerID": 45,
    "eventID": 12,
    "userID": 78,
    "checkingAt": "2026-06-02 19:15:00",
    "verifyMethod": "QR",
  }
}