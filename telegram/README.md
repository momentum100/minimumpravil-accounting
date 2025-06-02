# Telegram Finance Bot

Telegram bot for recording financial transactions and integrating with the Laravel accounting system.

## Features

- **Persistent menu buttons** below chat input for quick access
- **Inline button interface** for easy navigation
- **4-line text input format** for quick data entry
- **Russian language interface** (loaded from `ru.json`)
- **Admin-only access** (configured in `config.py`)
- **API integration** with Laravel backend using proper bulk format
- **API authentication** with Bearer token
- **Real-time validation** and confirmation
- **Cancel operation functionality** with return to menu
- **State management** for user interactions
- **Admin startup notifications** when bot starts
- **Comprehensive logging**

## 🚀 Recent Updates

✅ **Fund Transfer API Integration Completed!** 

The telegram bot now successfully integrates with the Laravel API for fund transfers using the new `/api/v1/transfer` endpoint.

### Key Features:
- **Username-based transfers**: Use usernames instead of user IDs  
- **Automatic commission calculation**: Agency users automatically get commission applied
- **Smart account selection**: Automatically finds appropriate USD accounts
- **Comprehensive error handling**: Clear error messages for various scenarios
- **Detailed transfer confirmations**: Shows commission info, transfer IDs, and amounts

## Setup

### 1. Install Dependencies

```bash
cd telegram
python -m venv venv
source venv/bin/activate  # On Windows: venv\Scripts\activate
pip install -r requirements.txt
```

### 2. Configuration

Edit `src/config/config.py`:
- Set your bot token (`BOT_API_KEY`)
- Configure admin user IDs (`ADMINS`)
- Set API endpoint (`API_ENDPOINT`)
- Set API authentication key (`API_KEY`)
- Set INTERNAL_API_USER_ID (`INTERNAL_API_USER_ID`)

Example configuration:
```python
BOT_NAME = "@Fb_unlock_bot"
BOT_API_KEY = "5490657427:AAGzJLCBHzqTvL4WSE7k36eQUfDqgd5qIHc"
ADMINS = [51337503, 397224949]
API_ENDPOINT = "http://localhost:8000/api/v1/"
API_KEY = "api_21fhsdfbHJvbjh24iusdjh2"
INTERNAL_API_USER_ID = 1
```

### 3. Run the Bot

```bash
python main.py
```

When the bot starts, all configured admins will receive a notification message.

### 4. Install Python Dependencies

```bash
cd telegram
pip install -r requirements.txt
```

### 5. Test API Integration

```bash
python test_api_integration.py
```

This will test connectivity between the bot and Laravel API.

## 🚀 Production Deployment with Systemd

### Setting up Telegram Bot as a System Service

For production deployment, you can run the telegram bot as a systemd service so it starts automatically on boot and restarts if it crashes.

#### 1. Create Systemd Service File

Create a service file for the telegram bot:

```bash
sudo nano /etc/systemd/system/telegram-finance-bot.service
```

Add the following content:

```ini
[Unit]
Description=Telegram Finance Bot
After=network.target
After=syslog.target

[Service]
Type=simple
User=www-data
Group=www-data
WorkingDirectory=/var/www/minimumpravil-accounting/telegram
Environment=PATH=/var/www/minimumpravil-accounting/telegram/venv/bin
ExecStart=/var/www/minimumpravil-accounting/telegram/venv/bin/python main.py
ExecReload=/bin/kill -HUP $MAINPID
KillMode=mixed
Restart=always
RestartSec=10

# Logging
StandardOutput=journal
StandardError=journal
SyslogIdentifier=telegram-finance-bot

[Install]
WantedBy=multi-user.target
```

#### 2. Set Proper Permissions

Ensure the bot files have correct ownership and permissions:

```bash
# Set ownership to www-data (or your web server user)
sudo chown -R www-data:www-data /var/www/minimumpravil-accounting/telegram

# Set proper permissions
sudo chmod -R 755 /var/www/minimumpravil-accounting/telegram
sudo chmod +x /var/www/minimumpravil-accounting/telegram/main.py

# Make sure virtual environment is accessible
sudo chmod -R 755 /var/www/minimumpravil-accounting/telegram/venv
```

#### 3. Enable and Start the Service

```bash
# Reload systemd to recognize the new service
sudo systemctl daemon-reload

# Enable the service to start on boot
sudo systemctl enable telegram-finance-bot.service

# Start the service
sudo systemctl start telegram-finance-bot.service

# Check service status
sudo systemctl status telegram-finance-bot.service
```

