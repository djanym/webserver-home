# Webserver Home Manager - LLM Agent Guidelines

Backend implementation directives are in `guidelines-backend.md`.

- MUST load and follow `SKILL.md` for non-trivial planning/execution quality.
- MUST load `project-features.txt` before implementation tasks to understand active features, flows, files, and functions.
- MUST keep this file focused on general rules and architecture constraints.

---

# General Description

Webserver Home Manager is a browser dashboard for managing local web projects and Apache virtual host configuration through a file-driven backend.

## Core principles:

- No database.
- Config-driven behavior.
- Managed projects remain independent and portable.
- App folder and managed project folders stay separated.
- App is located in a separate folder from managed projects
- App manages project metadata and Apache vhost files through a file/config-driven backend

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

---

# Source of Truth by Purpose

- `project-features.txt`: feature map, runtime behavior, route flow, involved files/functions.
- `guidelines-backend.md`: backend implementation contract and backend-specific constraints.
- `guidelines-cs.md`: coding style and naming/commenting/formatting rules.
- `guidelines.md` (this file): global architecture constraints and agent behavior rules.

---

# Architecture Constraints

- Runtime model: frontend SPA + backend PHP JSON API.
- Configuration source: files in `backend/config` and public app config.
- Module boundaries must stay strict:
  - Backend modules in `backend/modules/{module-name}`.
  - Frontend modules in `frontend-src/js/app/modules/{module-name}`.
- Avoid hardcoded machine-specific paths in feature logic.

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

# Backend Global Rules

- Backend root is `backend`.
- API-only backend (JSON responses only).
- Route declaration files must contain route mapping only (no business logic).
- Reuse shared request-flow contracts in `backend/inc` for validation, errors, and response emission.

---

# Frontend Global Rules

- SPA workflow only (no full-page reload flow).
- API communication must use shared request service.
- Keep module APIs and module UI code inside their module folders.

---

# Design and Change Rules

- Keep code minimal, practical, and maintainable.
- Prefer config-driven logic over hardcoded behavior.
- Do not add unnecessary abstractions/framework complexity.
- Keep backward compatibility unless change request says otherwise.

---

# LLM Agent Rules

Always:

- Read `project-features.txt` first for any feature or bug task.
- Follow module/folder structure already used in the repository.
- Load `guidelines-cs.md` before generating or editing code.
- For backend work, load `guidelines-backend.md` before editing handlers/modules.
- Reuse existing validation/error/response plumbing instead of duplicating it.
