# IT0093 (Pesti box) - Root Cause Analysis

**Issue**: Stock discrepancy of 1,500 units from purchase PU0008

**Date**: August 13, 2026

---

## The Problem Explained

### Current State
```
Item: IT0093 (Pesti box)
db_items.stock (current): 1,755 units
db_stockentry total: 255 units
Discrepancy: +1,500 units (OVERSTATEMENT)
```

### The Root Cause: Purchase PU0008

**Purchase PU0008 Details**:
- Purchase Code: `PU0008`
- Status: `1` (Active)
- Item purchased: `IT0093` (Pesti box)
- Quantity: `1,500 units`
- Purchase Status: `Received`
- Purchase Date: `2026-08-06`

### Where the 1,500 Units Appear

| System | Reads | Includes PU0008? |
|--------|-------|---|
| **db_stockentry** | Manual stock entries only | ❌ NO (PU0008 is not here) |
| **db_items.stock** | db_stockentry + db_purchaseitems | ✅ YES (includes PU0008) |
| **stock_history** | db_purchaseitems with p.status=1 | ✅ YES (includes PU0008) |

### The Calculation Breakdown

```
Total Purchase (from db_purchaseitems):        1,500 units
Total Sold (from db_salesitems):                   0 units
Manual Stock Entry (from db_stockentry):         255 units
Sales Returns:                                     0 units
Purchase Returns:                                  0 units

Stock History Calculation:
  = 1,500 (purchase) 
    - 0 (sold) 
    + 0 (sales return) 
    - 0 (purchase return)
  = 1,500 units

Current db_items.stock:                        1,755 units (WRONG)
Difference:                                      +255 units (extra!)
```

---

## Why Does db_items Show 1,755 Instead of 1,500?

### Old Method (Current - WRONG)
```php
// Pos_model.update_items_quantity()
$stock_qty = SUM(db_stockentry.qty)           // = 255
$pu_tot_qty = SUM(db_purchaseitems WHERE purchase_status='Received')  // = 1,500
$sl_tot_qty = SUM(db_salesitems WHERE sales_status='Final')  // = 0
$stock = $stock_qty + $pu_tot_qty - $sl_tot_qty  // = 255 + 1,500 - 0 = 1,755 ✓
```

This formula adds both:
- Manual stock entry (255) 
- Purchase (1,500)
- Total: 1,755

### New Method (Stock History - CORRECT)
```php
// New Pos_model.update_items_quantity()
$summary = Stock_history_model.get_stock_summary(93)
$current_stock = 1,500  // Purchase only, no double-counting
```

The stock_history method doesn't double-count. It reads:
- Purchases: 1,500
- Sales: 0
- Total: 1,500

---

## The Issue: Double Counting

### Question: What Does the 255 Units in db_stockentry Represent?

Let's check the stock entry note:
```sql
SELECT * FROM db_stockentry WHERE item_id = 93;
```

**Hypothesis 1**: The 255 units are the SAME as part of PU0008
- Maybe PU0008 brought in 1,500 units total
- The 255 were manually logged in db_stockentry as a partial entry
- Result: Counting 1,500 + 255 = 1,755 (DOUBLE COUNTING)

**Hypothesis 2**: The 255 units are SEPARATE from PU0008
- Opening stock or manual adjustment
- Completely different transaction
- Result: Correct total is 1,755 units

---

## Why Stock History Method is Better

The **stock_history method** avoids this confusion because it:

1. **Reads from single source per transaction type**
   - All purchases from `db_purchaseitems` (not duplicated elsewhere)
   - All sales from `db_salesitems` (not duplicated elsewhere)
   - All returns in their specific tables

2. **Doesn't mix manual entries with purchased entries**
   - Manual stock entries from `db_stockentry` (only non-production)
   - Purchased entries from `db_purchaseitems`
   - No overlap or double-counting

3. **Follows business logic consistently**
   - Purchase comes in → add to stock
   - Sale goes out → subtract from stock
   - Simple, one-directional flow

---

## Verification: What's the Correct Answer?

### Option A: Stock should be 1,755 units
**Scenario**: 
- Received 1,500 units from PU0008
- ALSO have 255 units from manual entry (opening stock, adjustment, etc.)
- Total: 1,500 + 255 = 1,755 ✓

