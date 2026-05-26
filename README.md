# NewsRoom API

**A Modern News & Articles Management System with API Versioning Architecture**

---

## Overview

NewsRoom is a Laravel-based RESTful API system for managing news articles with role-based access control, polymorphic relationships, and a clean API versioning strategy.

---

## Table of Contents

1. [Setup Instructions](#1-setup-instructions)
2. [Database Schema](#2-database-schema)
3. [API Endpoints](#3-api-endpoints)
4. [Architectural Decisions](#4-architectural-decisions)
5. [Features](#5-features)

---

## 1. Setup Instructions

### Prerequisites

- PHP 8.2+
- Composer
- MySQL 8.0+ / SQLite
- Laravel 11.x

### Installation

```bash
# Clone the repository
git clone https://github.com/khederAlkhateeb/NewsRoom.git
cd NewsRoomTask5

# Install dependencies
composer install

# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate

# Run migrations
php artisan migrate

# Seed the database with sample data
php artisan db:seed

# Start the development server
php artisan serve
```

The API will be available at: `http://127.0.0.1:8000`

### Configuration (.env)

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=Task5
DB_USERNAME=root
DB_PASSWORD=

CACHE_STORE=redis
SESSION_DRIVER=database
QUEUE_CONNECTION=database
```

---

## 2. Database Schema

### Entity-Relationship Diagram

```
┌─────────────┐       ┌─────────────┐       ┌─────────────┐
│    User     │       │   Article   │       │   Comment   │
├─────────────┤       ├─────────────┤       ├─────────────┤
│ id          │──┐    │ id          │──┐    │ id          │
│ name        │  │    │ user_id     │◀─┘    │ user_id     │◀─┐
│ email       │  └───▶│ title       │       │ body        │  │
│ password    │       │ content     │       │ commentable_ │  │
│ role        │       │ status      │       │ id          │  │
└─────────────┘       │ published_at│       │ commentable_│  │
      │               └─────────────┘       │ type        │  │
      │                     │              └─────────────┘  │
      │                     │                     ▲        │
      ▼                     ▼                     │        │
┌─────────────┐       ┌─────────────┐       ┌─────────────┐
│   Profile   │       │     Tag      │       │ Attachment  │
├─────────────┤       ├─────────────┤       ├─────────────┤
│ id          │       │ id          │       │ id          │
│ user_id     │◀─┐    │ name        │       │ file_path   │
│ bio         │  │    │ slug        │       │ file_type   │
│ avatar      │  │    └─────────────┘       │ attachable_ │──┘
└─────────────┘  │           │               │ id          │
                 │           │               │ attachable_ │
                 │           ▼               │ type        │
                 │    ┌─────────────┐        └─────────────┘
                 │    │  taggable   │
                 └───▶│ (pivot)     │
                      └─────────────┘
```

### Tables

| Table | Description |
|-------|-------------|
| `users` | User accounts with roles (admin, writer, reader) |
| `profiles` | User profile information (1:1 with users) |
| `articles` | News articles with writing and publishing status |
| `comments` | Polymorphic comments on articles |
| `tags` | Article tags with many-to-many relationship |
| `attachments` | Polymorphic file attachments |
| `taggables` | Pivot table for polymorphic tag relationships |
| `personal_access_tokens` | Laravel Sanctum authentication tokens |

---

## 3. API Endpoints

### Authentication

| Method | Endpoint | Description | Auth Required |
|--------|----------|-------------|---------------|
| POST | `/api/login` | User authentication | No |

**Request:**
```json
{
    "email": "admin@example.com",
    "password": "password123"
}
```

**Response:**
```json
{
    "success": true,
    "message": "Authenticated successfully.",
    "access_token": "1|abc123...",
    "token_type": "Bearer",
    "user": {
        "id": 1,
        "name": "Test User",
        "email": "test@example.com",
        "role": "admin"
    }
}
```

---

### API Version 1 (Web App)

**Base URL:** `/api/v1`

#### Public Endpoints

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/v1/articles` | List all published articles |

**Response (v1 - Lightweight):**
```json
{
    "data": [
        {
            "id": 1,
            "title": "Article Title",
            "content": "Article content...",
            "writer_name": "John Doe",
            "published_at": "2026-05-15T09:49:22+0000"
        }
    ]
}
```

#### Protected Endpoints (Requires Token)

| Method | Endpoint | Description | Roles |
|--------|----------|-------------|-------|
| POST | `/api/v1/articles` | Create new article | admin, writer |
| GET | `/api/v1/admin/dashboard` | Admin dashboard | admin |

---

### API Version 2 (Mobile App)

**Base URL:** `/api/v2`

#### Public Endpoints

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/v2/articles` | List all published articles |

**Response (v2 - Enhanced):**
```json
{
    "data": [
        {
            "id": 1,
            "title": "Article Title",
            "content": "Article content...",
            "writer_name": "John Doe",
            "published_at": "2026-05-15T09:49:22+0000",
            "tags": ["Technology", "Innovation"],
            "comments_count": 15,
            "reading_time": "5 min"
        }
    ]
}
```

---

### Create Article Request Body

```json
{
    "title": "Article Title",
    "content": "Full article content...",
    "status": "published"
}
```

---

## 4. Architectural Decisions

### 4.1 API Versioning Strategy

**Decision:** URL-based API versioning (`/api/v1/` and `/api/v2/`)

**Rationale:**
- Maintains backward compatibility with existing web clients
- Allows gradual migration for mobile app clients
- Version-specific resource responses without breaking changes

**Implementation:**
```php
Route::prefix('v1')->group(function () {
    // V1 routes - Legacy web app
});

Route::prefix('v2')->group(function () {
    // V2 routes - Enhanced mobile app
});
```

### 4.2 Laravel Sanctum Authentication

**Decision:** Used Laravel Sanctum for token-based API authentication

**Rationale:**
- Lightweight and simple setup
- No separate OAuth server required
- Perfect for SPA and mobile applications
- Built-in token management

### 4.3 Role-Based Access Control

**Decision:** Custom middleware for role-based access control

**Rationale:**
- Simple and maintainable role checking
- Easy to extend with new roles
- Integrates well with Sanctum authentication

**Middleware:**
```php
Route::middleware(['role:admin,writer'])->group(function () {
    // Admin and Writer routes
});
```

### 4.4 Polymorphic Relationships

**Decision:** Used polymorphic relationships for Comments and Attachments

**Rationale:**
- DRY code - single table for multiple entity types
- Future-proof for adding comments to other entities
- Cleaner database schema
- Easy migration path for new features

**Example:**
```php
// Comment can belong to Article, Product, or any other entity
public function commentable(): MorphTo
{
    return $this->morphTo();
}
```

### 4.5 Repository Pattern

**Decision:** Implemented Repository pattern for data access

**Rationale:**
- Separation of concerns
- Easier to test with mock data
- Centralized query logic
- Supports multiple data sources

**Structure:**
```
app/Repositories/
├── Contracts/
│   └── ArticleRepositoryInterface.php
└── Eloquent/
    └── EloquentArticleRepository.php
```

### 4.6 Observer Pattern

**Decision:** Used Laravel Observers for side effects

**Rationale:**
- Decoupled event handling
- Cleaner controller code
- Easy to maintain and test
- Supports multiple listeners

**Example:**
```php
// ArticleObserver triggers events on article changes
Article::observe(ArticleObserver::class);
```

### 4.7 Service Layer Pattern

**Decision:** Implemented Service classes for complex business logic

**Rationale:**
- Business logic separation from controllers
- Reusable across multiple controllers
- Easier unit testing
- Better code organization

**Services:**
```
app/Services/
├── Contracts/
│   └── NotificationServiceInterface.php
└── Notifications/
    ├── DatabaseNotificationService.php
    └── EmailNotificationService.php
```

---

## 5. Features

### Core Features

- **User Authentication**: Token-based auth with Laravel Sanctum
- **Role Management**: Admin, Writer, and Customer roles
- **Article Management**: Create, read, update, delete articles
- **Comments System**: Polymorphic comments with nested replies
- **Tags System**: Many-to-many tagging with taggable entities
- **File Attachments**: Polymorphic file storage
- **Admin Dashboard**: Analytics and statistics

### Technical Features

- **API Versioning**: v1 for web, v2 for mobile
- **Rate Limiting**: Protected API with throttle middleware
- **Caching**: Redis integration for performance
- **Queue Jobs**: Background processing for notifications
- **Events & Listeners**: Event-driven architecture
- **Custom Validation Rules**: Semantic title validation
- **Command-Line Tools**: Artisan commands for reporting

### API Resources

| Resource | Description |
|----------|-------------|
| `ArticleResource (v1)` | Lightweight article response |
| `ArticleResource (v2)` | Enhanced article with tags and metadata |

---

## Technologies

| Technology | Version | Purpose |
|------------|---------|---------|
| PHP | 8.2+ | Programming Language |
| Laravel | 11.x | PHP Framework |
| MySQL | 8.0+ | Relational Database |
| Redis | - | Caching & Queue |
| Laravel Sanctum | - | API Authentication |
| Spatie Permissions | - | Role Management |

---

## Test Credentials

| Role | Email | Password |
|------|-------|----------|
| Admin | test@example.com | password |
| Writer | writer@example.com | password |
| Customer | customer@example.com | password |

---

## Project Structure

```
NewsRoomTask5/
├── app/
│   ├── Console/Commands/        # Artisan commands
│   ├── Events/                  # Application events
│   ├── Http/
│   │   ├── Controllers/         # API & Web controllers
│   │   ├── Middleware/          # Custom middleware
│   │   ├── Requests/            # Form request validation
│   │   └── Resources/            # API resources
│   ├── Jobs/                    # Queue jobs
│   ├── Listeners/               # Event listeners
│   ├── Models/                  # Eloquent models
│   ├── Observers/               # Model observers
│   ├── Providers/               # Service providers
│   ├── Repositories/            # Data access layer
│   ├── Rules/                   # Custom validation rules
│   └── Services/                # Business logic services
├── config/                      # Laravel configuration
├── database/
│   ├── factories/               # Model factories
│   ├── migrations/              # Database migrations
│   └── seeders/                 # Database seeders
├── routes/                      # Application routes
└── tests/                      # Unit & Feature tests
```

---

## License

This project is open-sourced software.

---

**Developed by:** Kheder ghassan alkhateeb
