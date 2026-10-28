# Security Policy

## Supported scope

This repository is a local/project-level application and is not currently managed as a production service under a formal security program. However, the project should still be treated carefully when handling session data, payments, uploads, and database access.

## Reporting a vulnerability

Please do not open a public issue for a security problem.

Instead, report it privately to the project maintainer or repository owner with the following information:

- a description of the issue
- affected files or routes
- steps to reproduce the problem
- any suggested remediation or mitigation

## Key security expectations

Before deploying this app publicly, review the following areas:

- PHP session configuration and login flow
- direct database access and credentials
- form validation and sanitization
- file upload restrictions and file storage permissions
- PayPal configuration and transaction verification
- admin authentication and authorization checks
- output encoding in user-generated content

## Prohibited practice

Do not commit or expose:

- database credentials
- PayPal credentials or tokens
- real API keys
- production hosts or internal infrastructure details

## Recommended hardening steps

- move database and API secrets to environment-based configuration
- restrict file upload directories and validate MIME types
- add CSRF protections to forms
- review admin authorization flows before deployment
- monitor logs in `logs/` and rotate secrets when needed

## Current status

This project is suitable for local development and staging review, but it should be hardened before being treated as a public or production-ready platform.
