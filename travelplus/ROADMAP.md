# TravelPlus Development Roadmap

## Priority Queue

1. Customer booking lookup
   - Allow customers to look up a booking with booking code plus email or phone.
   - Show payment status, amount due/paid, tour summary, departure, and support actions.
   - Status: implemented; pending browser verification when the local PHP CLI has required extensions enabled.

2. Payment reconciliation for VietQR and VNPay
   - Improve admin filters for pending transfers/payments.
   - Add clearer confirmation workflow, status logs, and confirmation emails.
   - Status: implemented; pending migration/browser verification when the local PHP CLI has required extensions enabled.

3. Mini CRM for travel consultants
   - Consolidate contact forms, tour enquiries, AI chat leads, and unpaid bookings.
   - Track lead stages: new, consulting, won, lost.
   - Status: implemented; pending migration/browser verification when the local PHP CLI has required extensions enabled.

4. Tour wishlist and comparison
   - Let visitors save tours and compare price, departure, duration, destination, and included services.
   - Status: implemented with browser localStorage; pending browser verification when the local PHP CLI has required extensions enabled.

5. Automated booking emails
   - Send booking confirmation, payment reminders, document reminders, and status updates.
   - Status: implemented with email logs, admin browser sender, and optional `booking:send-reminders` command; pending migration/browser verification when the local PHP CLI has required extensions enabled.

6. SEO and content polish
   - Replace default README content with project-specific documentation.
   - Review sitemap, canonical tags, schema data, bilingual metadata, image alt text, and page speed.
   - Status: planned
