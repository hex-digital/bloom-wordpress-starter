# AGENTS.md — Bloom WordPress Starter (this package)

This file is for agents working **in this repository** (`bloom-wordpress-starter` / Composer package `hex-digital/bloom`).

## CRITICAL: Ignore destination-theme AGENTS.md under stubs/

**Always IGNORE any `AGENTS.md` under `stubs/`.**

That includes:

- `stubs/AGENTS.md`
- `stubs/**/AGENTS.md`

Those files are (or will be) written for the **destination Sage theme** after `bloom:install`. They must **not** guide work, conventions, or decisions inside this package repo.

Also: `stubs/root/.agents/` ships into themes as destination agent docs. Maintain those files when installer/stub behaviour changes, but do **not** treat them as the operating manual for this package. **This root `AGENTS.md` is the source of truth for managing this repo.**

---

## What this repo is

This is **not** a WordPress theme and **not** a Sage fork.

It is a **thin Composer library** that scaffolds an opinionated layer into an existing Roots Sage + Acorn theme. Surface area stays small on purpose: only new/changed files live here, so Bloom does not need constant rebasing when Sage updates.

```text
Sage theme (destination)
  └── composer require hex-digital/bloom
        └── wp acorn bloom:install
              ├── copy stubs/{bloom,app,resources,root} → theme paths
              └── patch composer.json / app.css / vite.config.*
```

