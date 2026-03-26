# Code Style — LLM Agent Rules

## General

- Write minimal, clean, readable code
- Prefer simple solutions
- Avoid over-engineering
- Avoid duplicated logic
- Keep functions small and single-purpose
- Use consistent naming
- Avoid unnecessary dependencies

---

## Backend (PHP)

- PHP 8.3
- Backend root: `/backend`
- Backend is API only
- Follow PSR-12
- Return JSON only
- Validate and sanitize all inputs

### Routing

- Use AltoRouter
- Routes file: `/backend/routes.php`
- No business logic in routes

### Structure

- Feature-based classes
- One responsibility per class
- Keep controllers thin
- Business logic in classes
- Classes must be placed in: `/backend/inc`

### Config

- Config folder: `/backend/config`
- Use environment variables
- No hardcoded values outside config

---

## API Response Format

Success:

```json
{
  "success": true,
  "data": {},
  "message": ""
}

Error:

{
  "success": false,
  "error": "message"
}
```

⸻

# Frontend
	•	SPA only
	•	No page reloads
	•	Use AJAX / Fetch
	•	Communicate with backend API only

## Folders:
```
/frontend-src
/frontend-public
```

⸻

# JavaScript
	•	Use modular JS
	•	Avoid globals
	•	Keep modules small
	•	Vanilla JS preferred
	•	jQuery allowed
	•	React allowed (lightweight only)

⸻

# React (Optional)
	•	Functional components only
	•	No Redux
	•	No heavy frameworks
	•	Minimal structure

⸻

# CSS
	•	Use SCSS only
	•	No inline styles
	•	Avoid deep nesting

⸻

# Build
	•	Use gulpfile.mjs
	•	SCSS → CSS
	•	JS bundling
	•	Image copy
	•	Watch changes

⸻

# Comments
	•	Comment why, not what
	•	Use // comments
	•	Use PHPDoc for classes/methods only
	•	File header required
	•	End comments with dot.

⸻

# Structure

/backend
/backend/inc
/backend/config
/frontend-src
/frontend-public


⸻

# Output Requirements

Generated code must be:
	•	Production-ready
	•	Minimal
	•	Maintainable
	•	Extendable

