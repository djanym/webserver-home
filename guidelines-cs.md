# Reusable Code Style Contract - LLM Agent Rules

Scope: all generated/edited code in this repository. This file defines implementation-level style rules only.

## Rule Language

- MUST = mandatory
- SHOULD = strong default (deviate only with explicit reason)
- NEVER = forbidden

---

## Global Engineering Style

- MUST keep changes minimal and focused on requested behavior.
- MUST prefer readability and maintainability over cleverness.
- MUST avoid over-engineering and unnecessary abstractions.
- MUST avoid duplicate logic when a shared helper is justified.
- MUST keep functions/methods single-purpose when practical.
- MUST avoid unnecessary dependencies.
- NEVER leave dead code, unused imports, or commented-out legacy blocks in final output.

---

## File and Namespace Rules (PHP)

- MUST keep one class per file.
- MUST keep one namespace per file.
- MUST omit the closing `?>` tag in pure PHP files.

---

## Naming Conventions

- MUST use clear, domain-driven names.
- MUST use `PascalCase` for classes/components.
- MUST use `camelCase` for class methods.
- MUST use `camelCase` for properties.
- MUST use `UPPER_CASE` for constants.
- MUST use `camelCase` for JavaScript/TypeScript variables and functions.
- MUST use `snake_case` for PHP variables and functions outside of classes.
- MUST use project-consistent function/method naming style in PHP.
- SHOULD use intent-revealing boolean prefixes such as `is`, `has`, `can`, `should`.
- SHOULD avoid abbreviations unless they are standard and unambiguous.

---

## PHP Style Rules

- MUST follow PSR-12 formatting rules.
- MUST declare visibility for all properties and methods.
- MUST NOT use `var` for properties.
- MUST type-hint parameters.
- MUST define return types.
- MUST use nullable types explicitly (`?Type`) when null is allowed.
- SHOULD use `declare(strict_types=1);` in new PHP files unless that conflicts with established file conventions.

### Class Member Order (PHP)

MUST keep this order inside classes:

1. constants
2. properties
3. constructor
4. public methods
5. protected methods
6. private methods

---

## Control Flow Rules

- MUST always use braces for control structures.
- SHOULD prefer early returns to reduce branching.
- SHOULD avoid deep nesting by extracting helpers or returning early.

---

## Arrays

- MUST use short array syntax (`[]`).

---

## Functions and Methods

- MUST keep functions/methods small and focused.
- MUST keep one responsibility per method.
- SHOULD extract repeated or branching-heavy logic into named helpers.

---

## JavaScript / React Style Rules

- MUST use ES modules (`import`/`export`).
- MUST avoid global mutable state.
- MUST keep modules cohesive and reasonably small.
- MUST use functional React components.
- MUST keep side effects out of render logic.
- SHOULD keep hooks near the top level of component bodies.
- NEVER call hooks conditionally.

---

## SCSS / CSS Style Rules

- MUST keep selectors shallow and predictable.
- MUST prefer reusable variables/mixins/tokens over duplicated literals.
- SHOULD keep nesting depth low (typically max 2-3 levels).
- NEVER rely on inline styles unless explicitly required by runtime constraints.

---

## Comments and Documentation

- MUST use PHPDoc for public PHP methods.
- MUST comment non-obvious intent, tradeoffs, and constraints.
- SHOULD avoid comments that restate obvious code behavior.
- MUST keep comments concise and accurate.
- NEVER leave stale or misleading comments after edits.
- NEVER remove existing comments unless explicitly requested. Only add new or update existing comments when code changes.

---

## Design Principles

- SHOULD prefer dependency injection over hard-coded dependencies.
- SHOULD follow SOLID principles in class and module design.

---

## Formatting and Cleanliness

- MUST keep consistent indentation and spacing.
- MUST remove unused variables/imports after edits.
- MUST keep files free of trailing whitespace.
- SHOULD keep line length readable and consistent with project tooling.

---

## Output Quality Expectations

- MUST produce production-ready code by default.
- MUST keep backwards compatibility unless change is explicitly requested.
- MUST include only necessary edits (no opportunistic refactors).
- MUST ensure syntactic validity of edited code.
- SHOULD state assumptions when requirements are ambiguous.
