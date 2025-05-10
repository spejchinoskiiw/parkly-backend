# QA Testing Environment Setup

This document provides instructions for setting up the QA testing environment with sample data for testing the Parkly application.

## Overview

The QA testing seed creates a standardized set of data for testing purposes, including:

- 3 facilities (Skopje, Bitola, Prilep)
- 3 users, one with each role (Admin, Manager, Regular User)
- 10 parking spots per facility (30 total)
- 1 reservation for a spot in Skopje assigned to the regular user

## Seeded Data Details

### Facilities

| Name   | Parking Spot Count |
|--------|-------------------|
| Skopje | 10                |
| Bitola | 10                |
| Prilep | 10                |

### Users

| Name         | Email               | Role    | Assigned Facility |
|--------------|---------------------|---------|-------------------|
| Admin User   | admin@iwconnect.com | Admin   | Skopje            |
| Manager User | manager@iwconnect.com | Manager | Bitola            |
| Regular User | user@iwconnect.com  | User    | Prilep            |

### Parking Spots

- Skopje: 10 spots (numbers 1-10)
- Bitola: 10 spots (numbers 1-10)
- Prilep: 10 spots (numbers 1-10)

### Reservations

One reservation for the Regular User:
- Spot: Skopje, Spot #1
- Time: Tomorrow, 9:00 AM - 5:00 PM
- Type: Scheduled

## How to Seed the QA Environment

There are two ways to seed the QA environment:

### Option 1: Using the dedicated command

```bash
php artisan db:seed-qa
```

This command will:
1. Ask for confirmation before running (unless --force is used)
2. Clean the existing database data
3. Seed the QA testing data

To bypass the confirmation prompt, use:

```bash
php artisan db:seed-qa --force
```

### Option 2: Using the database seeder with environment

Set your environment to 'qa' or 'staging' in your .env file:

```
APP_ENV=qa
```

Then run:

```bash
php artisan db:seed
```

The database seeder will automatically use the QA testing seeder when in 'qa' or 'staging' environments.

### Option 3: Direct seeder class

You can also run the QA testing seeder directly:

```bash
php artisan db:seed --class=Database\\Seeders\\QaTestingSeeder
```

## Reset and Refresh

To completely reset the database and reseed:

```bash
php artisan migrate:fresh --seed --seeder=Database\\Seeders\\QaTestingSeeder
```

## Notes for QA Testing

- All users have a predefined email format: {role}@iwconnect.com (e.g., admin@iwconnect.com)
- Facilities have city names and 10 spots each
- The Regular User has a reservation for spot #1 in Skopje facility for tomorrow
- The data structure follows the real production schema 