# Fund Transfer API

This document describes how to use the API endpoint for performing single fund transfers between users with automatic agency commission calculation.

## Endpoint

*   **URL:** `/api/v1/transfer`
*   **Method:** `POST`
*   **Route Name:** `api.v1.transfer.store`

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
*   **Format:** The request body should be a JSON object containing transfer details.

*   **Transfer Request Object Structure:**
    ```json
    {
      "from_user": "agent 007",
      "to_user": "petya",
      "amount": 100.00,
      "description": "Payment for services"
    }
    ```
    *   `from_user` (string, required): Username of the sender. Must be an existing active user.
    *   `to_user` (string, required): Username of the recipient. Must be an existing active user and different from `from_user`.
    *   `amount` (numeric, required): Transfer amount. Must be greater than 0.01.
    *   `description` (string, optional): Optional description for the transfer. Defaults to "API Transfer" if not provided.

*   **Example `curl` Request:**

    Replace `YOUR_APP_URL` with the actual base URL of your application (e.g., `http://127.0.0.1:8000`) and `YOUR_API_KEY` with the value of `INTERNAL_API_KEY` from your `.env` file.

    ```bash
    curl -X POST http://127.0.0.1:8000/api/v1/transfer \
    -H "Authorization: Bearer your_api_key_here" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json" \
    -d '{
      "from_user": "agent 007",
      "to_user": "petya",
      "amount": 100.00,
      "description": "Commission transfer"
    }'
    ```

## Commission Logic

The API automatically applies agency commission when transferring FROM agency users:

*   **Agency Users**: Users with `role = 'agency'` and `terms > 0`
*   **Commission Calculation**: `final_amount = original_amount + (original_amount × commission_rate)`
*   **Example**: Transfer $100 from agency with 7.77% commission = $107.77 final amount

### Available Test Users

Based on the current system configuration:

**Agency Users (with automatic commission):**
*   `"agent 007"` - 7.77% commission rate
*   `"agent 001"` - 1.11% commission rate  
*   `"agent 0013"` - 2.5% commission rate

**Buyer Users (no commission):**
*   `"petya"`
*   `"ivan"`
*   `"ivan2"`

**System Users:**
*   `"System"` - for system transfers
*   `"admin"` - owner account

## Processing

*   The endpoint is handled by the `singleTransferApi` method in `App\Http\Controllers\Admin\FundTransferController`.
*   Request validation ensures users exist and are different.
*   Processing steps:
    1. **User Resolution**: Find users by username and verify they are active
    2. **Account Selection**: Automatically find appropriate USD accounts for both users (prioritizes `*_MAIN` accounts)
    3. **Commission Calculation**: If sender is agency with terms > 0, automatically add commission
    4. **Fund Transfer Creation**: Create record in `fund_transfers` table
    5. **Transaction Recording**: Use `TransactionService` to create double-entry accounting records
    6. **Logging**: Comprehensive logging throughout the process

*   **Account Selection Logic**:
    *   Automatically finds USD accounts for both users
    *   Prioritizes accounts with `*_MAIN` suffix (BUYER_MAIN, AGENCY_MAIN, etc.)
    *   Throws error if no suitable USD account found for either user

*   **Database Records Created**:
    *   `fund_transfers`: Records transfer details including final amount and commission info
    *   `transactions`: Main transaction record with description and accounting period
    *   `transaction_lines`: Double-entry debit/credit lines for proper accounting

## Responses

*   **Success (Status Code: 200 OK):**
    *   Returned when transfer is completed successfully.
    *   Body:
        ```json
        {
          "success": true,
          "message": "Transfer completed successfully",
          "data": {
            "fund_transfer_id": 123,
            "transaction_id": 456,
            "from_user": "agent 007",
            "to_user": "petya",
            "original_amount": 100.00,
            "final_amount": 107.77,
            "commission_applied": true,
            "commission_rate": 0.0777,
            "commission_amount": 7.77,
            "transfer_date": "2024-01-15",
            "description": "Commission transfer"
          }
        }
        ```

*   **Validation Error (Status Code: 422 Unprocessable Entity):**
    *   Returned when request validation fails.
    *   Body:
        ```json
        {
          "success": false,
          "message": "Validation failed",
          "errors": {
            "from_user": ["The selected from user is invalid."],
            "to_user": ["The to user and from user must be different."]
          }
        }
        ```

