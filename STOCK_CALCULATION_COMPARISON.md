# Stock Calculation Method Comparison

**Question**: Which approach is better for maintaining db_items.stock?
- **Option A**: Pos_model.update_items_quantity() - Direct aggregation
- **Option B**: Stock_history_model.get_stock_summary() - Transaction ledger based

---

## Side-by-Side Comparison

### OPTION A: Pos_model.update_items_quantity()

**Location**: `application/models/Pos_model.php` line 440-467

**How it works**:
```php
// Query 1: Manual stock entries (opening + adjustments)
$stock_qty = SUM(db_stockentry.qty)

// Query 2: Received purchases
$pu_tot_qty = SUM(db_purchaseitems.purchase_qty) 
            WHERE purchase_status='Received'

// Query 3: Final sales ← PROBLEMATIC
$sl_tot_qty = SUM(db_salesitems.sales_qty) 
            WHERE sales_status='Final'

// Query 4: Purchase returns
$pu_return_tot_qty = SUM(db_purchaseitemsreturn.return_qty)

// Query 5: Sales returns
$sl_return_tot_qty = SUM(db_salesitemsreturn.return_qty)

// Calculate
stock = ((stock_qty + pu_tot_qty) - sl_tot_qty + sl_return_tot_qty) - pu_return_tot_qty

// Update db_items
UPDATE db_items SET stock = calculated_stock WHERE id = item_id
```

**Data Sources** (5 separate queries):
- ✓ db_stockentry
- ✓ db_purchaseitems (with purchase_status filter)
- ✓ db_salesitems (with sales_status filter) ← **Issue**
- ✓ db_purchaseitemsreturn
- ✓ db_salesitemsreturn
- ✗ Doesn't read: inventory_movements, db_damageitems, production data

**Called**: After each transaction (every time item is added to sale/purchase)

**Frequency**: High (called for every line item)

---

### OPTION B: Stock_history_model.get_stock_summary()

**Location**: `application/models/Stock_history_model.php` line 12-108

**How it works**:
```php
// Method 1: Read from transaction history (running balance)
$transactions = get_transaction_history($item_id, 0, 1000)
$last_transaction = end($transactions)
$current_stock = $last_transaction->new_quantity  ← Most accurate

// Method 2: Fallback if no history exists
IF $current_stock == 0:
    $current_stock = db_items.stock

// Also calculates detailed breakdown:
$summary['total_purchase'] = SUM(db_purchaseitems) WHERE db_purchase.status=1
$summary['total_sold'] = SUM(db_salesitems) WHERE db_sales.status=1
$summary['opening_stock'] = SUM(db_stockentry) WHERE note NOT LIKE '%Production%'
$summary['total_sell_return'] = SUM(db_salesitemsreturn) WHERE status=1
$summary['total_purchase_return'] = SUM(db_purchaseitemsreturn) WHERE status=1
$summary['production_output'] = SUM(db_stockentry) WHERE note LIKE '%Production Output%'
$summary['production_consumption'] = SUM(inventory_movements) WHERE type='PRODUCTION_CONSUME'
$summary['total_damaged'] = SUM(db_damageitems) WHERE status='approved'
```

**Data Sources** (Transaction history includes 7+ types):
- ✓ db_salesitems (with db_sales.status=1 join) ← **Correct filter**
- ✓ db_purchaseitems (with db_purchase.status=1 join)
- ✓ db_salesitemsreturn
- ✓ db_purchaseitemsreturn
- ✓ db_stockentry (manual adjustments)
- ✓ inventory_movements (production consumption) ← **Important**
- ✓ db_damageitems (approved damage) ← **Important**

**Called**: On-demand (user clicks "Sync" or views history)

**Frequency**: Low (manual trigger)

---

## Detailed Comparison Table

