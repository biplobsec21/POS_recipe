# Sales Stock Mismatch - Root Cause Analysis

**Issue**: When creating a sale for item IT0001, `db_items.stock` does not match `stock_history.current_stock`

**Date**: August 2026  
**Impact**: High - Inventory accuracy compromised  
**Status**: Diagnosed - Root cause identified

---

## The Problem

When you create a sale:

1. **db_items.stock shows one value** (e.g., 100)
2. **stock_history ledger shows a different value** (e.g., 95)
3. **Sync button shows another value** (e.g., 98)
4. Result: Three conflicting "truths" for the same item's stock

---

## Root Cause Analysis

### System 1: db_items.stock Calculation

**Location**: `application/models/Pos_model.php` → `update_items_quantity($item_id)`

**What it reads**:
```sql
-- Source 1: Manual stock entries (opening stock, adjustments)
SELECT SUM(qty) FROM db_stockentry WHERE item_id = X

-- Source 2: Received purchases
SELECT SUM(purchase_qty) FROM db_purchaseitems 
WHERE item_id = X AND purchase_status = 'Received'

-- Source 3: Final sales ← CRITICAL
SELECT SUM(sales_qty) FROM db_salesitems 
WHERE item_id = X AND sales_status = 'Final'

-- Source 4: Purchase returns
SELECT SUM(return_qty) FROM db_purchaseitemsreturn WHERE item_id = X

-- Source 5: Sales returns
SELECT SUM(return_qty) FROM db_salesitemsreturn WHERE item_id = X
```

**Calculation Formula**:
```
stock = ((stock_qty + purchase_qty) - sales_qty + sales_returns) - purchase_returns
```

---

### System 2: stock_history.current_stock Calculation

**Location**: `application/models/Stock_history_model.php` → `get_transaction_history($item_id)`

**What it reads** (from get_transaction_history method):
```sql
-- Source 1: Sales transactions
SELECT * FROM db_salesitems si
JOIN db_sales s ON s.id = si.sales_id
WHERE si.item_id = X AND s.status = 1

-- Source 2: Purchase transactions
SELECT * FROM db_purchaseitems pi
JOIN db_purchase p ON p.id = pi.purchase_id
WHERE pi.item_id = X AND p.status = 1

-- Source 3: Manual stock entries
SELECT * FROM db_stockentry
WHERE item_id = X AND status = 1

-- ... and 4 more sources
```

**Then builds**:
```
running_balance = 0
for each transaction (sorted by date):
    running_balance += transaction.quantity_change
current_stock = running_balance (of last transaction)
```

---

## The Critical Difference: Status Fields

### The Mismatch Point

| System | Reads | Status Filter | Trigger |
|--------|-------|---|---|
| **db_items.stock** | `db_salesitems` | WHERE `sales_status = 'Final'` | Called after EACH item is added to sale form |
| **stock_history** | `db_salesitems` (via get_transaction_history) | WHERE `db_sales.status = 1` | Called on-demand when viewing history |

### Timeline Example: When You Create Sale

**Step 1: User adds item to sales form**
```
Sales form shows: qty=10 for item IT0001
sales_status field = 'Draft' (not 'Final' yet)
```

**Step 2: System calls update_items_quantity() after each item**
```sql
SELECT SUM(sales_qty) FROM db_salesitems 
WHERE item_id = IT0001 AND sales_status = 'Final'
-- Returns: 0 (because sale status is still 'Draft')

-- So db_items.stock is NOT decremented yet
```

**Step 3: User clicks "Complete Sale" or "Submit"**
```
sales_status gets changed to 'Final' (or some other status)
update_items_quantity() is called again
```

**Step 4: View stock_history**
```sql
SELECT running_balance FROM ledger
WHERE db_sales.status = 1 (different field!)
-- Might include sales with different sales_status values
-- Returns: Different running balance
```

---

## Why This Causes Mismatches

### Scenario: Item IT0001 Stock Mismatch

**Initial State**:
- `db_items.stock` = 100
- `db_salesitems` table = empty
- `stock_history.current_stock` = 100 ✓ Match

**Action: Create new sale with 10 units of IT0001**

