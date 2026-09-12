# TOOLS — Usage of Terminal, Search, Linters, Tests

This file defines how the agent should use tools: terminal, search, filesystem, linters, formatters, tests, static analysis, and external tools.

---

## General Tool Principles

1. **Inspect before modifying.** The codebase is the authority. Search before creating.
2. **Reuse before creating.** Before building new reusable components (Traits, Actions, Repositories, Contracts), thoroughly search the existing codebase for similar or suitable implementations.
3. **Evidence-based.** Every pattern you follow MUST be backed by at least one existing example in the codebase.
4. **Minimize scope.** Only modify files directly relevant to the feature.

---

## Terminal Usage

- **Explain modifying commands first** before running them (git operations, installs, etc.).
- Use **non-interactive** versions of commands when available (`npm init -y` instead of `npm init`).
- Avoid commands likely to require user interaction (e.g., `git rebase -i`) — they may hang.
- **External processes/dev servers:** Do not run external development servers. Run them in a separate terminal and ask the user to provide relevant data (logs, errors).

---

## Filesystem & Paths

- **Use absolute paths** when referring to files with tools. Relative paths are not supported.
- Follow the current working directory of the shell.
- Never modify files outside the feature's context.

---

## Parallelism

- Execute multiple independent tool calls in parallel when feasible (e.g., searching the codebase).
- Do not run dependent commands in parallel — chain them sequentially.

---

## Linting & Formatting

### PHP
- Format with Laravel Pint: `./vendor/bin/pint` (PSR-12 Laravel preset).
- Run PHPStan/Larastan at level 5 (`phpstan.neon`, paths `app/`, `config/`, `database/`, `routes/`) before considering work complete: `./vendor/bin/phpstan analyse`.

### TypeScript/Vue
- Prettier with project config (organize-imports + tailwindcss plugins, printWidth 150, tabWidth 4).
- Run ESLint flat config with Vue and TypeScript rules (ignores `resources/js/components/ui/**`).
- Run via project scripts:
  - `bun run lint` (ESLint + fix)
  - `bun run format` / `bun run format:check` (Prettier)
  - `bunx vue-tsc --noEmit` (optional type check)
- These commands are what CI (`lint` workflow) runs — keep them green locally.

---

## JavaScript Runtime

- **Prefer `bun`** for all JavaScript-related tasks (install, dev, lint, format).
- Use `npm` as a fallback if `bun` is unavailable.
- Never mix package managers within a project — pick one and stay consistent.

---

## Static Analysis

- Run static analysis before considering work complete (level 5 per `phpstan.neon`):
  ```bash
  ./vendor/bin/phpstan analyse
  ```
- Fix all issues found. Do not proceed while static analysis errors remain.
- The `tests`/`lint` CI workflows re-run these checks on `develop`/`main` — local parity is required.

---

## Tests

- **Do NOT run the full test suite** unless explicitly asked.
- When tests ARE run, run only relevant tests:
  ```bash
  php artisan test --filter=TestName
  ```
- If tests fail, fix only the code related to the current feature.
- Do NOT mock in Feature tests (use real database with `RefreshDatabase`).

---

## External Tools

- Use the project's dependency manager (composer for PHP, bun/npm for JS).
- Do NOT modify Laravel's built-in code or vendor code without confirmation.
- Do NOT add dependencies without checking whether the codebase already has a suitable implementation.

---

## Background & Interactive Processes

- Use background processes (`&`) for commands unlikely to stop on their own.
- **Do NOT run long-running dev servers** (`composer dev`, `composer dev:ssr`, `php artisan serve`, `npm run dev`, `vite`). They may hang the session. Run them in a separate terminal and ask the user to provide logs/errors.
- If unsure whether a command may hang, ask the user.
- Always inform the user about commands that make system changes before running them.