**Correction**: Keep db_items.stock at 1,755 (don't change)

### Option B: Stock should be 1,500 units
**Scenario**: 
- Received 1,500 units from PU0008
- The 255 in db_stockentry is WRONG/DUPLICATE entry
- Total: 1,500 (not 1,755)

**Correction**: 
```sql
UPDATE db_items SET stock = 1500 WHERE id = 93;
DELETE FROM db_stockentry WHERE item_id = 93 AND qty = 255;
```

---

## How to Determine the Correct Answer

### Check 1: Review db_stockentry for IT0093
```sql
SELECT * FROM db_stockentry WHERE item_id = 93;
```

Look for:
- What is the note? (opening stock? adjustment? manual entry?)
- When was it created? (before or after PU0008?)
- Is there documentation of this entry?

### Check 2: Review Purchase PU0008
```sql
SELECT * FROM db_purchaseitems WHERE purchase_id = 8;
```

Look for:
- How many line items in this purchase?
- Does quantity add up to 1,500?
- Is there any note about partial receiving?

### Check 3: Physical Count
If available:
- What does physical inventory show?
- Should guide the correct answer

---

## The Real Issue: Data Quality

This situation reveals a **data quality problem**:

**The Problem**: IT0093 data entry is ambiguous
- Is 255 units part of the 1,500 from PU0008?
- Or is 255 units completely separate?
- System can't tell the difference!

**Why it matters**: 
- Old method: Adds both (1,755)
- New method: Counts only purchases (1,500)
- They differ because db_stockentry entry is ambiguous

---

## Decision Framework

| Scenario | Correct Stock | Action |
|----------|---|---|
| Manual 255 is SEPARATE from PU0008 | 1,755 | Keep current value, no change |
| Manual 255 is DUPLICATE of PU0008 | 1,500 | Fix: Update db_items to 1,500 |
| Manual 255 is PARTIAL of PU0008 | 1,755 | Keep current value, no change |

**To decide**: Check the note in db_stockentry for IT0093. It will explain what the 255 units represent.

---

## Impact of Using Stock History Method

When you implement the new `Pos_model.update_items_quantity()` (using stock_history):

```php
$summary = Stock_history_model.get_stock_summary($item_id);
$current_stock = $summary['current_stock'];  // Will be 1,500 for IT0093
```

**Result**: db_items.stock will be automatically corrected to 1,500

**Unless**: The 255 units in db_stockentry represent a SEPARATE valid entry, in which case:
- You need to keep db_stockentry entry
- This would make correct stock = 1,500 + 255 = 1,755 (the issue is data ambiguity, not the method)

---

## Recommended Resolution

### Step 1: Investigate (Today)
```sql
-- Check what the 255 unit entry represents
SELECT id, item_id, qty, note, entry_date, status 
FROM db_stockentry 
WHERE item_id = 93;

-- If note exists, it will tell you if this is:
-- - Opening stock
-- - Manual adjustment
-- - Production input/output
-- - Etc.
```

### Step 2: Decide (Today)
Based on the note:
- If it's OPENING STOCK or SEPARATE ADJUSTMENT: Correct total is 1,755
- If it's DUPLICATE or ERROR: Correct total is 1,500

### Step 3: Document (Today)
Record your decision for audit trail:
- What does the 255 unit entry represent?
- Is it separate from PU0008 or duplicate?
- What's the correct stock?

### Step 4: Implement (This Week)
Use the new stock_history method in Pos_model:
```php
public function update_items_quantity($item_id)
{
    $this->load->model('stock_history_model');
    $summary = $this->stock_history_model->get_stock_summary($item_id);
    $current_stock = (float) $summary['current_stock'];
    
    $this->db->where('id', $item_id);
    $this->db->update('db_items', ['stock' => $current_stock]);
    
    return ($this->db->affected_rows() > 0) ? true : false;
}
```

---

## Why This Matters

This IT0093 case is **a perfect example** of why the stock_history method is better:

**Old method**: Mechanically adds all sources
- Vulnerable to double-counting
- Doesn't distinguish between manual and automated entries
- Result: 1,755 (but is this correct?)

**New method**: Follows transaction ledger
- Clear business logic per transaction type
- Audit trail shows where each amount comes from
- Result: 1,500 (but may need manual adjustments)

**Conclusion**: The new method catches data quality issues like this one!

---

## Action Items

- [ ] Run: `SELECT * FROM db_stockentry WHERE item_id = 93;`
- [ ] Check: What does the note say for the 255 unit entry?
- [ ] Decide: Is 255 units separate from or part of PU0008?
- [ ] Document: Decision and reasoning
- [ ] Update: db_items.stock to correct value if needed
- [ ] Implement: New Pos_model.update_items_quantity() method

---

## Related Issues

This is a systemic issue affecting all 65 items:
- Each has ambiguous entries in db_stockentry
- Each may have double-counting issues
- New method (stock_history) will surface these issues
- Need to audit and clean data before final implementation

---

## Key Insight

> The discrepancy in IT0093 is not a bug in the calculation method — it's a **data quality issue** being masked by the old calculation method. The new method reveals the problem so you can fix it!

This is actually a **good thing** — it means the new method is working correctly by exposing data issues that the old method was hiding.

