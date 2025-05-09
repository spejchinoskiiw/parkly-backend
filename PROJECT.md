# Parkly - Parking Reservation App

## Project Overview
Parkly is a smart, mobile-first solution designed to streamline the process of reserving and managing corporate parking spots. This MVP is being developed for the IWConnect Hackathon 2025, with a 24-hour development timeframe. The application addresses inefficiencies in current parking systems, reduces administrative overhead, and enhances the employee experience.

## Target Users
- Internal corporate employees
- Facility managers
- Administrators

## Technology Stack
- **Backend**: Laravel PHP
- **Mobile App**: Flutter (iOS and Android)

## Key Objectives
1. Improve employee convenience through digital parking spot reservations
2. Optimize parking spot utilization with real-time availability
3. Reduce administrative workload for facilities teams
4. Enable policy compliance through role-based permissions

## MVP Features

### 1. User Authentication & Profile Management
- User registration limited to company email domains (@companyemail)
- Secure login with email/password
- Three distinct user roles:
  - **Admin**: Full system access, manage all locations and reservations
  - **Manager**: Manage specific locations and related reservations
  - **User**: Create, view, and manage personal reservations

### 2. Parking Management
- Admin ability to create and manage locations
- Admin/Manager ability to add parking spots to locations
- Parking spot details including identifier and QR code generation

### 3. Real-Time Parking Availability Display
- View available, reserved, and occupied parking spots
- Interactive display format (map or list view)
- Dynamic updates based on check-ins, check-outs, and reservations

### 4. Spot Reservation System
- Reserve specific parking spots
- Select date and time slot
- Prevention of double-booking
- Confirmation system

### 5. Reservation Modifications and Cancellations
- Edit existing reservations (date, time, spot)
- Cancel reservations
- Role-based editing permissions

### 6. Check-In and Check-Out Functionality
- QR code scanning for check-in verification
- Check-out process to release spots
- Physical QR codes placed at each parking spot

## Non-Functional Requirements

### 1. Performance and Scalability
- Designed for small initial user base (1-2 users for MVP)
- Architecture should support future scaling

### 2. Mobile Responsiveness
- Optimized for both iOS and Android smartphones
- Intuitive, touch-friendly interface
- Fast-loading content and seamless interactions

### 3. Time Constraints
- Complete development within 24-hour hackathon timeframe
- Focus on core MVP features first

## Future Enhancements (Post-MVP)
- Recurring reservations
- Waitlist functionality for fully booked periods
- Push notifications and email reminders
- Advanced parking spot allocation rules
- Comprehensive admin dashboard with analytics
- Calendar integrations