| Aspect | Option A (Pos_model) | Option B (Stock_history) |
|--------|---|---|
| **Status Filter for Sales** | `sales_status='Final'` | `db_sales.status=1` |
| **Status Filter for Purchase** | `purchase_status='Received'` | `db_purchase.status=1` |
| **Handles Production** | ❌ NO | ✓ YES (from inventory_movements) |
| **Handles Damage** | ❌ NO | ✓ YES (db_damageitems) |
| **Data Consistency** | ❌ Mismatches occur | ✓ Single source of truth |
| **Frequency** | 🔴 High - called every transaction | 🟢 Low - manual trigger |
| **Performance** | 5 separate queries + update | 7-12 separate queries |
| **Accuracy** | ❌ Status field mismatch issues | ✓ Complete transaction ledger |
| **Maintenance** | ❌ Hard to troubleshoot | ✓ Easy to verify in UI |
| **Audit Trail** | ❌ No - only final number | ✓ YES - full ledger history |
| **SQL Injection Risk** | 🔴 HIGH - string concatenation | 🟢 LOW - Query Builder |

---

## The Critical Differences

### Issue 1: Status Field Mismatch

**Option A**:
```php
WHERE sales_status='Final'  // Only counts finalized sales
```

**Option B**:
```php
WHERE db_sales.status=1  // Counts all active sales
```

**Result**: Different sales are counted at different times!

Example:
```
Sale created with sales_status='Draft'
- Option A: NOT counted (status != 'Final')
- Option B: Counted (db_sales.status = 1)
= MISMATCH occurs immediately
```

---

### Issue 2: Missing Data

**Option A doesn't read**:
- ❌ Production consumption/output from inventory_movements
- ❌ Approved damage from db_damageitems
- ❌ Production revert entries

**Option B reads from**:
- ✓ inventory_movements (all production)
- ✓ db_damageitems (approved damage)
- ✓ All transaction types

**Result**: Option A stock is incomplete for items involved in production/damage

Example:
```
Item produced (output +100):
- db_items.stock: NOT updated (no production support)
- stock_history: Correctly shows +100
= Massive discrepancy for manufactured items!
```

---

### Issue 3: Call Frequency

**Option A - Called frequently**:
```
1. Add item to sales form → update_items_quantity()
2. Add another item → update_items_quantity()
3. Change quantity → update_items_quantity()
4. Save sale → update_items_quantity()
= 4+ queries per transaction
```

**Option B - Called on-demand**:
```
1. User clicks "Sync All Items" button
2. Or views stock history
= Manual, controlled timing
```

**Result**: Option A causes database load spikes, Option B is predictable

---

## When Each Shows Correct Value

### Option A (Pos_model) Correct When:

✓ All transactions are in 'Final' or 'Received' status  
✓ No production involved  
✓ No damage recorded  
✓ No manual stock adjustments with special notes  
✓ Called at the EXACT right moment

### Option B (Stock_history) Correct When:

✓ Any transaction status (draft, pending, final)  
✓ Production included  
✓ Damage included  
✓ All manual adjustments  
✓ Complete transaction history available  
✓ Always (transaction ledger is source of truth)

---

## Performance Analysis

### Option A: 5-6 Database Queries
```sql
Query 1: SELECT SUM(qty) FROM db_stockentry WHERE item_id=X
Query 2: SELECT SUM(purchase_qty) FROM db_purchaseitems WHERE ... purchase_status='Received'
Query 3: SELECT SUM(sales_qty) FROM db_salesitems WHERE ... sales_status='Final'
Query 4: SELECT SUM(return_qty) FROM db_purchaseitemsreturn WHERE item_id=X
Query 5: SELECT SUM(return_qty) FROM db_salesitemsreturn WHERE item_id=X
Query 6: UPDATE db_items SET stock=X WHERE id=Y

Called: HIGH FREQUENCY (every transaction)
Total: 6 × number of line items = VERY HIGH
```

### Option B: 7-12 Database Queries
```sql
Query 1-7: Build transaction history (7 transaction types)
Query 8-12: Get detailed breakdown (purchase, sold, returns, production, damage)

Called: LOW FREQUENCY (manual "Sync" button)
Total: ~12 queries × 1 sync operation = LOW
```

---

## Recommendation: HYBRID APPROACH (BEST)

