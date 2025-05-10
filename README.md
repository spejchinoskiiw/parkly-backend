# Parkly Backend API

![Parkly Logo](https://via.placeholder.com/150x50?text=Parkly)

## Corporate Parking Reservation System - Backend

This repository contains the Laravel-based backend API for the Parkly parking reservation system. Built for the IWConnect Hackathon 2025, this API provides all the necessary endpoints to power the Parkly mobile application.

## Features

- **User Authentication API**: Secure login with company email validation
- **Role-Based Access Control**: Admin, Manager, and User permission management
- **Parking Management**: Location and spot management endpoints
- **Reservation API**: Create, read, update, and delete parking reservations
- **QR Code Generation**: Create unique QR codes for each parking spot
- **Check-In/Out System**: Validate parking usage through QR code scanning

## Technology Stack

- **Framework**: Laravel PHP
- **Database**: MySQL
- **Authentication**: Laravel Sanctum
- **API**: RESTful JSON API

## Getting Started

### Prerequisites

- PHP 8.2 or higher
- Composer
- MySQL
- Git

### Setup

1. Clone the repository
   ```
   git clone https://github.com/iwconnect/parkly-backend.git
   cd parkly-backend
   ```

2. Install dependencies
   ```
   composer install
   ```

3. Set up environment variables
   ```
   cp .env.example .env
   php artisan key:generate
   ```

4. Configure your database in `.env`
   ```
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=parkly
   DB_USERNAME=root
   DB_PASSWORD=
   ```

5. Run migrations
   ```
   php artisan migrate
   ```

6. Seed the database with initial data (optional)
   ```
   php artisan db:seed
   ```

7. Start the development server
   ```
   php artisan serve
   ```

### QA Testing Environment Setup

For QA testing purposes, we provide a dedicated seeder that creates a standardized dataset:

```
php artisan db:seed-qa
```

This command will populate the database with:
- 3 facilities (Skopje, Bitola, Prilep)
- 3 users with different roles (admin@iwconnect.com, manager@iwconnect.com, user@iwconnect.com)
- 10 parking spots per facility
- 1 sample reservation

For detailed information about the QA testing environment, see [QA Testing Documentation](docs/QA_TESTING.md).

## Project Structure

- `app/Models/` - Database models
- `app/Http/Controllers/` - API controllers
- `app/Http/Requests/` - Form requests and validation
- `app/Services/` - Business logic
- `database/migrations/` - Database structure
- `database/seeders/` - Initial data
- `routes/api.php` - API endpoint definitions
- `tests/` - Automated tests

## API Documentation

API documentation is available at `/api/documentation` when the server is running. It describes all endpoints, request formats, and response structures.

Key endpoints include:

- `POST /api/auth/register` - Register a new user
- `POST /api/auth/login` - Authenticate a user
- `GET /api/parking/spots` - List available parking spots
- `POST /api/reservations` - Create a new reservation
- `GET /api/reservations` - Get user reservations
- `PUT /api/reservations/{id}` - Update a reservation
- `DELETE /api/reservations/{id}` - Delete a reservation
- `POST /api/check-in` - Check in to a parking spot
- `POST /api/check-out` - Check out from a parking spot

## Testing

Run the automated test suite:
```
php artisan test
```

## Contribution Guidelines

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add some amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

## Hackathon Team

- Backend Developer: [Name]

## License

This project is proprietary and confidential. All rights reserved by IWConnect.

## Acknowledgments

- IWConnect Hackathon 2025 Organizers
- Inspired by existing parking management solutions mentioned in the project requirements