```
1. User adds item to form (sales_status auto-set to 'Draft')
2. System calls update_items_quantity(IT0001)
   - Looks for: sales_status = 'Final'
   - Finds: 0 sales (because status is 'Draft')
   - Calculates: stock = 100 + 0 = 100
   - Updates db_items: stock = 100 ✓

3. User clicks "Save Draft"
   - db_salesitems created with sales_status = 'Draft'
   - db_sales created with status = 1

4. View stock_history:
   - Looks for: db_sales.status = 1 (DIFFERENT FIELD)
   - Finds: This sale (with 'Draft' sales_status)
   - Calculates: running_balance = 100 - 10 = 90
   - Shows: current_stock = 90 ✗ MISMATCH!

5. Click "Sync All Items":
   - Reads stock_history = 90
   - Updates db_items.stock = 90
   - Now they match, but db_items was temporarily wrong
```

---

## The Three Status Fields Causing Confusion

| Table | Field | Values | Used By |
|-------|-------|--------|---------|
| `db_sales` | `status` | 0/1 (inactive/active) | stock_history queries |
| `db_salesitems` | `sales_status` | 'Draft', 'Final', 'Pending', etc | Pos_model.update_items_quantity() |
| `db_sales` | `sales_status` | Same as above | ??? (appears in form but which query uses it?) |

**The Problem**: 
- `Pos_model` filters on `db_salesitems.sales_status = 'Final'`
- `Stock_history` filters on `db_sales.status = 1`
- These are **different fields** with **different semantics**
- Result: Same sale counted at different times

---

## When Mismatches Occur

### Mismatch Type 1: Draft Sales Not Counted

```
Scenario: Sale created but NOT finalized
- db_items.stock = 100 (counts only 'Final' sales)
- stock_history.current_stock = 90 (counts all sales where db_sales.status=1)
- Difference: 10 units unaccounted

Fix: Either:
a) Don't count draft sales in stock_history
b) Decrement stock immediately when sale created (not Final)
```

### Mismatch Type 2: Timing Mismatch

```
Scenario: update_items_quantity() called at different times
- Sale created at 10:00 AM
- update_items_quantity() called at 10:00 (sales_status still 'Draft')
  - db_items shows full stock
- Sales status changed to 'Final' at 10:30 AM
- No recalculation until next sync
- stock_history shows reduced stock, db_items doesn't
```

### Mismatch Type 3: Multi-Step Sale Workflow

```
Scenario: Sale goes through states: Draft → Pending → Final
- After Draft: stock not updated
- After Pending: might be updated by background process
- After Final: definitely updated
- Result: Multiple intermediate states cause inconsistency
```

---

## The Complete Data Flow

```
┌─────────────────────────────────────────────────────────────┐
│ USER CREATES SALE FOR IT0001 (qty=10, current stock=100)    │
└─────────────────────────────────────────────────────────────┘
                            │
                            ▼
         ┌──────────────────────────────┐
         │ Sales_model.verify_save_and_ │
         │ update() called              │
         └──────────────────────────────┘
                            │
                ┌───────────┴───────────┐
                ▼                       ▼
        INSERT db_sales        INSERT db_salesitems
        (status=1)             (sales_status='Draft')
                │                       │
                └───────────┬───────────┘
                            ▼
         ┌──────────────────────────────────────┐
         │ Pos_model.update_items_quantity()    │
         │ called for item IT0001               │
         └──────────────────────────────────────┘
                            │
              ┌─────────────┴─────────────┐
              ▼                           ▼
    Query: sales_qty from          Should find: 0
    db_salesitems WHERE            (because sales_status
    sales_status='Final'             is 'Draft', not 'Final')
              │
              ▼
    ✗ PROBLEM: Decrement not applied!
    db_items.stock still = 100
              │
              ▼
         ┌────────────────────────────────────┐
         │ User views stock_history ledger    │
         └────────────────────────────────────┘
              │
              ▼
    Query: sales_qty from
    db_salesitems WHERE
    db_sales.status = 1
              │
              ▼
    ✓ FINDS this sale (status=1)
    Calculates: 100 - 10 = 90
              │
              ▼
    ✗ MISMATCH:
    db_items.stock = 100
    stock_history = 90
```

---

## Solution Approach

### Option A: Unify Status Checking (Recommended)

Make `Pos_model.update_items_quantity()` check **actual sale status**, not just 'Final':

```php
// CURRENT (problematic)
SELECT SUM(sales_qty) FROM db_salesitems 
WHERE item_id = X AND sales_status = 'Final'

// BETTER
SELECT SUM(sales_qty) FROM db_salesitems si
JOIN db_sales s ON s.id = si.sales_id
WHERE si.item_id = X AND s.status = 1
// This matches what stock_history does
```

**Advantage**: Both systems read from same source  
**Risk**: Might decrement stock for "Draft" sales (is that desired?)

