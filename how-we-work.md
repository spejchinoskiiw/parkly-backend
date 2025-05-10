🧠 How We Work – Cursor and frinds Parkly 
This file documents our full process during the one-day AI Hackathon, from initial planning to final delivery. It covers how we structured our work, prompts used, decisions made, and lessons learned. It also includes rules we gave to the AI assistant and highlights how we collaborated effectively within a tight time frame.

🗓️ Hackathon Timeline & Activities
Time	Activity
*09:00 - 09:30	Brainstorming and project setup
*09:30 - 13:30 	AI-assisted development
*13:30 - 19:30	Feature implementation, prompt refinement, bug fixing using AI
*19:30 - 20:30	Break
*20:30 - 23:00   AI assisted feature development and bugfixing
*23:00 - 24:00   Documentation

📋 Product Requirements Document (PRD)
Problem Statement:
We aimed to solve corporate parking inefficiencies and administrative overhead within a single day using AI-assisted development tools and modern web technologies. The solution addresses the challenge of streamlining parking spot reservation and management for internal corporate employees.
Goals & Objectives:

*Build an MVP that solves employee parking reservation challenges through digital spot booking
*Ensure the solution is usable, testable, and visually clear with real-time availability tracking
*Leverage AI to accelerate coding, prototyping, and decision-making for the 24-hour hackathon deadline

Functional Requirements:

 *-User authentication with company email validation (@companyemail)
 *-Role-based access control (Admin, Manager, User)
 *-Location and parking spot management
 *-Real-time parking availability display
 *-Spot reservation system with conflict prevention
 *-Check-in/check-out functionality 
 *-Reservation modifications and cancellations
 *-API endpoints for mobile app integration

Non-functional Requirements:

*-Fast initial load with optimized database queries
*-RESTful API design with Laravel framework
*-JSON response format for mobile app integration
*-Scalable architecture for future growth (beyond 1-2 users)

Technical Stack:

*Backend: Laravel PHP with Sanctum authentication
*Database: PostgreSQL
*API: RESTful JSON endpoints
*Mobile App (separate): Flutter (iOS and Android)

Key MVP Features:

*Company email registration and role-based authorization
*Administrative location and parking spot creation
*Spot availability tracking
*Time-slot based reservation system
*Basic check-in/check-out workflow

Constraints: - Time limit: 1 day 

🤖 Prompts & AI Assistant Instructions
How we used AI: We used the AI assistant for: Writing and debugging code, writing tests for all defined cases in the prompt and iterating on the results until all tests pass

