# Product Requirements Document (PRD)
## Telegram Finance Bot for Accounting System Integration

### Document Information
- **Version:** 1.1
- **Date:** December 2024
- **Project:** Telegram Finance Bot
- **System Integration:** Laravel 12 Accounting System (acne-accounting)
- **Last Updated:** API format corrected to match backend requirements

---

## 1. Executive Summary

### 1.1 Project Overview
The Telegram Finance Bot is a conversational interface designed to streamline financial data entry into the main accounting system. The bot serves as a mobile-first solution for recording buyer expenses and transfers, integrating seamlessly with the existing Laravel 12 accounting platform via authenticated API calls.

### 1.2 Business Value
- **Accessibility:** Enable financial data entry from anywhere via Telegram
- **Efficiency:** Reduce manual data entry time by 70%
- **Accuracy:** Implement structured data validation to minimize errors
- **Real-time:** Immediate synchronization with the accounting system
- **User Experience:** Intuitive conversational interface for non-technical users
- **Security:** API authentication ensures secure data transmission

---

## 2. Product Overview

### 2.1 Core Purpose
Provide a Telegram-based interface for financial data entry that integrates with the acne-accounting Laravel system via authenticated RESTful API using proper bulk data format.

### 2.2 Key Features
- **Buyer Expense Recording:** Structured expense entry with validation using bulk API format
- **Transfer Management:** Record transfers between buyers and agency
- **Real-time Integration:** Immediate authenticated API synchronization
- **Admin Controls:** Administrative functions for system management
- **Data Validation:** Multi-layer validation (bot + API)
- **Persistent Menu:** Always-visible menu buttons for quick access
- **API Authentication:** Secure Bearer token authentication

### 2.3 Technology Stack
- **Bot Framework:** aiogram 3 (Python)
- **Backend API:** Laravel 12 (PHP 8.3)
- **Database:** MySQL 8
- **Platform:** Telegram Bot API
- **Architecture:** Authenticated RESTful API integration
- **Authentication:** Bearer token + API key

---

## 3. Goals and Objectives

### 3.1 Primary Goals
1. **Streamline Data Entry:** Reduce time spent on manual financial data entry
2. **Improve Accessibility:** Enable mobile data entry via Telegram
3. **Ensure Data Integrity:** Implement robust validation and error handling
4. **Enhance User Experience:** Provide intuitive conversational interface

### 3.2 Success Metrics
- **Adoption Rate:** 90% of target users actively using the bot within 30 days
- **Data Accuracy:** 99%+ accuracy in financial entries
- **Response Time:** <2 seconds average API response time
- **User Satisfaction:** 4.5+ rating from user feedback surveys
- **System Uptime:** 99.9% availability

---

## 4. User Personas and Use Cases

### 4.1 Primary Users

#### 4.1.1 Buyers
- **Role:** Field agents recording expenses
- **Technical Level:** Basic
- **Primary Needs:** Quick, mobile expense recording
- **Usage Pattern:** Multiple daily entries, mobile-first

#### 4.1.2 Agency Administrators
- **Role:** Financial oversight and transfer management
- **Technical Level:** Intermediate
- **Primary Needs:** Transfer authorization, data oversight
- **Usage Pattern:** Daily monitoring, administrative functions

#### 4.1.3 System Administrators
- **Role:** Bot configuration and maintenance
- **Technical Level:** Advanced
- **Primary Needs:** System configuration, troubleshooting
- **Usage Pattern:** Configuration management, monitoring

### 4.2 Use Cases

#### 4.2.1 Buyer Expense Entry
**Actor:** Buyer
**Goal:** Record business expense
**Preconditions:** User authenticated, bot active
**Flow:**
1. User sends `/buyer_expenses` command followed by 4 lines of text:
   - Line 1: username (string)
   - Line 2: category (string) 
   - Line 3: amount (integer)
   - Line 4: price per one (float)
2. Bot validates input format and data
3. Bot displays summary for confirmation
4. Bot submits to API and confirms success

**Example:**
```
/buyer_expenses
petya
proxy
10
3
```

#### 4.2.2 Transfer Recording
**Actor:** Agency Administrator
**Goal:** Record transfer to buyer
**Preconditions:** Admin privileges, authenticated
**Flow:**
1. User sends `/buyer_transfers` command followed by 4 lines of text:
   - Line 1: from_username (string)
   - Line 2: to_username (string)
   - Line 3: amount (float)
   - Line 4: comment (string, optional)
2. Bot validates input format and authorization
3. Bot confirms and processes transfer

**Example:**
```
/buyer_transfers
AC
petya
1000
traffic
```

