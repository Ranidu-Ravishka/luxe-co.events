# Luxe & Co. Events

![Luxe & Co. Events logo](luxe-co-events-logo.png)

Luxe & Co. Events is a wedding-planning and client-management web application for couples and event planners. The project combines a public marketing site with practical booking, payment, account, and admin workflows for wedding services and planning.

This repository is a PHP + MySQL application with a complete front-end experience, service catalog, booking flow, admin management screens, and a payment integration layer. It is intended to serve as a working wedding-planning platform rather than a purely static showcase.

## What the project does

The application currently includes:

- Public landing page with hero, service overview, gallery, testimonials, and conversion-focused CTAs
- Service categories and service detail pages
- Wedding planning inquiry form with multi-step data capture
- Guest list and invitation file upload workflow
- Cart and checkout flow for selected services
- User registration and login experience
- Account dashboard for profile, bookings, payment history, and settings
- Admin dashboard for managing bookings, payments, testimonials, gallery, service categories, and users
- PayPal sandbox payment configuration and processing flow
- Blog and portfolio-style content sections
- Image and upload handling for service and gallery assets

## Current status

This project is a functional, database-backed application with a strong front-end presentation layer. Some areas are still more template-like than production-hardened, especially around validation, consistency, and environment configuration, but the core user and admin flows are implemented in the repository.

## Technology stack

- PHP 8.x
- MySQL / MariaDB
- HTML5, CSS3, JavaScript
- Bootstrap 5
- jQuery
- Font Awesome
- PayPal sandbox integration for payment processing
- Apache or Nginx web server

## Repository structure

```text
.
├── admin/                  # Admin dashboard pages and AJAX endpoints
├── assets/                 # CSS, JavaScript, and shared client assets
├── database/               # SQL schema and data import file
├── includes/               # Shared PHP config, auth helpers, and process scripts
├── logs/                   # Runtime and PayPal error logs
├── pages/                  # Public frontend pages and customer flows
├── uploads/                # User and admin uploaded media files
├── index.php               # Root redirect
├── luxe-co-events-logo.png # Brand asset
├── README.md               # Main project documentation
├── README copy.md          # Legacy/secondary documentation copy
├── .DS_Store               # OS metadata (not project source)
└── wedding_planning.sql    # Legacy/backup SQL dump reference (see database/)
```

> Note: The project does not currently include a package manager manifest such as `package.json` or a framework app structure. It is a traditional PHP web application.

## Prerequisites

Before running the project locally, ensure you have:

- PHP 8.1 or newer
- MySQL or MariaDB
- Apache or Nginx
- A browser for testing the UI
- Access to the local database server with a user that can create and modify databases

## Installation and setup

1. Clone the repository:

```bash
git clone <repository-url>
cd luxe-co.events
```

2. Create the database.

If you are using MySQL/MariaDB, create a database named `wedding_planning` and import the schema from `database/wedding_planning.sql`:

```bash
mysql -u root -p
CREATE DATABASE wedding_planning;
USE wedding_planning;
SOURCE database/wedding_planning.sql;
```

3. Update database credentials.

Open `includes/config.php` and confirm the connection settings match your local MySQL setup:

```php
$host = 'localhost';
$dbname = 'wedding_planning';
$username = 'root';
$password = '';
```

4. Start the app.

With PHP’s built-in server:

```bash
php -S 127.0.0.1:8000
```

Then open:

```text
http://127.0.0.1:8000/
```

The root `index.php` redirects to the main public pages under `pages/index.php`.

## Local development notes

- Public pages live under `pages/`.
- Shared logic and global configuration live under `includes/`.
- Admin pages live under `admin/` and use a separate set of session and permission checks.
- Uploaded files are stored under `uploads/`.
- Runtime logs are stored in `logs/`.

## Environment and configuration

This project does not currently use `.env` files. Configuration is primarily stored directly in PHP files:

- `includes/config.php` for the database connection
- `includes/paypal_config.php` for PayPal settings

Do not commit or expose real credentials, live PayPal credentials, or production database details in the repository.

## Admin access

The SQL seed data includes an administrator account in the `admin_users` table. The exact default credentials are stored in the database schema and should be treated as development-only values unless intentionally changed.

## Features by area

### Public site

- Wedding marketing landing page
- Service catalog and category pages
- Wedding inquiry form
- Gallery and testimonial presentation
- Payment and booking flow

### User area

- Registration and login
- Dashboard/account access
- Booking history and payment history
- Wedding planning records

### Admin area

- Service and category management
- Gallery management
- Booking oversight
- User management
- Payment monitoring
- Testimonials moderation
- Blog content flow

## Security considerations

The repository contains a working app but still has areas that should be reviewed before production deployment:

- Direct database credentials are embedded in PHP configuration files
- Some forms and flows are not fully hardened against CSRF or request validation patterns
- Uploaded files should be restricted and monitored in production
- PayPal integration should be configured carefully for live use
- Session-based auth should be reviewed against production hardening requirements

## Testing and quality checks

This repository does not currently include a formal automated test suite or package-based CI pipeline. Validation is primarily manual and relies on browser-based flows and database checks.

Recommended manual checks:

- login and registration flow
- service selection and booking flow
- payment approval/cancel flow
- admin dashboard navigation
- upload workflows for guest list and invites
- session redirects and account access behavior

## Deployment

The project is designed for a typical PHP hosting setup:

- Host the repository root on Apache or Nginx
- Point the document root to the project directory or a folder that exposes the app entry points
- Ensure PHP and MySQL are available on the deployment server
- Import the schema into the target database
- Update the PHP config values for the production environment
- Configure PayPal with the correct live or sandbox endpoints before releasing to production

## Roadmap / future improvements

Potential future work includes:

- stronger validation and sanitization across all form handlers
- migration to a more structured PHP framework or modern MVC pattern
- environment-variable-based configuration
- automated tests and CI coverage
- production security hardening and rate limiting
- full API documentation and standardization

## License

There is no explicit open-source license file present in this repository. Before reusing this project commercially or publicly, confirm the appropriate licensing and asset permissions for your deployment and any third-party images or branding assets.

## Contributors

The project includes a group of student/academic collaborators documented in the earlier project history. Their names are preserved in the legacy project notes and should be treated as historical attribution rather than a current maintainer list unless updated.

## Summary

Luxe & Co. Events is an event-planning and booking platform built as a PHP/MySQL web app with a polished brand experience and a real operational workflow. It is suitable for local development and further refinement, but it should be reviewed and hardened before being treated as a production-ready public platform.

