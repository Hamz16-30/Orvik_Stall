# ORVIK Store — Phase 2 Architecture Documentation

## 1. Authentication Architecture

ORVIK Store uses standard, battle-tested Laravel 11 session authentication designed specifically for shared-hosting environments (such as IONOS Premium Web Hosting) without requiring Node.js or Redis servers.

- **Password Hashing**: Passwords are never stored in plain text; Bcrypt hashing is enforced via Laravel's `Hash::make()`.
- **Session Security**: Session IDs are regenerated upon login (`$request->session()->regenerate()`) and completely invalidated on logout (`$request->session()->invalidate()`).
- **Throttling & Rate Limiting**: Logins and password reset requests are protected by IP and email-based rate limiting (`throttle:5,1` — maximum 5 attempts per minute).
- **CSRF Protection**: All state-changing endpoints (`POST`, `PUT`, `PATCH`, `DELETE`) require verified CSRF tokens.

---

## 2. Multi-Tenant Architecture & Tenant Isolation

ORVIK Store operates as an isolated SaaS multi-tenant platform:
- **Tenant Definition**: Each business stall / retail shop is represented by a `businesses` record.
- **Tenant Context (`App\Services\TenantContext`)**: Resolved securely from the authenticated user's active membership (`business_user`), stored in the user's session (`active_business_id`).
- **Input Tampering Prevention**: Users cannot access another tenant by submitting a rogue `business_id` in request payloads or URL parameters.
- **Global Tenant Scope (`App\Traits\BelongsToTenant`)**: Future business-owned records (e.g. Products, Sales, Customers, Reports) automatically inherit global query constraints scoped to `business_id`.

---

## 3. Role & Permission Architecture

The authorization engine is permission-based rather than hard-coding role strings across controllers:

- **System Roles (`is_system = true`)**:
  - `OWNER`: Full operational and administrative control. Cannot be deleted, suspended, or demoted by staff.
  - `MANAGER`: Daily operations (Dashboard, Sales, Inventory, Reports, Customers, Staff view).
  - `CASHIER`: Rapid point-of-sale operator (Dashboard, Sales view & create, Inventory view).
- **Permission Matrix**: Granular permissions grouped by module (`dashboard.view`, `sales.create`, `inventory.update`, `staff.manage`, `roles.manage`, `settings.update`, `audit.view`, etc.).
- **Server-Side Enforcement**: Enforced via `App\Http\Middleware\AuthorizePermission` (`middleware('permission:staff.manage')`) and Laravel Model Policies (`StaffPolicy`, `BusinessPolicy`, `RolePolicy`).

---

## 4. Staff Management & Invitation System

- **Staff Invitations**: Owners and authorized managers generate secure, cryptographically random 64-character invitation tokens with a 7-day expiration window.
- **Single-Use Enforcement**: Tokens are permanently invalidated upon acceptance (`accepted_at = now()`) and cannot be replayed.
- **Status Safeguards**: Accounts can be `active`, `suspended`, or `deactivated`. Suspended accounts are immediately blocked at the middleware level (`EnsureAccountIsActive`).

---

## 5. Append-Only Audit Logging (`audit_logs`)

All critical security events are recorded with IP address, User Agent, and timestamp:
- `user.registered`
- `user.login` / `user.logout`
- `user.password_reset_requested` / `user.password_reset_completed`
- `staff.invited` / `staff.invitation_accepted` / `staff.role_updated` / `staff.suspended`
- `role.created` / `role.updated` / `role.deleted`
- `business.updated`

*Note: Passwords, tokens, and secrets are stripped prior to persistence.*

---

## 6. How to Create a Role & Add a Permission

### Adding a New Permission:
1. Define the permission record in `database/seeders/RolesAndPermissionsSeeder.php`:
```php
Permission::create([
    'name' => 'expenses.create',
    'display_name' => 'Create Expenses',
    'description' => 'Log daily stall operating expenses',
    'module' => 'expenses',
]);
```
2. Run `php artisan db:seed --class=RolesAndPermissionsSeeder`.

### Creating a Custom Role Programmatically:
```php
$role = Role::create([
    'name' => 'inventory_auditor',
    'display_name' => 'Inventory Auditor',
    'description' => 'Conducts stock counts and audits dead stock',
    'is_system' => false,
]);
$role->permissions()->sync([$permissionId1, $permissionId2]);
```

---

## 7. How to Test Tenant Isolation

Run the automated test suite using PHPUnit:
```powershell
C:\php\php.exe vendor/phpunit/phpunit/phpunit --colors=never
```
The `tests/Feature/TenantIsolationTest.php` suite explicitly tests cross-tenant boundaries by provisioning two separate businesses and asserting that User A receives `403 Forbidden` when attempting to access or manipulate User B's resources.