#### 4. Service Management Commands

```bash
# Start the service
sudo systemctl start telegram-finance-bot

# Stop the service
sudo systemctl stop telegram-finance-bot

# Restart the service
sudo systemctl restart telegram-finance-bot

# Check service status
sudo systemctl status telegram-finance-bot

# View service logs
sudo journalctl -u telegram-finance-bot -f

# View recent logs
sudo journalctl -u telegram-finance-bot --since "1 hour ago"

# View logs from specific date
sudo journalctl -u telegram-finance-bot --since "2024-12-01"
```

#### 5. Configuration for Different Users

If you want to run the service as a different user (not www-data), update the service file:

```ini
# For a specific user (replace 'youruser' with actual username)
User=youruser
Group=youruser

# Make sure the user has access to the files
```

Then set ownership accordingly:
```bash
sudo chown -R youruser:youruser /var/www/minimumpravil-accounting/telegram
```

#### 6. Environment Variables

If you need to set additional environment variables, add them to the service file:

```ini
[Service]
Environment=PATH=/var/www/minimumpravil-accounting/telegram/venv/bin
Environment=PYTHONPATH=/var/www/minimumpravil-accounting/telegram
Environment=PYTHONUNBUFFERED=1
# Add other environment variables as needed
```

#### 7. Log Rotation

To prevent log files from growing too large, you can configure log rotation. Create a logrotate configuration:

```bash
sudo nano /etc/logrotate.d/telegram-finance-bot
```

Add:
```
/var/www/minimumpravil-accounting/telegram/bot.log {
    daily
    missingok
    rotate 7
    compress
    delaycompress
    notifempty
    postrotate
        systemctl reload telegram-finance-bot
    endscript
}
```

#### 8. Monitoring and Health Checks

Add a simple health check script:

```bash
# Create health check script
sudo nano /var/www/minimumpravil-accounting/telegram/health_check.sh
```

```bash
#!/bin/bash
# Simple health check for telegram bot service

SERVICE_NAME="telegram-finance-bot"

if systemctl is-active --quiet $SERVICE_NAME; then
    echo "✅ $SERVICE_NAME is running"
    exit 0
else
    echo "❌ $SERVICE_NAME is not running"
    echo "Attempting to restart..."
    systemctl restart $SERVICE_NAME
    sleep 5
    if systemctl is-active --quiet $SERVICE_NAME; then
        echo "✅ $SERVICE_NAME restarted successfully"
        exit 0
    else
        echo "❌ Failed to restart $SERVICE_NAME"
        exit 1
    fi
fi
```

Make it executable:
```bash
sudo chmod +x /var/www/minimumpravil-accounting/telegram/health_check.sh
```

#### 9. Troubleshooting Service Issues

**Service won't start:**
```bash
# Check service status
sudo systemctl status telegram-finance-bot

# Check service logs
sudo journalctl -u telegram-finance-bot --no-pager

# Test manually
cd /var/www/minimumpravil-accounting/telegram
sudo -u www-data /var/www/minimumpravil-accounting/telegram/venv/bin/python main.py
```

**Permission issues:**
```bash
# Check file ownership
ls -la /var/www/minimumpravil-accounting/telegram/

# Check virtual environment
ls -la /var/www/minimumpravil-accounting/telegram/venv/bin/

# Fix permissions if needed
sudo chown -R www-data:www-data /var/www/minimumpravil-accounting/telegram
```

**Python path issues:**
```bash
# Test Python executable
sudo -u www-data /var/www/minimumpravil-accounting/telegram/venv/bin/python --version

# Test imports
sudo -u www-data /var/www/minimumpravil-accounting/telegram/venv/bin/python -c "import sys; print(sys.path)"
```

#### 10. Security Considerations

- **File Permissions**: Ensure config files with API keys are not readable by other users
- **Service User**: Run the service as a dedicated user with minimal privileges
- **Log Access**: Restrict access to log files containing sensitive information

```bash
# Secure config file
sudo chmod 600 /var/www/minimumpravil-accounting/telegram/src/config/config.py
sudo chown www-data:www-data /var/www/minimumpravil-accounting/telegram/src/config/config.py

# Secure log file
sudo chmod 640 /var/www/minimumpravil-accounting/telegram/bot.log
sudo chown www-data:www-data /var/www/minimumpravil-accounting/telegram/bot.log
```

