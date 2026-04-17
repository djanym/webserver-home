# Backend LLM Agent Contract

Scope: `/backend` only. Output: JSON API responses only.

## Execution Model

- Routing is declaration-only: register endpoints in `/backend/routes.php` and `/backend/modules/{module-name}/routes.php`.
- Endpoint behavior lives in module handlers/classes; do not place business logic in route files.
- Shared runtime utilities are in `/backend/inc`; import and reuse, do not re-implement cross-cutting utilities.

## Backend Foundations

- PHP version target: 8.3.
- Backend root: `/backend`.
- Backend is API-only (no server-rendered view layer).
- Feature implementation should be class/service-driven, with thin handlers.
- Keep one responsibility per class/service where practical.

## Backend Style and Structure

- Follow PSR-12 formatting and whitespace rules.
- Use typed parameters and return types where practical.
- Keep endpoint behavior in handlers/classes, not in `routes.php`.
- Keep module boundaries strict and avoid cross-module direct mutations.
- Prefer `declare(strict_types=1);` for new files unless the surrounding module conventions conflict.

## Routing and Module Structure

- Use AltoRouter for route registration.
- Main route bootstrap: `/backend/routes.php`.
- Module route files: `/backend/modules/{module-name}/routes.php`.
- Handlers in each module should delegate to module classes/services.
- Keep route files declaration-only.

## Core Class Contracts (Read Headers First)

- `/backend/inc/Generic.php` - base request-flow contract (validation wrappers, error container wiring, success/error JSON emitters).
- `/backend/inc/Validator.php` - rule execution contract; canonical validation rule engine.
- `/backend/inc/AppError.php` - structured error aggregation contract used by backend pipeline.
- For details, read class headers in those files before using methods.

## API Pipeline
- Each feature runs through module `routes.php` -> module `handlers.php` -> module class/service.
- Feature flow should validate input, execute behavior, then return normalized JSON response.
- Prefer `Generic::sendSuccessResponse()` and `Generic::sendErrorResponse()` when working in Generic-based classes.
- Doing something should be put in try{...}() method in class.

## Validation Pipeline

- Define per-field rule maps in feature class/module, for example `private array $createProjectFields = [ ... ]`.
- Run input through `Generic::filterValidateAll()` for whitelist + validation pass.
- `Generic::validateField()` delegates rule execution to `Validator::validate()`.
- On first failing rule per field, add error to `AppError`; stop flow when `hasErrors()` is true.
- For rule semantics, method names, and supported options, inspect `Validator` class implementation and header.
- Create `filterValidateSpecific()` method per class for cross-field and business checks.

## Error and Response Contract

- Create/accumulate errors with `AppError::add(code, message[, data])`.
- Use stable machine field/code keys (response `errors` object keys) and short deterministic messages.
- If handler/class has errors, return with `Generic::sendErrorResponse()`.
- `sendErrorResponse()` serializes `AppError` into JSON in shape: `{ success: false, errors: { code: message } }` (+ optional payload).
- Success responses must keep a consistent envelope for each endpoint family (for example `success`, `data`, optional `message`).

## Config Contract

- Config directory: `/backend/config`.
- Use config/env values for paths, hosts, and commands.
- No hardcoded environment-specific values in feature logic.

## Hard Constraints

- No database assumptions; config/file-driven behavior only.
- No hardcoded host/path constants when config/env source exists.