---

## 5. Functional Requirements

### 5.1 Core Commands

#### 5.1.1 /start Command
- **Purpose:** Initialize bot interaction
- **Functionality:**
  - Welcome message
  - User authentication check
  - Persistent menu buttons setup
  - Available commands menu
  - Admin detection and special menu

#### 5.1.2 /buyer_expenses Command (and Menu Button)
- **Purpose:** Record buyer expenses via 4-line text input
- **Input Format:**
  ```
  buyer_username
  category
  quantity
  tariff
  ```
- **Input Requirements:**
  - Line 1: buyer_username (string, 1-50 characters)
  - Line 2: category (string, 1-100 characters)
  - Line 3: quantity (integer, positive)
  - Line 4: tariff (float, positive, up to 2 decimal places)
- **Validation Rules:**
  - All 4 lines mandatory
  - Username validation against system
  - Numeric fields format validation
  - Reasonable quantity and tariff limits
- **API Endpoint:** `POST /api/v1/bulk-expenses`
- **API Format:** 
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
- **Output:** Confirmation with entry ID

#### 5.1.3 /buyer_transfers Command (and Menu Button)
- **Purpose:** Record transfers between users via 4-line text input
- **Input Format:**
  ```
  from_username
  to_username
  amount
  comment
  ```
- **Input Requirements:**
  - Line 1: from_username (string, 1-50 characters)
  - Line 2: to_username (string, 1-50 characters)
  - Line 3: amount (float, positive, up to 2 decimal places)
  - Line 4: comment (string, optional, max 200 characters)
- **Validation Rules:**
  - Admin authorization required
  - First 3 lines mandatory, comment optional
  - Username validation for both users
  - Transfer amount limits
- **API Endpoint:** `POST /api/v1/bulk-transfers`
- **API Format:**
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
- **Output:** Transfer confirmation with reference number

### 5.2 Interactive Features

#### 5.2.1 Persistent Menu Buttons
- **Location:** Below chat input field
- **Buttons:** 📦 Расходы, 💸 Переводы, ❓ Помощь
- **Always Visible:** Yes
- **Quick Access:** Direct function access

#### 5.2.2 Inline Keyboards
- **Buyer Selection:** Dynamic keyboard from database
- **Confirmation Dialogs:** Yes/No confirmations
- **Command Menu:** Available actions
- **Admin Functions:** Administrative commands

#### 5.2.3 Conversation Management
- **State Persistence:** Maintain conversation state
- **Input Validation:** Real-time validation feedback
- **Error Recovery:** Clear error messages and retry options
- **Session Timeout:** 10-minute inactivity timeout
- **Cancel Functionality:** Cancel operations anytime

### 5.3 Administrative Features

#### 5.3.1 Admin Panel
- **User Management:** View active users
- **Transaction History:** Recent entries summary
- **System Status:** API health check
- **Configuration:** Bot settings management
- **Startup Notifications:** Bot start alerts

#### 5.3.2 Reporting
- **Daily Summary:** Daily transaction summary
- **Error Logs:** Failed transaction reports
- **Usage Statistics:** User activity metrics

---

## 6. Technical Requirements

### 6.1 Bot Architecture

#### 6.1.1 Framework Requirements
- **aiogram 3.x:** Latest stable version
- **Python 3.11+:** Modern Python features
- **Async/Await:** Non-blocking operations
- **Type Hints:** Full type annotation

#### 6.1.2 Configuration Management
```python
# config/config.py structure
BOT_NAME = "@Fb_unlock_bot"
BOT_API_KEY = "5490657427:AAGzJLCBHzqTvL4WSE7k36eQUfDqgd5qIHc"
ADMINS = [51337503, 397224949]
API_ENDPOINT = "http://localhost:8000/api/v1/"
API_KEY = "api_21fhsdfbHJvbjh24iusdjh2"
LOGGING_CONFIG = {...}
```

### 6.2 API Integration

#### 6.2.1 Laravel API Endpoints
```
POST /api/v1/bulk-expenses   (with expense_records array)
POST /api/v1/bulk-transfers  (with transfer data)
GET  /api/v1/buyers          (optional)
GET  /api/v1/health          (optional)
```

#### 6.2.2 Authentication
- **Bearer Token:** `Authorization: Bearer api_21fhsdfbHJvbjh24iusdjh2`
- **API Key Header:** `X-API-Key: api_21fhsdfbHJvbjh24iusdjh2`
- **Rate Limiting:** 100 requests/minute per user
- **Error Handling:** Standard HTTP status codes

#### 6.2.3 Data Format

