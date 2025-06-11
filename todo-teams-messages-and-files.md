### Multi-Team Collaboration Features

### 1. Overview
Transform the existing single-user Laravel application into a multi-team collaborative platform with messaging, file sharing, and team management capabilities.

### 2. Goals
- Enable users to create and manage multiple teams
- Implement team-based messaging system with inbox functionality
- Add file upload and sharing capabilities within teams
- Provide seamless team switching in the UI
- Design for future email notification integration

### 3. Database Schema

#### 3.1 Teams Table
```sql
teams
- id (bigint, primary key)
- name (string, required)
- description (text, nullable)
- owner_id (bigint, foreign key to users.id)
- created_at (timestamp)
- updated_at (timestamp)
```

#### 3.2 Team Members Table
```sql
team_members
- id (bigint, primary key)
- team_id (bigint, foreign key to teams.id)
- user_id (bigint, foreign key to users.id)
- role (enum: ['owner', 'member'], default: 'member')
- joined_at (timestamp)
- created_at (timestamp)
- updated_at (timestamp)
- unique index on (team_id, user_id)
```

#### 3.3 Messages Table
```sql
messages
- id (bigint, primary key)
- sender_id (bigint, foreign key to users.id)
- team_id (bigint, foreign key to teams.id)
- subject (string, required)
- body (text, required)
- created_at (timestamp)
- updated_at (timestamp)
```

#### 3.4 Message Recipients Table
```sql
message_recipients
- id (bigint, primary key)
- message_id (bigint, foreign key to messages.id)
- recipient_id (bigint, foreign key to users.id)
- read_at (timestamp, nullable)
- created_at (timestamp)
- updated_at (timestamp)
- index on (recipient_id, read_at)
```

#### 3.5 Files Table
```sql
files
- id (bigint, primary key)
- user_id (bigint, foreign key to users.id)
- filename (string, required)
- original_filename (string, required)
- mime_type (string, required)
- size (bigint, required)
- path (string, required)
- created_at (timestamp)
- updated_at (timestamp)
```

#### 3.6 File Teams Table
```sql
file_teams
- id (bigint, primary key)
- file_id (bigint, foreign key to files.id)
- team_id (bigint, foreign key to teams.id)
- shared_by (bigint, foreign key to users.id)
- shared_at (timestamp)
- created_at (timestamp)
- updated_at (timestamp)
- unique index on (file_id, team_id)
```

### 4. Feature Requirements

#### 4.1 Team Management

##### 4.1.1 Team Creation
- **Endpoint**: `POST /api/teams`
- **Validation**:
    - Team name: required, max 255 characters
    - Description: optional, max 1000 characters
- **Business Logic**:
    - User creating the team becomes the owner
    - Creator is automatically added as a team member with 'owner' role

##### 4.1.2 Add Team Members
- **Endpoint**: `POST /api/teams/{teamId}/members`
- **Request Body**:
  ```json
  {
    "email": "user@example.com"
  }
  ```
- **Validation**:
    - Only team owners can add members
    - User must exist in the system
    - User cannot be added twice to the same team
- **Response**: Return error if user doesn't exist

##### 4.1.3 List User's Teams
- **Endpoint**: `GET /api/user/teams`
- **Response**: List of all teams the authenticated user belongs to

##### 4.1.4 Switch Teams
- **Session Management**: Store `current_team_id` in session
- **Endpoint**: `POST /api/user/current-team`
- **Request Body**:
  ```json
  {
    "team_id": 1
  }
  ```

#### 4.2 Messaging System

##### 4.2.1 Send Message
- **Endpoint**: `POST /api/teams/{teamId}/messages`
- **Request Body**:
  ```json
  {
    "recipient_ids": [1, 2, 3],
    "subject": "Message subject",
    "body": "Message content"
  }
  ```
- **Validation**:
    - All recipients must be members of the team
    - Subject: required, max 255 characters
    - Body: required
- **Business Logic**:
    - Create one message record
    - Create message_recipient records for each recipient

##### 4.2.2 Inbox
- **Endpoint**: `GET /api/user/inbox`
- **Query Parameters**:
    - `team_id` (optional): Filter by team
    - `unread` (optional): Show only unread messages
- **Response**: Paginated list of messages with sender info and read status

##### 4.2.3 Mark as Read
- **Endpoint**: `PATCH /api/messages/{messageId}/read`
- **Business Logic**: Update `read_at` timestamp in message_recipients table

##### 4.2.4 Unread Count
- **Endpoint**: `GET /api/user/unread-count`
- **Response**:
  ```json
  {
    "unread_count": 5
  }
  ```

#### 4.3 File Management

##### 4.3.1 File Upload
- **Endpoint**: `POST /api/files`
- **Request**: Multipart form data
- **Validation**:
    - Allowed extensions: txt, doc, docx, pdf, jpg, jpeg, png
    - Max file size: 10MB (configurable)
- **Storage**:
    - Store in `storage/app/files/{userId}/{uniqueId}_{filename}`
    - Generate unique ID using UUID

##### 4.3.2 Share File with Team
- **Endpoint**: `POST /api/files/{fileId}/share`
- **Request Body**:
  ```json
  {
    "team_ids": [1, 2]
  }
  ```
- **Validation**:
    - User must own the file
    - User must be a member of the teams

##### 4.3.3 Revoke File Share
- **Endpoint**: `DELETE /api/files/{fileId}/teams/{teamId}`
- **Validation**: User must own the file

##### 4.3.4 List User's Files
- **Endpoint**: `GET /api/user/files`
- **Response**: Include sharing status for each file

##### 4.3.5 List Team Files
- **Endpoint**: `GET /api/teams/{teamId}/files`
- **Response**: All files shared with the team, including owner information

##### 4.3.6 Download File
- **Endpoint**: `GET /api/files/{fileId}/download`
- **Validation**: User must have access (owner or team member)

### 5. UI/UX Requirements

#### 5.1 Team Switcher
- Location: Top navigation bar
- Display: Current team name with dropdown
- Functionality: Click to show list of user's teams

#### 5.2 User Menu
- Trigger: Click on user profile picture
- Menu Items:
    - Inbox (with unread count badge if > 0)
    - My Files
    - Teams
    - Profile Settings
    - Logout

#### 5.3 Inbox Page
- Layout:
    - Message list with sender, subject, preview, timestamp
    - Unread messages highlighted
    - Click to view full message
    - Compose button
- Filters: All messages, Unread only, By team

#### 5.4 Files Page
- Two tabs:
    - "My Files": User's uploaded files with share/revoke options
    - "Team Files": Files shared with current team
- File actions: Download, Share (for owned files), View details

#### 5.5 Team Management Page
- Create new team button
- List of user's teams
- For owned teams: Add members interface

### 6. Security Requirements

- All endpoints must be authenticated
- Implement authorization checks for team membership
- File access must verify team membership or ownership
- Sanitize file names to prevent directory traversal
- Implement rate limiting for file uploads

### 7. API Error Responses

Standard error format:
```json
{
  "error": {
    "code": "VALIDATION_ERROR",
    "message": "The given data was invalid.",
    "details": {
      "email": ["The email field is required."]
    }
  }
}
```

Error codes:
- `UNAUTHORIZED`: User not authenticated
- `FORBIDDEN`: User lacks permission
- `NOT_FOUND`: Resource not found
- `VALIDATION_ERROR`: Input validation failed
- `USER_NOT_FOUND`: Specified user doesn't exist
- `ALREADY_MEMBER`: User already in team
- `FILE_TOO_LARGE`: File exceeds size limit
- `INVALID_FILE_TYPE`: File type not allowed
