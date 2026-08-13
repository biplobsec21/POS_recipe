# Ledger Stock History vs db_items Discrepancy Analysis

**Date**: August 13, 2026  
**Analysis Type**: Complete Stock Verification  
**Total Items Analyzed**: 173 with discrepancies

---

## Executive Summary

**Critical Finding**: **173 out of ~210 items** have discrepancies between:
- **db_items.stock** (current cached value in system)
- **Ledger Stock** (calculated from purchase/sales transaction history)

### Key Numbers
| Metric | Value |
|--------|-------|
| **Items with Discrepancies** | 173 |
| **Total Discrepancy Amount** | 168,322.27 units |
| **Overstated Items** | 165 (164,641.18 units) |
| **Understated Items** | 8 (3,681.09 units) |
| **Most Critical Item** | IT0091: 90,516.27 unit discrepancy |

---

## Understanding the Discrepancy

### What is "Ledger Stock"?

The ledger calculates stock from official transaction records:

```
Ledger Stock = Purchase - Sales + Sales Returns - Purchase Returns
```

**Data Sources**:
- **Purchases**: From `db_purchaseitems` (official purchase records)
- **Sales**: From `db_salesitems` (official sales records)
- **Returns**: From return tables with status = 1

**Key characteristic**: Ledger only counts OFFICIAL transactions, not manual adjustments

### What is "db_items.stock"?

The cached stock value currently stored in the database.

**How it's updated**: By `Pos_model.update_items_quantity()` method (which has the wrong logic!)

**Problem**: Includes manual stock entries from `db_stockentry` which may not align with official transactions

---

## The Three Patterns

### Pattern 1: OVERSTATED (165 items - 164,641.18 units)

**What it means**: 
- db_items shows MORE stock than ledger calculation
- Example: IT0091 shows 90,516.27 but ledger shows 0
- System thinks we have 90,516 units but we actually have 0

**Root Cause**: 
- Likely NO purchases in ledger for these items
- But db_items has value (from where?)
- Suggests manual entries in db_stockentry that aren't linked to official transactions

**Example Items**:
```
IT0091: 90,516.27 overstated (Nomal Stekar)
IT0090: 24,625.00 overstated (Special stekar)
IT0097: 15,950.00 overstated (ST peket)
IT0017: 5,532.50 overstated (Cake box)
```

### Pattern 2: UNDERSTATED (8 items - 3,681.09 units)

**What it means**:
- db_items shows LESS stock than ledger calculation
- Example: IT0042 shows -25.91 but ledger shows 3,500.00
- System thinks we're negative, but ledger shows we have 3,500!

**Root Cause**:
- Official purchases exist in ledger
- But db_items doesn't reflect them properly
- Suggests purchase records exist but weren't synced to db_items

**Example Items**:
```
IT0042: -3,525.91 difference (Ledger +3,500, db_items -25.91)
IT0015: -62.50 difference (Ledger +72, db_items 9.50)
IT0016: -59.50 difference (Ledger +66, db_items 6.50)
IT0007: -28.96 difference (Ledger +30, db_items 1.04)
IT0063: -3.51 difference (Ledger +12, db_items 8.49)
IT0055: -0.56 difference (Ledger 0, db_items -0.56)
IT0057: -0.10 difference (Ledger +16, db_items 15.90)
IT0073: -0.05 difference (Ledger 0, db_items -0.05)
```

### Pattern 3: NO LEDGER DATA (0 stock in ledger)

**What it means**:
- Item has NO purchase history
- db_items shows stock (from somewhere)
- Ledger calculation = 0

**Root Cause**: 
- Item was entered manually into inventory
- Never officially purchased
- No transaction records exist

**Example Items**: 
- Majority of the 165 overstated items

---

## Critical Items Requiring Attention

### TIER 1: CRITICAL (≥1000 unit discrepancy)

| Item Code | Item Name | db_items | Ledger | Difference | Issue |
|-----------|-----------|----------|--------|-----------|-------|
| IT0091 | Nomal Stekar | 90,516.27 | 0 | +90,516 | OVERSTATED |
| IT0090 | Special stekar | 24,625 | 0 | +24,625 | OVERSTATED |
| IT0097 | ST peket | 15,950 | 0 | +15,950 | OVERSTATED |
| IT0017 | Cake box 1-6 lbs | 5,532.50 | 0 | +5,532.50 | OVERSTATED |
| IT0042 | Dim / egg | -25.91 | 3,500 | -3,525.91 | UNDERSTATED |
| IT0127 | Saddamban Regular | 2,329 | 0 | +2,329 | OVERSTATED |
| IT0135 | Jhal patis Regular | 1,609.73 | 0 | +1,609.73 | OVERSTATED |

---

## Why This Happened

### Root Cause 1: Wrong Status Filters in Pos_model

