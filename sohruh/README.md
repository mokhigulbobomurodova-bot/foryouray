# Video Platform with Database

## Features
- Single-page website with video content
- User registration and login system
- Video viewing statistics tracking
- Session management
- Responsive design for mobile and desktop

## Database Setup

### 1. Requirements
- PHP 7.0+
- MySQL/MariaDB
- Web server (Apache/Nginx)

### 2. Installation Steps

#### Step 1: Database Configuration
1. Open `config.php`
2. Update database credentials:
```php
$host = "localhost";        // Your database host
$username = "root";         // Your database username
$password = "";             // Your database password
$database = "video_platform"; // Database name
```

#### Step 2: Create Database
1. Upload all files to your server
2. Open `setup_database.php` in your browser
3. This will create the database and required tables

#### Step 3: File Structure
```
/shohruh/
├── index.html          # Main website file
├── config.php          # Database configuration
├── api.php            # API endpoints
├── setup_database.php # Database setup script
├── 1.mp4              # Video file 1
├── 2.mp4              # Video file 2
├── 3.mp4              # Video file 3
├── 4.mp4              # Video file 4
└── README.md          # This file
```

## Database Tables

### users
- `id` - Primary key
- `username` - Unique username
- `email` - Unique email
- `password_hash` - Encrypted password
- `created_at` - Registration timestamp

### video_stats
- `id` - Primary key
- `user_id` - Foreign key to users (nullable for anonymous)
- `video_id` - Video identifier (1-4)
- `watched_count` - Number of times watched
- `last_watched` - Last viewing timestamp

### user_sessions
- `id` - Primary key
- `user_id` - Foreign key to users
- `session_token` - Unique session identifier
- `created_at` - Session creation time
- `expires_at` - Session expiration time

## API Endpoints

### Track Video Viewing
```
POST api.php?action=track_video
{
    "video_id": 1,
    "user_id": 123  // Optional (for logged-in users)
}
```

### Get Viewing Statistics
```
GET api.php?action=get_stats&user_id=123
```

### User Registration
```
POST api.php?action=register
{
    "username": "user123",
    "email": "user@example.com",
    "password": "password123"
}
```

### User Login
```
POST api.php?action=login
{
    "username": "user123",
    "password": "password123"
}
```

### Verify Session
```
POST api.php?action=verify_session
{
    "session_token": "abc123..."
}
```

## Features Explanation

### 1. Video Tracking
- Every video view is automatically tracked
- Anonymous users: tracked by session
- Logged-in users: tracked by user ID
- Statistics include watch count and last watched time

### 2. Session Management
- Sessions expire after 30 days
- Automatic session verification on page load
- Secure session tokens

### 3. Responsive Design
- Desktop: 2x2 grid layout
- Tablets: Horizontal video items
- Mobile phones: Vertical layout with scrolling

### 4. Automatic Thumbnails
- Thumbnails generated from first frame of videos
- No need for separate image files

## Security Features
- Password hashing with PHP's `password_hash()`
- SQL injection prevention with prepared statements
- Session token validation
- CORS headers for API access

## Usage
1. Upload files to server
2. Run `setup_database.php` once
3. Add your video files (1.mp4, 2.mp4, 3.mp4, 4.mp4)
4. Open `index.html` in browser

The website will automatically track all video views and store them in the database, persisting across sessions and devices for logged-in users.
