# CHMS Santa Rosa — XAMPP Localhost Prototype

**Title:** Design and Development of a Community Health Management System for the City Health Office of Santa Rosa, Laguna

This project is configured for **localhost XAMPP only**. It does not contain InfinityFree-specific configuration.

## Requirements
- XAMPP with Apache, PHP 8.x and MySQL/MariaDB
- VS Code (optional, for editing)
- Modern browser
- Internet access is only needed for the Bootstrap, Bootstrap Icons, Chart.js and Tesseract.js CDN assets used by the prototype. The PHP/MySQL application itself runs on localhost.

## Installation
1. Install XAMPP.
2. Start **Apache** and **MySQL** from the XAMPP Control Panel.
3. Extract this project to:
   `C:\xampp\htdocs\CHMS\`
4. Open `http://localhost/phpmyadmin/`.
5. Create/import the database by opening the **Import** tab and selecting:
   `C:\xampp\htdocs\CHMS\database\schema.sql`
   The SQL file creates `chms_santa_rosa` automatically. If phpMyAdmin asks you to select a database first, create `chms_santa_rosa` and then import the file.
6. Verify `config/config.php` contains:
   - DB_HOST = localhost
   - DB_NAME = chms_santa_rosa
   - DB_USER = root
   - DB_PASS = empty string
7. Open `http://localhost/CHMS/setup.php`.
8. Create the first System Administrator. No test account is pre-created.
9. After successful setup, **delete or rename `setup.php`**. The page disables itself once an account exists, but removing it is recommended for the thesis deployment.
10. Log in at `http://localhost/CHMS/login.php`.
11. The public portal is at `http://localhost/CHMS/public/`.

## Creating staff
1. Sign in as System Administrator.
2. Open **Users & Staff**.
3. Create a staff ID, name, email and password.
4. Select a role and, where appropriate, the staff member's service.
5. For doctors/dentists/psychiatrists, assign appointments to the exact staff account so the server-side patient filter can restrict access.

## RBAC
The system uses `roles`, `permissions` and `role_permissions`. Sidebar visibility is permission-aware, but pages also call server-side `require_permission()` checks. A user without a permission receives HTTP 403 even when they manually type a URL.

Doctor/assigned-provider privacy is enforced in SQL queries using `assigned_staff_id`. Non-admin users see only assigned health records/appointments where applicable. Mental-health service records are not exposed on public pages.

## Patient IDs
When a resident is created, the database auto-increment ID is used to generate a unique Patient ID such as `SR-2026-000001`. Staff do not manually type this ID.

## Public appointments
Only services marked `public_booking_enabled=1` are shown. The schema enables Dental, Immunization and Family Planning. Daily capacity is enforced by a server-side count of Pending/Scheduled appointments. Public pages do not expose diagnosis, address, contact details, medical history or vaccination history.

## OCR
The OCR workflow is:
1. Upload image(s).
2. Validate file type, MIME and size on the server.
3. Run Tesseract.js in the browser.
4. Display extracted text in an editable field.
5. Save OCR text to the OCR document as `Extracted`.
6. A permitted staff member manually reviews and chooses Verify or Reject.

OCR output is **never automatically inserted into official resident/health records**. The prototype intentionally separates digitization from verification. The browser loads Tesseract.js from its CDN, so the first OCR run requires browser internet access. For a fully offline deployment, download the required Tesseract.js assets/language data into the project and update `ocr.php` accordingly.

## Empty patient database
The SQL schema intentionally inserts only static system configuration: roles, permissions, service definitions and settings. It does not insert residents, patients, appointments, medical records, vaccinations or staff accounts.

## Testing checklist
- `/setup.php`: create administrator, then remove/disable it.
- `/login.php`: correct and incorrect password.
- Admin: create one staff account for each role and test the sidebar.
- Direct URL: while logged in as a restricted role, manually open `users.php`, `services.php`, `audit.php`, etc.; unauthorized pages must return 403.
- Residents: create a real test record, verify generated Patient ID, search/filter, archive and restore.
- Appointments: create assignments, test statuses, and attempt to exceed the service daily capacity.
- Doctor privacy: assign two appointments to different doctors and verify each provider sees only their assigned records.
- Vaccinations/records: create and archive records according to permissions.
- OCR: upload a non-sensitive sample scan, correct the extracted text, verify/reject it, and confirm no resident record was auto-created from OCR.
- Public portal: submit an appointment using non-sensitive test information, then check its minimal public status.
- Reports/analytics: confirm empty database gives zero counts and that charts change after legitimate test records are created.
- Audit: confirm login, create/update/archive/OCR/appointment actions appear.

## Security notes for a thesis prototype
This is a localhost academic prototype, not a production clinical information system. Before any real deployment, add HTTPS, hardened session cookies, stronger account recovery/MFA, rate limiting, database least-privilege accounts, encrypted backups, retention policies, privacy impact assessment, access reviews and compliance controls appropriate to Philippine health-data requirements.