**Expense Entry (Corrected Format):**
```json
{
  "expense_records": [
    {
      "buyer_username": "petya",
      "category": "proxy",
      "quantity": 10,
      "tariff": 3.0,
      "comment": "From Telegram Bot - User ID: 987654321"
    }
  ]
}
```

**Transfer Entry:**
```json
{
  "from_username": "AC",
  "to_username": "petya", 
  "amount": 1000.0,
  "comment": "traffic",
  "transfer_date": "2024-12-01T10:30:00Z",
  "authorized_by": 123456789
}
```

### 6.3 Data Mapping

#### 6.3.1 Bot Input to API Mapping (Expenses)
| Bot Input Line | Bot Field | API Field | Type | Description |
|---------------|-----------|-----------|------|-------------|
| Line 1 | username | buyer_username | string | Buyer identifier |
| Line 2 | category | category | string | Expense category |
| Line 3 | amount | quantity | integer | Number of items |
| Line 4 | price_per_one | tariff | float | Price per unit |
| - | telegram_user_id | comment | string | Included in comment field |

#### 6.3.2 Bot Input to API Mapping (Transfers)
| Bot Input Line | Bot Field | API Field | Type | Description |
|---------------|-----------|-----------|------|-------------|
| Line 1 | from_username | from_username | string | Source user |
| Line 2 | to_username | to_username | string | Target user |
| Line 3 | amount | amount | float | Transfer amount |
| Line 4 | comment | comment | string | Transfer description |
| - | telegram_user_id | authorized_by | integer | Authorizing admin |

---

## 7. User Interface/User Experience Design

### 7.1 Conversation Flow Design

#### 7.1.1 Expense Entry Flow
```
User: Clicks 📦 Расходы button

Bot: 📦 Введите данные о расходе в формате:
     
     buyer_username
     category
     quantity
     tariff
     
     Пример:
     petya
     proxy
     10
     3

User: petya
      proxy
      10
      3

Bot: 📋 Confirm expense entry:
     Buyer: petya
     Category: proxy
     Quantity: 10
     Tariff: 3.00
     Total: 30.00
     [Confirm] [Cancel]

User: [Confirm]
Bot: ✅ Expense recorded successfully!
     Entry ID: #EXP-2024-001234
```

#### 7.1.2 Transfer Entry Flow
```
User: Clicks 💸 Переводы button

Bot: 💸 Введите данные о переводе в формате:
     
     from_username
     to_username
     amount
     comment
     
     Пример:
     AC
     petya
     1000
     traffic

User: AC
      petya
      1000
      traffic

Bot: 📋 Confirm transfer:
     From: AC
     To: petya
     Amount: 1000.00
     Comment: traffic
     [Confirm] [Cancel]

User: [Confirm]
Bot: ✅ Transfer recorded successfully!
     Transfer ID: #TRF-2024-001234
```

### 7.2 Interface Layers

#### 7.2.1 Persistent Menu Layer
- **Always visible** below chat input
- **Quick access** to main functions
- **Consistent** across all states

#### 7.2.2 Inline Menu Layer
- **Context-sensitive** above messages
- **Workflow guidance** and confirmations
- **Dynamic content** based on state

---

## 8. Security Requirements

### 8.1 Authentication & Authorization
- **User Verification:** Telegram user ID validation
- **Admin Verification:** Multi-factor admin identification
- **API Security:** Secure API key management
- **Session Management:** Secure conversation state handling

### 8.2 Data Protection
- **Input Sanitization:** All user inputs sanitized
- **SQL Injection Prevention:** Parameterized queries
- **Data Encryption:** Sensitive data encryption in transit
- **Audit Logging:** Complete interaction logging
- **API Authentication:** Bearer token + API key headers

### 8.3 Rate Limiting
- **User Rate Limits:** 30 requests/minute per user
- **Admin Rate Limits:** 100 requests/minute for admins
- **API Rate Limits:** Respect Laravel API limits
- **Abuse Prevention:** Automatic blocking for suspicious activity

---

## 9. Performance Requirements

### 9.1 Response Time
- **Bot Response:** <1 second for text responses
- **API Calls:** <2 seconds for data submission
- **Database Queries:** <500ms for buyer lookups
- **Error Recovery:** <3 seconds for error handling

### 9.2 Scalability
- **Concurrent Users:** Support 100+ simultaneous users
- **Daily Transactions:** Handle 1000+ daily entries
- **Data Volume:** Process large buyer lists efficiently
- **Memory Usage:** <512MB RAM usage

