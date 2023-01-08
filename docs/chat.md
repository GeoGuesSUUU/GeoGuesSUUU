# CHAT DOC

## ACCESS

The chat server is accessible through :
```
http://localhost:8001/
```

## Events

- onOpen
- onMessage
  - @SendMessage
  - @GetMessages
  - @GetCountConnection
- onError
- onClose

## Save Message in DB

To save message in database, please respect the following minimal format
```
{
    event: '@SendMessage',
    user: {
        id: <user_id>
    },
    content: <message_content>
}
```

## OnMessage Request/Response

| **Request**         | **Response**     |
|---------------------|------------------|
| @SendMessage        | @Message         |
| @GetMessages        | @Messages        |
| @GetCountConnection | @ConnectionCount |


**@SendMessage** Response (@Message) :
```
{
    event: '@Message',
    response: {
        user: {
            id: <user_id>
            name: <user_name>
            color: <color>
            isAdmin: <boolean>
            isVerified: <boolean>
        },
        content: <message_content>
        publishAt: <date>
    }
}
```

**@GetMessages** Response (@Message) :
```
{
    event: '@Messages',
    response: [
        {
            user: {
                id: <user_id>
                name: <user_name>
                color: <color>
                isVerified: <boolean>
            },
            content: <message_content>
            publishAt: <date>
        }
    ]
}
```

**@GetCountConnection** Response (@Message) :
```
{
    event: '@ConnectionCount',
    response: {
        count: <number>
    }
}
```