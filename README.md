# SKIM Framework Demo

A demo project covering the core features of the SKIM framework — a PHP 8.5+ micro-framework built for speed, clarity, and zero magic.

## What this is

An example application demonstrating the key capabilities of SKIM:
- **Routing** with parameters and groups
- **Controllers** for handling HTTP requests
- **Models** with property hooks (PHP 8.4)
- **Middleware** for authentication and CORS
- **Views** with fragment rendering for HTMX
- **Migrations** for database structure
- **CLI commands** for data seeding
- **Caching** with Redis and file fallback
- **Sessions** via Redis
- **SSE / realtime** streaming (economy dashboard demo)

## Project layout

```
demo/
├── app/
│   ├── Commands/          # CLI commands
│   │   └── SeedBasicCommand.php      # Seeds demo data
│   ├── Controllers/       # HTTP controllers
│   │   ├── AdminController.php       # Admin panel (protected)
│   │   ├── ApiController.php         # JSON API
│   │   ├── AuthController.php        # Login/logout
│   │   ├── EconomyController.php     # SSE realtime demo
│   │   ├── HomeController.php        # Home page
│   │   └── PostController.php        # Public posts
│   ├── Events/            # Application events
│   ├── Middleware/        # Middleware
│   │   └── AuthMiddleware.php        # Auth check
│   ├── Models/            # Data models
│   └── views/             # Templates (.html fragments)
├── config/                # Configuration (PHP arrays)
│   ├── app.php            # Core settings
│   ├── cache.php          # Cache settings
│   ├── db.php             # Database settings
│   └── realtime.php       # SSE/realtime settings
├── migrations/            # DB migrations
├── public/                # Public files
│   ├── css/
│   └── index.php          # Entry point (all requests)
├── docker/                # Docker configuration
├── .env                   # Environment variables (not committed)
├── composer.json          # PHP dependencies
├── docker-compose.yml     # Docker services
└── routes.php             # Route definitions
```

## How to run

### Requirements
- Docker and Docker Compose
- PHP 8.5+ (if running locally without Docker)

### Via Docker (recommended)

1. **Clone and install dependencies:**
```bash
docker-compose up -d --build
docker-compose exec app composer install
```

2. **Run migrations:**
```bash
docker-compose exec app bin/skim migrate
```

3. **Seed demo data:**
```bash
docker-compose exec app bin/skim seed:basic
```

4. **Open in browser:**
- App: http://localhost:8082
- Admin panel: http://localhost:8082/admin

**Demo credentials:**
- Email: `admin@example.com`
- Password: `secret`

### Locally (without Docker)

1. **Install dependencies:**
```bash
composer install
```

2. **Configure `.env`** (point to your MySQL/Redis)

3. **Run migrations:**
```bash
bin/skim migrate
```

4. **Seed data:**
```bash
bin/skim seed:basic
```

5. **Start the PHP server:**
```bash
php -S localhost:8080 -t public
```

## What you'll see

### Home page (`/`)
- Greeting with the app name
- Links to main sections
- Framework info
- Page generation time (at the bottom)

### Posts page (`/posts`)
- A list of 5 published posts with demo data:
  - "Getting Started with SKIM Framework"
  - "Understanding query_gen"
  - "Property Hooks in PHP 8.4"
  - "Cache: Redis-first with File Fallback"
  - "Fragment Rendering with HTMX"
- Each post shows a title and short excerpt
- Clicking a post opens the full version

### Post page (`/posts/{id}`)
- Full post title and content
- Post author
- Creation date
- "Back" button to the list

### Login form (`/login`)
- Email and Password fields
- "Sign in" button
- Error messages on invalid credentials

### Admin panel (`/admin`) — after login
- **Dashboard** — system overview, statistics
- **Users** — user table (Admin User, Editor User)
  - Add a new user
  - Delete users
- **Posts** — all posts table (including drafts)
  - 5 published + 1 draft ("Draft: Upcoming Features")
  - Create a new post
  - Delete posts
- "Logout" button in the top right corner

### Economy demo (`/demo/economy`)
- SSE realtime streaming example
- Live-updating dashboard via `EconomyController` + `realtime` config

### API endpoints
- `/api/users` — JSON array of users
- `/api/posts` — JSON array of posts
- `/api/posts/{id}` — JSON details of a single post

### Debug Toolbar (when APP_DEBUG=true)
- Rendered at the bottom of every page
- Request execution time
- SQL queries
- Memory usage
- Cache hits/misses

### Demo data after `seed:basic`
- **2 users:**
  - `admin@example.com` / `secret` (role: admin)
  - `editor@example.com` / `secret` (role: editor)
- **6 posts:**
  - 5 published (visible at `/posts`)
  - 1 draft (visible only in the admin panel)

## Routes

### Public
- `GET /` — Home page
- `GET /health` — Health check (API)
- `GET /error` — Test error page
- `GET /posts` — Post list
- `GET /posts/{id}` — Post page
- `GET /demo/economy` — SSE realtime demo

### Auth
- `GET /login` — Login form
- `POST /login` — Login handler
- `GET /logout` — Logout

### Admin (protected by middleware)
- `GET /admin` — Dashboard
- `GET /admin/users` — User list
- `POST /admin/users` — Create user
- `POST /admin/users/{id}/delete` — Delete user
- `GET /admin/posts` — Post list
- `POST /admin/posts` — Create post
- `POST /admin/posts/{id}/delete` — Delete post

### API
- `GET /api/users` — JSON user list
- `GET /api/posts` — JSON post list
- `GET /api/posts/{id}` — JSON post details

## Design decisions

### Why PHP arrays for configuration?
- IDE autocomplete works out of the box
- No extra parsers (YAML/INI)
- `env()` available for 12-factor style

### Why Redis-first cache?
- Redis is fast and reliable
- File fallback prevents cascade failure
- Sessions also in Redis for scalability

### Why Property Hooks?
- Auto-normalization (email → lowercase)
- Dirty tracking without getters/setters
- Computed fields directly in properties

### Why fragment rendering?
- HTMX partial updates without a JS framework
- Reusable template blocks
- Simple and fast

### Why query_gen?
- Dynamic SQL without string concatenation
- `%where%` and `%set%` tokens disappear when keys are absent
- Safe conditional queries

## Docker services

- **app** — PHP 8.5+ application (ports 8082, 5175)
- **mysql** — MySQL 8.0 (port 3308)
- **pgsql** — PostgreSQL 16 (port 5435) — alternative secondary DB
- **redis** — Redis 7 (internal network)

## Available commands

```bash
bin/skim migrate              # Run migrations
bin/skim seed:basic           # Seed demo data
bin/skim route:list           # List all routes
bin/skim cache:clear          # Clear cache
```

## Configuration

Main settings in `.env`:

```env
APP_NAME="SKIM App"
APP_ENV=local
APP_DEBUG=true
APP_KEY=base64:changeme

DB_DRIVER=mysql
DB_HOST=mysql
DB_PORT=3306
DB_NAME=skim_dev
DB_USER=skim
DB_PASS=secret

CACHE_DRIVER=redis
REDIS_HOST=redis
REDIS_PORT=6379
```

## Resources

- [SKIM Framework GitHub](https://github.com/skimphp/framework)
- PHP 8.4 property hooks documentation
- HTMX for dynamic UI without JS frameworks

## License

MIT — demo project for educational purposes.
