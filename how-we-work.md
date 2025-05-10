This README explains the process of developing the Parkly app backend using AI agents

Before initial development PLANNING.md, PROJECT.md and README.md were generated using Claude Sonnet3.7 AI

First prompt:
This is a project specification. Ask me questions about the project until you are certain you fully understand it. After you are sure and only after you are sure you fully understand it, write a PROJECT.md and TASKS.md file to be used in further prompts
 
 This prompt was mostly a sucess as the AI asked relevant questions before generating the files

 Example asked questions: 
 Is this app primarily meant for internal corporate use or will it be available to the public?
Do you have any specific technologies or frameworks in mind for development?
Are there any specific deadlines for this hackathon project?
What is the expected user base size initially and for scaling?
Are there any specific features from the competitor examples that you particularly want to prioritize?
Will this app need to integrate with any existing company systems (like HR databases for employee verification)?
Are there any specific security requirements beyond what's mentioned in the document?
How many parking lots/spots is this system expected to manage?

Answering the questions seemed to provide enough context for the AI to generate the files. 
It did make some poor decisions, like suggesting making load balancers and Oauth2 for the MVP, but enforcing the idea that this was a 24 hour hackathon MVP and we need a minimal subset of features seemed to snap it out of this.

After I was satisfied with the documents I followed up w with: 
using this planning document write a TASKS.md file defining concrete tasks that should be completed in order to create the MVP

These files were supplied to Cursor along with the files  .cursor/laravel-rule.mdc which defines some rules for working with Laravel and the following user prompt to be sent on every request:

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

The results of this were not terrible but also not good, Cursor still preferred to write custom logic although it did adhere to some Laravel standards - most often it did not write documentation on its own though and always had to be nudged in that direction. 

An example of a suprising result was this prompt: 
I need you now to begin working on Locations and Parking Spots. Do not expand on the features outside of the following provided scope, write unit tests for EVERY custom class you create, put custom helper methods in util classes and put domain logic in DomainService classes, keep controllers short. Use Laravel code wherever possible eg. authorization should be done via Gates not custom code. 

SCOPE:
1. Create a Locations api resource, all users can READ all locations
2. Admin users can CREATE/UPDATE/DELETE all locations
3. Manager users can UPDATE/DELETE locations they manage

1. Create a Parking api resource, all locations have many parking spots, we should know how many spots a location is created with
2. ALL users can READ all parking spots
3. Admin users can UPDATE/DELETE/CREATE parking spots for ALL locations
4. Manager users can CREATE/UPDATE/DELETE parking spots ONLY for locations they manage

What I discovered was that although detailed, this prompt seemed to lack some necessary context that would inform Cursor when to stop, since it continued to make changes until the automatic pause of commands after 25 api calls. 

With some edits, this version: 

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

I also discovered that the laravel context needs to be manually added, which means in the previous requests it was missing. After this, Cursor adhered to Laravel standards much more. For some reason it always tries to register old (legacy) Laravel service providers even with the laravel context added. IF this is allowed it will spin out of control trying to fix the errors it caused.