| Path | Role |
|---|---|
| `src/` | Package PHP (`HexDigital\Bloom\`) — service provider + Acorn commands |
| `stubs/` | Files copied into the destination theme |
| `composer.json` | Package deps + Acorn provider registration |
| `README.md` | Human install docs |
| `AGENTS.md` (this file) | Agent guide for **this** repo only |

Runtime coupling is `roots/acorn ^6.0` plus a modern Sage layout (`app/`, `resources/`, Vite, `wp acorn`). **Do not pin a Sage theme version in this package.**

---

## Stub → destination mapping

Authoritative map is `$stubRootDestinations` in `src/Commands/InstallCommand.php`:

| Stub root | Destination in Sage theme |
|---|---|
| `stubs/bloom/` | `Bloom/` |
| `stubs/app/` | `app/` |
| `stubs/resources/` | `resources/` |
| `stubs/root/` | theme root (e.g. `config/`, `.agents/`, `screenshot.png`) |

**Copy semantics**

- File-by-file copy; create missing directories
- Never overwrite an existing destination file unless `--force`
- No content merging on copy — merges happen only via installer patch methods

---

## Common tasks

### Add a new stub (copy-only)

1. Put the file under the correct stub root, mirroring the theme-relative path.
   - Example: theme `Bloom/Helpers/FooHelper.php` → `stubs/bloom/Helpers/FooHelper.php`
   - Example: theme `config/poet.php` → `stubs/root/config/poet.php`
2. Prefer new Bloom-namespaced code under `stubs/bloom/` over replacing Sage core files in `stubs/app/`.
3. Destination agent docs belong under `stubs/root/.agents/` (or `stubs/AGENTS.md` if you add one for themes). Those remain ignored as guidance **for this repo**.
4. No `InstallCommand` change is needed unless the destination path falls outside the four stub roots.

### Modify / overlay an existing Sage theme file

Choose deliberately:

| Mechanism | When to use | Behaviour |
|---|---|---|
| **Full-file stub** under `stubs/{bloom,app,resources,root}` | Bloom owns the file end-to-end | Copied on first install; overwritten only with `--force` |
| **Installer patch** in `InstallCommand` | Must merge into a stock Sage file without replacing it | Idempotent string/JSON edit every install |

Current patch targets (called from `handle()`):

| Method | Destination file | What it does |
|---|---|---|
| `patchComposerAutoload()` | theme `composer.json` | Ensures `autoload.psr-4["Bloom\\"] = "Bloom/"` |
| `patchAppCss()` | `resources/css/app.css` | Inserts `@import "./bloom-base.css";` and `@source "../../Bloom/";` after Tailwind import |
| `patchViteConfig()` | `vite.config.js` or `.ts` | Adds `@bloom` alias, `editor.css` / `admin.css` inputs, and `base` path |

**Adding a new patch**

1. Add an idempotent method on `InstallCommand` (detect already-patched → skip; else insert; on failure warn + print manual instructions).
2. Call it from `handle()` after `copyStubRoots()`.
3. Keep edits minimal and safe to re-run.
4. Update `stubs/root/.agents/skills/installer/SKILL.md` so scaffolded themes document the new behaviour.

### Add PHP dependencies for the destination theme

| Need | Where |
|---|---|
| Dep available to every theme that requires Bloom (usual) | This repo’s `composer.json` `require` — themes get it transitively |
| Theme `composer.json` change beyond Bloom PSR-4 autoload | Extend `InstallCommand` with an idempotent patch; do not add silent unrelated side effects |
| Shared Blocks/Components/etc. | Separate package `hex-digital/bloom-modules` (used by `bloom:module-add` / `bloom:module-new`) |

After changing this package’s `composer.json`, themes need a Composer update on their side; the installer does not `composer require` for them.

### Add npm dependencies for the destination theme

The installer **does not** touch theme `package.json` today. This package also has **no** first-class theme-style Node build.

When npm deps must be installed into destination themes:

1. Add an idempotent `patchPackageJson()` (or similar) on `InstallCommand`.
2. Call it from `handle()`.
3. Merge only the required `dependencies` / `devDependencies` / scripts; skip keys already present.
4. Do not introduce a full Sage/Vite app into *this* repo unless that is an intentional product change.
5. Update the installer skill under `stubs/root/.agents/skills/installer/`.

### Update destination-theme agent docs

When stub mapping, commands, styling, or module conventions change, update:

- `stubs/root/.agents/SKILLS.md` (index)
- Matching `stubs/root/.agents/skills/<topic>/SKILL.md`

That keeps scaffolded themes accurate. It does **not** replace this root `AGENTS.md`.

### Change package PHP / commands

| Piece | Path |
|---|---|
| Service provider | `src/BloomServiceProvider.php` |
| `bloom:install` | `src/Commands/InstallCommand.php` |
| `bloom:setup` | `src/Commands/BloomInit.php` |
| `bloom:module-add` | `src/Commands/AddModules.php` |
| `bloom:module-new` | `src/Commands/MakeModule.php` |

Acorn auto-registers the provider via `composer.json` → `extra.acorn.providers`.

Lint / format:

```bash
composer run lint
composer run format
```

---

## Commands consumers run (for context)

```bash
wp acorn bloom:install [--force] [--diff]
wp acorn bloom:setup
wp acorn bloom:module-new ["Name"] [-B|-C|-K|-A]
wp acorn bloom:module-add [module]
```

After install, themes typically run `composer dump-autoload && npm install && npm run build`.

---

## Guardrails

- Keep surface area low: stubs + small installer patches. Do not vendor a full Sage tree.
- Do not edit destination client themes from this repo; change stubs/commands so installs/upgrades deliver the change.
- `--force` overwrites existing scaffolded files and can wipe theme customizations in those paths.
- `--diff` uses a small hard-coded path list in `handleDiff()` and is partly stale (e.g. tokens live under `stubs/resources/css/base/`). When changing tracked stub paths, update that mapping.
- Vite `base` is currently always written as `/wp-content/themes/{theme}/public/build/`. Bedrock-style `/app/themes/...` may need a manual theme-side fix (documented in README).
- This repo has no theme `package.json`. The Node job in `.github/workflows/main.yml` is a theme-era leftover — do not assume `npm install` / `npm run build` work here when changing CI.
- Prefer `Bloom\` code in stubs over replacing Sage’s `app/` files when both are viable.

---

## What not to do

1. **Do not follow `stubs/**/AGENTS.md` while working in this repository.**
2. Do not treat `stubs/root/.agents/` as the guide for maintaining this package (maintain it for consumers; operate from this file).
3. Do not turn this package into a full Sage theme checkout.
4. Do not expand surface area with unrelated Sage/vendor copies.
5. Do not assume theme `package.json` is patched unless you add that installer support.
)