### Benefits of Systemd Service

- ✅ **Auto-start**: Bot starts automatically on server boot
- ✅ **Auto-restart**: Bot restarts automatically if it crashes
- ✅ **Logging**: Centralized logging through systemd journal
- ✅ **Management**: Easy start/stop/restart commands
- ✅ **Monitoring**: Integration with system monitoring tools
- ✅ **Security**: Runs with appropriate user permissions
- ✅ **Resource Control**: Can set memory/CPU limits if needed

## 🔧 API Configuration Improvements

### Centralized User ID Management

The bot now uses a centralized `INTERNAL_API_USER_ID` configuration that matches Laravel's setup:

- **Laravel**: Uses `INTERNAL_API_USER_ID=1` in `.env`
- **Telegram Bot**: Uses `INTERNAL_API_USER_ID = 1` in `config.py`
- **Benefits**: 
  - Consistent user tracking across API calls
  - Simplified API integration (no need to pass user IDs manually)
  - Better audit trail in Laravel logs
  - Centralized configuration management

### Automatic User Context

All API calls now automatically include the configured user context:
- **Expenses**: Include both Telegram user ID and API user ID in comments
- **Transfers**: Use API user ID for authorization tracking
- **Logging**: Shows which API user ID is being used for each call

## 🧪 Testing Fund Transfers

## Usage

### Getting Started

1. Start a conversation with the bot using `/start`
2. You'll see:
   - **Persistent menu buttons** below the chat input: 📦 Расходы, 💸 Переводы, ❓ Помощь
   - **Inline menu** with two action buttons
3. Use either the persistent buttons or inline buttons to navigate

### Persistent Menu Buttons

The bot provides persistent buttons below the chat input line:

- **📦 Расходы** - Quick access to expense recording
- **💸 Переводы** - Quick access to transfer recording  
- **❓ Помощь** - Show help information

These buttons are always visible and provide the fastest way to access bot functions.

### Recording Employee Expenses

**Method 1: Using persistent menu button**
1. Click the **📦 Расходы** button below chat input
2. Follow the prompts for 4-line input

**Method 2: Using inline menu**
1. Click the **📦 Расходы сотрудника** inline button
2. Follow the prompts for 4-line input

**Input format:**
```
buyer_username
category
quantity
tariff
```

**Example:**
```
petya
proxy
10
3
```

**API Data Mapping:**
- Line 1 (buyer_username) → `buyer_username` field
- Line 2 (category) → `category` field  
- Line 3 (quantity) → `quantity` field (integer)
- Line 4 (tariff) → `tariff` field (float)
- Telegram User ID → included in `comment` field

### Recording Transfers

**Method 1: Using persistent menu button**
1. Click the **💸 Переводы** button below chat input
2. Follow the prompts for 4-line input

**Method 2: Using inline menu**
1. Click the **💸 Переводы между пользователями** inline button
2. Follow the prompts for 4-line input

**Input format:**
```
from_username
to_username
amount
comment
```

**Example:**
```
AC
petya
1000
traffic
```

### Help Function

Click the **❓ Помощь** persistent button to see:
- Available commands summary
- Usage instructions
- Format examples

### Navigation

- **Cancel Operation**: Click ❌ **Отмена** to cancel current operation
- **Back to Menu**: Click 🔙 **Назад в меню** to return to main menu
- **Start Over**: Use `/start` command to return to main menu anytime
- **Quick Access**: Use persistent menu buttons for immediate action

### Legacy Support

The bot still supports the old command format for compatibility:

#### /buyer_expenses
```
/buyer_expenses
buyer_username
category
quantity
tariff
```

#### /buyer_transfers
```
/buyer_transfers
from_username
to_username
amount
comment
```

## User Experience Flow

```
/start → Persistent Menu + Inline Menu → Select Action → Enter Data → Confirm → Success/Error → Back to Menu
```

1. **Start**: `/start` shows both persistent and inline menus
2. **Selection**: Choose action via persistent button or inline button
3. **Input Prompt**: Clear instructions with cancel option
4. **Data Entry**: User sends 4-line text
5. **Validation**: Real-time error checking
6. **Confirmation**: Review data before submission
7. **Result**: Success message or error with back button
8. **Return**: Back to main menu or use persistent buttons for next action

## Admin Features

### Startup Notifications

