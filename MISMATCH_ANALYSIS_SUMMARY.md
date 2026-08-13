# Stock Mismatch Analysis Summary

**Analysis Date**: August 13, 2026  
**Database Scanned**: piooneer_testing  
**Total Items Found**: 65 with discrepancies

---

## Quick Facts

| Metric | Value |
|--------|-------|
| **Total Items in System** | 130+ |
| **Items with Issues** | 65 |
| **Items with NO Issues** | 70+ |
| **CRITICAL Severity** | 3 items |
| **HIGH Severity** | 9 items |
| **MEDIUM Severity** | 14 items |
| **LOW Severity** | 36 items |
| **Largest Discrepancy** | IT0042: -3,507.09 units |
| **Most Common Pattern** | UNDERSTATEMENT (43 items) |

---

## The 3 Most Critical Issues

### 🔴 IT0042 - Dim / egg
- **Current Stock**: -25.91 units (NEGATIVE!)
- **Correct Stock**: -3,533 units
- **Discrepancy**: -3,507.09 units
- **Issue**: Severely understated
- **Action**: INVESTIGATE FIRST - Why is stock negative?

### 🔴 IT0096 - Mofin pepar
- **Current Stock**: 2,860 units
- **Correct Stock**: 860 units
- **Discrepancy**: -2,000 units
- **Issue**: Overstated by 2,000 units
- **Action**: Review sales & production records

### 🔴 IT0093 - Pesti box
- **Current Stock**: 1,755 units
- **Correct Stock**: 255 units
- **Discrepancy**: -1,500 units
- **Issue**: Overstated by 1,500 units
- **Action**: Audit production batches

---

## Pattern Analysis

### Understatement (43 items) - db_items shows MORE than actual
**Examples**:
- IT0096: Shows 2,860 but should be 860 (-2,000)
- IT0043: Shows 324.25 but should be 40 (-284.25)
- IT0001: Shows 1,322.39 but should be 1,070 (-252.39)

**Root Cause**: Likely missing sales/consumption entries or incorrect production yield

### Overstatement (17 items) - db_items shows LESS than actual
**Examples**:
- IT0131: Shows 135 but should be 141 (+6)
- IT0092: Shows 5.85 but should be 11 (+5.15)
- IT0061: Shows 9.60 but should be 14 (+4.40)

**Root Cause**: Likely duplicate stock entries or missed negative adjustments

---

## Files Created for You

| File | Purpose | Use Case |
|------|---------|----------|
| **FIND_ALL_STOCK_MISMATCHES.sql** | Comprehensive diagnostic | Run once to identify all issues |
| **STOCK_MISMATCH_REPORT.sql** | Clean report version | Run for clean output |
| **STOCK_MISMATCH_FINDINGS.md** | Detailed analysis doc | Read for understanding |
| **BULK_STOCK_CORRECTIONS.sql** | All corrections at once | Use with CAUTION |
| **JHAL_PATIS_CORRECTION_FINAL.sql** | Single item template | Use as template for individual fixes |

---

## Recommended Action Plan

### Phase 1: Investigate CRITICAL (Today)
```bash
# 1. Review why IT0042 has negative stock
mysql -e "SELECT * FROM db_items WHERE id = 42;"

# 2. Check IT0096 production batches
mysql -e "SELECT * FROM production_batches WHERE id IN (SELECT recipe_id FROM recipes WHERE output_product_id = 96);"

# 3. Check sales records
mysql -e "SELECT * FROM db_salesitems WHERE item_id = 96 LIMIT 10;"
```

### Phase 2: Fix HIGH Priority (This Week)
Use template to create individual correction scripts for each of 9 items:
```bash
# Template:
UPDATE db_items SET stock = <correct_value> WHERE id = <item_id>;
```

### Phase 3: Monitor MEDIUM Priority (Next Week)
- Review 14 items manually
- Decide which to correct immediately
- Leave ambiguous ones for human decision

### Phase 4: Batch Fix LOW Priority (Next Week)
- 36 items with small discrepancies
- Most are rounding errors
- Safe to bulk correct

### Phase 5: Implement Prevention (Ongoing)
- [ ] Fix Pos_model.update_items_quantity() (see SALES_STOCK_MISMATCH_ANALYSIS.md)
- [ ] Add nightly reconciliation
- [ ] Create alerts for discrepancies > 10 units
- [ ] Add real-time sync for production

---

## How to Use the Files

### To Run Diagnostic
```bash
mysql -h 127.0.0.1 -u root -proot piooneer_testing < STOCK_MISMATCH_REPORT.sql
```

### To Get Clean Report
```bash
# First backup
mysqldump -h 127.0.0.1 -u root -proot piooneer_testing > backup.sql

# Then run report
mysql -h 127.0.0.1 -u root -proot piooneer_testing < STOCK_MISMATCH_REPORT.sql > report.txt
```

