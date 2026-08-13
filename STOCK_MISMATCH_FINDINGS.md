# Stock Mismatch Analysis Report

**Date**: August 13, 2026  
**Database**: piooneer_testing  
**Total Items Analyzed**: 130+  
**Items with Discrepancies**: 60+  
**Items with No Issues**: 70+

---

## Executive Summary

Comprehensive diagnostic found **60+ items with stock discrepancies** between `db_items.stock` (system snapshot) and `db_stockentry` SUM (source of truth). 

**Most Critical Finding**: 3 items have **CRITICAL discrepancies ≥1000 units**:
- IT0042 (Dim / egg): -3,507.09 units **UNDERSTATEMENT**
- IT0096 (Mofin pepar): -2,000.00 units **UNDERSTATEMENT**
- IT0093 (Pesti box): -1,500.00 units **UNDERSTATEMENT**

These indicate serious data entry errors or missing transactions.

---

## Severity Breakdown

### 🔴 CRITICAL Issues (≥1000 units)
**Count**: 3 items  
**Affected Items**:
1. **IT0042** - Dim / egg
   - Current: -25.91 units
   - Should be: -3,533 units
   - Discrepancy: -3,507.09 units (UNDERSTATEMENT)
   - **Impact**: System shows POSITIVE stock, actual is NEGATIVE 3,533

2. **IT0096** - Mofin pepar
   - Current: 2,860 units
   - Should be: 860 units
   - Discrepancy: -2,000 units (UNDERSTATEMENT)
   - **Impact**: Stock overstated by 2,000 units

3. **IT0093** - Pesti box
   - Current: 1,755 units
   - Should be: 255 units
   - Discrepancy: -1,500 units (UNDERSTATEMENT)
   - **Impact**: Stock overstated by 1,500 units

### 🟠 HIGH Priority Issues (100-999 units)
**Count**: 7 items

| Item Code | Item Name | Current Stock | Correct Stock | Discrepancy | Issue |
|-----------|-----------|---|---|---|---|
| IT0094 | coke sprite botol | 643 | 115 | -528 | Overstated |
| IT0095 | Poster pepar | 1,574.50 | 1,074 | -500.50 | Overstated |
| IT0043 | piaje Onion | 324.25 | 40 | -284.25 | Overstated |
| IT0001 | moyda | 1,322.39 | 1,070 | -252.39 | Overstated |
| IT0004 | Oil | 460.34 | 277 | -183.34 | Overstated |
| IT0003 | Dalda | 315.64 | 151 | -164.64 | Overstated |
| IT0002 | Sugar | 459.36 | 358 | -101.36 | Overstated |
| IT0050 | Bason / bashon | 108.50 | 8 | -100.50 | Overstated |
| IT0033 | Sugar pepar | 158 | 58 | -100 | Overstated |

### 🟡 MEDIUM Priority Issues (10-99 units)
**Count**: 14 items - Including items like:
- IT0015 (Huyep crem Tropical): -78.50 units
- IT0016 (Huyep crem vivo): -72.50 units
- IT0013 (Venila powder): -55.56 units
- IT0065 (Murgi hen): -34.49 units
- And 10 more...

### 🟢 LOW Priority Issues (<10 units)
**Count**: 36+ items - Mostly minor discrepancies due to rounding

---

## Pattern Analysis

### Pattern 1: UNDERSTATEMENT (Most Common)
**Affected**: 43 items  
**What it means**: db_items.stock shows HIGHER than actual  
**Root Cause**: Missing sales/consumption entries in db_stockentry OR errors in production output recording

Example: IT0096 (Mofin pepar)
- db_items shows: 2,860 units
- db_stockentry shows: 860 units
- Missing: 2,000 units (sales not recorded? production error?)

### Pattern 2: OVERSTATEMENT (Less Common)
**Affected**: 17 items  
**What it means**: db_items.stock shows LOWER than actual  
**Root Cause**: Extra stock entries added to db_stockentry OR purchase entries recorded twice

Example: IT0131 (02 bait Chiken Patis Regular)
- db_items shows: 135 units
- db_stockentry shows: 141 units
- Extra: 6 units (duplicate entry? missed sales?)

