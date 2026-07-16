# TravelPlus Development Roadmap

## Priority Queue

1. Customer booking lookup
   - Allow customers to look up a booking with booking code plus email or phone.
   - Show payment status, amount due/paid, tour summary, departure, and support actions.
   - Status: completed and verified.

2. Payment reconciliation for VietQR and VNPay
   - Improve admin filters for pending transfers/payments.
   - Add clearer confirmation workflow, status logs, and confirmation emails.
   - Status: completed and verified.

3. Mini CRM for travel consultants
   - Consolidate contact forms, tour enquiries, AI chat leads, and unpaid bookings.
   - Track lead stages: new, consulting, won, lost.
   - Status: completed and verified.

4. Tour wishlist and comparison
   - Let visitors save tours and compare price, departure, duration, destination, and included services.
   - Status: completed with browser localStorage and responsive comparison UI.

5. Automated booking emails
   - Send booking confirmation, payment reminders, document reminders, and status updates.
   - Status: completed with email logs, admin browser sender, and optional local command.

6. SEO and content polish
   - Replace default README content with project-specific documentation.
   - Review sitemap, canonical tags, schema data, bilingual metadata, image alt text, and page speed.
   - Status: completed. Sitemap URLs and hreflang, private-flow noindex rules, bilingual metadata, schemas, image alt text, error pages and key image assets were audited and verified.

7. Production readiness and HTTP hardening
   - Verify robots, sitemap, public/private routes, branded errors, caching and compression under both supported hosting layouts.
   - Hide framework version headers, enable HTTPS-only HSTS, and restrict unused browser permissions.
   - Status: completed and verified locally; production headers take effect after publishing the updated `.htaccess` files.

8. Database query and index optimization
   - Consolidate repeated admin aggregates and analytics counts into fewer queries.
   - Keep departure-date filters indexable and add composite indexes for booking lookup, CRM, catalog, media, reviews, translations, email logs and analytics journeys.
   - Status: completed. The phpMyAdmin SQL is idempotent and the target queries were verified with MySQL EXPLAIN.

9. Shared-hosting system health
   - Provide an admin-only status page for PHP, database indexes, email, HTTPS, writable directories and disk space.
   - Keep diagnostics read-only and available without command-line access.
   - Status: completed with actionable checks and no-cache responses.

10. Admin error log viewer
   - Read recent CodeIgniter warnings and errors without command-line or File Manager access.
   - Limit file reads and result counts, support filters, and redact credentials and customer contact details.
   - Status: completed with a read-only, admin-only log viewer.

11. Admin navigation structure
   - Group admin pages by overview, sales, content and system responsibilities.
   - Keep active-page context clear and make the sidebar independently scrollable on shorter screens.
   - Status: completed and verified on desktop and mobile.

12. Centralized website contact settings
   - Manage the public hotline, contact email and social links from one admin page.
   - Reuse the values across public pages, structured data and automated email templates without database queries.
   - Status: completed with JSON persistence for shared hosting; no SQL or command-line step is required.

13. PHP runtime diagnostics
   - Show the active PHP family, handler, loaded php.ini and additional INI file count in system health.
   - Display upload_max_filesize and post_max_size separately, including the effective bottleneck.
   - Status: completed with read-only diagnostics and unit coverage.

14. Centralized company and office information
   - Manage tax ID, tour operator license and bilingual office details from website settings.
   - Reuse office details across contact pages, footer and AI knowledge without database queries.
   - Status: completed with backward-compatible JSON defaults and no hosting command requirement.

15. Checkout promotion code hardening
   - Separate coupon apply and remove actions so an empty submission returns the correct validation message.
   - Localize checkout feedback and show discount rows only when a discount is active.
   - Status: completed with isolated discount calculation coverage and responsive checkout feedback.
