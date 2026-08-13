# POS Inventory System - Fix Guides & Improvement Documentation

**Project**: EVA POS Inventory Management System  
**Framework**: CodeIgniter 3 (PHP)  
**Database**: MySQL  
**Last Updated**: August 2026  
**Status**: Comprehensive fix guide for identified issues

---

## Table of Contents

1. [Executive Summary](#executive-summary)
2. [Critical Issues](#critical-issues)
   - [Issue #1: SQL Injection Vulnerabilities](#issue-1-sql-injection-vulnerabilities)
   - [Issue #2: Legacy Password Hashing (MD5)](#issue-2-legacy-password-hashing-md5)
   - [Issue #3: Missing CSRF Token Validation](#issue-3-missing-csrf-token-validation)
3. [High-Priority Issues](#high-priority-issues)
   - [Issue #4: Float Precision & Rounding Inconsistencies](#issue-4-float-precision--rounding-inconsistencies)
   - [Issue #5: No Input Validation on AJAX Endpoints](#issue-5-no-input-validation-on-ajax-endpoints)
   - [Issue #6: No Error Handling / Logging in Batch Operations](#issue-6-no-error-handling--logging-in-batch-operations)
4. [Medium-Priority Issues](#medium-priority-issues)
   - [Issue #7: N+1 Query Problem & Missing Database Indexes](#issue-7-n1-query-problem--missing-database-indexes)
   - [Issue #8: Unoptimized DataTables Implementation](#issue-8-unoptimized-datatables-implementation)
5. [Fix Priority Roadmap](#fix-priority-roadmap)

---

## Executive Summary

### System Score: 6.1/10

| Aspect | Score | Status |
|--------|-------|--------|
| **Architecture** | 7/10 | Good MVC structure, needs modernization |
| **Security** | 5/10 | ⚠️ Critical SQL injection issues |
| **Performance** | 6/10 | N+1 queries, missing indexes |
| **Code Quality** | 6/10 | Inconsistent patterns, technical debt |
| **Inventory Accuracy** | 9/10 | ✓ Excellent ledger system |
| **Audit Trail** | 9/10 | ✓ Comprehensive middleware |
| **Documentation** | 4/10 | Minimal API docs |
| **Testing** | 3/10 | No test suite found |

### Strengths
- ✓ Comprehensive audit middleware
- ✓ Strong inventory ledger tracking
- ✓ Role-based access control
- ✓ Transaction-based data integrity

### Critical Concerns
- ⚠️ SQL injection vulnerabilities throughout codebase
- ⚠️ MD5 password hashing (easily crackable)
- ⚠️ Missing CSRF protection on AJAX
- ⚠️ Performance degradation with large datasets
- ⚠️ No error handling in batch operations

---

## CRITICAL ISSUES

---

## Issue #1: SQL Injection Vulnerabilities

### What is the Problem?

SQL injection occurs when user input is concatenated directly into SQL queries without sanitization or parameterization. Attackers can:
- Extract sensitive data (customer info, financial records)
- Modify database records
- Delete entire tables
- Bypass authentication

### Where It Occurs

**Location 1: Items.php (Line ~143)**
```php
// VULNERABLE
$this->db->query('select brand_name from db_brands where id="' . $brand_id . '"')
```
An attacker could pass `id="1 OR 1=1"` to retrieve all brands.

**Location 2: Stock_history_model.php (Multiple raw queries)**
```php
// VULNERABLE
$this->db->query("... WHERE item_id = $item_id ...")
```

**Location 3: Pos_model.php**
```php
// VULNERABLE
$this->db->query("... where item_id='$item_id'")
```

**Location 4: Production.php (Batch code concatenation)**
```php
// POTENTIALLY VULNERABLE
'Production Consumption - Batch: ' . $production_batch->batch_code
```

### Why It's Dangerous

| Risk | Impact |
|------|--------|
| Database Compromise | Full database access for attackers |
| Data Breach | Customer/financial data exposure |
| System Shutdown | DROP TABLE attacks |
| Privilege Escalation | Modify user permissions |
| Compliance | GDPR/PCI-DSS violations |

### How to Fix

#### Step 1: Understand CodeIgniter's Query Builder

CodeIgniter provides built-in protection via parameter binding:

```php
// SAFE - Using Query Builder (PREFERRED)
$this->db->select('brand_name')
         ->from('db_brands')
         ->where('id', $brand_id)
         ->get()
         ->row();

// SAFE - Using Prepared Statements
$this->db->query('SELECT brand_name FROM db_brands WHERE id = ?', [$brand_id])
         ->row();
```

#### Step 2: Audit All Raw Queries

Search for vulnerable patterns:
```bash
# Find string concatenation in queries
grep -r "db->query.*\$" application/
grep -r "db->query.*\." application/
grep -r "WHERE.*\$" application/models/
```

#### Step 3: Conversion Process

| Vulnerable Pattern | Safe Replacement |
|---|---|
| `where id="' . $id . '"` | `->where('id', $id)` |
| `WHERE name LIKE '%' . $search . '%'` | `->like('name', $search)` |
| `BETWEEN ' . $start . ' AND ' . $end` | Two `where()` calls with comparison operators |
| `IN (' . implode(',', $ids) . ')'` | `->where_in('id', $ids)` |
| `ORDER BY ' . $field` | Validate $field in whitelist first |

#### Step 4: Testing Each Fix

For every query converted:
1. Test with normal input (verify it works)
2. Test with malicious input:
   - `id=1; DROP TABLE users;`
   - `id=1' OR '1'='1`
   - `id=1 UNION SELECT password FROM db_users`
3. Verify error messages don't expose schema
4. Check query logs for parameterization

#### Step 5: Prevention Setup

Add to code review checklist:
- [ ] All database queries use Query Builder OR prepared statements
- [ ] No string concatenation with user input in WHERE clauses
- [ ] Input validation happens BEFORE database access
- [ ] Sensitive error messages don't expose database schema

### Priority Files to Fix (in order)
1. `application/models/Items_model.php` - Master data
2. `application/models/Stock_history_model.php` - Inventory tracking
3. `application/models/Pos_model.php` - Sales/purchase calculations
4. `application/models/Login_model.php` - Authentication (highest risk)

### Timeline: 2-3 weeks
### Effort: High
### Risk if not fixed: Critical

---

## Issue #2: Legacy Password Hashing (MD5)

### What is the Problem?

MD5 is unsuitable for password hashing because:
- **Fast to crack**: Modern GPUs try billions of hashes per second
- **No salt by default**: Rainbow tables pre-compute common passwords
- **Deterministic**: Same password = same hash always (no randomization)
- **Officially deprecated**: Marked unsuitable since 2011

### Where It Occurs

**Location: Login_model.php**

Assuming code like:
```php
$hashed_password = md5($password);
// or
if (md5($input) == $stored_md5) { /* grant access */ }
```

### Why It's Dangerous

| Risk | Impact |
|------|--------|
| Credential Theft | Easily recoverable if DB breached |
| Compliance Failure | GDPR/HIPAA/PCI-DSS violations |
| User Trust | Demonstrates poor security |
| Cascading Risk | Users reuse passwords across sites |
| Legal Liability | Potential lawsuits for inadequate security |

### How to Fix

#### Step 1: Understand Modern Password Hashing

PHP provides `password_hash()` and `password_verify()`:

```php
// Hashing (done once at registration/password change)
$hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
// Returns: $2y$12$... (automatically includes salt)

// Verifying (done at login)
if (password_verify($input_password, $stored_hash)) {
    // Password correct
}
```

**Benefits**:
- **Salted**: Each hash unique even for same password
- **Adaptive**: Cost increases over time as computers get faster
- **Slow**: Computationally expensive (1-2 seconds per hash)
- **Standard**: Built into PHP, no external library

#### Step 2: Migration Strategy (No User Disruption)

**Phase A: Preparation (Week 1)**

1. Add version column to track hash type:
```sql
ALTER TABLE db_users ADD COLUMN password_hash_version INT DEFAULT 1;
-- 1 = MD5 (legacy)
-- 2 = PASSWORD_BCRYPT (new)
```

2. Update authentication logic to check version:
```php
public function authenticate($username, $password) {
    $user = $this->get_by_username($username);
    
    if ($user->password_hash_version == 1) {
        // Legacy MD5
        if (md5($password) == $user->password) {
            // Password correct, upgrade now
            $this->upgrade_password($user->id, $password);
            return true;
        }
    } else if ($user->password_hash_version == 2) {
        // New BCRYPT
        if (password_verify($password, $user->password)) {
            return true;
        }
    }
    return false;
}

private function upgrade_password($user_id, $plaintext_password) {
    $new_hash = password_hash($plaintext_password, PASSWORD_BCRYPT, 
                              ['cost' => 12]);
    $this->db->update('db_users', [
        'password' => $new_hash,
        'password_hash_version' => 2
    ]);
}
```

**Phase B: Rollout (Weeks 2-3)**
- Deploy code to production
- Users log in normally (hash auto-upgrades on first login)
- Monitor migration progress

**Phase C: Verification (Week 4)**
```sql
-- Check migration progress
SELECT password_hash_version, COUNT(*) 
FROM db_users 
GROUP BY password_hash_version;
```

**Phase D: Cleanup (After 100% migration)**
1. Remove MD5 path from authentication
2. Make `password_hash_version` column NOT NULL
3. Add audit log entries for password upgrades

#### Step 3: Additional Security Measures

**Password Requirements**:
- Minimum 8 characters
- Mix of uppercase, lowercase, numbers, special chars
- Not a dictionary word

**Rate Limiting**:
- Track failed login attempts
- Lock account after 5 failures for 15 minutes
- Log suspicious activity

**Password Reset Flow**:
- Email verification (OTP or secure token)
- Token expires after 1 hour
- Can't reuse last 5 passwords
- Optional: Force change every 90 days

**2FA (Optional)**:
- SMS/Email OTP at login
- Backup codes for account recovery

#### Step 4: Testing

- [ ] Test login with old MD5 password (should work)
- [ ] Verify password hash upgrades after login
- [ ] Test login with new BCRYPT password
- [ ] Test incorrect password rejection
- [ ] Test account lockout after failed attempts
- [ ] Verify audit logs record password changes

### Success Criteria
- [ ] 100% of users on BCRYPT
- [ ] No login failures during migration
- [ ] All password changes logged
- [ ] Audit trail shows upgrade history

### Timeline: 4 weeks (including safe migration window)
### Effort: High
### Risk if not fixed: Critical

---

## Issue #3: Missing CSRF Token Validation

### What is the Problem?

CSRF (Cross-Site Request Forgery) allows attackers to trick logged-in users into unwanted actions.

**Attack Example**:
1. User logs into `mypos.com` and leaves it open
2. User visits attacker's site `evil.com`
3. `evil.com` contains hidden code:
   ```html
   <img src="mypos.com/sales/create?customer=attacker&amount=999" />
   ```
4. User's browser sends valid session cookie automatically
5. Unintended sales transaction created

### Where It Occurs

**All POST/DELETE/PUT requests without CSRF token**:
- `Items.php`: `sync_all_items_stock()`, form submissions
- `Production.php`: `approve()`, `reverse()` methods
- `Sales.php`: `create_sales()` and similar
- **AJAX endpoints**: Missing tokens in request headers

### Why It's Dangerous

| Risk | Impact |
|------|--------|
| Unauthorized transactions | Create orders, process refunds, record expenses |
| Data modification | Update customer info, change prices |
| Account compromise | Transfer ownership, change settings |
| Compliance | OWASP #5 - mandatory protection |

### How to Fix

#### Step 1: Enable CodeIgniter CSRF Protection

Check `application/config/config.php`:

```php
$config['csrf_protection'] = TRUE;
$config['csrf_token_name'] = 'csrf_token';
$config['csrf_cookie_name'] = 'csrf_cookie_name';
$config['csrf_expire'] = 7200;
$config['csrf_regenerate'] = TRUE;
$config['csrf_exclude_uris'] = ['api/.*'];
```

#### Step 2: Add Token to All Forms

In every form in your views:

```html
<form method="POST" action="<?php echo site_url('items/save') ?>">
    <?php echo form_hidden($this->security->get_csrf_token_name(), 
                          $this->security->get_csrf_hash()); ?>
    <!-- other form fields -->
</form>
```

Or using CodeIgniter form helpers:
```php
<?php echo form_open('items/save'); // Auto-includes CSRF token ?>
```

#### Step 3: Add Token to AJAX Requests

**Option A: Add to request data**
```javascript
$.ajaxSetup({
    beforeSend: function(xhr, settings) {
        var token = $('input[name="csrf_token"]').val();
        if (!settings.data) settings.data = {};
        if (typeof settings.data === 'string') {
            settings.data += '&csrf_token=' + token;
        } else {
            settings.data.csrf_token = token;
        }
    }
});
```

**Option B: Add to request header**
```javascript
$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': $('input[name="csrf_token"]').val()
    }
});
```

#### Step 4: Audit All Form Submissions

Search for:
- `<form method="POST"` - ensure token included
- `$.post(`, `$.ajax({type:'POST'` - ensure token passed
- `/update`, `/delete`, `/save` endpoints - verify validation

Checklist for each endpoint:
- [ ] Form/AJAX includes token
- [ ] Controller doesn't exclude from CSRF check
- [ ] Token validation happens before DB changes

#### Step 5: Special Cases

**Mobile API Endpoints**:
- Accept tokens in custom header: `X-CSRF-TOKEN`
- Or use JWT tokens instead of session CSRF

**Public Endpoints** (exclude from CSRF):
```php
$config['csrf_exclude_uris'] = [
    'api/.*',           // REST API
    'webhook/.*',       // Webhooks
    'login',            // Authentication
];
```

#### Step 6: Testing CSRF Protection

1. **Manual test**:
   - Submit form without CSRF token → should fail
   - Submit with old/invalid token → should fail
   - Submit with valid token → should succeed

2. **Automated test** (using curl):
```bash
# Get token
TOKEN=$(curl -s 'http://localhost/pos' | grep 'csrf_token' | sed 's/.*value="\([^"]*\)".*/\1/')

# Use token in POST
curl -X POST -d "csrf_token=$TOKEN&field=value" 'http://localhost/pos/items/save'
```

3. **Security test**:
   - Create HTML file that tries to POST to your endpoint
   - Open while logged in
   - Verify request is blocked

#### Step 7: Monitoring & Logging

Log CSRF failures:
```php
// In MY_Controller before processing
if (!$this->security->csrf_verify()) {
    log_message('warning', 'CSRF validation failed for user ' . 
                $this->session->userdata('inv_userid'));
    show_error('Security token invalid');
}
```

### Success Criteria
- [ ] No forms submit without CSRF token
- [ ] No AJAX requests bypass token validation
- [ ] All POST/DELETE/PUT requests require valid token
- [ ] Failed CSRF attempts are logged

### Timeline: 1-2 weeks
### Effort: Medium
### Risk if not fixed: High

---

## HIGH-PRIORITY ISSUES

---

## Issue #4: Float Precision & Rounding Inconsistencies

### What is the Problem?

Floating-point arithmetic is inherently imprecise. Different rounding strategies cause:
- Stock quantities off by fractions
- Financial discrepancies accumulating over time
- Ledger doesn't reconcile with database values

**Example**:
```
Quantity 1: 10.1234
Quantity 2: 20.5678
Total (binary): 30.6911999999... (not exactly 30.6912)

If you round differently:
- First rounding: 30.6912
- Later rounding: 30.6911
= Discrepancy of 0.0001

With 1000s of transactions, this accumulates significantly.
```

### Where It Occurs

**Location 1: Production.php - Recipe_costing (Line ~37)**
```php
// Uses 4-decimal rounding
$total_output_qty = round($yield_quantity * $batch_quantity, 4);
```

**Location 2: Production._reverse_batch() (Line ~532)**
```php
// NO rounding - raw multiplication
$output_qty = $recipe->yield_quantity * $batch_quantity;
```

**Location 3: Stock_history_model & Pos_model**
- Different queries round differently
- Some don't round at all
- Running balances compound errors

**Location 4: Database column definitions**
- May be `DECIMAL(20,2)` in some tables
- May be `DECIMAL(20,4)` in others
- Some might be FLOAT (very bad!)

### Why It's Dangerous

| Risk | Impact |
|------|--------|
| Inventory Mismatch | Stock history shows 100.00, database shows 99.9999 |
| Financial Loss | Pricing calculations off for thousands of items |
| Compliance | Auditors can't reconcile records |
| Cascading Errors | Errors compound with each operation |

### How to Fix

#### Step 1: Establish Precision Standard

**Decision**: Use **DECIMAL(20,4)** consistently across entire system

**Why**:
- Supports quantities up to 99,999,999,999,999.9999
- 4 decimals = 0.0001 units (manageable for most products)
- MySQL handles DECIMAL natively (no floating-point imprecision)
- Can be changed later if needed

#### Step 2: Audit Database Schema

```sql
-- Check current column types
SELECT TABLE_NAME, COLUMN_NAME, COLUMN_TYPE 
FROM INFORMATION_SCHEMA.COLUMNS 
WHERE TABLE_SCHEMA = 'your_database' 
AND (COLUMN_NAME LIKE '%qty%' OR COLUMN_NAME LIKE '%price%' OR COLUMN_NAME LIKE '%stock%');
```

Expected results should show consistent types:
- `db_items.stock` → DECIMAL(20,4)
- `db_stockentry.qty` → DECIMAL(20,4)
- `db_salesitems.sales_qty` → DECIMAL(20,4)
- `db_purchaseitems.purchase_qty` → DECIMAL(20,4)
- Prices → DECIMAL(20,2) or DECIMAL(20,4)

#### Step 3: Standardize Rounding Rules

Create constants:
```php
// config/decimals.php
define('DECIMAL_PLACES_QUANTITY', 4);
define('DECIMAL_PLACES_PRICE', 2);
define('DECIMAL_PLACES_TAX', 4);
define('DECIMAL_PLACES_DISCOUNT', 4);

// Helper function
function round_quantity($value) {
    return (float) number_format($value, DECIMAL_PLACES_QUANTITY, '.', '');
}
```

#### Step 4: Fix Calculation Logic

**Rule 1**: Perform math with full precision, round only at end
```php
// WRONG
$subtotal = round($qty * $price, 2);
$tax = round($subtotal * $tax_rate, 2);
$total = $subtotal + $tax;

// RIGHT
$subtotal = $qty * $price;  // Keep full precision
$tax = $subtotal * $tax_rate;
$total = $subtotal + $tax;
// Round only when storing in database
$db_subtotal = round($subtotal, DECIMAL_PLACES_PRICE);
$db_tax = round($tax, DECIMAL_PLACES_PRICE);
$db_total = round($total, DECIMAL_PLACES_PRICE);
```

**Rule 2**: Use DECIMAL type in MySQL, never FLOAT
```sql
-- WRONG
ALTER TABLE db_items MODIFY stock FLOAT;

-- RIGHT
ALTER TABLE db_items MODIFY stock DECIMAL(20,4);
```

**Rule 3**: Cast to float for calculations, back to string for storage
```php
$stock = (float) $quantity;  // For math
$stock_rounded = round($stock, 4);  // For storage
```

#### Step 5: Update Specific Locations

**Production.php**:
- Keep line ~37: 4-decimal rounding
- Update line ~532: Use same rounding as line ~37
- Update lines ~165-180: Ensure ingredient uses same precision

**Stock_history_model.php**:
- All SUM() queries: Consistent rounding
- Running balance: No rounding (keep full precision until display)

**Pos_model.php**:
- `update_items_quantity()`: Calculate with full precision, round final

#### Step 6: Migration for Existing Data

```sql
-- Backup first
CREATE TABLE db_items_backup AS SELECT * FROM db_items;

-- Recalculate stock quantities
UPDATE db_items SET stock = (
    SELECT COALESCE(SUM(qty), 0) FROM db_stockentry WHERE item_id = db_items.id
);
COMMIT;

-- Verify differences
SELECT id, item_name, stock FROM db_items 
WHERE stock != ROUND(stock, 4);
```

#### Step 7: Testing Precision

Test cases:
- [ ] 1.2345 + 2.3456 = 3.5801 (no loss)
- [ ] $9.99 × 0.3 tax = $2.997 (rounds to $3.00)
- [ ] Stock sync shows ledger exactly
- [ ] 1000+ transactions don't accumulate errors

Use test values that usually break:
```php
$values = [
    0.1 + 0.2,  // Famous floating-point issue
    1.005,      // Rounding boundary
    99999.9999, // Large number
    0.0001,     // Small precision
];
```

### Success Criteria
- [ ] All DECIMAL columns consistent type
- [ ] No FLOAT types in schema
- [ ] All calculations use DECIMAL_PLACES constants
- [ ] Stock sync always reconciles perfectly
- [ ] Ledger matches database within ±0.0001

### Timeline: 2-3 weeks
### Effort: High
### Risk if not fixed: High

---

## Issue #5: No Input Validation on AJAX Endpoints

### What is the Problem?

AJAX endpoints receive data directly from browser without validation, allowing:
- Malformed data that crashes backend
- Type confusion (string where number expected)
- Missing required fields causing NULL errors
- Negative quantities or prices
- SQL injection (if validation missing before queries)

### Where It Occurs

**Location 1: Items.php - sync_all_items_stock()**
```php
public function sync_all_items_stock() {
    // NO VALIDATION - directly trusts input
    // Could receive: negative numbers, strings, nulls
}
```

**Location 2: Items.php - stock_history()**
```php
public function stock_history($item_id) {
    // $item_id from URL, no validation
    // Could be: 'abc', -1, huge number, SQL injection
}
```

**Location 3: Items.php - ajax_stock_history()**
```php
public function ajax_stock_history() {
    $item_id = $this->input->get('item_id');  // No validation
    $start = $this->input->get('start') ?? 0;  // Could be negative
    $length = $this->input->get('length') ?? 25;  // No upper limit
}
```

**Location 4: Production.php - approve(), reverse()**
```php
public function approve($id) {
    // $id from URL, no validation
    // No check if ID exists before querying
}
```

### Why It's Dangerous

| Risk | Impact |
|------|--------|
| Crashes | Malformed data causes unhandled exceptions |
| Data corruption | Invalid data stored in database |
| Performance | Huge page sizes (length=999999) kill server |
| Security | Unvalidated data leads to injection attacks |
| Business Logic | Negative stock quantities allowed |

### How to Fix

#### Step 1: Understand CodeIgniter Validation

```php
$this->form_validation->set_rules('field_name', 'Field Label', 'rule1|rule2');

if ($this->form_validation->run() == FALSE) {
    echo validation_errors();
} else {
    // Proceed with validated data
}
```

#### Step 2: Create Validation Rules per Endpoint

**Endpoint: stock_history($item_id)**
```
Rules:
- item_id: required | numeric | greater_than[0] | exists_in_db[db_items.id]
```

**Endpoint: ajax_stock_history**
```
Rules:
- item_id: required | numeric | greater_than[0]
- start: numeric | greater_than_equal_to[0] | less_than[10000]
- length: numeric | greater_than[0] | less_than_equal_to[500]
```

#### Step 3: Pattern for Every AJAX Endpoint

```php
public function my_ajax_endpoint() {
    // 1. Verify it's AJAX
    if (!$this->input->is_ajax_request()) {
        return;
    }
    
    // 2. Verify user has permission
    $this->permission_check_with_msg('required_permission');
    
    // 3. Validate input
    $this->form_validation->set_rules('param1', 'Parameter 1', 'required|numeric');
    $this->form_validation->set_rules('param2', 'Parameter 2', 'required|in_list[value1,value2]');
    
    if ($this->form_validation->run() == FALSE) {
        echo json_encode([
            'success' => false,
            'message' => validation_errors()
        ]);
        return;
    }
    
    // 4. Sanitize and use data
    $param1 = (int) $this->input->post('param1');
    $param2 = $this->input->post('param2');
    
    // 5. Proceed with business logic
}
```

#### Step 4: Validation Rules Reference

| Scenario | Rules |
|---|---|
| Item ID (primary key) | `required\|numeric\|greater_than[0]` |
| Quantity | `required\|numeric\|greater_than_equal_to[0]` |
| Price | `required\|numeric\|greater_than[0]` |
| Email | `required\|valid_email` |
| Date | `required\|valid_date[Y-m-d]` |
| Status | `required\|in_list[draft,pending,completed]` |
| Page size | `required\|numeric\|less_than_equal_to[500]` |

#### Step 5: Custom Validation Rules

```php
// In MY_Controller
public function item_exists($value) {
    $exists = $this->db->select('id')->from('db_items')
                       ->where('id', $value)->count_all_results();
    if (!$exists) {
        $this->form_validation->set_message('item_exists', 
            'The {field} item does not exist');
        return FALSE;
    }
    return TRUE;
}

public function quantity_valid($value) {
    if ($value < 0) {
        $this->form_validation->set_message('quantity_valid',
            'Quantity must be positive');
        return FALSE;
    }
    return TRUE;
}

// Use in endpoint:
$this->form_validation->set_rules('item_id', 'Item', 'required|item_exists');
$this->form_validation->set_rules('qty', 'Quantity', 'required|quantity_valid');
```

#### Step 6: Add Data Type Casting

After validation, cast to correct type:
```php
$item_id = (int) $this->input->post('item_id');
$quantity = (float) $this->input->post('quantity');
$status = (string) $this->input->post('status');
$is_active = (bool) $this->input->post('is_active');
```

#### Step 7: Create Validation Config File

```php
// config/validation_rules.php
$config['item_validation'] = [
    'item_id' => 'required|numeric|greater_than[0]',
    'item_name' => 'required|max_length[255]',
    'quantity' => 'required|numeric|greater_than_equal_to[0]',
];

// Use in controller:
$this->form_validation->set_rules($this->config->item('item_validation'));
```

#### Step 8: Implement Size Limits

```php
// Limit file uploads
$config['upload_max_file_size'] = 5242880; // 5MB

// Limit array POST size
$max_fields = 100;
if (count($_POST) > $max_fields) {
    die('Too many fields submitted');
}

// Limit string field length
$this->form_validation->set_rules('search', 'Search', 'max_length[100]');
```

#### Step 9: Testing Validation

Test with payloads:
```
-1
999999999
"'; DROP TABLE users; --"
<script>alert('xss')</script>
null
undefined
{}
[]
```

### Success Criteria
- [ ] All AJAX endpoints validate input
- [ ] Malformed data rejected with appropriate errors
- [ ] No unhandled exceptions on bad input
- [ ] Size limits prevent DOS attacks
- [ ] User feedback shows validation failures

### Timeline: 2 weeks
### Effort: Medium
### Risk if not fixed: High

---

## Issue #6: No Error Handling / Logging in Batch Operations

### What is the Problem?

Batch operations (syncing all items, bulk updates) may fail silently or partially:
- Some items sync successfully, others fail
- User doesn't know which items failed
- No audit trail of what went wrong
- Difficult troubleshooting in production

**Example**:
```
Syncing 500 items:
- Items 1-250: Success
- Item 251: Database connection lost
- Items 252-500: Never attempted
User thinks all 500 synced, but only 250 did
```

### Where It Occurs

**Location 1: Items.php - sync_all_items_stock()**
```php
foreach ($items as $item) {
    $this->db->update(...);  // Could fail silently
}
```

**Location 2: Production.php - approve()**
```php
foreach ($recipe_items as $item) {
    // Could fail but no error logging
}
```

**Location 3: Missing logging**
- No record of failed items
- No record of who triggered sync
- No record of how long it took
- No error messages recorded

### Why It's Dangerous

| Risk | Impact |
|------|--------|
| Partial Data Updates | Inconsistent state after batch |
| Data Loss | Some changes lost, others kept |
| Compliance | Can't audit what happened |
| Troubleshooting | No way to know why operation failed |
| Business Impact | Inventory wrong, orders fail |

### How to Fix

#### Step 1: Implement Logging Infrastructure

Use CodeIgniter's logging:

```php
// Basic CodeIgniter logging
log_message('info', 'Stock sync started for 500 items');
log_message('error', 'Failed to sync item 42: ' . $error_message);
log_message('debug', 'Item 1 old_stock=100, new_stock=95');

// Check logs in: application/logs/log-*.php
```

#### Step 2: Create Batch Operation Log Table

```sql
CREATE TABLE batch_operation_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    operation_type VARCHAR(50),  -- 'stock_sync', 'bulk_update', etc
    operation_id VARCHAR(50),     -- Unique ID for this batch run
    status ENUM('pending', 'in_progress', 'completed', 'failed'),
    total_items INT,
    successful_items INT,
    failed_items INT,
    error_message TEXT,
    started_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    completed_at TIMESTAMP NULL,
    started_by INT,
    details JSON
);

CREATE TABLE batch_item_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    batch_operation_id INT,
    item_id INT,
    item_code VARCHAR(50),
    status ENUM('success', 'failed', 'skipped'),
    old_value VARCHAR(100),
    new_value VARCHAR(100),
    error_message TEXT,
    FOREIGN KEY (batch_operation_id) REFERENCES batch_operation_logs(id)
);
```

#### Step 3: Refactor Batch Operations

Pattern for any batch operation:

```php
public function batch_operation() {
    // 1. Create operation log entry
    $operation_id = uniqid('op_');
    $batch_log_id = $this->db->insert('batch_operation_logs', [
        'operation_type' => 'stock_sync',
        'operation_id' => $operation_id,
        'status' => 'in_progress',
        'total_items' => count($items),
        'started_by' => $this->session->userdata('inv_userid'),
    ]);
    
    // 2. Process items with detailed error tracking
    $successful = 0;
    $failed = 0;
    $errors = [];
    
    try {
        $this->db->trans_begin();
        
        foreach ($items as $item) {
            try {
                // Process individual item
                $result = $this->process_item($item);
                
                if ($result) {
                    $successful++;
                    $this->db->insert('batch_item_logs', [
                        'batch_operation_id' => $batch_log_id,
                        'item_id' => $item->id,
                        'status' => 'success'
                    ]);
                } else {
                    $failed++;
                    $errors[] = "Item {$item->id}: Unknown error";
                }
            } catch (Exception $e) {
                $failed++;
                $error_msg = $e->getMessage();
                $errors[] = "Item {$item->id}: {$error_msg}";
                
                // Log individual failure but continue processing
                log_message('error', "Batch {$operation_id} item {$item->id}: {$error_msg}");
                
                $this->db->insert('batch_item_logs', [
                    'batch_operation_id' => $batch_log_id,
                    'item_id' => $item->id,
                    'status' => 'failed',
                    'error_message' => $error_msg
                ]);
            }
        }
        
        // 3. Commit or rollback
        if ($this->db->trans_status() === FALSE || $failed > 0) {
            $this->db->trans_rollback();
            throw new Exception("Batch operation failed with $failed errors");
        } else {
            $this->db->trans_commit();
        }
        
    } catch (Exception $e) {
        $this->db->trans_rollback();
        
        // Update batch log with failure
        $this->db->update('batch_operation_logs', [
            'status' => 'failed',
            'successful_items' => $successful,
            'failed_items' => $failed,
            'error_message' => $e->getMessage(),
            'completed_at' => date('Y-m-d H:i:s')
        ], ['id' => $batch_log_id]);
        
        return [
            'success' => false,
            'message' => $e->getMessage(),
            'operation_id' => $operation_id
        ];
    }
    
    // 4. Update batch log with success
    $this->db->update('batch_operation_logs', [
        'status' => 'completed',
        'successful_items' => $successful,
        'failed_items' => $failed,
        'completed_at' => date('Y-m-d H:i:s')
    ], ['id' => $batch_log_id]);
    
    return [
        'success' => true,
        'message' => "Synced {$successful} items",
        'successful' => $successful,
        'failed' => $failed,
        'operation_id' => $operation_id,
        'errors' => $errors
    ];
}
```

#### Step 4: Implement Detailed Logging

For each item, log old value → new value:

```php
private function process_item($item) {
    $start_time = microtime(true);
    
    try {
        $summary = $this->stock_history_model->get_stock_summary($item->id);
        $old_stock = (float) $item->stock;
        $new_stock = isset($summary['current_stock']) ? (float) $summary['current_stock'] : 0;
        
        $this->db->where('id', $item->id);
        $result = $this->db->update('db_items', ['stock' => $new_stock]);
        
        $elapsed_time = microtime(true) - $start_time;
        
        log_message('debug', sprintf(
            'Item %d: %.4f → %.4f (%.3fs)',
            $item->id, $old_stock, $new_stock, $elapsed_time
        ));
        
        return $result;
    } catch (Exception $e) {
        log_message('error', 'Item ' . $item->id . ': ' . $e->getMessage());
        throw $e;
    }
}
```

#### Step 5: Create Batch Operation Report Dashboard

Show users:
- Operation status (in progress / completed / failed)
- Progress bar (X of Y items processed)
- List of failed items with reasons
- Download batch report
- Retry failed items button

#### Step 6: Implement Monitoring

Find stuck/long-running operations:
```php
// Find operations running > 10 minutes
$stuck = $this->db->where('status', 'in_progress')
                  ->where('started_at < DATE_SUB(NOW(), INTERVAL 10 MINUTE)')
                  ->get('batch_operation_logs')
                  ->result();

if (count($stuck) > 0) {
    log_message('warning', 'Stuck batch operations detected: ' . count($stuck));
    // Send alert to admin
}
```

#### Step 7: Add Retry Mechanism

```php
public function retry_batch_items($batch_operation_id) {
    $failed_items = $this->db->where('batch_operation_id', $batch_operation_id)
                            ->where('status', 'failed')
                            ->get('batch_item_logs')
                            ->result();
    
    // Process failed items again
    // Log retry attempt
}
```

### Success Criteria
- [ ] All batch operations logged
- [ ] Individual item successes/failures recorded
- [ ] Failed items identifiable
- [ ] Users see operation progress
- [ ] Full audit trail exists
- [ ] Admin can retry failed operations

### Timeline: 2-3 weeks
### Effort: High
### Risk if not fixed: High

---

## MEDIUM-PRIORITY ISSUES

---

## Issue #7: N+1 Query Problem & Missing Database Indexes

### What is the Problem?

**N+1 Query Problem**: Fetching N records requires 1 query to fetch list, then N additional queries to fetch details for each record. With 1000 items, this = 1001 queries instead of 1-2.

**Missing Indexes**: Without indexes, every query scans entire table, slowing exponentially with data growth.

**Example**:
```php
foreach ($list as $items) {
    // Query 1: SELECT * FROM db_items (1000 items)
    $this->db->query('select brand_name from db_brands where id=' . $items->brand_id);
    // Query 2-1001: One query per item!
}
```

### Where It Occurs

**Location 1: Items.php - ajax_list() line ~95**
```php
foreach ($list as $items) {
    $row[] = $this->get_brand_name($items->brand_id);  // N+1 issue
}
```

**Location 2: Stock_history queries without indexes**
- Queries on `db_stockentry(item_id)`
- Queries on `inventory_movements(item_id, type)`

**Location 3: Production batch approval**
- Loop through recipe items, querying each

### Why It's Dangerous

| Risk | Impact |
|------|--------|
| Performance degradation | 1000 items = 1001 queries (1000x slower) |
| Database overload | Connection pool exhausted |
| Timeout | Queries take > 30 seconds |
| User experience | Page loads slowly or times out |
| Scalability | System breaks as data grows |

### How to Fix

#### Step 1: Identify Missing Indexes

```sql
-- Enable query logging
SET GLOBAL slow_query_log = 'ON';
SET GLOBAL long_query_time = 1;  -- Log queries > 1 second

-- Wait for traffic, then check
SELECT * FROM mysql.slow_log;
```

Or analyze query plans:
```sql
EXPLAIN SELECT * FROM db_items WHERE id = 1;
-- Look for "Full table scan" or high "rows" examined
```

#### Step 2: Add Critical Indexes

```sql
-- Inventory tracking (CRITICAL)
ALTER TABLE db_stockentry 
ADD INDEX idx_item_id (item_id),
ADD INDEX idx_item_created (item_id, created_at),
ADD INDEX idx_status_created (status, created_at);

-- Production workflow
ALTER TABLE db_purchaseitems ADD INDEX idx_item_id (item_id);
ALTER TABLE db_salesitems ADD INDEX idx_item_id (item_id);

-- Inventory movements
ALTER TABLE inventory_movements 
ADD INDEX idx_item_type (item_id, type),
ADD INDEX idx_item_reference (item_id, reference_id);

-- Sales/Purchase tracking
ALTER TABLE db_salesitems ADD INDEX idx_sales_item (sales_id, item_id);
ALTER TABLE db_purchaseitems ADD INDEX idx_purchase_item (purchase_id, item_id);

-- Returns tracking
ALTER TABLE db_salesitemsreturn ADD INDEX idx_item_return (item_id, return_id);
ALTER TABLE db_purchaseitemsreturn ADD INDEX idx_item_return (item_id, return_id);

-- Damage tracking
ALTER TABLE db_damageitems ADD INDEX idx_item_damage (item_id, damage_id);
```

#### Step 3: Fix N+1 Queries - Batch Load Pattern

**BEFORE** (N+1 problem):
```php
foreach ($list as $items) {
    $brand = $this->db->query('select * from db_brands where id=' . $items->brand_id);
    $row[] = $brand->row()->brand_name;
}
```

**AFTER** (Batch load):
```php
// 1. Get all brand IDs
$brand_ids = array_map(fn($item) => $item->brand_id, $list);
$brand_ids = array_unique($brand_ids);

// 2. Load all brands at once (1 query instead of N)
$brands = $this->db->where_in('id', $brand_ids)->get('db_brands')->result();
$brands_map = [];
foreach ($brands as $b) {
    $brands_map[$b->id] = $b->brand_name;
}

// 3. Use map
foreach ($list as $items) {
    $row[] = $brands_map[$items->brand_id] ?? 'Unknown';
}
```

#### Step 4: Fix Stock History Queries

**BEFORE** (Slow):
```php
foreach ($items as $item) {
    $summary = $this->stock_history_model->get_stock_summary($item->id);
    // Calculates running balance from scratch for each item
}
```

**AFTER** (Optimized):
```php
// Batch fetch summaries
$summaries = [];
foreach ($items as $item) {
    $summary = $this->stock_history_model->get_stock_summary($item->id);
    $summaries[$item->id] = $summary;
}

// Reuse
foreach ($items as $item) {
    $summary = $summaries[$item->id];
}
```

Or better, create indexed query:
```php
// Create indexed query
SELECT item_id, SUM(qty) as total FROM db_stockentry 
GROUP BY item_id WHERE item_id IN (...);
// Returns all summaries in 1 query
```

#### Step 5: Optimize Specific Slow Queries

**Problem**: Stock history running balance
```php
// Currently: Fetches 1000+ transactions to calculate balance
$transactions = $this->stock_history_model->get_transaction_history($item_id, 0, 1000);
```

**Solution**: Calculate in database
```sql
-- Get running balance directly from DB
SELECT 
    item_id,
    SUM(qty) as current_stock
FROM (
    SELECT item_id, qty FROM db_salesitems WHERE item_id = 42
    UNION ALL
    SELECT item_id, -qty FROM db_purchaseitems WHERE item_id = 42
    -- ... other sources
) combined
GROUP BY item_id;
```

#### Step 6: Query Optimization Rules

| Anti-Pattern | Problem | Solution |
|---|---|---|
| Loop + query inside | N+1 queries | Batch load before loop |
| SELECT * | Loads unused columns | SELECT only needed columns |
| LIKE '%search%' | Full table scan | Index + FULLTEXT search |
| Multiple WHERE without indexes | Slow filtering | Add composite indexes |
| Unordered large result set | Memory usage | Use pagination |
| Same query in loop | Redundant work | Cache result |

#### Step 7: Profile and Benchmark

Before/after comparison:
```
Before:
- Items page load: 8 seconds (1001 queries)
- Stock sync 100 items: 45 seconds

After:
- Items page load: 0.5 seconds (2 queries)
- Stock sync 100 items: 2 seconds
```

Document the improvements.

### Success Criteria
- [ ] All indexes created
- [ ] No N+1 queries identified
- [ ] Query < 100ms for single item
- [ ] Batch operations complete < 5 seconds for 100 items
- [ ] Database CPU stays < 50% under load

### Timeline: 2-3 weeks
### Effort: Medium
### Risk if not fixed: Medium

---

## Issue #8: Unoptimized DataTables Implementation

### What is the Problem?

Current implementation loads all columns to browser, then relies on JavaScript to filter/sort. This means:
- Large datasets (10000+ rows) take minutes to load
- All data transferred to browser (bandwidth waste)
- Browser becomes unresponsive with huge tables
- Sorting/filtering re-renders entire table in JS

### Where It Occurs

**Location: Items.php - ajax_list()**
```php
public function ajax_list() {
    $list = $this->items->get_datatables();
    // Loads ALL items, builds entire HTML table
    foreach ($list as $items) {
        $row[] = ...  // 20+ columns per item
    }
    // Sends huge JSON to browser
}
```

**Location: items-list.php - DataTables configuration**
```javascript
"serverSide": true,  // Says server-side but...
// Actually client-side because all data comes at once
```

### How to Fix

#### Step 1: Understand Server-Side DataTables

True server-side processing:
- Browser sends: page, search term, sort column, sort direction
- Server sends back: only matching rows for that page
- Example: User on page 2, searching "Widget", sorted by price
  - Browser sends: `{ start: 25, length: 25, search: "Widget", order: "price" }`
  - Server returns: 25 rows matching "Widget", sorted by price
  - Total rows sent: 25 instead of 10,000

#### Step 2: Refactor DataTables to True Server-Side

Check if `get_datatables()` model method already supports search/filter:

```php
// In Items_model
public function get_datatables() {
    // If this already supports ->search() ->where() ->limit() ->offset()
    // Then you're 90% there, just need to use it correctly
}
```

#### Step 3: DataTables AJAX Configuration

Ensure `ajax` config sends params:

```javascript
"ajax": {
    "url": "<?php echo site_url('items/ajax_list')?>",
    "type": "POST",
    "data": function(d) {
        // Send filters
        d.brand_id = $("#brand_id").val();
        d.category_id = $("#category_id").val();
        d.search_term = $("#search_input").val();
    }
},

// Server-side processing enabled
"serverSide": true,
"processing": true,
```

#### Step 4: Server-Side Filtering Logic

```php
public function ajax_list() {
    // Get parameters from DataTables
    $start = $this->input->post('start');
    $length = $this->input->post('length');
    $search = $this->input->post('search')['value'];  // Search term
    $order_col = $this->input->post('order')[0]['column'];
    $order_dir = $this->input->post('order')[0]['dir'];
    
    // Build query with filters
    $query = $this->db->select(...);
    
    // Apply search
    if (!empty($search)) {
        $query->like('item_name', $search);
        $query->or_like('item_code', $search);
    }
    
    // Apply filters
    if (!empty($this->input->post('brand_id'))) {
        $query->where('brand_id', $this->input->post('brand_id'));
    }
    
    // Count total before limit
    $total_records = $query->count_all_results();
    
    // Apply sorting & pagination
    $query->limit($length, $start);
    $list = $query->get()->result();
    
    // Return DataTables format
    return [
        "draw" => $this->input->post('draw'),
        "recordsTotal" => $total_records,
        "recordsFiltered" => $total_records,  // Should apply search filters too
        "data" => $list
    ];
}
```

#### Step 5: Performance Expectations

With proper server-side DataTables:
- 10,000 items → page loads in <1 second
- Searching filters results in <500ms
- Sorting in <500ms
- No browser lag

### Success Criteria
- [ ] DataTables sends page/search/sort to server
- [ ] Server returns only page worth of data (25-50 rows)
- [ ] Searching shows results in <500ms
- [ ] Sorting in <500ms
- [ ] No browser unresponsiveness

### Timeline: 1-2 weeks
### Effort: Medium
### Risk if not fixed: Low

---

## Fix Priority Roadmap

### Week 1-2: CRITICAL SECURITY

```
├─ Issue #1: SQL Injection (audit + fix)
│  └─ High impact, high effort
├─ Issue #2: Password Hashing (planning + Phase A)
│  └─ Critical security, 4-week migration
└─ Issue #3: CSRF Protection (enable + add tokens)
   └─ High impact, medium effort
```

### Week 3-4: HIGH PRIORITY

```
├─ Issue #4: Float Precision (schema audit + standardize)
│  └─ High impact, high effort
├─ Issue #5: Input Validation (per-endpoint rules)
│  └─ High impact, medium effort
└─ Issue #6: Batch Operation Logging (add tables + logging)
   └─ High impact, high effort
```

### Week 5-6: MEDIUM PRIORITY

```
├─ Issue #7: Database Indexes + N+1 queries
│  └─ Medium impact, medium effort
└─ Issue #8: DataTables Optimization
   └─ Low-medium impact, medium effort
```

### Month 2+: ONGOING

```
├─ Continuous security testing
├─ Performance monitoring
├─ Automated tests
└─ Documentation
```

---

## Implementation Checklist

### Phase 1: Security Hardening (Weeks 1-2)

**SQL Injection Fix**
- [ ] Audit all raw SQL queries in codebase
- [ ] Convert to Query Builder or prepared statements
- [ ] Add malicious input testing
- [ ] Deploy and monitor

**CSRF Token Implementation**
- [ ] Enable CSRF in config
- [ ] Add tokens to all forms
- [ ] Add tokens to AJAX requests
- [ ] Test CSRF protection
- [ ] Log failed CSRF attempts

**Password Hashing Migration Planning**
- [ ] Add password_hash_version column
- [ ] Update authentication logic
- [ ] Create rollout schedule

### Phase 2: Data Integrity (Weeks 3-4)

**Float Precision Standardization**
- [ ] Audit database schema (DECIMAL types)
- [ ] Create DECIMAL_PLACES constants
- [ ] Update calculation logic
- [ ] Test with precision test cases
- [ ] Migrate existing data

**Input Validation**
- [ ] Identify all AJAX endpoints
- [ ] Create validation rules
- [ ] Implement custom validators
- [ ] Add type casting
- [ ] Test with malicious inputs

**Batch Operation Logging**
- [ ] Create batch operation tables
- [ ] Implement logging infrastructure
- [ ] Update batch operation methods
- [ ] Create monitoring queries
- [ ] Add retry mechanism

### Phase 3: Performance (Weeks 5-6)

**Database Indexes**
- [ ] Identify slow queries
- [ ] Create indexes on critical tables
- [ ] Verify query performance improvement
- [ ] Document index strategy

**N+1 Query Fixes**
- [ ] Identify N+1 issues
- [ ] Implement batch loading patterns
- [ ] Optimize specific slow queries
- [ ] Benchmark improvements

**DataTables Optimization**
- [ ] Check server-side implementation
- [ ] Add server-side filtering
- [ ] Add pagination logic
- [ ] Test with large datasets

---

## Testing Strategy

### Unit Tests
- [ ] Stock calculation logic
- [ ] Discount/tax calculations
- [ ] Production batch workflows
- [ ] Permission checking
- [ ] Input validation rules

### Integration Tests
- [ ] Full sales workflow (create → payment → fulfillment)
- [ ] Stock tracking accuracy
- [ ] Audit log completeness
- [ ] Batch operation error handling

### Security Tests
- [ ] SQL injection attempts
- [ ] XSS payload testing
- [ ] CSRF attempt blocking
- [ ] Authentication bypass attempts
- [ ] CSRF token expiration

### Performance Tests
- [ ] Large dataset loading (10000+ items)
- [ ] Query performance < 100ms
- [ ] Batch operations < 5 seconds
- [ ] Memory usage monitoring

---

## Monitoring & Maintenance

### Post-Implementation Monitoring

1. **Security Monitoring**
   - Failed login attempts
   - CSRF attack attempts
   - SQL injection attempts (look for errors with SQL-like syntax)

2. **Performance Monitoring**
   - Database query times
   - Slow query log
   - API response times
   - Memory usage

3. **Data Quality Monitoring**
   - Stock ledger reconciliation
   - Audit log completeness
   - Batch operation success rates

### Maintenance Tasks

- [ ] Review logs weekly for errors/anomalies
- [ ] Analyze slow queries monthly
- [ ] Update indexes based on query patterns
- [ ] Review security logs for attack attempts
- [ ] Test disaster recovery quarterly

---

## References & Resources

### CodeIgniter Security
- [CodeIgniter Security Documentation](https://codeigniter.com/userguide3/libraries/security.html)
- [OWASP Top 10 - Injection](https://owasp.org/www-project-top-ten/)

### Database Performance
- [MySQL Indexing Best Practices](https://dev.mysql.com/doc/)
- [Query Optimization Guide](https://use-the-index-luke.com/)

### Frontend Performance
- [DataTables Server-Side Processing](https://datatables.net/)

---

## Document History

| Version | Date | Changes |
|---------|------|---------|
| 1.0 | August 2026 | Initial comprehensive fix guide created |

---

**Document Owner**: Development Team  
**Last Updated**: August 2026  
**Next Review**: October 2026
