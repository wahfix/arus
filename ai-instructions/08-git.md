# GIT — Branching, Commits, Version Control

This file defines git branching and commit conventions for LingSID. The default/mainline branch is **`develop`**; `main` holds verified releases.

---

## Branching

### Universal Rules
- **`develop`** is the default integration branch (verified, tested code).
- **`main`** is the protected release branch.
- **DO NOT** commit directly to `develop` or `main`.
- Each feature/bugfix gets its own short-lived branch created from `develop`.
- Do NOT push or open a PR unless explicitly asked.

### Branch Base Decision Tree
```
What are you working on?
├── A new feature → create branch from develop:  feat/{short-description}
├── A bug fix     → create branch from develop:  fix/{short-description}
├── Refactor/tests/docs/chore → branch from develop
└── A hotfix      → create branch from the affected release (only when one is in flight)
```

### Branch Workflow (Standard)
```
1. git checkout develop
2. git pull origin develop
3. git checkout -b feat/{short-description}
4. ... work ...
5. git add <intended files only>
6. git commit -m "feat: concise description"
7. (do NOT push unless asked)
```

---

## Commits

### Required Discipline
- Inspect `git status`, `git diff`, and `git log` before committing.
- Stage only intended files; never commit secrets or artifacts.
- Do not `git add .` blindly — review what is staged.
- Write a concise summary commit message matching repo style.
- **DO NOT** create empty commits.

### Prohibited Operations
- Committing directly to `develop` or `main`.
- Force-pushing.
- Pushing secrets or credentials.
- Committing generated/vendor files (`node_modules`, `vendor`, build output, `.env`).
- Amending commits unless explicitly asked.

---

## Commit Message Format (Conventional Commits)

**Format:**
```
<type>(<optional scope>): <concise description>
```

**Allowed types:**

| Type | Purpose | Example |
|------|---------|---------|
| `feat` | New feature | `feat: add article management feature` |
| `fix` | Bug fix | `fix: pass validated payload to create article action` |
| `refactor` | Refactor without behavior change | `refactor: extract resident update into action` |
| `test` | Add/fix tests | `test: cover circular membership guard` |
| `docs` | Documentation | `docs: update instruction system` |
| `chore` | Maintenance/deps | `chore: bump leravel/pint` |
| `style` | Formatting, no logic change | `style: pint format actions` |

**Rules:**
1. Description concise and specific (imperative mood).
2. Lowercase type and description; no period at the end.
3. Max ~72 characters for the subject line.
4. Use precise domain terms (`feat: add sid residents export`, not `feat: do stuff`).

**Examples:**
```
feat: add sid residents index page
fix: guard against circular group membership
refactor: move article validation into ruled action
```

---

## CI (GitHub Actions)

- `.github/workflows/` runs on push/PR to `develop` and `main`:
  - **tests** — PHPUnit suite.
  - **lint** — frontend ESLint/Prettier checks.
- Local work must pass the same checks the CI runs before a merge is expected: PHPStan level 5, `laravel/pint`, `bun run lint`, `bun run format:check`.

---

## New Project Initialization

When creating a new project from scratch, initialize git first (`git init`) with `develop` as the primary branch before any feature work. Do not start committing onto `main`/`master` directly.

---

## Speculative Refactoring Policy

- **DO NOT refactor working code** — only fix what is broken for the current feature.
- **DO NOT fix issues** not directly related to the current task.
- **DO NOT "improve"** existing code during a feature implementation.