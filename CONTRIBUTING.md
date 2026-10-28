# Contributing

Thank you for helping improve Luxe & Co. Events.

This repository is a PHP-based wedding planning application, so contributions should respect the existing project structure and keep changes small, understandable, and safe.

## Working with the project

### Development setup

1. Clone the repository.
2. Create a local MySQL database named `wedding_planning`.
3. Import the schema from `database/wedding_planning.sql`.
4. Update the local database configuration in `includes/config.php`.
5. Start the app using a local PHP server or your preferred Apache/Nginx setup.

Example:

```bash
php -S 127.0.0.1:8000
```

Then open the project in a browser at:

```text
http://127.0.0.1:8000/
```

## Project conventions

### PHP

- Keep logic simple and readable.
- Prefer explicit checks and direct session handling rather than introducing framework patterns without a clear need.
- Do not add credentials or secrets directly into documentation or code comments.
- Preserve existing route names and public page links when making changes.

### Front-end

- CSS and JavaScript are currently stored in `assets/css` and `assets/js`.
- Keep styling consistent with the brand and avoid mixing multiple visual systems in the same page.
- Test UI changes across desktop and mobile layouts.

### Database

- Always review SQL changes for compatibility with the current schema.
- If you add or change tables, document the changes and update the schema file where appropriate.
- Never run destructive database operations without validation and a clear backup plan.

## Pull requests

Before opening a PR:

- make sure the relevant workflow still works locally
- avoid unrelated refactors
- keep commit scope focused
- explain the change clearly in the PR description
- mention any config changes or environment assumptions

## Security expectations

- Do not commit real database credentials, PayPal credentials, API tokens, or production URLs.
- Validate file uploads and user-submitted input before merging.
- Be careful with session handling and redirects.

## Documentation

When changing behavior, update the related documentation in the main README or architecture notes. Stale docs are a source of confusion and should be corrected with the code change.

## Need help?

Open an issue or ask for clarification before making a major architectural change. This project is a working application, and the larger the change, the more important it is to confirm the intended behavior before editing shared files.
