# Webserver Home Manager — LLM Agent Guidelines

# General Description

Webserver Home Manager is a **web-based dashboard** for managing **local web development projects** and **Apache virtual hosts**.

- App runs in browser
- App located in **separate folder**
- Projects located in **server root**
- App manages projects **externally**
- Each project = independent website

App automates:

- Project creation
- Folder structure creation
- Virtual host configuration
- Project import
- Project management

---

# Terminology

| Term | Description |
|------|-------------|
| App | Webserver Home Manager dashboard |
| Project | Individual website project |
| Server Root | Parent directory containing projects |
| Project Config | `project-config.php` inside project |
| Server Config | `server-config.php` in server root |

---

# Architecture

## Config-Driven (No Database)

- No database
- File-based configuration

### server-config.php

Stores:

- Project list
- Minimal metadata

### project-config.php

Stores:

- Project metadata
- Domain
- Paths
- Settings

## Rules

- Each project independent
- Projects portable
- Config separation required

---

# Structure

## Server Root

/server-root/
├── project-1/
├── project-2/
└── server-config.php

## App Location

/server-root/
├── webserver-home/

Rules:

- App NOT inside project
- Projects NOT inside app
- App manages parent folder

---

# Project Folder Structure

/server-root/
├── my-project/
│   ├── docs/
│   ├── [website-domain-name]/
│   └── project-config.php

Directories:

- `[website-domain-name]/` — where root of website.
- `docs/` — project additional files such as media, docs, etc.

Explanation: project name can be not the same name as domain. For example, `my-project` can be `petshop.com`. And virtual host can be `petshop.server.local`. All of this should be configurable.

---

# Apache Logic

App must:

- Create virtual hosts
- Remove virtual hosts
- Update virtual hosts
- Restart Apache
- Map domains to projects

Each project:

- Separate virtual domain
- Separate document root

---

# Tech Stack

## Core

- PHP 8.3
- HTML
- CSS
- JavaScript
- React (optional, lightweight)

## Environment

- Apache (Homebrew)
- macOS
- No database required

## Architecture

- Backend = PHP API
- Frontend = SPA
- Communication = AJAX / Fetch
- No heavy frameworks

---

# App Structure

/backend - for backend api.
/frontend-src - for js, css, images, and other sources which should be generated and exported to `frontend-public`.
/frontend-public - public folder. Assets will be generated from frontend-src.

## Module-First Structure

- Backend modules: `/backend/modules/{module-name}`
- Frontend modules: `/frontend-src/js/app/modules/{module-name}`
- Each module owns its own routing and feature logic
- Shared backend classes/utilities stay in `/backend/inc`, `/backend/config`, `/backend/routes.php`
- Shared frontend request wrapper stays in `/frontend-src/js/app/services/api.js`

---

# Backend

## Rules

- Backend root: `/backend`
- Backend independent
- API only
- JSON responses only

## Routing

- Uses AltoRouter library
- Route bootstrap in `/backend/routes.php`
- Module route definitions in `/backend/modules/{module-name}/routes.php`

Rules:

- No logic in routes
- `backend/routes.php` should only bootstrap and register module routes
- Module handlers should contain endpoint behavior (for example in `handlers.php`)

## Architecture

- No strict MVC
- Feature-based classes
- Module-specific functionality should live in module folders
- Shared/common classes may be placed in `inc` folder

## Config

/backend/config

Rules:

- Use environment variables
- No hardcoded paths

---

# Frontend

## Rules

- SPA only
- No reloads
- API communication only

Allowed:

- Vanilla JS
- jQuery
- React (lightweight)

React Rules:

- Functional components
- Minimal structure
- No heavy frameworks
- No complex state managers

## Frontend Module Rules

- Keep the shared request/response wrapper in `/frontend-src/js/app/services/api.js`
- Put module API helpers in `/frontend-src/js/app/modules/{module-name}/{module-name}-api.js`.
- Api functions should be named `api{ActionName}`
- Module API helpers should expose `(apiRoute, data, method = 'POST')`-style wrappers
- Put module UI entry in `/frontend-src/js/app/modules/{module-name}/{module-name}.js`
- Keep module components inside the module folder

---

# CSS

Source files in `/frontend-src/src/scss/`.
Rules:

- Use SCSS
- No inline styles
- Shallow selectors
- Reusable styles

---

# Build System

## Tools

- Gulp
- Webpack
- Babel
- SCSS

## Gulp file

frontend-src/gulpfile.mjs - for generating frontend assets.

---

### JavaScript / React

- Bundle JS / JSX
- Transpile via Babel
- Bundle via Webpack

Source files in `frontend-src/js/app/`.
Output files in `frontend-public/assets/js/build/`.
Use `run:gulp:js` npm script to build JS.

---

### CSS

Source files in `frontend-src/src/scss/`.
Output files in `frontend-public/assets/css/`.
Use `run:gulp:css` npm script to build CSS.

### Images

Store images in `frontend-src/src/images/`.
All images will be copied to `frontend-public/assets/images/` during build.

---

# Design Rules

- No database
- Projects portable. Means can be copied on another machine with this app and it should work.
- Config-driven
- Simple architecture

---

# LLM Agent Rules

Always:

- Keep code minimal
- Follow folder structure
- Keep backend independent
- Use config-driven logic
- Avoid complexity
- Avoid heavy frameworks
- Use JSON API
- Keep projects portable