### Problem with Option A: Too many queries, status mismatch, incomplete data
### Problem with Option B: Calculated on-demand, doesn't update db_items live

### Solution: **Make db_items the cache, stock_history the source of truth**

```php
// NEW: Better approach
public function update_items_quantity($item_id)
{
    // Instead of 5 separate queries that have issues,
    // just call the reliable stock_history method
    
    $this->load->model('stock_history_model');
    $summary = $this->stock_history_model->get_stock_summary($item_id);
    $current_stock = $summary['current_stock'];
    
    // Update db_items.stock with accurate value
    $this->db->where('id', $item_id);
    $this->db->update('db_items', ['stock' => $current_stock]);
    
    return true;
}
```

**Benefits**:
- ✓ Single source of truth: stock_history_model
- ✓ Includes production, damage, all transaction types
- ✓ Same status logic throughout system
- ✓ Consistent with what users see in history
- ✓ Easy to maintain and troubleshoot
- ✓ Audit trail preserved

---

## The Answer to Your Question

### **Option B (Stock_history) is Better** because:

1. **Complete Data**
   - Includes production consumption/output
   - Includes approved damage
   - Includes all transaction types
   - Option A misses critical data

2. **Consistent Status Filtering**
   - Uses db_sales.status=1 (authoritative)
   - Not just sales_status='Final' (insufficient)
   - Matches what users see in history

3. **Single Source of Truth**
   - Transaction ledger is the source of truth
   - Easier to audit and verify
   - When mismatch occurs, you know to fix transaction data

4. **Auditability**
   - Full transaction history is available
   - Users can see the "why" not just the "what"
   - Easier debugging

### **BUT** modify it this way:

Replace Pos_model.update_items_quantity() with:

```php
public function update_items_quantity($item_id)
{
    $this->load->model('stock_history_model');
    
    // Get authoritative stock from transaction history
    $summary = $this->stock_history_model->get_stock_summary($item_id);
    $current_stock = (float) $summary['current_stock'];
    
    // Update db_items.stock cache
    $this->db->where('id', $item_id);
    $result = $this->db->update('db_items', ['stock' => $current_stock]);
    
    if (!$result) {
        log_message('error', 'Failed to update stock for item: ' . $item_id);
        return false;
    }
    
    return true;
}
```

---

## Implementation Plan

### Step 1: Update Pos_model.update_items_quantity()
Replace the 5-query approach with call to Stock_history_model

### Step 2: Test
- Create sales → verify stock updates correctly
- Create production → verify stock updates correctly  
- Record damage → verify stock updates correctly
- Run sync → verify no further changes needed

### Step 3: Cleanup
- Remove old calculation logic
- Remove dead code from Pos_model
- Add documentation

---

## Summary Table: Which to Use When

| Task | Use |
|------|-----|
| **Displaying current stock in items list** | `db_items.stock` (cache) |
| **Calculating stock after transaction** | `Stock_history_model.get_stock_summary()` |
| **Viewing stock history/audit trail** | `Stock_history_model.get_transaction_history()` |
| **Syncing discrepancies** | `sync_current_stock()` (which uses Stock_history) |
| **Batch operations** | `Stock_history_model.get_stock_summary()` |
| **Production/damage workflows** | `Stock_history_model` (only option) |

---

## Files to Modify

1. **application/models/Pos_model.php**
   - Replace `update_items_quantity()` method

2. **application/models/Stock_history_model.php**
   - No changes needed (already correct)

3. **application/controllers/Items.php**
   - `sync_current_stock()` already uses correct method ✓
   - `sync_all_items_stock()` already uses correct method ✓

---

## Expected Results After Fix

**Before**:
```
Sale IT0001 created:
- db_items.stock = 100 (not updated - sales_status not 'Final')
- stock_history = 90 (updated - db_sales.status=1)
- Mismatch: 10 units
```

**After**:
```
Sale IT0001 created:
- db_items.stock = 90 (updated via stock_history)
- stock_history = 90 (updated - db_sales.status=1)
- Match: ✓ Consistent
```

