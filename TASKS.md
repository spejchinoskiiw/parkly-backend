# Parkly Backend - MVP Development Tasks

This document outlines the specific, concrete tasks for implementing the Parkly Backend API within the 24-hour hackathon timeframe. Tasks are organized by development phase and assigned estimated completion times to ensure the MVP is delivered efficiently.

## Phase 1: Setup & Foundation (Hours 0-3)

### Project Initialization
- [ ] Create Laravel project with composer
- [ ] Configure environment variables (.env)
- [ ] Set up Git repository and initial commit
- [ ] Install required packages (Sanctum, QR code generator)

### Database Design
- [ ] Create users migration with role field
- [ ] Create locations migration
- [ ] Create parking_spots migration with status field
- [x] Create reservations migration
- [ ] Create check_ins migration
- [ ] Define foreign key relationships
- [ ] Add appropriate indexes for performance

### Authentication Foundation
- [ ] Configure Sanctum for token authentication
- [ ] Create login/register controllers
- [ ] Implement company email validation (@companyemail)
- [ ] Set up middleware for role-based access

### Base API Structure
- [ ] Define API routes in api.php
- [ ] Create controller stubs for all resource endpoints
- [ ] Implement base Controller class with response formatting
- [ ] Set up exception handling for API responses

## Phase 2: Core Feature Development (Hours 3-18)

### User & Role Management (Hours 3-6)
- [ ] Create User model with role enum (admin, manager, user)
- [ ] Implement UserController with CRUD operations
- [ ] Create RegisterController with email domain validation
- [ ] Implement AuthController (login, logout)
- [ ] Create role-based middleware (isAdmin, isManager)
- [ ] Implement password hashing and validation
- [ ] Add API endpoint for user profile management
- [ ] Create form requests for validation
- [ ] Write tests for authentication flows

### Location & Parking Spot Management (Hours 6-10)
- [ ] Create Location model
- [ ] Implement LocationController with CRUD operations
- [ ] Create permissions for location management
- [ ] Implement ParkingSpotController
- [ ] Create relationship between locations and spots
- [ ] Implement spot status management logic
- [ ] Create QR code generation service
- [ ] Add endpoint to generate and retrieve QR codes
- [ ] Implement manager-specific location access
- [ ] Write tests for location and spot management

### Reservation System (Hours 10-15)
- [x] Create Reservation model
- [x] Implement ReservationController with CRUD operations
- [x] Create reservation validation logic
- [x] Build conflict detection algorithm
- [x] Implement date/time availability checking
- [x] Create endpoints for listing available spots by time
- [ ] Implement user-specific reservation listings
- [ ] Add reservation modification with permission checks
- [ ] Create cancellation functionality
- [ ] Implement reservation status management
- [x] Write tests for reservation flows

### Check-in/Check-out Functionality (Hours 15-18)
- [ ] Create CheckIn model
- [ ] Implement CheckInController
- [ ] Create QR code validation service
- [ ] Implement check-in endpoint with spot status update
- [ ] Create check-out endpoint with status reset
- [ ] Add validation for check-in/out permissions
- [ ] Implement reservation status updates on check-in/out
- [ ] Create spot occupancy tracking
- [ ] Write tests for check-in/out flows

## Phase 3: Testing & Refinement (Hours 18-22)

### Testing
- [ ] Complete unit tests for models
- [ ] Implement feature tests for critical endpoints
- [ ] Test role-based access controls
- [ ] Verify reservation conflict prevention
- [ ] Test QR code generation and validation
- [ ] Validate check-in/out functionality
- [ ] Run full test suite and fix issues

### API Refinement
- [ ] Implement proper error responses
- [ ] Add request validation for all endpoints
- [ ] Optimize database queries
- [ ] Add pagination for list endpoints
- [ ] Implement filtering options for reservations
- [ ] Add sorting parameters
- [ ] Ensure consistent JSON response format

### Security Enhancements
- [ ] Add rate limiting for auth endpoints
- [ ] Review and strengthen permission checks
- [ ] Implement token expiration policy
- [ ] Add security headers
- [ ] Review for SQL injection vulnerabilities
- [ ] Test for common security issues

## Phase 4: Documentation & Finalization (Hours 22-24)

### API Documentation
- [x] Document all endpoints in README or dedicated docs
- [ ] Create example API requests (curl/Postman)
- [ ] Document authentication flow
- [ ] Add error code explanations

### Final Review
- [ ] Run final test suite
- [ ] Verify all MVPs features are working
- [ ] Check for performance bottlenecks
- [ ] Ensure code consistency and standards
- [ ] Commit final changes and tag release

### Deployment Preparation
- [ ] Create database seeder with sample data
- [ ] Prepare installation instructions
- [ ] Document environment requirements
- [ ] Create demo admin account

## Priority Tasks (Must Complete)

If time becomes constrained, focus on these core tasks to ensure a functional MVP:

1. **Authentication & Authorization**
   - User registration with role support
   - Login functionality
   - Basic role permissions

2. **Core Data Management**
   - Location creation and management
   - Parking spot management
   - QR code generation

3. **Basic Reservation Functionality**
   - [x] Creating reservations
   - [ ] Listing reservations
   - [x] Simple conflict prevention

4. **Check-in Essentials**
   - Basic check-in with QR validation
   - Spot status tracking

## Evaluation Checkpoints

### Checkpoint 1 (Hour 3)
- [ ] Project scaffolding complete
- [ ] Database migrations created
- [ ] Authentication system functional

### Checkpoint 2 (Hour 10)
- [ ] User management complete
- [ ] Location and parking spot functionality working
- [ ] QR code generation implemented

### Checkpoint 3 (Hour 15)
- [ ] Reservation system fully functional
- [ ] Conflict prevention working
- [ ] User permissions properly enforced

### Checkpoint 4 (Hour 18)
- [ ] Check-in/out system implemented
- [ ] All core features functional

### Checkpoint 5 (Hour 22)
- [ ] All tests passing
- [ ] API optimized and refined
- [ ] Documentation in progress

### Final Checkpoint (Hour 24)
- [ ] Complete MVP delivered
- [ ] Documentation complete
- [ ] Ready for demonstration