---

## Root Causes (Hypothesis)

### Root Cause 1: Data Entry Errors in Production Output
**Items Affected**: IT0042, IT0096, IT0093, IT0043  
**Evidence**: Large discrepancies in CRITICAL/HIGH categories, often 2-3x factor

### Root Cause 2: Missing Sales/Consumption Records
**Items Affected**: Most UNDERSTATEMENT items  
**Evidence**: db_items.stock is higher than db_stockentry, indicating sales weren't deducted

### Root Cause 3: Duplicate Entries
**Items Affected**: Most OVERSTATEMENT items  
**Evidence**: db_stockentry has more than db_items, suggesting duplicate manual entries

### Root Cause 4: Floating-Point Rounding
**Items Affected**: Many LOW priority items (0.25, 0.27, 0.50 unit discrepancies)  
**Evidence**: Consistent small discrepancies, likely cumulative rounding errors

### Root Cause 5: System Status Mismatch
**Items Affected**: All affected items  
**Evidence**: Related to earlier issue where `db_salesitems.sales_status='Final'` vs `db_sales.status=1` mismatch

---

## Top 10 Items Requiring Correction

| Rank | Item Code | Item Name | Correction Needed | Urgency |
|------|-----------|-----------|---|---|
| 1 | IT0042 | Dim / egg | -3,507.09 | CRITICAL |
| 2 | IT0096 | Mofin pepar | -2,000.00 | CRITICAL |
| 3 | IT0093 | Pesti box | -1,500.00 | CRITICAL |
| 4 | IT0094 | coke sprite botol | -528.00 | HIGH |
| 5 | IT0095 | Poster pepar | -500.50 | HIGH |
| 6 | IT0043 | piaje Onion | -284.25 | HIGH |
| 7 | IT0001 | moyda | -252.39 | HIGH |
| 8 | IT0004 | Oil | -183.34 | HIGH |
| 9 | IT0003 | Dalda | -164.64 | HIGH |
| 10 | IT0002 | Sugar | -101.36 | HIGH |

---

## Comparison to IT0135 (Jhal patis)

The issue you just fixed (IT0135) was categorized as **LOW priority**:
- Current: 1,609.73 units
- Should be: 1,610 units
- Discrepancy: +0.27 units (OVERSTATEMENT)
- **Status**: Essentially matched after your correction ✓

This shows the IT0135 issue was relatively minor compared to the CRITICAL/HIGH priority items above.

---

## Recommended Action Plan

### Phase 1: Investigate CRITICAL Items (Immediate)
1. **IT0042 (Dim / egg)** - Check why stock is negative
2. **IT0096 (Mofin pepar)** - 2,000 unit discrepancy needs investigation
3. **IT0093 (Pesti box)** - 1,500 unit discrepancy needs investigation

**Actions**:
- Review all production batches for these items
- Check if sales were recorded but not synchronized
- Check if purchases were imported incorrectly

### Phase 2: Fix HIGH Priority Items (This Week)
1. Correct the 7 HIGH priority items (100-999 units)
2. Generate correction SQL for each
3. Execute with audit logging

### Phase 3: Monitor MEDIUM Priority Items (This Week)
1. Review the 14 MEDIUM priority items
2. Correct if root cause is clear
3. Leave for manual review if ambiguous

### Phase 4: Clean Up LOW Priority Items (Next Week)
1. Most are likely rounding errors
2. Can be batch corrected
3. Implement floating-point formatting fix (already done in views)

### Phase 5: Prevent Future Issues (Ongoing)
1. Implement the fix from SALES_STOCK_MISMATCH_ANALYSIS.md
2. Change Pos_model.update_items_quantity() to use stock_history logic
3. Add nightly reconciliation job
4. Add real-time alerts for large discrepancies

---

## SQL Files Generated

### 1. FIND_ALL_STOCK_MISMATCHES.sql
Comprehensive diagnostic script with 10 sections

### 2. STOCK_MISMATCH_REPORT.sql
Clean report version (working)

