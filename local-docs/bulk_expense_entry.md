# Bulk Daily Expense Entry (Web UI)

This document describes the feature for entering multiple daily expense records via a web interface.

## Access

*   **URL:** `/admin/bulk-expenses/create`
*   **Route Name:** `admin.bulk-expenses.create`
*   **Roles:** Accessible only to users with the `admin`, `finance`, or `owner` role.
    *   Access is controlled by the `admin` middleware group (checking for `admin` or `owner`) applied to `/admin/*` routes and the `admin_or_finance` middleware (checking for `admin`, `finance`, or `owner`) applied specifically to this route.

## Functionality

1.  **Interface:** Provides a large textarea for pasting or typing expense data.
2.  **Input Format:**
    *   Each expense record must consist of exactly **4 non-empty lines**.
    *   Line 1: Buyer Username (must match the `name` field of an existing user with the `buyer` role).
    *   Line 2: Category (string, e.g., "farmik", "creo").
    *   Line 3: Quantity (numeric, non-negative).
    *   Line 4: Tariff (numeric, non-negative).
    *   Records should be separated by at least one blank line (optional, but improves readability).
3.  **Processing (`POST` to `/admin/bulk-expenses` - Route Name: `admin.bulk-expenses.store`):
    *   The input text is split into lines, trimmed, and empty lines are removed.
    *   The lines are grouped into chunks of 4.
    *   Each chunk is validated:
        *   Must contain exactly 4 lines.
        *   Quantity and Tariff must be numeric and non-negative.
        *   The Buyer Username must correspond to an existing user with the `buyer` role.
    *   For each valid record:
        *   A `DailyExpense` model is created with:
            *   `buyer_id`: ID of the found buyer user.
            *   `operation_date`: Set to the current date (`Carbon::now()->toDateString()`) when the form is processed.
            *   `category`: From input.
            *   `quantity`: From input.
            *   `tariff`: From input.
            *   `total`: Calculated as `quantity * tariff`.
            *   `created_by`: ID of the logged-in admin/finance/owner user submitting the form.
            *   `comment`: Automatically set to "Bulk entry via web form".
        *   The creation of a `DailyExpense` triggers the `DailyExpenseObserver`, which automatically creates the corresponding `Transaction` and `TransactionLine` records for the double-entry accounting.
4.  **Logging:**
    *   **Raw Input Log:** Before validation, each raw 4-line input chunk (even invalid ones) is immediately logged as a JSON array to `app/private/logs/bulk_expenses_raw_input.log`.
    *   **Processing Status Log:** After attempting validation and saving, the processing outcome for each record is logged as a single line in JSON format to `app/private/logs/bulk_expenses.log`.
    *   The processing log entry includes:
        *   `submitted_at`: Timestamp of submission.
        *   `submitted_by`: ID of the user who submitted.
        *   `status`: 'success' or 'failed'.
        *   `buyer_username`, `category`, `quantity`, `tariff`: Input values.
        *   `raw_input_ref`: Reference to the original 4-line chunk.
        *   `error`: Error message if processing failed.
5.  **Feedback:**
    *   If all records process successfully, the user is redirected back to the form with a success message indicating the number of records processed.
    *   If any errors occur during validation or saving, the user is redirected back to the form with:
        *   A list of specific errors encountered (including the starting line number and buyer username for context).
        *   The original input data repopulated in the textarea (`old('expense_data')`).

## Related Files

*   **Controller:** `App\Http\Controllers\Admin\BulkExpenseController` (`create`, `store` methods)
*   **View:** `resources\views\admin\bulk-expenses\create.blade.php`
*   **Middleware:** `App\Http\Middleware\EnsureUserIsAdminOrFinance` (Alias: `admin_or_finance`)
*   **Routes:** Defined in `routes/web.php` within the `admin` group.
*   **Model:** `App\Models\DailyExpense`
*   **Observer:** `App\Observers\DailyExpenseObserver` (Handles transaction creation)
*   **Log Files:**
    *   `app/private/logs/bulk_expenses_raw_input.log` (Raw 4-line input)
    *   `app/private/logs/bulk_expenses.log` (Processing status) 