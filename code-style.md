Code Style & Structure Rules

This document defines coding standards, architecture rules, and constraints for the Webserver Home Manager project.
The LLM agent must follow these rules when generating or modifying code.


⸻

2. General Code Style

Principles
•	Write clean, minimal, readable code
•	Prefer simple solutions over complex abstractions
•	Keep files small and focused
•	Keep functions short and single-purpose
•	Use consistent naming conventions
•	Use predictable file structure
•	Avoid unnecessary abstractions
•	Avoid over-engineering

⸻

3. Backend Rules (PHP)

Backend Structure
•	Backend root folder: /backend
•	Backend must be fully independent
•	Backend must function without frontend dependency
•	Backend acts strictly as API

Routing
•	Routing handled via AltoRouter
•	Routes defined in:

/backend/routes.php

Rules:
•	All routes must be added to routes.php
•	Keep route files clean
•	No business logic inside routes

Architecture
•	No strict MVC required
•	Prefer feature-based classes
•	Each functionality should be separated into its own class

Backend Rules
•	Follow PSR-12
•	Return JSON only
•	Use consistent response structure
•	Validate all inputs
•	Sanitize all inputs
•	Avoid duplicated logic
•	Keep controllers thin
•	Keep business logic in classes

Configuration

Configuration folder:

/backend/config

Rules:
•	Use environment variables
•	Avoid hardcoded values outside configuration files.
•	Keep config readable
•	Keep config modular

⸻

4. Frontend Rules

Architecture
•	Frontend must work as Single Page Application (SPA)
•	No page reloads
•	Use AJAX / Fetch for API calls
•	Frontend communicates with backend API only

Frontend Folder

Public frontend:

/frontend-public

Source files:

/frontend-src


⸻

JavaScript Rules
•	Use modular JavaScript
•	Keep modules small
•	Avoid global scope pollution
•	Keep logic simple
•	Avoid unnecessary dependencies

Allowed:
•	Vanilla JS
•	jQuery
•	Lightweight React

⸻

React Rules

React may be used only if simple and lightweight

Rules:
•	No heavy React architecture
•	No complex folder structure
•	No Redux
•	No heavy state managers
•	No large UI frameworks

Allowed Structure Example:

/frontend-src
/components
/templates
app.js

Guidelines:
•	Keep components small
•	Use functional components
•	Avoid complex abstraction
•	Avoid over-componentization

⸻

5. CSS / Styling Rules

CSS Architecture
•	Use SCSS
•	Do not write styles directly in production files

Source:

/frontend-src

Compiled output:

/frontend-public

Build system:
•	gulpfile.mjs
•	Babel

⸻

Styling Guidelines
•	Use semantic class names
•	Keep selectors shallow
•	Avoid deep nesting
•	Prefer reusable styles
•	Prefer component-based styles
•	Avoid inline styles
•	Keep CSS readable

⸻

6. Build System

Build Tools:
•	Gulp
•	Babel
•	SCSS Compiler

Build Tasks:
•	Compile SCSS → CSS
•	Compile JS → JS bundle
•	Optimize images
•	Copy assets

Files:

gulpfile.mjs


⸻

7. Project Structure Rules

General Rules
•	Organize by feature
•	Keep responsibilities separated
•	Keep backend and frontend independent
•	Use consistent naming

Example:

/backend
/frontend-public
/frontend-src


⸻

8. Comments & Documentation

Rules:
•	Comment why, not what
•	Avoid obvious comments
•	Keep comments short
•	Keep documentation practical

Good:

// Avoid duplicate domain creation

Bad:

// increment i
$i++;


⸻

9. API Response Rules

Backend must return consistent JSON:

Example:

{
"success": true,
"data": {},
"message": ""
}

Error Example:

{
"success": false,
"error": "Project not found"
}


⸻

10. Output Expectations

Generated code must be:
•	Production-ready
•	Clean and readable
•	Easy to maintain
•	Easy to extend
•	Minimal and efficient

⸻

11. Additional Technical Constraints
    •	Avoid heavy dependencies
    •	Avoid complex build systems
    •	Avoid unnecessary libraries
    •	Prefer native browser APIs

⸻

12. Project Data

Project-specific data stored in:

project-config.php

Server-level data stored in:

server-config.php

No database required.

Database usage is optional only for future features.

⸻

13. Summary for LLM Agent

Always:
•	Follow config-driven architecture
•	Keep frontend lightweight
•	Keep backend API-based
•	Avoid complexity
•	Maintain clean structure
•	Keep code modular
•	Keep code simple