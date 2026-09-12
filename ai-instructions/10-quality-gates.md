# QUALITY GATES — Before Considering Work Complete

All the following conditions MUST be met before any task is considered complete.

---

## Mandatory Quality Gates

1. [ ] Code follows existing patterns (has analogous examples in the codebase).
2. [ ] Static analysis passes (project's configured level, e.g., phpstan level 5).
3. [ ] Relevant tests pass (only if tests were requested/run).
4. [ ] No unrelated code was modified.
5. [ ] Code style matches neighboring files.
6. [ ] No comments were added unless asked.
7. [ ] No secrets or sensitive data were introduced.
8. [ ] Scope is limited to the requested feature.
9. [ ] All naming conventions followed.
10. [ ] No speculative changes were made.
11. [ ] No `dd()`, `dump()`, or `ray()` in committed code.
12. [ ] File organization respects the feature's context.
13. [ ] `MASTER_BUILD_SPECIFICATION.md` was read before writing code — or, if it was missing, it was created (complete, detailed, precise) via operator Q&A and confirmed before any code.

---

## Verification Decision Tree

```
Have I finished a coding task?
├── Did I read the build specification (MASTER_BUILD_SPECIFICATION.md), or create it via operator Q&A?
│   └── NO → Read/create it. Do not finalize.
├── Did I run static analysis?
│   └── NO → Run it now. Do not finalize.
├── Did I test what I changed (if requested)?
│   └── NO → Run relevant tests. Do not finalize.
├── Did I modify only files relevant to this feature?
│   └── NO → Revert unrelated changes. Do not finalize.
├── Does my code follow existing project patterns?
│   └── NO → Find an analogue and align. Do not finalize.
└── Everything passes → Finalize.
```

---

## Final Verification Checklist

1. All files created/modified are necessary.
2. Code follows all project conventions.
3. Static analysis passes.
4. No speculative changes were made.
5. Scope is limited to the requested feature.
6. `MASTER_BUILD_SPECIFICATION.md` existed (or was created and confirmed) before coding — no assumptions contradict it.
7. Self-audit against the Master Self-Audit Checklist (root `ai-instructions.md`, section 10).

---

## Project-Specific Quality Gates

For the LingSID project, additional gates apply (see `12-project-specific/lingusid.md`):

- [ ] `./vendor/bin/phpstan analyse` passes at level 5 (paths `app/`, `config/`, `database/`, `routes/`).
- [ ] Code formatted with `laravel/pint`.
- [ ] `bun run lint` and `bun run format:check` pass locally (same commands as the CI `lint` workflow).
- [ ] No static call to instance methods (`XxxAction::handle()`).
- [ ] No references to non-existent classes/methods; all imports verified against the codebase.
- [ ] No ActivityLog/audit deletion and no loss of history for important mutations.
