# Architecture Overview

This repository is a traditional PHP web application for a wedding-planning platform. It integrates a marketing/public frontend, a user account flow, a booking and payment process, and an administrative dashboard.

## High-level structure

```text
.
├── admin/                  # Admin dashboard and management screens
├── assets/                 # CSS, JS, and static frontend resources
├── database/               # Database schema and seed SQL
├── includes/               # Shared PHP utilities, config, and request handlers
├── logs/                   # Error and runtime logs
├── pages/                  # Public user pages and checkout flows
├── uploads/                # Guest lists, invitations, gallery assets, and profile images
├── index.php               # Root redirect for the public app
├── luxe-co-events-logo.png # Branding asset
├── README.md               # Main project documentation
├── SECURITY.md             # Security reporting guidelines
├── CONTRIBUTING.md         # Contribution guidance
└── ...
```

## Core application layers

### 1. Public frontend (`pages/`)

The public application is centered around customer-facing pages such as:

- home/landing page
- services and category pages
- wedding-planning inquiry flows
- booking and checkout flows
- payment status pages
- account dashboard and user management

These pages are served directly as PHP views and often include shared layouts from `includes/header.php` and `includes/footer.php`.

### 2. Shared logic (`includes/`)

The `includes/` directory holds the working glue for the application:

- `config.php` establishes the MySQL connection and contains shared helpers
- `header.php` and `footer.php` supply the shared site shell, stylesheets, and scripts
- `process_planning.php` handles the wedding inquiry form processing
- `logout.php` manages user sign-out
- `paypal_config.php` stores PayPal sandbox configuration

This pattern is typical for a PHP app built without a formal framework.

### 3. Admin backend (`admin/`)

The admin interface includes areas for:

- users
- bookings
- testimonials
- gallery content
- service categories
- wedding plans and payment monitoring

The admin pages use the same PHP/MySQL setup and session checks to control access.

### 4. Database (`database/`)

The main schema is stored in `database/wedding_planning.sql`. It contains the application tables for:

- users
- admin users
- bookings
- payments
- gallery
- testimonials
- services and categories
- wedding plans
- blog posts and logs

This is the main persistence layer for the system.

### 5. Static assets (`assets/`)

The front-end styling and behavior are bundled into the CSS and JavaScript in `assets/`:

- `assets/css/style.css` contains the main styling rules
- `assets/js/main.js` manages front-end behavior and interactions

### 6. Uploads (`uploads/`)

The application stores uploaded media in `uploads/` for:

- service images
- gallery assets
- profile images
- guest list imports
- invitation files

These files are part of the runtime data set and should be backed up and protected in deployed environments.

## Request flow

A typical user path is:

1. User opens a public page under `pages/`
2. Shared header/footer include common layout and scripts
3. Form submission or booking action posts to an `includes/` script or page handler
4. PHP processes the request, validates input, and accesses MySQL
5. Session state is updated for login, booking, or account flows
6. User is redirected back to a page or admin screen

## Session and auth model

The app uses PHP sessions stored server-side. The primary checks are defined in `includes/config.php` and used by relevant pages to guard access for:

- logged-in users
- administrator sessions
- user account access

The app is not using an external auth provider or a modern JWT system in the current codebase.

## Payment flow

The repository includes a PayPal integration layer with sandbox configuration in `includes/paypal_config.php` and related payment-processing routes. The flow is centered around registering PayPal success/cancel URLs and writing payment records to the database.

## Operational notes

- The app relies on direct PHP configuration instead of an `.env` system.
- Database credentials and PayPal credentials are not meant to be exposed in documentation or source control.
- Logs are written to `logs/` and should be reviewed during debugging or deployment.
- The current codebase is structured for simplicity rather than framework-driven separation.

## Production considerations

Before production deployment, review:

- database credentials and secret handling
- user input validation
- file upload restrictions
- session hardening
- CSRF protections
- logging and monitoring

This app is functional and workable for a local or staging environment, but it still needs production hardening before treating it as a live public platform.