When the bot starts up, all users listed in `ADMINS` configuration will receive a notification message:
"🤖 Бот учета финансов запущен и готов к работе!"

This helps admins know when the bot is available and operational.

### Access Control

Only users with IDs listed in the `ADMINS` configuration can:
- Use any bot commands
- See menu interfaces
- Submit data to the API

## Interface Types

### 1. Persistent Menu (Reply Keyboard)
- **Location**: Below chat input field
- **Always visible**: Yes
- **Buttons**: 📦 Расходы, 💸 Переводы, ❓ Помощь
- **Purpose**: Quick access to main functions

### 2. Inline Menu (Inline Keyboard)
- **Location**: Above messages
- **Context**: Shows with specific messages
- **Buttons**: Various action and confirmation buttons
- **Purpose**: Structured interaction flow

## API Integration

### Endpoints

The bot communicates with the Laravel backend via these endpoints:

- **POST** `/api/v1/bulk-expenses` - Submit expense records in bulk format
- **POST** `/api/v1/bulk-transfers` - Submit transfer records

### Authentication

All API requests include authentication headers:
```http
Authorization: Bearer api_21fhsdfbHJvbjh24iusdjh2
X-API-Key: api_21fhsdfbHJvbjh24iusdjh2
Content-Type: application/json
Accept: application/json
```

### Expense Data Format

**Bot Input:**
```
petya
proxy
10
3
```

**API Request:**
```json
{
  "expense_records": [
    {
      "buyer_username": "petya",
      "category": "proxy",
      "quantity": 10,
      "tariff": 3.0,
      "comment": "From Telegram Bot - User ID: 51337503"
    }
  ]
}
```

**cURL Example:**
```bash
curl --location 'http://127.0.0.1:8000/api/v1/bulk-expenses' \
--header 'Authorization: Bearer api_21fhsdfbHJvbjh24iusdjh2' \
--header 'Content-Type: application/json' \
--header 'Accept: application/json' \
--data '{
  "expense_records": [
    {
      "buyer_username": "petya", 
      "category": "proxy",
      "quantity": 10,
      "tariff": 3.0,
      "comment": "From Telegram Bot - User ID: 51337503"
    }
  ]
}'
```

### Transfer Data Format

**Bot Input:**
```
AC
petya
1000
traffic
```

**API Request:**
```json
{
  "from_username": "AC",
  "to_username": "petya",
  "amount": 1000.0,
  "comment": "traffic",
  "transfer_date": "2024-12-01T10:30:00Z",
  "authorized_by": 51337503
}
```

## File Structure

```
telegram/
├── src/
│   ├── config/
│   │   └── config.py          # Bot configuration
│   ├── lang/
│   │   └── ru.json           # Russian language strings
│   ├── utils/
│   │   ├── lang.py           # Language utility
│   │   ├── api_client.py     # API client with authentication
│   │   └── validation.py     # Input validation
│   └── handlers.py           # Bot message handlers
├── main.py                   # Main bot file
├── requirements.txt          # Python dependencies
└── README.md                # This file
```

## State Management

The bot tracks user states to provide a smooth experience:

- **Default State**: Show main menu
- **waiting_expense_data**: Waiting for expense input after button press
- **waiting_transfer_data**: Waiting for transfer input after button press
- **confirmation**: Waiting for Yes/No confirmation
- **States are cleared**: On cancel, error, success, or /start

## Logging

The bot logs all activities to:
- **Console output** (INFO level)
- **bot.log file** (detailed logging including API requests/responses)
- **Admin notifications**: Sent to all admins on startup

### Log Examples

```
2024-12-01 10:30:00 - INFO - User 51337503 selected expenses from menu
2024-12-01 10:30:15 - INFO - Making POST request to http://localhost:8000/api/v1/bulk-expenses
2024-12-01 10:30:15 - DEBUG - Using API key: api_21fhsd...
2024-12-01 10:30:16 - INFO - Response status: 201
2024-12-01 10:30:16 - INFO - Expense submitted successfully by user 51337503
```

## Security

- Only admin users (configured in `ADMINS`) can use the bot
- All inputs are validated before API submission
- API authentication via Bearer token and X-API-Key headers
- Comprehensive error handling and logging
- Session cleanup after operations
- State management prevents unauthorized access
- Startup notifications confirm bot operational status
- API key logging shows only first 10 characters for security

## Development

### Adding New Commands

