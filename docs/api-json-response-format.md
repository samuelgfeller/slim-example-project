# Standard API JSON response format

## Success

Note: mandatory `"data"` (`null` if nothing)

```json
{
  "status": "success",
  "data": {
    "posts": [
      {
        "id": 1,
        "title": "A blog post",
        "body": "Some useful content"
      },
      {
        "id": 2,
        "title": "Another blog post",
        "body": "More content"
      }
    ]
  }
}
```

## Error
Note: `"data"` is not mandatory but always welcome.  

Note 2: HTTP Code also in the response body would be overkill as we have already the information from the HTTP response.
```json
{
  "status": "error",
  "message": "Validation error",
  "data": [
    {
      "message": "Email required but not given",
      "field": "email"
    },
    {
      "message": "Required minimum length is 3",
      "field": "name"
    }
  ]
}
```


## Warning

There is no warning entry in the jsend documentation, but I find it useful sometimes

```json

{
  "status": "warming",
  "message": "User wasn't updated"
}
```

----
### Source
* https://github.com/omniti-labs/jsend
* https://stackoverflow.com/questions/12806386/is-there-any-standard-for-json-api-response-format