---
name: bloom-javascript-conventions
description: Bloom's lightweight JavaScript style expectations for block/component/theme JS files. Read this before writing or reviewing JS in a Bloom theme.
---

# JavaScript conventions

No strict prescription — OOP or functional style is both fine. Priorities:

- Clear, simple variable and function names.
- Comment where behaviour isn't obvious.
- If global JS is needed, break it down into components/modules and import/load it in the main `app.js` rather than adding ad-hoc inline scripts.
- Block/component-scoped JS (`buttons.js` alongside `Buttons.php`/`buttons.blade.php`) stays scoped to that block/component — see the module-system skill.
- Strip `console.log`s before opening a PR.
