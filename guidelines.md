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

---

# Backend

## Rules

- Backend root: `/backend`
- Backend independent
- API only
- JSON responses only

## Routing

- Uses AltoRouter library
- Routes in:

/backend/routes.php

Rules:

- No logic in routes
- Add routes only in routes.php

## Architecture

- No strict MVC
- Feature-based classes
- Classes should be placed in `inc` folder.

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

---

# CSS

## Source

/frontend-src/scss/

## Output

/frontend-public/assets/css/

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

## File

frontend-src/gulpfile.mjs - for generating frontend assets.

---

### JavaScript / React

- Bundle JS / JSX
- Transpile via Babel
- Bundle via Webpack

Input:

frontend-src/src/js/

Output:

frontend-public/assets/js/

---

### Images

Copy:

frontend-src/src/images/

To:

frontend-public/assets/images/

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