### Option B: Update Stock Immediately on Sale Creation

When creating `db_salesitems` entry, immediately decrement `db_items.stock`:

```php
// In Sales_model.verify_save_and_update()
$this->db->insert('db_salesitems', $salesitems_entry);

// NEW: Immediately update stock
$this->db->set('stock', 'stock - ' . (float)$sales_qty, FALSE);
$this->db->where('id', $item_id);
$this->db->update('db_items');
```

**Advantage**: Stock is always accurate, no timing issues  
**Risk**: If sale fails/reverted, need to reverse the decrement

### Option C: Remove Status Filter Entirely

Count all `db_salesitems` entries regardless of status:

```php
SELECT SUM(sales_qty) FROM db_salesitems 
WHERE item_id = X
// No sales_status filter
```

**Advantage**: Simple, includes all states  
**Risk**: Doesn't differentiate between draft/final/cancelled

### Option D: Create Explicit Inventory Movements for Sales

Like production uses `inventory_movements`, create entries for sales:

```php
// When sale created
INSERT inventory_movements (
    item_id = IT0001,
    qty = -10,
    type = 'SALES',
    reference_id = sales_id
)

// Both db_items and stock_history read from this
```

**Advantage**: Single source of truth, like production  
**Risk**: Requires schema changes, refactoring multiple queries

---

## Current State of the Code

### Pos_model.update_items_quantity() - Line 440-467

```php
$q9 = $this->db->query("select coalesce(SUM(sales_qty),0) as sl_tot_qty 
                        from db_salesitems 
                        where item_id='$item_id' 
                        and sales_status='Final'");  ← FILTER HERE
```

### Stock_history_model.get_transaction_history() - Sales Section

```php
$this->db->select(...);
$this->db->from('db_salesitems si');
$this->db->join('db_sales s', 's.id = si.sales_id', 'left');
$this->db->where('si.item_id', $item_id);
$this->db->where('s.status', 1);  ← DIFFERENT FILTER HERE
```

**These two queries read different data!**

---

## Recommended Fix

**Modify `Pos_model.update_items_quantity()` to match `Stock_history` logic**:

```php
// File: application/models/Pos_model.php
// Method: update_items_quantity()

// BEFORE (Line ~445)
$q9 = $this->db->query("select coalesce(SUM(sales_qty),0) as sl_tot_qty 
                        from db_salesitems 
                        where item_id='$item_id' 
                        and sales_status='Final'");

// AFTER (use JOIN to match stock_history logic)
$q9 = $this->db->query("select coalesce(SUM(si.sales_qty),0) as sl_tot_qty 
                        from db_salesitems si
                        JOIN db_sales s ON s.id = si.sales_id
                        where si.item_id='$item_id' 
                        and s.status=1");
```

**Why this fixes it**:
- Both systems now read from the same logical data source
- `db_sales.status=1` is the authoritative "sale is active" flag
- Both db_items and stock_history now count the same sales
- No more timing mismatches

---

## Testing the Fix

After implementing, verify:

```bash
1. Create a new sale for IT0001 (qty=10)
2. Check db_items.stock value
3. Check stock_history.current_stock value
4. They should now match ✓

5. Save sale as 'Draft'
6. Check both values again
7. They should still match ✓

8. Finalize sale to 'Final'
9. Check both values
10. They should remain matching ✓
```

---

## Implementation Checklist

- [ ] Modify `Pos_model.update_items_quantity()` to join with `db_sales` table
- [ ] Test with new sales creation
- [ ] Test with draft sales
- [ ] Test with finalized sales
- [ ] Run sync to verify no further discrepancies
- [ ] Check historical sales data for accumulated mismatches
- [ ] Document status field meanings for future developers

---

## Additional Improvements to Consider

1. **Add purchase status join too** - Same issue likely exists for purchases
2. **Create DB indexes** on `db_salesitems(sales_id)` and `db_salesitems(item_id)` for JOIN performance
3. **Add transaction logging** - Log every stock update with reason
4. **Automated reconciliation** - Run sync daily to catch any residual mismatches
5. **Unit tests** - Create tests that verify stock consistency after each operation

---

## Files to Review

- `application/models/Pos_model.php` - Contains problematic query
- `application/models/Stock_history_model.php` - Reference implementation (correct)
- `application/models/Sales_model.php` - Where sales are created
- `FIX_GUIDES.md` - Section #4 (Float Precision) also affects stock calculations
