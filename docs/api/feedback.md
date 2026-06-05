## Feedback Endpoint

Base Url `/api/v1`<br>

---

### Submit feedback

`POST /api/feedback` <br>

Request Body
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
Response Body `201 SUCCESS`
```json
{
  "success": true,
  "message": "Feedback submitted successfully.",
  "data": {
    "feedbackId": 101
  }
}
```
---

Response Body `400 ERROR`
```json
{
  "success": false,
  "message": "Invalid Rating Value.",
  "data": null 
}
```
---

### Get all Feedbacks of a specific event

`GET /api/feedback/event/{eventId}` <br>

Response Body `201 SUCCESS`
```json
{
  "success": true,
  "message": "Feedback retrieved successfully.",
  "data": {
    {
      "feedbackId": 101,
      "participantName": "John Silva",
      "organizationRating": 5,
      "contentRating": 4,
      "experienceRating": 5,
      "comment": "Excellent event with very informative sessions."
    }
  }
}
```
-----------

Response Body `404 ERROR`
```json
{
  "success": false,
  "message": "Feedback not found.",
  "data": null 
}
```
---

### Get one specific feedback 

`GET /api/feedback/{feedbackId}` <br>

Response Body `201 SUCCESS`
```json
{
  "success": true,
  "message": "Feedback details retrieved successfully.",
  "data": {
    "feedbackId": 101,
    "eventId": 15,
    "participantId": 42,
    "organizationRating": 5,
    "contentRating": 4,
    "experienceRating": 5,
    "comment": "Excellent event with very informative sessions."
  }
}
```
----------

Response Body `404 ERROR`
```json
{
  "success": false,
  "message": "Feedback not found.",
  "data": null 
}
```
---

### Get Sentiment Analysis

`GET /api/feedback/{feedbackId}/sentiment` <br>

Response Body `201 SUCCESS`
```json
{
    {
  "success": true,
  "message": "Sentiment analysis retrieved successfully.",
  "data": {
    "feedbackId": 101,
    "sentiment": "Positive",
  }
}
}
```
-----

Response Body `404 ERROR`
```json
{
  "success": false,
  "message": "Sentiment analysis not found.",
  "data": null 
}
```
---

esponse Body `403 ERROR`
```json
{
  "success": false,
  "message": "You do not have permission to perform this action."
  "data": null 
}
```
---

Response Body `500 ERROR`
```json
{
  "success": false,
  "message": "An unexpected error occured."
  "data": null 
}
```
------

### Get Feedback Analytics

`GET /api/feedback/event/{eventId}/analytics` <br>

Response Body `201 SUCCESS`
```json 
{
  "success": true,
  "message": "Feedback analytics retrieved successfully.",
  "data": {
    "totalFeedbacks": 150,
    "averageOrganizationRating": 4.6,
    "averageContentRating": 4.4,
    "averageExperienceRating": 4.7,
    "positiveFeedbacks": 120,
    "neutralFeedbacks": 20,
    "negativeFeedbacks": 10
  }
}
```
-----

Response Body `404 ERROR`
```json
{
  "success": false,
  "message": "Feedback analytics not found.",
  "data": null 
}
```
---

Response Body `403 ERROR`
```json
{
  "success": false,
  "message": "You do not have permission to perform this action."
  "data": null 
}
```
---

Response Body `500 ERROR`
```json
{
  "success": false,
  "message": "An unexpected error occured."
  "data": null 
}
```