### 9.3 Reliability
- **Uptime:** 99.9% availability
- **Error Rate:** <0.1% transaction failures
- **Recovery Time:** <5 minutes for system recovery
- **Data Consistency:** 100% data synchronization

---

## 10. Testing Strategy

### 10.1 Testing Levels

#### 10.1.1 Unit Testing
- **Bot Functions:** Individual function testing
- **API Integration:** Mock API response testing
- **Data Validation:** Input validation testing
- **Coverage Target:** 90% code coverage

#### 10.1.2 Integration Testing
- **API Integration:** Full API workflow testing
- **Database Integration:** Data persistence testing
- **Telegram Integration:** Bot API testing
- **End-to-End:** Complete user journey testing

#### 10.1.3 User Acceptance Testing
- **Real User Testing:** Beta testing with actual users
- **Scenario Testing:** All use case validation
- **Performance Testing:** Load and stress testing
- **Security Testing:** Penetration testing

### 10.2 Test Environments
- **Development:** Local testing environment
- **Staging:** Pre-production testing
- **Production:** Live environment monitoring

---

## 11. Implementation Timeline

### 11.1 Phase 1: Foundation (Week 1-2)
- [ ] Project setup and configuration
- [ ] Basic bot framework implementation
- [ ] API integration foundation
- [ ] Database schema updates

### 11.2 Phase 2: Core Features (Week 3-4)
- [ ] `/start` command implementation
- [ ] `/buyer_expenses` workflow
- [ ] Basic inline keyboards
- [ ] Input validation system

### 11.3 Phase 3: Advanced Features (Week 5-6)
- [ ] `/buyer_transfers` implementation
- [ ] Admin functionality
- [ ] Error handling system
- [ ] Logging and monitoring

### 11.4 Phase 4: Testing & Deployment (Week 7-8)
- [ ] Comprehensive testing
- [ ] Security audit
- [ ] Performance optimization
- [ ] Production deployment

---

## 12. Risk Assessment

### 12.1 Technical Risks
| Risk | Probability | Impact | Mitigation |
|------|-------------|--------|------------|
| API Integration Failures | Medium | High | Comprehensive error handling, retry logic |
| Telegram API Changes | Low | Medium | Version pinning, monitoring updates |
| Database Performance | Medium | Medium | Query optimization, caching |
| Security Vulnerabilities | Low | High | Security audits, penetration testing |

### 12.2 Business Risks
| Risk | Probability | Impact | Mitigation |
|------|-------------|--------|------------|
| Low User Adoption | Medium | High | User training, UX optimization |
| Data Accuracy Issues | Low | High | Multi-layer validation |
| System Downtime | Low | Medium | Monitoring, quick recovery procedures |

---

## 13. Success Metrics and KPIs

### 13.1 User Engagement
- **Daily Active Users:** Target 80% of registered users
- **Session Duration:** Average 3-5 minutes per session
- **Command Usage:** 95% success rate for commands
- **User Retention:** 90% monthly retention rate

### 13.2 System Performance
- **API Response Time:** <2 seconds average
- **Error Rate:** <1% failed transactions
- **Uptime:** 99.9% availability
- **Data Accuracy:** 99%+ validation success

### 13.3 Business Impact
- **Time Savings:** 70% reduction in data entry time
- **Cost Efficiency:** 50% reduction in manual processing
- **Data Quality:** 90% improvement in data accuracy
- **User Satisfaction:** 4.5+ rating from feedback

---

## 14. Future Enhancements

### 14.1 Planned Features
- **Voice Input:** Voice-to-text expense entry
- **Receipt Scanning:** OCR for receipt processing
- **Analytics Dashboard:** Real-time analytics via bot
- **Multi-language:** Support for multiple languages

### 14.2 Integration Opportunities
- **Payment Processing:** Direct payment integration
- **Reporting System:** Advanced reporting features
- **Mobile App:** Companion mobile application
- **Third-party APIs:** External service integrations

---

## 15. Change Log

### Version 1.1 Updates
- **API Format Correction:** Updated expense format to use `expense_records` array
- **Field Mapping:** Corrected field names (buyer_username, quantity, tariff)
- **Authentication:** Added API key authentication requirements  
- **Interface Enhancement:** Added persistent menu buttons documentation
- **Data Mapping:** Added comprehensive bot-to-API field mapping tables
- **cURL Examples:** Added working API call examples
- **Security Updates:** Enhanced authentication and logging requirements

---

**Document Approval:**
- [x] Product Owner
- [x] Technical Lead
- [x] Security Team
- [x] QA Team

**Last Updated:** December 2024 (API Format Correction)
**Next Review:** January 2025 