### 3. JHAL_PATIS_CORRECTION_FINAL.sql
Specific correction for IT0135 (already done)

---

## Next Steps

### For IT0042 (Dim / egg) - CRITICAL
```sql
-- Check current state
SELECT 'Current db_items.stock' as metric, stock FROM db_items WHERE id = 42;
SELECT 'db_stockentry total' as metric, SUM(qty) FROM db_stockentry WHERE item_id = 42;

-- Check if negative stock is intentional
SELECT * FROM db_items WHERE id = 42;

-- If correction needed:
UPDATE db_items SET stock = -3533 WHERE id = 42;
```

### For IT0096 (Mofin pepar) - CRITICAL
```sql
-- Correction for -2,000 unit discrepancy
UPDATE db_items SET stock = 860 WHERE id = 96;
```

### For IT0093 (Pesti box) - CRITICAL
```sql
-- Correction for -1,500 unit discrepancy
UPDATE db_items SET stock = 255 WHERE id = 93;
```

---

## Investigation Checklist

- [ ] Review all production batches for CRITICAL items
- [ ] Check for duplicate sales entries
- [ ] Check for missing purchase records
- [ ] Review manual stock adjustment entries
- [ ] Check if any items have negative stock (should they?)
- [ ] Verify floating-point precision issues (likely for LOW issues)
- [ ] Run reconciliation after implementing fixes
- [ ] Monitor for new discrepancies

---

## Related Documentation

- **SALES_STOCK_MISMATCH_ANALYSIS.md** - Root cause of mismatch pattern
- **STOCK_CALCULATION_COMPARISON.md** - Why stock_history is the source of truth
- **FIX_GUIDES.md** - Overall system fixes (Issue #4: Float Precision)
- **JHAL_PATIS_STOCK_CORRECTION.sql** - Example correction script

---

## Key Insights

1. **UNDERSTATEMENT is dominant** (43 vs 17 items)
   - Suggests sales/consumption entries aren't being synchronized properly
   - Related to the db_salesitems.sales_status vs db_sales.status issue

2. **CRITICAL issues are rare but severe** (3 items)
   - These suggest fundamental data entry errors
   - Need human investigation before correction

3. **LOW priority items are numerous** (36 items)
   - Mostly rounding errors
   - Display formatting fix (3 decimal places) should help

4. **Pattern consistency**
   - All CRITICAL/HIGH items show significant overstatement in db_items
   - Suggests systematic issue with how stock is updated or calculated

5. **IT0135 fix was successful**
   - Demonstrates correction process works
   - Now use same process for other items

---

## Recommended Prevention Strategy

### Immediate (Next 24 hours)
1. Fix Pos_model.update_items_quantity() to use stock_history logic
2. Create correction scripts for CRITICAL items
3. Implement corrections after investigation

### Short-term (This week)
1. Sync all items using corrected logic
2. Fix HIGH priority items
3. Monitor for discrepancies

### Long-term (Ongoing)
1. Add nightly reconciliation
2. Create alerts for large discrepancies (>10 units)
3. Implement real-time stock sync
4. Add transaction logging for all stock changes
5. Create monthly audit reports

---

## Questions for Investigation

1. Why do CRITICAL items show such large discrepancies?
   - Is stock ever supposed to be negative (like IT0042)?
   - Did a migration lose data?
   - Were imports done incorrectly?

2. Why is UNDERSTATEMENT more common?
   - Are sales not being synced?
   - Is production calculation wrong?
   - Is there a timing issue?

3. Should negative stock be allowed?
   - IT0042 has -25.91 units in db_items
   - IT0015, IT0016, IT0063 also have negative or near-zero values
   - Is this a business requirement or data error?

---

## Conclusion

The diagnostic found a **systematic stock synchronization issue** affecting 60+ items. The issue is most severe for CRITICAL (3 items) and HIGH (7 items) priority items. 

**Immediate Action Required**: Investigate IT0042, IT0096, and IT0093 before correcting to understand root cause.

**Long-term Solution**: Implement the fix from SALES_STOCK_MISMATCH_ANALYSIS.md to prevent future mismatches.