### To Fix Individual Items
Copy template from JHAL_PATIS_CORRECTION_FINAL.sql and customize:
```sql
-- Change these values for each item:
UPDATE db_items SET stock = <new_value> WHERE id = <item_id>;
```

### To Fix All At Once (NOT RECOMMENDED)
```bash
# Only after investigating CRITICAL items!
mysql -h 127.0.0.1 -u root -proot piooneer_testing < BULK_STOCK_CORRECTIONS.sql
```

---

## Key Insights

### 1. Systematic Issue
All 65 items show the same pattern: **db_items.stock is not synced with db_stockentry**

**Root Cause**: The Pos_model.update_items_quantity() method uses wrong status filters
- Reads: `db_salesitems.sales_status = 'Final'`
- Should read: `db_sales.status = 1` (like stock_history does)

**Solution**: See SALES_STOCK_MISMATCH_ANALYSIS.md for the fix

### 2. Understatement Dominance
43 understatement vs 17 overstatement suggests:
- Sales are being entered but not synced to db_items
- OR production yield is incorrectly calculated
- OR both issues simultaneously

### 3. IT0135 (Jhal patis) Was Minor
Your recent fix (3,503 → 1,609.73) was actually one of the better cases!
- After correction: 0.27 unit discrepancy (almost perfect)
- CRITICAL items are 1000x worse

### 4. Floating-Point Precision
Many LOW items have exactly 0.50 or 0.25 unit differences
- Indicates cumulative rounding errors
- Display formatting fix (3 decimals) already applied ✓

### 5. Negative Stock Exists
Multiple items have negative stock:
- IT0042: -25.91 units (then corrected to -3,533!)
- IT0015: -69 units
- IT0016: -66 units
- Question: Is this intentional or a bug?

---

## Expected Results After Corrections

### Before Corrections
```
Item: IT0096 (Mofin pepar)
db_items.stock: 2,860
db_stockentry total: 860
Difference: -2,000 units (MISMATCH)
```

### After Corrections
```
Item: IT0096 (Mofin pepar)
db_items.stock: 860
db_stockentry total: 860
Difference: 0 units (MATCH!)
```

---

## Safety Considerations

⚠️ **CRITICAL**: Always backup before correcting!
```bash
mysqldump -h 127.0.0.1 -u root -proot piooneer_testing > backup_$(date +%Y%m%d_%H%M%S).sql
```

✅ **Good**: All correction scripts use transactions (can ROLLBACK)

✅ **Good**: Scripts include verification queries

⚠️ **Caution**: BULK_STOCK_CORRECTIONS.sql is all-or-nothing
- Use JHAL_PATIS_CORRECTION_FINAL.sql template instead for individual items
- Test with one item first

---

## Questions to Answer Before Correcting

1. **Is negative stock intentional?**
   - If YES: Keep IT0042 as -3,533
   - If NO: Investigate why it's negative

2. **What happened to the 2,000 missing units of IT0096?**
   - Lost in import?
   - Never entered?
   - In different item?

3. **Why does this pattern exist for ALL 65 items?**
   - System-wide sync issue
   - Recent import problem?
   - Migration error?

4. **Should we fix all 65 or investigate each one?**
   - Recommended: Fix CRITICAL 3 after investigation, then proceed cautiously
   - NOT recommended: Bulk fix without understanding root cause

---

## Next Immediate Actions

1. **Read**: STOCK_MISMATCH_FINDINGS.md (detailed analysis)
2. **Run**: STOCK_MISMATCH_REPORT.sql (confirm findings)
3. **Investigate**: IT0042, IT0096, IT0093 (CRITICAL items)
4. **Decide**: Fix all, or investigate each?
5. **Backup**: Before any corrections
6. **Correct**: Using template from JHAL_PATIS_CORRECTION_FINAL.sql
7. **Verify**: Run report again to confirm

---

## Related Issues

This is related to earlier findings:
- **SALES_STOCK_MISMATCH_ANALYSIS.md** - Why sales stock mismatches occur
- **STOCK_CALCULATION_COMPARISON.md** - Why stock_history is more reliable
- **FIX_GUIDES.md** - Issue #4: Float Precision (now fixed in views)

All point to same root cause: **Pos_model.update_items_quantity() uses wrong logic**

---

## Support

If you need help:
1. Check STOCK_MISMATCH_FINDINGS.md for detailed analysis
2. Use JHAL_PATIS_CORRECTION_FINAL.sql as template for corrections
3. Test corrections on non-critical items first
4. Keep backups of all states

---

**Report Complete** ✓

65 items with discrepancies identified. Severity breakdown: 3 CRITICAL, 9 HIGH, 14 MEDIUM, 36 LOW.

Ready to correct? Start with investigation of the 3 CRITICAL items.