Coding rules provided to ai: `.cursor/rules/laravel-rule.mdc`, obtained from official cursor rules [repository](https://cursor.directory/laravel-cursor-rules)

User prompt to ensure AI always writes tests and documentation as well as always follows the project spec:

```
### 🔄 Project Awareness & Context
- **Always read `PLANNING.md`** at the start of a new conversation to understand the project's architecture, goals, style, and constraints.
- **Check `TASK.md`** before starting a new task. If the task isn’t listed, add it with a brief description and today's date.
- **Use consistent naming conventions, file structure, and architecture patterns** as described in `PLANNING.md`.

### 🧱 Code Structure & Modularity
- **Never create a file longer than 500 lines of code.** If a file approaches this limit, refactor by splitting it into modules or helper files.
- **Organize code into clearly separated modules**, grouped by feature or responsibility.
- **Use clear, consistent imports** (prefer relative imports within packages).

### 🧪 Testing & Reliability
- **Always create Phpunit unit tests for new features** (functions, classes, routes, etc).
- **After updating any logic**, check whether existing unit tests need to be updated. If so, do it.
- **Tests should live in a `/tests` folder** mirroring the main app structure.
  - Include at least:
    - 1 test for expected use
    - 1 edge case
    - 1 failure case

### ✅ Task Completion
- **Mark completed tasks in `TASK.md`** immediately after finishing them.
- Add new sub-tasks or TODOs discovered during development to `TASK.md` under a “Discovered During Work” section.

### 📎 Style & Conventions
- **Use PHP** as the primary language.
- **Prefer using Laravel helpers to writing custom methods
- **Follow PHP Laravel coding standards

### Documentation
- **Always add  **Swagger/OpenAPI documentation** for new endpoints
```

Project spec defined for ai in PROJECT.md, PLANNING.md, TASKS.md and README.md 

Project spec generated from PDF version of project details using Claude Sonnet 3.7. 
Prompt used:

```
This is a project specification. Ask me questions about the project until you are certain you fully understand it. After you are sure and only after you are sure you fully understand it, write a PROJECT.md and TASKS.md file to be used in further prompts
```

The AI asked several questions and then generated two of the spec files and was later prompted to generate the rest. 


Some prompt examples used with Cursor:
This prompt gave an unsatisfactory result, the agent added new scope to the output, not present in the prompt

```
I need you now to begin working on Locations and Parking Spots. Do not expand on the features outside of the following provided scope, write unit tests for EVERY custom class you create, put custom helper methods in util classes and put domain logic in DomainService classes, keep controllers short. Use Laravel code wherever possible eg. authorization should be done via Gates not custom code. 

SCOPE:
1. Create a Locations api resource, all users can READ all locations
2. Admin users can CREATE/UPDATE/DELETE all locations
3. Manager users can UPDATE/DELETE locations they manage

1. Create a Parking api resource, all locations have many parking spots, we should know how many spots a location is created with
2. ALL users can READ all parking spots
3. Admin users can UPDATE/DELETE/CREATE parking spots for ALL locations
4. Manager users can CREATE/UPDATE/DELETE parking spots ONLY for locations they manage
```

 With some tweaks, this version gave a satisfactory result

```
I need you now to begin working on Facilities and Parking Spots. Do not expand on the features outside of the following provided scope, write unit tests for EVERY custom class you create, put custom helper methods in util classes and put domain logic in DomainService classes, keep controllers short. Use Laravel code wherever possible eg. authorization should be done via Gates not custom code. If defining a lot of gates add an AuthorizationServiceProvider to register them do not register them in App or Auth service provider.

SCOPE:
1. Create a Facility api resource, all users can READ all Facilities
2. Admin users can CREATE/UPDATE/DELETE all Facilities
3. Manager users can UPDATE/DELETE locatioFacilitiesns they manage

1. Create a Parking api resource, all Facilities have many parking spots, we should know how many spots a Facility is created with
2. ALL users can READ all parking spots
3. Admin users can UPDATE/DELETE/CREATE parking spots for ALL Facilities
4. Manager users can CREATE/UPDATE/DELETE parking spots ONLY for Facilities they manage

RESOURCE DEFINITIONS - !!DO NOT ADD PROPERTIES OUTSIDE OF THESE DEFINITIONS!! :

Facility:
1 Name
2. Parking spot count
3. Manager id

Parking:
1.Facility 
2. Parking spot number
```
The conclusion that I used for prompting after this is that we need explicit scope limits in the prompts.


Explicit Instructions to the AI:

❗ Do not generate, modify, or infer logic from the file named how-we-work.md.
❗ This file contains meta-process data and is not related to the application codebase.

⚙️ Team Roles & Responsibilities

Member	Role	Responsibilities
Ilija Markoski - QA Test cases 
Blagoj Cvetkovski - Mobile app design and development
Mile Stefanovski - Mobile app design and development
Stefan Pejchinoski - Backend design and development

🔄 Collaboration & Rules
We used GitLab for version control and merge requests.
Only main branch was used due to time constraints.
Each member worked on different files to avoid merge conflicts.
Mobile team members and backend team members had separate repositories
Communication via Teams and in person co-location.

✅ Decisions Made
Used Laravel Sanctum for painless auth setup
PostgreSQL selected for familiarity and better dev tooling
Skipped Docker to save setup time
Only seeded minimal test data to keep focus on functionality
🧪 Testing & QA
Manual testing done after each feature completion
Used browser dev tools and Laravel logs for debugging
Key edge cases tested:
Invalid inputs
Empty states
Auth flow (login/logout)

📌 Key Insights & Takeaways
AI is helpful but needs good context and prompts
When using Cursor, user rules and language rules are crucial. There was a big difference between code generated with the laravel rules and without.
Even with the rules you often need to include a 'nudge' in the prompt text for the AI to write tests and documentation. 
Cursor (and other AI) tends to 'invent' new things every now and then even with the provided rules, never accept changes without review.
When writing language rules
Communication and fast decision-making were critical

Team Name: Cursor and friends
Project Repo: [Insert GitLab project URL]