# 🌿 CLIENT WEBSITE - by Hex Digital

<p align="center">
  <strong>WordPress starter theme with a modern development workflow</strong>
  <br />
  Built by Hex Digital - https://www.hexdigital.com
</p>

## Requirements

Make sure you have all this software is installed before moving on:

- [WordPress](https://wordpress.org/) >= 5.4
- [PHP](https://secure.php.net/manual/en/install.php) >= 8.1
- [Composer](https://getcomposer.org/download/) >= 2.3.5
- [Node.js](http://nodejs.org/) >= 20.0

### Theme installation

Clone the repo into the `themes` directory of your WordPress installation.

### Dependencies

- Run `composer install` from the theme directory to install php dependencies
- Run `npm install` from the theme directory to install front end dependencies

### Environment Variables

- Copy `.env.example` to `.env`, and modify the following variables as needed:

`BUD_PROXY` - The URL to access your local version of the site. If you're using something like [LocalWP](https://localwp.com/), this would be the "site domain". E.g `http://bloom.local`

`PUBLIC_PATH` - This is the file path to *theme's* public directory. E.g. `/wp-content/themes/bloom/public/`

### Build commands

- `npm run dev` — Compile assets when file changes are made, start Browsersync session
- `npm run build` — Compile and optimize the files in your assets directory

## Theme Development

Edit files in `/config` for theme configuration.

Edit `app/setup.php` to enable or disable theme features, setup navigation menus, post thumbnail sizes, and sidebars.

## Bloom and Sage

This repo is built on Hex's Bloom framework, which in turn is built on-top of [Sage](https://roots.io/sage/).

Bloom is an opinionated, productivity-driven WordPress starter theme with a modern development workflow.

See more about Bloom here: https://www.github.com/hex-digital/bloom-wordpress-starter

### Updating Sage framework

We may wish to update this repo with the latest changes in Sage.

The `upstream` branch tracks Sage 10.

The easiest way to update this theme is to pull in the latest changes from Sage
into the `upstream` branch, then create a PR from `upstream` to `main`.

This will highlight all Sage changes, as well as any conflicts to solve.

### Documentation
**Bloom**
https://www.github.com/hex-digital/bloom-wordpress-starter

**Sage**
Please see the [Sage documentation](https://roots.io/sage/docs/) for the underlying theme.

## Contributing

Contributions are welcome from everyone.

## Troubleshooting

If you see the below error, it's likely that you haven't run `npm run dev` or `npm run build`:

```
The manifest [/path/to/theme/public/manifest.json] cannot be found.
```