1. Add language strings to `src/lang/ru.json`
2. Create validation function in `src/utils/validation.py`
3. Add button to persistent or inline menu in `handlers.py`
4. Add callback handler and state management
5. Create data processing function
6. Update API client if new endpoint needed
7. Test with actual admin user IDs

### API Integration Guidelines

- Use bulk format for expense endpoints (`expense_records` array)
- Include authentication headers in all requests
- Map bot field names to API field names correctly
- Include Telegram context in comment fields
- Log API interactions for debugging

### Interface Design

- **Persistent Menu**: For frequently used actions
- **Inline Menu**: For workflow and confirmations
- **Cancel Buttons**: Available during data entry
- **Back Buttons**: Return to menu from results

### Testing

Test the bot with actual admin user IDs configured in `config.py`:

1. Test startup notification delivery
2. Test persistent menu button functionality
3. Test inline menu navigation
4. Test data entry flow for both commands
5. Test validation with invalid inputs
6. Test cancel functionality at each step
7. Test error handling and recovery
8. Test API integration with real Laravel backend
9. Verify API authentication works
10. Check logs for proper request/response handling

## Troubleshooting

### Common Issues

1. **Bot doesn't respond**: Check bot token and network connectivity
2. **No startup notification**: Check admin IDs in config and bot permissions
3. **Persistent buttons not showing**: Restart conversation with `/start`
4. **API errors**: Verify Laravel backend is running and endpoints are accessible
5. **Authentication errors**: Check API_KEY in config matches Laravel expectations
6. **Permission denied**: Ensure user ID is in the `ADMINS` list
7. **Validation errors**: Check input format matches required 4-line structure
8. **State issues**: Use `/start` to reset bot state
9. **Wrong API format**: Verify expense data uses `expense_records` array format

### Error Recovery

- Use `/start` command to reset bot state anytime
- Cancel button is available during data entry
- Back button returns to menu from error states
- All states are automatically cleared on errors
- Persistent menu always available for quick access

### API Debugging

Check `bot.log` for detailed information:
- Request URLs and methods
- Request data being sent
- API key usage (first 10 chars)
- Response status codes
- Response content
- Error messages

### Laravel Backend Requirements

Ensure your Laravel API:
- Accepts `POST /api/v1/bulk-expenses` with `expense_records` array
- Accepts `POST /api/v1/bulk-transfers` with transfer data
- Validates Bearer token: `api_21fhsdfbHJvbjh24iusdjh2`
- Returns appropriate HTTP status codes (200/201 for success)
- Returns JSON responses with proper error messages 

## 🔧 Setup & Configuration

### 1. Laravel API Configuration

First, ensure your Laravel app has the proper API key configured:

```bash
# In acne-accounting/.env, add:
INTERNAL_API_KEY=api_21fhsdfbHJvbjh24iusdjh2
INTERNAL_API_USER_ID=1
```

### 2. Start Laravel Application

```bash
cd acne-accounting
php artisan serve
# Should be running on http://localhost:8000
```

### 3. Verify API Configuration

The telegram bot configuration should match Laravel:

**File: `telegram/src/config/config.py`**
```python
API_ENDPOINT = "http://localhost:8000/api/v1/"
API_KEY = "api_21fhsdfbHJvbjh24iusdjh2"  # Must match Laravel INTERNAL_API_KEY
INTERNAL_API_USER_ID = 1  # Must match Laravel INTERNAL_API_USER_ID
```

### 4. Install Python Dependencies

```bash
cd telegram
pip install -r requirements.txt
```

### 5. Test API Integration

```bash
python test_api_integration.py
```

This will test connectivity between the bot and Laravel API.

## 🔧 API Configuration Improvements

### Centralized User ID Management

The bot now uses a centralized `INTERNAL_API_USER_ID` configuration that matches Laravel's setup:

- **Laravel**: Uses `INTERNAL_API_USER_ID=1` in `.env`
- **Telegram Bot**: Uses `INTERNAL_API_USER_ID = 1` in `config.py`
- **Benefits**: 
  - Consistent user tracking across API calls
  - Simplified API integration (no need to pass user IDs manually)
  - Better audit trail in Laravel logs
  - Centralized configuration management

### Automatic User Context

All API calls now automatically include the configured user context:
- **Expenses**: Include both Telegram user ID and API user ID in comments
- **Transfers**: Use API user ID for authorization tracking
- **Logging**: Shows which API user ID is being used for each call

## 🧪 Testing Fund Transfers