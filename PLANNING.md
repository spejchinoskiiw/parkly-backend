# Parkly Backend - MVP Planning Document

## Overview

This document outlines the planning and scope for developing the Minimum Viable Product (MVP) of the Parkly Backend API within the 24-hour IWConnect Hackathon timeframe. It details the core features, development approach, resource allocation, and milestone targets to guide the implementation process.

## 24-Hour MVP Scope

The primary goal is to deliver a functional backend API that supports the essential features of a corporate parking reservation system. The MVP will focus on:

### Core MVP Features

1. **User Authentication & Role Management**
   - User registration with company email validation (@companyemail)
   - User login/authentication
   - Implementation of 3 distinct roles: Admin, Manager, User
   - Role-based access control

2. **Location & Parking Spot Management**
   - Create and manage parking locations (Admin)
   - Add, edit, and remove parking spots (Admin/Manager)
   - Associate parking spots with locations
   <!-- - QR code generation for parking spots --> QR codes are not strictly needed on backend for checkout/checkin

3. **Reservation System**
   - Create reservations for specific date/time and parking spot
   - View reservations (with appropriate role-based filtering)
   - Edit/cancel reservations (with appropriate permissions)
   - Prevent double-booking conflicts

4. **Check-in/Check-out Functionality**
   - Check-in 
   - Check-out process
   - Track spot occupancy status

### Out of Scope for MVP

The following features are considered important but will be postponed for future development:

- Recurring reservations
- Waitlist functionality
- Advanced analytics and reporting
- Push notifications
- External calendar integrations
- Mobile app development (separate repository)

## Development Approach

### Architecture

- RESTful API design using Laravel's resource controllers
- JWT or Sanctum for authentication
- MySQL database with optimized schema
- Repository pattern for data access

### Database Schema (Core Tables)

1. **users**
   - id (PK)
   - name
   - email (unique, company domain validated)
   - password (hashed)
   - role (enum: admin, manager, user)
   - created_at, updated_at

2. **locations**
   - id (PK)
   - name
   - address
   - manager_id (FK to users)
   - created_at, updated_at

3. **parking_spots**
   - id (PK)
   - location_id (FK to locations)
   - spot_identifier (e.g., "A1", "B12")
   - qr_code (unique identifier)
   - status (enum: available, reserved, occupied)
   - created_at, updated_at

4. **reservations**
   - id (PK)
   - user_id (FK to users)
   - parking_spot_id (FK to parking_spots)
   - start_time (datetime)
   - end_time (datetime)
   - status (enum: active, completed, cancelled)
   - created_at, updated_at

5. **check_ins**
   - id (PK)
   - reservation_id (FK to reservations)
   - check_in_time (datetime)
   - check_out_time (datetime, nullable)
   - created_at, updated_at

## Time Allocation

The 24-hour development period will be divided into the following phases:

### Phase 1: Setup & Foundation (3 hours)
- Project initialization
- Database design and migrations
- Authentication system setup
- Base API structure

### Phase 2: Core Feature Development (15 hours)
- User & Role Management (3 hours)
- Location & Parking Spot Management (4 hours)
- Reservation System (5 hours)
- Check-in/Check-out Functionality (3 hours)

### Phase 3: Testing & Refinement (4 hours)
- Unit and feature testing
- API validation and error handling
- Performance optimization
- Security checks

### Phase 4: Documentation & Finalization (2 hours)
- API documentation
- Setup instructions
- Final review and polishing

## Development Milestones

### Milestone 1 (Hour 3): Project Foundation
- Laravel project setup complete
- Database migrations implemented
- Authentication system functional
- Basic API structure defined

### Milestone 2 (Hour 8): User & Location Management
- User registration/login API working
- Role-based permissions implemented
- Location management endpoints functional
- Parking spot management API complete

### Milestone 3 (Hour 16): Reservation System
- Reservation creation API working
- Reservation listing with filters implemented
- Modification/cancellation functionality complete
- Conflict prevention logic implemented

### Milestone 4 (Hour 20): Check-in/Check-out System
- QR code generation system implemented
- Check-in/check-out endpoints functional
- Spot status tracking working correctly

### Milestone 5 (Hour 24): Complete MVP
- All testing completed
- Documentation finalized
- API fully functional and ready for demo

## Technical Considerations

### Performance
- Implement database indexing for frequently queried fields
- Use eager loading to prevent N+1 query issues
- Optimize spot availability queries

### Security
- Input validation on all endpoints
- Proper authorization checks at controller and service levels
- Protection against common vulnerabilities (CSRF, XSS, SQL injection)
- Rate limiting for authentication endpoints

### API Structure

The API will follow this structure:

```
/api/auth
  - POST /register
  - POST /login
  - POST /logout

/api/users
  - GET /
  - GET /{id}
  - PUT /{id}

/api/locations
  - GET /
  - POST /
  - GET /{id}
  - PUT /{id}
  - DELETE /{id}

/api/locations/{locationId}/spots
  - GET /
  - POST /
  - GET /{id}
  - PUT /{id}
  - DELETE /{id}
  - GET /{id}/qrcode

/api/reservations
  - GET /
  - POST /
  - GET /{id}
  - PUT /{id}
  - DELETE /{id}

/api/reservations/{reservationId}/check-in
  - POST /

/api/reservations/{reservationId}/check-out
  - POST /
```

## Quality Assurance

To ensure a robust MVP despite the time constraints:

- Implement automated tests for critical paths (authentication, reservations)
- Use Laravel's validation system to ensure data integrity
- Implement proper error handling and logging
- Conduct manual testing for key user flows

## Contingency Planning

If time becomes a constraint, the following prioritization will be applied:

1. **Must Have**
   - User authentication and role system
   - Basic location and parking spot management
   - Simple reservation creation and listing

2. **Should Have**
   - Reservation editing and cancellation
   - QR code generation

3. **Nice to Have**
   - Check-in/check-out functionality
   - Advanced filtering and sorting

## Post-Hackathon Roadmap

After the initial MVP, planned enhancements include:

1. Implement recurring reservations
2. Add waitlist functionality
3. Develop notifications system
4. Integrate with external calendars
5. Enhance admin dashboard with analytics
6. Implement mobile app features

## Conclusion

This plan provides a focused approach to developing the Parkly backend API within the 24-hour hackathon constraint. By prioritizing core functionality and maintaining clear milestones, the team aims to deliver a functional MVP that demonstrates the key value proposition of the Parkly system while establishing a solid foundation for future development.