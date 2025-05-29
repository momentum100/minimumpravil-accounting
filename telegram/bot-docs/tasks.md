# Telegram Finance Bot - Development Tasks

## Current Issues & Requirements

### 1. Language Support Configuration 🌐
**Requirement:** Use only Russian language strings from `ru.json`
- **Priority:** MEDIUM
- **Status:** Open
- **Details:**
  - All bot messages must be in Russian
  - Load strings from `telegram/src/lang/ru.json`
  - Implement proper language file structure
  - Remove any hardcoded English strings
- **Files to update:**
  - `telegram/src/lang/ru.json` (currently empty)
  - Bot message handlers
  - Error messages
  - User interface text

### 2. API Integration & Authorization 🔐
**Requirement:** Send data packs to API endpoint only by authorized users
- **Priority:** HIGH
- **Status:** Open
- **Details:**
  - Only users listed in `ADMINS = [51337503, 397224949]` can send data
  - API endpoint: `http://localhost:8000/api/v1/`
  - Implement proper authorization checks
  - Secure data transmission to Laravel backend
- **Security considerations:**
  - Validate user ID against admin list
  - Implement proper error handling for unauthorized access
  - Log all API interactions

## Bot Menu Structure 📋

### Inline Menu Requirements
**3-item inline keyboard menu:**

#### 1. /start Command
- **Function:** Initialize bot interaction
- **Description:** Welcome message with available options
- **Authorization:** All users
- **Response:** Show main menu with available commands

#### 2. /buyer_expenses Command  
- **Function:** Record daily employee expenses via 4-line text input
- **Description:** Log employee daily expenses to accounting system
- **Authorization:** Admin users only
- **Input Format:**
  ```
  /buyer_expenses
  username
  category
  amount
  price_per_one
  ```
- **Example:**
  ```
  /buyer_expenses
  petya
  proxy
  10
  3
  ```
- **API Endpoint:** `POST API_ENDPOINT/bulk-expenses`

#### 3. /buyer_transfers Command
- **Function:** Record expenses for employee (transfers) via 4-line text input
- **Description:** Record transfer payments between users
- **Authorization:** Admin users only  
- **Input Format:**
  ```
  /buyer_transfers
  from_username
  to_username
  amount
  comment
  ```
- **Example:**
  ```
  /buyer_transfers
  AC
  petya
  1000
  traffic
  ```
- **API Endpoint:** `POST API_ENDPOINT/bulk-transfers`

## Implementation Checklist ✅

### Phase 1: Foundation Setup
- [ ] Configure Russian language support (`ru.json`)
- [ ] Use admin authorization from `config.py` (no implementation needed - just check user ID against `ADMINS` list)
- [ ] Test API connectivity to Laravel backend

### Phase 2: Core Commands
- [ ] Implement `/start` command with inline menu
- [ ] Create `/buyer_expenses` workflow
- [ ] Create `/buyer_transfers` workflow
- [ ] Add proper error handling and validation

### Phase 3: Integration & Testing
- [ ] Test all commands with authorized users
- [ ] Verify unauthorized access is blocked
- [ ] Test API data submission
- [ ] Validate Russian language display

## Technical Specifications

### Configuration Files
- **Bot Config:** `telegram/src/config/config.py`
- **Language File:** `telegram/src/lang/ru.json`
- **Admin Users:** `[51337503, 397224949]`
- **API Base:** `http://localhost:8000/api/v1/`

### API-Only Architecture
- **No direct database connection** - bot communicates only via API
- **Data source:** Laravel backend provides all data via API endpoints
- **Expense endpoint:** `POST /api/v1/bulk-expenses`
- **Transfer endpoint:** `POST /api/v1/bulk-transfers`
- **Data submission:** Multi-line text parsing and JSON payload

### API Integration
- **Framework:** aiogram 3
- **HTTP Client:** aiohttp for API calls
- **Authentication:** Bearer token or API key
- **Data Format:** JSON payload
- **Input Processing:** Parse 4-line text input per command

## Notes & Considerations 📝

1. **Error Handling:** All operations must have proper error handling with Russian error messages
2. **Logging:** Implement comprehensive logging for debugging and audit trails
3. **Security:** Validate all user inputs before API submission
4. **Performance:** Optimize API calls and caching where appropriate
5. **Scalability:** Design for multiple concurrent users

## Dependencies

### Python Packages
- `aiogram>=3.0`
- `aiohttp`
- `asyncio`
- `python-dotenv`

### External Services
- **Telegram Bot API**
- **Laravel 12 API** (acne-accounting)

---

**Last Updated:** December 2024  
**Next Review:** Weekly during development phase 