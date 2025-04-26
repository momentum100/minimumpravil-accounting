# Authentication and Routing Strategy

This document outlines the authentication and routing strategy implemented in the ACNE Accounting application (Laravel 12).

## 1. User Roles

The application defines user roles using an `enum` column named `role` in the `users` table. The defined roles are:

*   `owner`
*   `finance`
*   `buyer`
*   `admin`
*   `agency`

## 2. Authentication

*   Standard Laravel authentication provided by Breeze/Fortify is used.
*   The primary controller handling login is `App\Http\Controllers\Auth\AuthenticatedSessionController`.
*   User registration routes (`/register`) are currently disabled in `routes/auth.php`.

## 3. Login Redirection

Upon successful login, users are redirected based on their role. This logic is implemented in the `store` method of `App\Http\Controllers\Auth\AuthenticatedSessionController`:

*   **Buyers (`buyer` role):** Redirected to `/buyer/dashboard` (route name `buyer.dashboard`).
*   **Admins (`admin` role):** Redirected to `/admin/dashboard` (route name `admin.dashboard`).
*   **Other Roles:** Redirected to the default `/dashboard` (route name `dashboard`).
*   The `redirect()->intended()` method is used to redirect users back to their originally requested page if applicable, falling back to the role-based dashboard.

## 4. Middleware

Several custom middleware classes are used to control access based on roles:

*   **`App\Http\Middleware\EnsureUserIsAdmin` (Alias: `admin`)**
    *   Ensures the authenticated user has the `admin` role.
    *   Applied to the `/admin/*` route group.
*   **`App\Http\Middleware\EnsureUserIsBuyer` (Alias: `buyer`)**
    *   Ensures the authenticated user has the `buyer` role.
    *   Applied to the `/buyer/*` route group.
*   **`App\Http\Middleware\EnsureUserIsNotBuyer` (Alias: `not.buyer`)**
    *   Ensures the authenticated user does *not* have the `buyer` role.
    *   Applied specifically to the `DELETE /profile` route to prevent buyers from deleting their accounts.
*   **`App\Http\Middleware\RedirectIfAuthenticated` (Core Middleware)**
    *   Redirects already authenticated users away from guest routes (e.g., `/login`).
    *   Modified to include role-based redirection logic:
        *   Buyers are redirected to `/buyer/dashboard`.
        *   Other authenticated users are redirected to `/dashboard`.

Middleware aliases (`admin`, `buyer`, `not.buyer`) are registered in `bootstrap/app.php`.

## 5. Routing

Route definitions are primarily split between:

*   **`routes/web.php`**: Contains main application routes, including:
    *   Standard `/dashboard`.
    *   Profile routes (`/profile`) within an `auth` middleware group.
    *   Admin route group (`/admin/*`) protected by `auth`, `verified`, and `admin` middleware.
    *   Buyer route group (`/buyer/*`) protected by `auth`, `verified`, and `buyer` middleware.
    *   Includes `routes/auth.php`.
*   **`routes/auth.php`**: Contains standard authentication routes provided by Breeze/Fortify (login, password reset, email verification etc.), excluding registration.

### Key Route Groups & Access Control:

*   **`/` (Welcome Page):** Publicly accessible.
*   **`/dashboard`:** Accessible only to authenticated and verified users (non-admin, non-buyer roles default here).
*   **`/login`, `/forgot-password`, etc.:** Guest routes, handled by `routes/auth.php`.
*   **`/profile` (`profile.edit`, `profile.update`, `profile.destroy`):** Accessible to authenticated users. Account deletion (`profile.destroy`) is further restricted by:
    *   The `not.buyer` middleware at the route level.
    *   Conditionally hidden UI elements in `profile/edit.blade.php` for buyers.
*   **`/admin/*`:** Accessible only to authenticated, verified users with the `admin` role.
*   **`/buyer/dashboard`:** Accessible only to authenticated, verified users with the `buyer` role. 