**Current (WRONG)**:
```php
// Reads from db_stockentry + db_purchaseitems (purchase_status='Received')
$stock = $stockentry + $purchases - $sales + $returns
```

**Problem**: Mixes manual entries with official transactions

**New (CORRECT - Stock History)**:
```php
// Reads from official transaction tables with status=1
$stock = $purchases - $sales + $sales_returns - $purchase_returns
```

### Root Cause 2: Manual Stock Entries Without Documentation

Many items in `db_stockentry` have:
- No note (NULL)
- No explanation
- No link to official transactions

These inflate db_items.stock but don't appear in ledger

### Root Cause 3: Purchase Records Without Sync

Some items have purchases in the system but:
- db_items.stock wasn't updated
- Or was updated incorrectly
- Result: Ledger shows stock, db_items doesn't

---

## Impact Assessment

### Business Impact

**Inventory is 168,322 units off!**

| Scenario | Impact |
|----------|--------|
| **Audit/Stocktake** | Counting physical inventory will find 165,000+ missing units |
| **Purchasing** | System thinks we have stock we don't have |
| **Sales** | System allows sales of items we don't have in stock |
| **Financial** | Inventory asset value is wrong |
| **Operations** | Stock-outs happen unexpectedly |

### Financial Impact

If average item cost is 100 Taka:
- 164,641 units × 100 Taka = **16,464,100 Taka inventory overstatement**

---

## Resolution Strategy

### Phase 1: Understand the Data (This Week)

**Question 1**: For items with zero ledger stock but positive db_items stock
- Were these manually entered into inventory?
- Do we physically have these items?
- Should they be there?

**Question 2**: For items with negative db_items stock
- Why does the system show negative stock?
- Is this a business rule or a bug?
- Should these be corrected to match ledger?

### Phase 2: Implement Stock History Method (This Week)

Replace the current `Pos_model.update_items_quantity()` with:

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

**Result**: All db_items.stock will recalculate based on official transactions only

### Phase 3: Handle Manual Entries (This Week)

For items with no ledger data but db_items stock:
- Document what they represent
- Either:
  - Add to db_stockentry with proper note
  - Or create dummy purchase records in system

### Phase 4: Reconciliation (Next Week)

1. Run sync with new method
2. Physical inventory count
3. Adjust for discrepancies
4. Document any unexplained variances

---

## Detailed Breakdown by Discrepancy Size

### 100,000+ Units Overstated
```
IT0091: 90,516.27 (Nomal Stekar)
IT0090: 24,625.00 (Special stekar)
```

### 10,000 - 99,999 Units Overstated
```
IT0097: 15,950.00 (ST peket)
```

### 1,000 - 9,999 Units Overstated
```
IT0017: 5,532.50 (Cake box 1-6 lbs)
IT0127: 2,329.00 (Saddamban Regular)
IT0135: 1,609.73 (Jhal patis Regular)
IT0181: 1,259.00 (Special Box Dry Cake)
IT0099: 1,175.00 (senduice peket)
IT0100: 1,027.00 (Biscut box)
IT0167: 1,023.00 (Normal Chanachur Regular)
IT0173: 968.00 (Biscuite Regular)
IT0098: 785.00 (St peket logo)
```

### 100 - 999 Units Overstated
```
40+ items with discrepancies in this range
```

### 1 - 99 Units Overstated
```
100+ items with small discrepancies
```

### Understated (Ledger > db_items)
```
IT0042: -3,525.91 (Ledger +3,500 vs db_items -25.91)
IT0015: -62.50
IT0016: -59.50
IT0007: -28.96
IT0063: -3.51
IT0055: -0.56
IT0057: -0.10
IT0073: -0.05
```

---

## Recommended Actions by Item Category

### For Top 10 Critical Items
- [ ] Manually verify each one
- [ ] Check if purchases exist
- [ ] Check physical inventory
- [ ] Document findings
- [ ] Create correction plan

### For Remaining 163 Items
- [ ] Implement new stock_history method
- [ ] Let system recalculate
- [ ] Review results
- [ ] Correct any outliers

---

## SQL Files Provided

1. **LEDGER_DISCREPANCY_REPORT.sql** - This diagnostic report
2. **LEDGER_VS_STOCK_DISCREPANCY.sql** - Original (more complex) version

---

## Next Steps

**Immediate (Today)**:
1. ✅ Review this analysis
2. Read the Top 10 critical items above
3. Get clarification on where these items came from

**This Week**:
4. Implement new Pos_model.update_items_quantity()
5. Run full sync
6. Review results

**Next Week**:
7. Physical inventory count
8. Reconcile discrepancies
9. Implement nightly verification

---

## Key Insight

> The huge discrepancy (173 items, 168,322 units) indicates that the system has never been properly synced since data entry. Implementing the stock_history method will fix this by using official transaction records as the source of truth.

---

**Report Complete** ✓

**Action Required**: Review critical items and decide whether to keep manual values or sync to ledger.