*   **Transfer Error (Status Code: 500 Internal Server Error):**
    *   Returned when transfer processing fails (insufficient funds, account issues, etc.).
    *   Body:
        ```json
        {
          "success": false,
          "message": "Transfer failed: No suitable USD account found for sender 'username'."
        }
        ```

*   **Unauthorized (Status Code: 401 Unauthorized):**
    *   Returned if the API key is missing, invalid, or not provided correctly.
    *   Body:
        ```json
        { "message": "Unauthorized." }
        ```

## Example Usage Scenarios

### 1. Agency Transfer with Commission

**Request:**
```bash
curl -X POST http://127.0.0.1:8000/api/v1/transfer \
  -H "Authorization: Bearer your_api_key" \
  -H "Content-Type: application/json" \
  -d '{
    "from_user": "agent 007",
    "to_user": "petya",
    "amount": 100.00,
    "description": "Agency commission transfer"
  }'
```

**Response:**
```json
{
  "success": true,
  "message": "Transfer completed successfully",
  "data": {
    "fund_transfer_id": 123,
    "transaction_id": 456,
    "from_user": "agent 007",
    "to_user": "petya",
    "original_amount": 100.00,
    "final_amount": 107.77,
    "commission_applied": true,
    "commission_rate": 0.0777,
    "commission_amount": 7.77,
    "transfer_date": "2024-01-15",
    "description": "Agency commission transfer"
  }
}
```

### 2. Regular Transfer (No Commission)

**Request:**
```bash
curl -X POST http://127.0.0.1:8000/api/v1/transfer \
  -H "Authorization: Bearer your_api_key" \
  -H "Content-Type: application/json" \
  -d '{
    "from_user": "petya",
    "to_user": "ivan",
    "amount": 50.00,
    "description": "Regular transfer"
  }'
```

**Response:**
```json
{
  "success": true,
  "message": "Transfer completed successfully",
  "data": {
    "fund_transfer_id": 124,
    "transaction_id": 457,
    "from_user": "petya",
    "to_user": "ivan",
    "original_amount": 50.00,
    "final_amount": 50.00,
    "commission_applied": false,
    "commission_rate": null,
    "commission_amount": null,
    "transfer_date": "2024-01-15",
    "description": "Regular transfer"
  }
}
```

### 3. System to User Transfer

**Request:**
```bash
curl -X POST http://127.0.0.1:8000/api/v1/transfer \
  -H "Authorization: Bearer your_api_key" \
  -H "Content-Type: application/json" \
  -d '{
    "from_user": "System",
    "to_user": "agent 007",
    "amount": 5000.00,
    "description": "Account funding"
  }'
```

## Error Handling

Common error scenarios and their responses:

### Invalid Username
```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "from_user": ["The selected from user is invalid."]
  }
}
```

### Same User Transfer
```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "to_user": ["The to user and from user must be different."]
  }
}
```

### Inactive User
```json
{
  "success": false,
  "message": "Transfer failed: Both users must be active to perform transfers."
}
```

### No Suitable Account
```json
{
  "success": false,
  "message": "Transfer failed: No suitable USD account found for recipient 'username'."
}
```

### Invalid Amount
```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "amount": ["The amount must be at least 0.01."]
  }
}
```

## Related Files

*   **Controller:** `App\Http\Controllers\Admin\FundTransferController` (`singleTransferApi` method)
*   **Service:** `App\Services\TransactionService` (handles double-entry accounting)
*   **Middleware:** `App\Http\Middleware\AuthenticateInternalApiKey` (Alias: `auth.internal_api`)
*   **Routes:** Defined in `routes/api.php`
*   **Models:** 
    *   `App\Models\User` (user management)
    *   `App\Models\Account` (account management)
    *   `App\Models\FundTransfer` (transfer records)
    *   `App\Models\Transaction` (transaction records)
    *   `App\Models\TransactionLine` (double-entry lines)
*   **Config:** `config/services.php` (for API key configuration)
*   **Environment:** `.env` (for `INTERNAL_API_KEY`)

## Integration Notes

*   This API endpoint can be used by external systems (like Telegram bots) to automate fund transfers
*   All transfers are logged comprehensively for audit purposes
*   The endpoint maintains the same business logic as the web interface
*   Commission calculation is automatic and cannot be disabled via API (maintains business rule consistency)
*   Only USD transfers are currently supported
*   The API defaults to using the admin user (ID: 1) for `created_by` if no authentication context is available 