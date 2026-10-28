# Luxe & Co. Events — Legacy Documentation Copy

This file preserves an earlier, simplified project description and should be treated as a historical note rather than the current source of truth.

For the active and up-to-date project guidance, see [README.md](README.md).

## Original intent

The earlier version described a wedding planning system with a frontend-focused design and user-facing flows. The current repository has expanded beyond that description and includes:

- MySQL-backed data models
- login and account management flows
- admin functionality
- booking and payment processing
- service catalog and media upload support

## Important clarification

This project is not only a static front-end mockup. It is a PHP application with database-backed functionality and operational admin workflows.

## Current working directories

```text
admin/        Shared admin screens and management modules
assets/      Frontend CSS, JavaScript, and shared static files
includes/     Shared PHP helpers, config, and processing logic
pages/       Public customer-facing pages
database/     SQL schema and import files
uploads/      User- and admin-uploaded assets
logs/         Application runtime and errors
```

## Recommended next actions

- Use the root [README.md](README.md) as the primary onboarding and setup document.
- Verify database credentials and local environment before running the app.
- Reassess production readiness before exposing the app publicly.
- Document any future feature work in the main project README rather than in legacy copies.
