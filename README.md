# Bloom for Sage

Bloom is an opinionated starter layer for Roots Sage themes. It scaffolds a `Bloom/` directory, starter view files, CSS tokens/base styles, helper classes, and config used by the Bloom workflow.

## Requirements

- PHP 8.2+
- Roots Sage with Acorn
- Node.js/npm for asset builds

## Private Package Access (Required)

`hex-digital/bloom` is currently a private GitHub package. Before running `composer require`, configure your theme project to access private GitHub repositories.

1. Add the package repository to your theme `composer.json`:

```json
  "repositories": [
    {
      "type": "vcs",
      "url": "git@github.com:hex-digital/bloom-wordpress-starter.git"
    }
  ]
```

Check you're authenticated with Github, by running the following code in your terminal:

```
  ssh -T git@github.com
```

If successful you should see a message like:
> Hi! You've successfully authenticated, but GitHub does not provide shell access.


## Install in an Existing Sage Theme

1. Require the package in your Sage theme:

```bash
composer require hex-digital/bloom
```

2. Run the installer command from your theme root:

```bash
wp acorn bloom:install
```

3. Install/build frontend assets:

```bash
npm install
npm run build
```

## What the Installer Changes

The `bloom:install` command:

- creates `Bloom/` directories (`Blocks`, `Components`, `Composers`, `Livewire`, etc.)
- copies starter files into `resources/views`, `resources/css`, `resources/images`, and `resources/fonts`
- copies package root files into the theme root (`screenshot.png`)
- copies GitHub automation files into `.github/` (workflows + labeler config)
- copies Bloom config from `bloom-config/` into `Bloom/config`
- copies app-level config from `app-config/` into root `config/`
- patches `resources/css/app.css` to include Bloom CSS
- patches `vite.config.js`/`vite.config.ts` to include Bloom aliases and entries
- patches `composer.json` with `Bloom\\ => Bloom/` autoload when missing

Config files are copied only when missing. Use `wp acorn bloom:install --force` to overwrite existing files.

## Config Source vs Destination

Bloom keeps config in two package source directories:

- `bloom-config/` (published/scaffolded to `Bloom/config/`)
- `app-config/` (published/scaffolded to root `config/`)

Both destinations are named `config`, but they are separated by location and purpose:

- `Bloom/config/*` is Bloom runtime configuration consumed by the package.
- `config/*` is app-level Acorn/Laravel configuration.

## Publish Tags

You can publish config sets independently:

```bash
wp acorn vendor:publish --tag=bloom-config
wp acorn vendor:publish --tag=bloom-app-config
```

## Vite Updates Applied by Installer

Bloom updates your Vite config to include:

- `resolve.alias`:
  - `@bloom: '/Bloom'`
- `laravel.input` entries:
  - `'resources/css/editor.css'`
  - `'resources/css/admin.css'`

For non-Bedrock Sage themes, Bloom also ensures:

- `base: '/wp-content/themes/{theme-name}/public/build/'`

During `bloom:install`, Bloom auto-detects the theme directory name from the current path (for example, `wp-content/themes/{theme-name}`) and uses that value to build the Vite `base` path.

## Bedrock vs Non-Bedrock Base Path

- Bedrock example:
  - `/app/themes/{theme-name}/public/build/`
- Non-Bedrock example:
  - `/wp-content/themes/{theme-name}/public/build/`

If your project structure differs, adjust `base` manually in Vite.

## Reinstalling / Updating Stubs

- Show what changed in stubs since your last install:

```bash
wp acorn bloom:install --diff
```

- Force overwrite scaffolded files:

```bash
wp acorn bloom:install --force
```

## Development

Run code style checks with Laravel Pint:

```bash
composer run lint
```

Auto-fix style issues:

```bash
composer run format
```

