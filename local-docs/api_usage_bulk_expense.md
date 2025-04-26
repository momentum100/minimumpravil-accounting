# Bulk Daily Expense Entry (API)

This document describes how to use the API endpoint for submitting bulk daily expense records.

## Endpoint

*   **URL:** `/api/v1/bulk-expenses`
*   **Method:** `POST`
*   **Route Name:** `api.v1.bulk-expenses.store`

## Authentication

*   Requires API Key authentication.
*   The API key must be provided in the `Authorization` header as a Bearer token:
    ```
    Authorization: Bearer your_secure_api_key_here
    ```
*   The valid API key is configured in `config/services.php` and sourced from the `INTERNAL_API_KEY` environment variable.
*   Access is controlled by the `auth.internal_api` middleware alias, which points to `App\Http\Middleware\AuthenticateInternalApiKey`.

## Request Body

*   **Content-Type:** `application/json`
*   **Format:** The request body should be a JSON object containing a single key `expense_records`. The value of `expense_records` should be an array of objects, where each object represents one expense record.

*   **Expense Record Object Structure:**
    ```json
    {
      "buyer_username": "petya",
      "category": "creo",
      "quantity": 1,
      "tariff": 44,
      "comment": "Optional comment from API" // Optional field
    }
    ```
    *   `buyer_username` (string, required): Name of an existing user with the `buyer` role.
    *   `category` (string, required): Expense category.
    *   `quantity` (numeric, required): Must be non-negative.
    *   `tariff` (numeric, required): Must be non-negative.
    *   `comment` (string, optional): An optional comment for the expense.

*   **Example Request Body:**
    ```json
    {
      "expense_records": [
        {
          "buyer_username": "petya",
          "category": "creo",
          "quantity": 1,
          "tariff": 44
        },
        {
          "buyer_username": "ivan",
          "category": "farmik",
          "quantity": 10.5,
          "tariff": 2.1,
          "comment": "From Telegram Bot"
        }
      ]
    }
    ```

*   **Example `curl` Request:**

    Replace `YOUR_APP_URL` with the actual base URL of your application (e.g., `http://127.0.0.1:8000`) and `YOUR_API_KEY` with the value of `INTERNAL_API_KEY` from your `.env` file.

    ```bash
    curl -X POST http://127.0.0.1:8000/api/v1/bulk-expenses \
    -H "Authorization: Bearer api_key" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json" \
    -d '{
      "expense_records": [
        {
          "buyer_username": "petya",
          "category": "creo",
          "quantity": 1,
          "tariff": 44
        },
        {
          "buyer_username": "ivan",
          "category": "farmik",
          "quantity": 10.5,
          "tariff": 2.1,
          "comment": "From Telegram Bot"
        }
      ]
    }'
    ```

## Processing

*   The endpoint is handled by the `storeApi` method in `App\Http\Controllers\Admin\BulkExpenseController`.
*   The `expense_records` array is validated.
*   Each record in the array is processed individually:
    *   Input fields (`buyer_username`, `category`, `quantity`, `tariff`, `comment`) are validated (similar rules as web UI: buyer exists, numeric fields are valid).
    *   If valid, a `DailyExpense` record is created:
        *   `buyer_id`: ID of the found buyer user.
        *   `operation_date`: Set to the current date (`Carbon::now()->toDateString()`) when the API request is processed.
        *   `category`: From input.
        *   `quantity`: From input.
        *   `tariff`: From input.
        *   `total`: Calculated as `quantity * tariff`.
        *   `created_by`: Set to the user ID configured in `config('services.internal_api.user_id')` (sourced from `INTERNAL_API_USER_ID` env var).
        *   `comment`: From the optional `comment` field in the input, or null.
    *   Creation triggers the `DailyExpenseObserver` for automatic `Transaction` generation.
*   **Logging:** Similar to the web UI, each attempted record (validation success/failure, db success/failure) is logged as a JSON line to `app/private/logs/bulk_expenses_api.log`. The log entry includes `api_user_id` (from config), status, the input record, and any error message.

## Responses

*   **Success (Status Code: 200 OK):**
    *   Returned if all records in the request were processed successfully.
    *   Body:
        ```json
        {
          "message": "Successfully processed X expense records.",
          "processed_count": X,
          "results": [
            { "status": "success", "input": { ... record 1 ... } },
            { "status": "success", "input": { ... record 2 ... } }
          ]
        }
        ```
*   **Partial Success / Errors (Status Code: 422 Unprocessable Entity):**
    *   Returned if some records failed validation or saving, but others might have succeeded.
    *   Body:
        ```json
        {
          "message": "Processed X records with Y errors.",
          "processed_count": X,
          "error_count": Y,
          "results": [
            { "status": "success", "input": { ... record 1 ... } },
            { "status": "failed", "input": { ... record 2 ... }, "error": "Buyer user 'xyz' not found." },
            { "status": "failed", "input": { ... record 3 ... }, "error": "Quantity must be numeric." }
          ]
        }
        ```
*   **Bad Request (Status Code: 400 Bad Request):**
    *   Returned if the overall request structure is invalid (e.g., missing `expense_records` key, not valid JSON).
    *   Body:
        ```json
        { "message": "Invalid request format. Expected JSON with 'expense_records' array." }
        ```
*   **Unauthorized (Status Code: 401 Unauthorized):**
    *   Returned if the API key is missing, invalid, or not provided correctly.
    *   Body:
        ```json
        { "message": "Unauthorized." }
        ```

## Related Files

*   **Controller:** `App\Http\Controllers\Admin\BulkExpenseController` (`storeApi` method - *to be implemented*)
*   **Middleware:** `App\Http\Middleware\AuthenticateInternalApiKey` (Alias: `auth.internal_api`)
*   **Routes:** Defined in `routes/api.php`.
*   **Model:** `App\Models\DailyExpense`
*   **Observer:** `App\Observers\DailyExpenseObserver`
*   **Log File:** `app/private/logs/bulk_expenses_api.log` (API processing status)
*   **Config:** `config/services.php` (for API key and user ID)
*   **Environment:** `.env` (for `INTERNAL_API_KEY`, `INTERNAL_API_USER_ID`) 