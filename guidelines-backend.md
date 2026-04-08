# Backend LLM Agent Contract

Scope: `/backend` only. Output: JSON API responses only.

## Execution Model

- Routing is declaration-only: register endpoints in `/backend/routes.php` and `/backend/modules/{module-name}/routes.php`.
- Endpoint behavior lives in module handlers/classes; do not place business logic in route files.
- Shared runtime utilities are in `/backend/inc`; import and reuse, do not re-implement cross-cutting utilities.

## Core Class Contracts (Read Headers First)

- `/backend/inc/Generic.php` - base request-flow contract (validation wrappers, error container wiring, success/error JSON emitters).
- `/backend/inc/Validator.php` - rule execution contract; canonical validation rule engine.
- `/backend/inc/AppError.php` - structured error aggregation contract used by backend pipeline.
- For details, read class headers in those files before using methods.

## API Pipeline
- Each feature runs through module routes.php -> module handlers.php -> module class.
- Doing something should be put in try{...}() method in class.
- It should validate input, then do the thing, then return success response with `Generic::sendSuccessResponse()`.

## Validation Pipeline

- Define per-field rule maps in feature class/module. Such as `private array $createProjectFields = [ ... ]` with field names as keys and rule names as values.
- Run input through `Generic::filterValidateAll()` for whitelist + validation pass.
- `Generic::validateField()` delegates rule execution to `Validator::validate()`.
- On first failing rule per field, add error to `AppError`; stop flow when `hasErrors()` is true.
- For rule semantics, method names, and supported options, inspect `Validator` class implementation and header.
- Create `filterValidateSpecific()` method per class for specific field validations.

## Error Pipeline

- Create/accumulate errors with `AppError::add(code, message[, data])`.
- Use stable machine field/code keys (response `errors` object keys) and short deterministic messages.
- If handler/class has errors, return with `Generic::sendErrorResponse()`.
- `sendErrorResponse()` serializes `AppError` into JSON in shape: `{ success: false, errors: { code: message } }` (+ optional additional response payload).

## Hard Constraints

- No database assumptions; config/file-driven behavior only.
- No hardcoded host/path constants when config/env source exists.
- Keep module boundaries strict; avoid cross-module direct mutations.

