# Coal Management API Documentation

## Base URL
```
http://localhost:8000/api
```

## Endpoints

### Users

#### Get All Users
```http
GET /user
```

#### Get Specific User
```http
GET /users/{user_id}
```

### Projects

#### Get All Projects
```http
GET /projects
```

#### Get Specific Project
```http
GET /projects/{project_id}
```

### Clients

#### Get All Clients
```http
GET /clients
```

#### Get Specific Client
```http
GET /clients/{client_id}
```

### Activities

#### Get All Activities
```http
GET /activities
```

#### Get Specific Activity
```http
GET /activities/{activity_id}
```

## Response Format

### Success Response
```json
{
    "success": true,
    "message": "Success message here",
    "data": {
        // Response data
    }
}
```

### Error Response
```json
{
    "success": false,
    "message": "Error message here",
    "error": "Detailed error message",
    "trace": "Stack trace (in development)"
}
```

## Models

### User
- id
- name
- email
- roles
- projects
- recent_activities
- statistics
- created_at
- updated_at

### Project
- id
- name
- type
- priority
- status
- client
- created_at

### Client
- id
- name
- email
- phone
- address
- projects
- users
- statistics
- created_at
- updated_at

### Activity
- id
- description
- properties
- created_at
