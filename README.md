# Medical Appointment API

A medical appointment scheduling system built with **Laravel 12**, **React 19**, **TypeScript**, and **PostgreSQL**.

## Tech Stack

| Layer       | Technology                          |
|-------------|-------------------------------------|
| Backend     | Laravel 12, PHP 8.4                 |
| Frontend    | React 19, TypeScript, Tailwind CSS 4 |
| Database    | PostgreSQL 14                       |
| SPA Bridge  | Inertia.js v2                       |
| Build Tool  | Vite 7                              |
| Containers  | Docker, Docker Compose              |

## Prerequisites

- [Docker](https://docs.docker.com/get-docker/) & [Docker Compose](https://docs.docker.com/compose/install/)
- No PHP, Node.js, or PostgreSQL installation required locally

## Quick Start (Docker)

```bash
# 1. Clone the repository
git clone https://github.com/OElyazami/medical-appointment-api.git
cd medical-appointment-api

# 2. Start the application
cd _init
docker compose up -d --build

# 3. Wait ~30 seconds for the database healthcheck and migrations to complete
# Check that all services are running:
docker compose ps
```

The application will be available at **http://localhost:8090**

### What happens on first start

The entrypoint script automatically:
1. Creates `.env` from `.env.example`
2. Generates the `APP_KEY`
3. Caches configuration, routes, and views
4. Runs database migrations
5. Seeds the database with 20 sample doctors

## Services

| Service  | Port (Host) | Description                     |
|----------|-------------|---------------------------------|
| nginx    | 8090        | Reverse proxy                   |
| app      | (internal)  | Laravel PHP-FPM                 |
| db       | 5433        | PostgreSQL database             |

## Useful Commands

All commands must be run from the `_init/` directory.

```bash
# View container status
docker compose ps

# View app logs
docker compose logs app --tail 50

# View all logs
docker compose logs -f

# Run artisan commands
docker compose exec app php artisan <command>

# Generate a new APP_KEY
docker compose exec app php artisan key:generate --force

# Re-run migrations
docker compose exec app php artisan migrate --force

# Seed the database
docker compose exec app php artisan db:seed --class=DoctorSeeder --force

# Open a shell inside the app container
docker compose exec app sh

# Stop all services
docker compose down

# Stop and remove all data (volumes)
docker compose down -v

# Rebuild after code changes
docker compose build app
docker compose up -d
```

## Local Development (without Docker)

If you prefer running without Docker:

```bash
# 1. Install dependencies
composer install
npm install

# 2. Copy environment file
cp .env.example .env

# 3. Configure .env with your local PostgreSQL credentials
#    DB_HOST=127.0.0.1
#    DB_PORT=5432
#    DB_DATABASE=medical_app
#    DB_USERNAME=postgres
#    DB_PASSWORD=postgres

# 4. Generate app key
php artisan key:generate

# 5. Run migrations and seed
php artisan migrate
php artisan db:seed --class=DoctorSeeder

# 6. Start the development servers (Laravel + Vite concurrently)
composer dev
```

The app will be available at **http://localhost:8000**

## Pages

| URL          | Description                                      |
|--------------|--------------------------------------------------|
| `/`          | Dashboard (Doctors, Availability, Booking)       |

## API Endpoints

Base URL: `http://localhost:8090/api`

### Doctors

| Method   | Endpoint                          | Description             |
|----------|-----------------------------------|-------------------------|
| `GET`    | `/api/doctors`                    | List all active doctors | |
| `GET`    | `/api/doctors/{id}/availability`  | Get available time slots|

### Appointments

| Method | Endpoint             | Description       |
|--------|----------------------|-------------------|
| `POST` | `/api/appointments`  | Book an appointment |

### Example Requests

**List doctors:**
```bash
curl http://localhost:8090/api/doctors
```

**List doctors with filters:**
```bash
curl "http://localhost:8090/api/doctors?specialization=Cardiology&search=smith&per_page=10"
```

**Create a doctor:**
```bash
curl -X POST http://localhost:8090/api/doctors \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Dr. Jane Smith",
    "specialization": "Cardiology",
    "email": "jane.smith@hospital.com",
    "phone": "+1234567890"
  }'
```

**Check availability:**
```bash
curl "http://localhost:8090/api/doctors/1/availability?date=2026-02-17"
```

**Book an appointment:**
```bash
curl -X POST http://localhost:8090/api/appointments \
  -H "Content-Type: application/json" \
  -d '{
    "doctor_id": 1,
    "patient_name": "John Doe",
    "start_time": "2026-02-17 09:00:00",
    "reason": "Annual checkup"
  }'
```

## Project Structure

```
├── _init/                      # Docker configuration
│   ├── Dockerfile              # Multi-stage build (Node → Composer → PHP-FPM)
│   ├── docker-compose.yml      # Service orchestration
│   └── docker/
│       ├── entrypoint.sh       # Container startup script
│       ├── nginx/
│       │   └── default.conf    # Nginx reverse proxy config
│       └── php/
│           ├── php.ini         # PHP production settings
│           └── opcache.ini     # OPcache configuration
├── app/
│   ├── DataTransferObjects/    # DTOs for type-safe data passing
│   ├── Http/
│   │   ├── Controllers/Api/    # API controllers (thin)
│   │   └── Requests/           # Form requests with validation
│   ├── Models/                 # Eloquent models
│   └── Services/               # Business logic layer
├── database/
│   ├── factories/              # Model factories for seeding
│   ├── migrations/             # Database schema
│   └── seeders/                # Database seeders
├── resources/js/               # React frontend
│   ├── lib/api/                # API client modules
│   ├── pages/                  # Inertia pages
│   │   ├── dashboard.tsx       # Main dashboard
│   │   └── dashboard/          # Dashboard sub-components
│   └── types/                  # TypeScript type definitions
└── routes/
    ├── api.php                 # API routes
    └── web.php                 # Web routes
```

## Architecture

```
Request → FormRequest (validate) → DTO → Controller → Service → Model
```

- **Form Requests** handle validation rules
- **DTOs** provide type-safe data transfer between layers
- **Controllers** are thin — delegate to services
- **Services** contain business logic (e.g., pessimistic locking for bookings)
- **Models** define relationships, scopes, and accessors

## Key Features

- **Doctor CRUD** with query scopes
- **Availability slots** generated from working hours (Mon-Fri, 09:00-17:00) with 30-minute intervals
- **Appointment booking** with pessimistic locking to prevent double-booking under concurrent requests
- **React dashboard** with tabbed interface for managing doctors, checking availability, and booking appointments
