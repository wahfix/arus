# AI INSTRUCTION SYSTEM — OVERVIEW

This folder contains the **AI instruction system** used by the **LingSID** project (Laravel 12 + Inertia/Vue 3 + TypeScript). Universal modules stay reusable; project-specific rules live in `12-project-specific/lingusid.md`.

## Architecture

The system separates **universal rules** (apply to every project) from **project-specific rules** (apply to a single project):

| Scope | Meaning |
|-------|---------|
| GLOBAL | Applies to all projects and all tasks |
| UNIVERSAL | Applies to all projects using this system |
| PROJECT-SPECIFIC | Applies only when the repository matches a defined project |
| MODULE / LANGUAGE / FRAMEWORK / TASK | Applies only to a specific part/context |

## File Responsibilities

| File | Responsibility |
|------|----------------|
| root `ai-instructions.md` | **Canonical constitution / single entry point.** How to read the system, file map, shared principles, priority system, rule scope model, semantic strength hierarchy, workflow summary, project onboarding, conflict resolution, quality gates, final verification. |
| `01-governance.md` | Priority hierarchy, rule scope determination, conflict resolution protocol, invariants. |
| `02-agent-workflow.md` | Mandatory workflow: Understand → Inspect → Find Analogues → Plan → Implement → Static Analysis → Test → Diff Review → Style Review → Finalize. Decision trees for each step. |
| `03-architecture.md` | Layer architecture (Controller → Action → Repository → Model), dependency directions, context-based organization, routing, frontend org, abstraction philosophy, binding architectural decisions. |
| `04-coding-standards.md` | PHP & TypeScript/Vue style, comment policy, import ordering, formatting rules (Pint/Prettier/ESLint), file organization. |
| `05-naming.md` | Naming for PHP classes, Actions, methods, DB, routes, frontend, enums, prohibited names. |
| `06-testing.md` | Framework, `#[Test]` attribute, naming, organization, mock rules, patterns, when to run tests. |
| `07-security.md` | Auth, authz (Spatie Permission), validation, rate limiting, sensitive data (NIK/PII), audit trails, media uploads, CSRF, key security rules. |
| `08-git.md` | Branching from `develop`, conventional commits, prohibited operations, CI workflows. |
| `09-tools.md` | Terminal, paths, parallelism, linting (PHPStan/Pint), JS runtime (bun), tests, external tools. |
| `10-quality-gates.md` | Universal quality gates, verification decision tree, final checklist, project-specific gates pointer. |
| `11-forbidden-behavior.md` | Explicit prohibitions (architecture, style, implementation, security, scope). |
| `12-project-specific/` | Project-specific modules — LingSID invariants in `lingusid.md`, verbatim canonical snippet bank in `canonical-snippets.md`. Add a file here per project. |

## How to Use

1. Read root `ai-instructions.md` as the entry point.
2. Read `01-governance.md` and `02-agent-workflow.md`.
3. Apply the relevant topical modules (`03`–`11`) for the task.
4. Load matching modules from `12-project-specific/` when they apply.
5. **Read `MASTER_BUILD_SPECIFICATION.md` at the project root** (or create it via detailed operator Q&A if missing) — never write code without it.

## Authoring Instruction Sets

When creating or updating instruction sets in this repository, treat the **`laravel/` set as the
reference template**: mirror its constitution layout, module split (`01`–`11`,
`12-project-specific/`), precision, and source-anchored verbatim snippets
(`canonical-snippets.md`). Always re-run `./setup-ai-rules.sh <framework>` (after removing the
stale `ai-instructions/master`) so template, master, and distributed copies stay identical.

## Adding a New Project

1. Each project's unique invariants go into `12-project-specific/{project}.md`.
2. Never generalize project-specific rules into universal rules, and vice versa.
3. Keep the universal modules (`01`–`11`) free of project-specific references so they stay reusable.