# 📁 Log Parser Application

A web-based log file parser and analyzer built with Symfony 7.3 and Docker. This application allows users to upload, parse, and analyze log files with advanced filtering and export capabilities.

## Features

- **User Authentication**: Secure login and registration system
- **Log File Upload**: Support for `.log` and `.txt` files (max 10MB)
- **Log Parsing**: Automatic parsing of log entries with pattern matching
- **Advanced Filtering**: Filter logs by:
  - Type (ERROR, WARNING, INFO, DEBUG)
  - Channel
  - File name
  - Date range
  - Search in messages
- **Pagination**: Efficient browsing of large log datasets
- **PDF Export**: Generate PDF reports of filtered log entries
- **User Management**: Each user has their own isolated log storage
- **Responsive UI**: Modern interface built with Tailwind CSS

## Tech Stack

- **Backend**: PHP 8.3, Symfony 7.3
- **Database**: PostgreSQL 15
- **Web Server**: Nginx
- **Containerization**: Docker & Docker Compose
- **Frontend**: Twig, Tailwind CSS, Stimulus, Turbo
- **PDF Generation**: mPDF

## Prerequisites

- Docker and Docker Compose installed
- Git
- Make (optional, for using Makefile commands)

## Installation

### 1. Clone the repository

```bash
git clone https://github.com/zakki-z/logParser.git
cd logParser
```

### 2. Configure environment variables

Create a `.env` file in the `code` directory:

```bash
cp code/.env.dev code/.env
```

Update the database credentials in `code/.env`:

```env
DATABASE_URL="postgresql://changeME:changeMe@postgres:5432/changeME?serverVersion=15&charset=utf8"
MAILER_DSN=smtp://mailcatcher:1025
APP_SECRET=your_secret_key_here
```

### 3. Start the Docker containers

Using Make:
```bash
make up
```

Or using Docker Compose directly:
```bash
docker compose up -d --force-recreate --remove-orphans
```

### 4. Install dependencies

```bash
docker compose exec symfony composer install
```

### 5. Set up the database

```bash
# Create the database
docker compose exec symfony php bin/console doctrine:database:create

# Run migrations
docker compose exec symfony php bin/console doctrine:migrations:migrate
```

### 6. Generate APP_SECRET (if needed)

```bash
docker compose exec symfony php bin/console secrets:generate-keys
```

## Usage

### Accessing the Application

- **Main Application**: http://localhost:8000
- **Adminer (Database UI)**: http://localhost:8080
- **Mailcatcher**: http://localhost:1080

### Default Services Ports

- **Nginx**: 8000 (HTTP), 8001 (HTTPS)
- **PostgreSQL**: 5432
- **Adminer**: 8080
- **Mailcatcher**: 1025 (SMTP), 1080 (Web UI)

### Using the Application

1. **Register a new account** at `/register`
2. **Login** with your credentials at `/login`
3. **Upload a log file** at `/upload`
4. **View and filter logs** at `/log`
5. **Export to PDF** using the "Download PDF" button
6. **Clear all logs** using the "Clear Everything" button

### Log File Format

The application expects log files in the following format:

```
[YYYY-MM-DD HH:MM:SS] channel.LEVEL: Log message content
```

Example:
```
[2024-01-15 10:30:45] app.ERROR: Database connection failed
[2024-01-15 10:31:00] security.WARNING: Invalid login attempt
[2024-01-15 10:31:15] app.INFO: User successfully logged in
```

## Development

### Useful Make Commands

```bash
# Build containers
make build

# Start containers
make up

# Stop containers
make stop

# Enter Symfony container
make enter

# Restart Nginx
make restart-nginx

# Load fixtures
make load-fixtures

# Remove and reinstall project
make install
```

### Direct Docker Commands

```bash
# View logs
docker compose logs -f symfony

# Clear Symfony cache
docker compose exec symfony php bin/console cache:clear

# Run tests
docker compose exec symfony php bin/phpunit
```

### Project Structure

```
logParser/
├── code/                    # Symfony application
│   ├── assets/             # Frontend assets
│   ├── config/             # Configuration files
│   ├── migrations/         # Database migrations
│   ├── public/             # Public directory (entry point)
│   ├── src/                # Source code
│   │   ├── Controller/     # Controllers
│   │   ├── Entity/         # Doctrine entities
│   │   ├── Form/           # Form types
│   │   ├── Repository/     # Repositories
│   │   └── Service/        # Services
│   ├── templates/          # Twig templates
│   └── var/               # Cache, logs, uploads
├── infra/                  # Infrastructure configuration
│   ├── nginx/             # Nginx configuration
│   └── php/               # PHP configuration
├── compose.yaml           # Docker Compose configuration
└── Makefile              # Make commands

```

### Key Services

- **LogFileProcessor**: Handles file upload and processing
- **LogParser**: Parses log entries from uploaded files
- **LogFilterService**: Applies filters to log queries
- **PdfMaker**: Generates PDF reports
- **FileInfoFactory**: Creates file information entities
- **Validation**: Validates uploaded files

## Database Schema

### Main Entities

- **User**: User accounts with authentication
- **FileInfo**: Metadata about uploaded log files
- **LogEntries**: Parsed log entries with relationships to files and users

## Security

- Password hashing using Symfony's password hasher
- CSRF protection enabled
- Form validation
- User isolation (users can only see their own logs)
- File upload restrictions (type and size)

## Troubleshooting

### Database Connection Issues

If you encounter database connection problems:

1. Check PostgreSQL container is running:
```bash
docker compose ps
```

2. Verify database credentials in `.env` file

3. Test connection using Adminer at http://localhost:8080

### Permission Issues

If you encounter permission issues:

```bash
# Fix permissions for var directory
docker compose exec symfony chown -R www-data:www-data var/
```

### Cache Issues

Clear Symfony cache:

```bash
docker compose exec symfony php bin/console cache:clear
```
