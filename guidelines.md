# Webserver Home Manager - LLM Agent Guidelines

Backend implementation directives for agents are in `guidelines-backend.md`.

# General Description

Webserver Home Manager is a web dashboard for managing local web development projects and Apache virtual hosts.

- App runs in browser
- App is located in a separate folder from managed projects
- Projects are created/imported under a configurable server root
- App manages project metadata and Apache vhost files through a file/config-driven backend
- Each project is an independent website

App automates:

- Project creation
- Project folder structure creation
- Apache virtual host file generation
- Project import and listing
- Project management actions

---

# Terminology

| Term | Description |
|------|-------------|
| App | Webserver Home Manager dashboard (`webserver-home`) |
| Project | Individual managed website under the configured projects root |
| Project Registry | Per-project metadata file (`project.registry.json`) in project root |
| Main Projects Registry | Global projects registry file (path from `path_to_projects_registry`) |
| Server Config | Backend config in `/backend/config` (`server-config.php`, `app-config.php`) |

---

# Architecture

## Config-Driven (No Database)

- No database
- File-based configuration and registries

## Rules

- Projects must remain independent and portable
- Runtime behavior must come from config files/env, not hardcoded machine paths
- Module boundaries should stay strict (backend modules + frontend modules)

---

# Structure

## Server Root (Example)

```text
/server-root/
|- project-1/
|  `- project.registry.json
|- project-2/
|  `- project.registry.json
`- .webserver-home/   (main projects registry folder)
```

## App Location (Example)

```text
/server-root/
|- webserver-home/
|  |- backend/
|  |- frontend-src/
|  `- frontend-public/
|- project-1/
`- project-2/
```

Rules:

- App is not inside a managed project
- Managed projects are not inside the app folder
- App manages projects through configured paths

---

# Tech Stack

## Core

- PHP 8.3
- HTML
- SCSS/CSS
- JavaScript (ES modules)
- React (lightweight functional components)

## Environment

- Apache (Homebrew)
- macOS
- No database required

## Runtime Architecture

- Backend = PHP JSON API
- Frontend = SPA
- Communication = Fetch/AJAX
- Build = Gulp + Webpack + Babel + Sass/PostCSS

---

# App Structure

- `/backend` - backend API, config, shared classes, modules
- `/frontend-src` - source assets (JS/JSX, SCSS, images, fonts)
- `/frontend-public` - built/public assets and app public config

## Module-First Structure

- Backend modules: `/backend/modules/{module-name}`
- Frontend modules: `/frontend-src/js/app/modules/{module-name}`
- Shared backend code: `/backend/inc`, `/backend/config`, `/backend/routes.php`
- Shared frontend request wrapper: `/frontend-src/js/app/services/api.js`

---

# Backend

Primary backend agent contract: `guidelines-backend.md`.

## Rules

- Backend root: `/backend`
- API-only backend
- JSON responses only
- No business logic in route declaration files

## Routing

- Router: AltoRouter
- Route bootstrap: `/backend/routes.php`
- Module route definitions: `/backend/modules/{module-name}/routes.php`
- Module routes are auto-discovered from `/backend/modules/*/routes.php`

## Execution Pipeline

- Route -> module handler (`handlers.php`) -> module class/service
- Reuse shared request-flow classes in `/backend/inc`
- For validation/errors/JSON response flow, follow class header contracts first:
  - `/backend/inc/Generic.php`
  - `/backend/inc/Validator.php`
  - `/backend/inc/AppError.php`

## Config

- Config files: `/backend/config/app-config.php`, `/backend/config/server-config.php`
- Use environment/config driven values
- Avoid hardcoded paths in feature code

---

# Frontend

## Rules

- SPA only
- No full-page reload workflow
- API communication via shared wrapper

## Frontend Module Rules

- Shared API wrapper: `/frontend-src/js/app/services/api.js`
- Module API helpers: `/frontend-src/js/app/modules/{module-name}/{module-name}-api.js`
- API helper naming: `api{ActionName}`
- Module UI entry: `/frontend-src/js/app/modules/{module-name}/{module-name}.js`
- Module components stay inside module folder

---

# CSS / SCSS

- Source SCSS: `/frontend-src/scss/`
- Output CSS: `/frontend-public/assets/css/`

Rules:

- Use SCSS source files
- No inline styles for app UI
- Keep selectors shallow and reusable

---

# Build System

## Tools

- Gulp
- Webpack
- Babel
- Sass/PostCSS

## Build Entry

- Build config/task runner: `/frontend-src/gulpfile.mjs`

### JavaScript / React

- Source entry: `/frontend-src/js/app/appEntry.js`
- Source modules: `/frontend-src/js/app/`
- Output bundle path: `/frontend-public/assets/js/build/`
- Script: `run:gulp:js`

### CSS

- Source: `/frontend-src/scss/`
- Output: `/frontend-public/assets/css/`
- Script: `run:gulp:css`

### Images and Fonts

- Source images: `/frontend-src/images/` -> `/frontend-public/assets/images/`
- Source fonts: `/frontend-src/fonts/` -> `/frontend-public/assets/fonts/`

---

# Code Style

All technical code-style rules are maintained in `guidelines-cs.md`.

- Always load and follow `guidelines-cs.md` before generating or editing code
- Backend-specific implementation/style constraints live in `guidelines-backend.md`
- Keep this file focused on architecture/runtime contracts, not low-level formatting rules

---

# Design Rules

- No database
- Projects must remain portable between machines
- Config-driven behavior
- Keep architecture simple and maintainable

---

# LLM Agent Rules

Always:

- Keep code minimal and practical
- Follow folder/module structure
- Keep backend API-only and JSON-only
- Prefer config-driven logic
- Avoid heavy frameworks and unnecessary complexity
- Keep projects portable
- Load `guidelines-cs.md` for code style and naming/commenting standards
- For backend tasks, load `guidelines-backend.md` before editing backend handlers/modules
- Reuse `Generic`, `Validator`, and `AppError` contracts instead of duplicating request/validation/error plumbing
