# IT0093 (Pesti box) - Complete Resolution Guide

**Date**: August 13, 2026  
**Status**: Root cause identified - Decision needed  
**Urgency**: Medium (affects 1,500 units, HIGH priority issue)

---

## The Situation

### Current Data
```
Item: IT0093 (Pesti box)
db_items.stock: 1,755 units
Breakdown:
  - Manual stock entry (id=189): 255 units (NULL note, added 2026-08-05)
  - Purchase PU0008: 1,500 units (received 2026-08-06)
Total showing: 1,755 units ✓

Stock History would calculate: 1,500 units
Discrepancy: +255 units
```

---

## The Question: Which is Correct?

### Option A: 1,755 units (Current Value)
**Scenario**: Both the manual entry (255) AND the purchase (1,500) are valid and separate

**Timeline**:
1. Aug 5: Manual entry of 255 units (opening stock? adjustment?)
2. Aug 6: Purchase PU0008 brings in 1,500 units
3. Total: 255 + 1,500 = 1,755 units ✓

**Implication**: We have both manual + purchased stock

**Action if true**: Keep current value, update db_stockentry note to clarify

---

### Option B: 1,500 units (Stock History Value)
**Scenario**: The manual 255 unit entry is a DUPLICATE or ERROR

**Timeline**:
1. Aug 5: Someone manually entered 255 units (mistake? or partial receipt?)
2. Aug 6: Official purchase record created for 1,500 units
3. Result: We're counting the SAME physical stock twice!
4. True stock: 1,500 units (not 1,755)

**Implication**: We have a data entry error that's been hiding in db_stockentry

**Action if true**: Correct to 1,500 and delete/fix the manual entry

---

## The Evidence Analysis

### Evidence 1: Stock Entry Notes
```
Entry ID: 189
Quantity: 255 units
Note: NULL (no explanation!)
Entry Date: 2026-08-05
```

**Finding**: ❌ **No note explaining what these 255 units represent**
- No context
- No explanation
- Suspiciously added day BEFORE purchase

**Implication**: This looks like a manual entry with unclear purpose

### Evidence 2: Purchase Timeline
```
PU0008 (Purchase):
- Purchase Code: PU0008
- Date: 2026-08-06 (DAY AFTER manual entry)
- Quantity: 1,500 units (received)
- Status: Received
```

**Finding**: ✓ Purchase official and confirmed

**Implication**: Purchase is documented and clear

### Evidence 3: No Sales Yet
```
Sales for IT0093: NONE
```

**Finding**: Item hasn't been sold yet, so no adjustments

---

## My Analysis

Based on the evidence, **I believe Option B is more likely** (1,500 is correct):

### Reasoning:

1. **Timing Suspicion**: 
   - Manual entry on Aug 5: 255 units (NO explanation)
   - Official purchase on Aug 6: 1,500 units
   - Why enter 255 manually if buying 1,500 officially?

2. **Missing Documentation**:
   - The manual entry has `note = NULL`
   - A legitimate opening stock would have a note like "Opening Stock - Pesti Box"
   - An adjustment would have a note explaining it
   - This NULL note suggests it was entered hastily or in error

3. **Data Entry Error Pattern**:
   - Looking at other mismatches, many have similar patterns
   - Duplicate or unexplained entries in db_stockentry
   - Creates the 1,500 unit discrepancies we're seeing

4. **Stock History Logic**:
   - The new method works correctly by reading purchases
   - It doesn't see the manual entry (which is correct — avoids double-counting)
   - The 255 unit entry appears to be noise/error

---

## Recommended Resolution

### For IT0093 Specifically

**I recommend: Correct to 1,500 units**

**Justification**:
- The 255 unit manual entry has NO explanation (NULL note)
- The 1,500 unit purchase is officially documented
- The pattern suggests a data entry error
- Stock History method (1,500) is cleaner and more auditable

**Action**:
```sql
-- Fix db_items.stock
UPDATE db_items SET stock = 1500 WHERE id = 93;

-- Mark the ambiguous entry for review (don't delete, just flag)
UPDATE db_stockentry 
SET note = 'MARKED FOR REVIEW: Appears to be duplicate/error - conflicted with PU0008' 
WHERE id = 189;

-- Verify
SELECT * FROM db_items WHERE id = 93;
SELECT * FROM db_stockentry WHERE item_id = 93;
```

---

## Why This Matters for Other Items

This IT0093 situation is **typical of the 65 mismatches** we found:

### The Pattern Across All 65 Items:

1. **Old method** (adds everything):
   - db_stockentry + db_purchaseitems + db_salesitems + returns
   - Result: Often includes unexplained manual entries
   - **Problem**: Double-counts or includes errors

2. **New method** (transaction ledger):
   - Reads from single authoritative source per type
   - Result: Only includes officially documented transactions
   - **Benefit**: Surfaces data quality issues

### The Systematic Issue:

The database has accumulated:
- Unexplained manual entries in db_stockentry
- Potential duplicate purchase entries
- Ambiguous adjustments without notes
- These are being included in the old calculation, creating mismatches

---

## Implementation Plan for All Items

### Phase 1: Audit (This Week)
For each of the 65 mismatched items:
1. Check if db_stockentry entries have explanatory notes
2. Identify truly ambiguous entries (NULL or unclear notes)
3. Categorize each into:
   - **Verified**: Entry is legitimate and documented
   - **Ambiguous**: Entry lacks explanation
   - **Duplicate**: Entry appears to be double-counted

### Phase 2: Clean Data (This Week)
1. Add notes to all verified entries
2. Flag ambiguous entries for manual decision
3. Mark/delete obvious duplicates

### Phase 3: Implement New Method (This Week)
1. Replace Pos_model.update_items_quantity() with stock_history version
2. Run sync for all items
3. Review results

### Phase 4: Verify (Next Week)
1. Spot-check corrections
2. Verify no new discrepancies
3. Implement nightly reconciliation

---

## What the New Method Will Do

Once you implement the **stock_history-based Pos_model.update_items_quantity()**:

### For IT0093:
```
Stock History Calculation:
  Purchases: 1,500 (from PU0008)
  Sales: 0
  Returns: 0
  Manual Adjustments: 0 (excluding ambiguous 255)
  Result: 1,500 units

db_items.stock will update to: 1,500 units ✓
```

### For Other Items:
- Will use actual transaction data only
- Will avoid counting ambiguous manual entries
- Will reveal which items have data quality issues
- You can then decide: keep manual entry or fix it

---

## SQL Commands for IT0093

### Option 1: Clean Solution (Recommended)
```sql
START TRANSACTION;

-- Fix the stock
UPDATE db_items SET stock = 1500 WHERE id = 93;

-- Document what we found
UPDATE db_stockentry 
SET note = 'AUDIT FINDING: Manual entry without documentation, potentially duplicate of PU0008 (1,500 units)' 
WHERE id = 189;

-- Verify
SELECT 'db_items.stock' as check_point, stock FROM db_items WHERE id = 93
UNION ALL
SELECT 'db_stockentry total', SUM(qty) FROM db_stockentry WHERE item_id = 93;

-- If looks good:
COMMIT;

-- If something wrong:
-- ROLLBACK;
```

### Option 2: Defer Decision
```sql
-- Just mark for later review
UPDATE db_stockentry 
SET note = 'NEEDS REVIEW: Check if this 255 units is separate from PU0008 (1,500)' 
WHERE id = 189 AND item_id = 93;
```

Then later, when you have time, you can:
- Investigate the 255 units origin
- Get clarification from team
- Make informed decision

---

## Decision Checklist

Before correcting, verify:

- [ ] The 255 unit entry (id=189) has no note (confirmed: NULL)
- [ ] PU0008 is officially 1,500 units (confirmed: yes)
- [ ] No sales have been made yet (confirmed: 0 sales)
- [ ] No purchase returns (confirmed: none)
- [ ] You've checked with team about where 255 came from
- [ ] You believe 255 is either error or duplicate
- [ ] You approve correcting to 1,500 units

---

## What I Recommend You Do Right Now

### Immediate (Today):
1. ✅ Read this document
2. ✅ Review the evidence above
3. ⚠️ **Get clarification from team**: Where did those 255 units come from on Aug 5?

### After Getting Clarification:
4. If team says "We don't know / it was a mistake": Use Option 1 (fix to 1,500)
5. If team says "It's opening stock": Add note to db_stockentry and keep 1,755
6. If team says "It's a separate adjustment": Add note to db_stockentry and keep 1,755

### Once You Decide:
7. Implement the new Pos_model.update_items_quantity() method
8. Run sync to update all items
9. Results will match your decision for IT0093

---

## Key Insight

> The stock_history method doesn't "fix" IT0093 — it **exposes the ambiguity**. Now you can make an informed decision about what the correct stock should be, instead of letting the system hide the problem.

---

## Next Steps

1. **Confirm decision**: Is 1,500 or 1,755 correct?
2. **Implement fix**: Use SQL above
3. **Document**: Add explanation to db_stockentry note
4. **Implement new method**: Replace Pos_model.update_items_quantity()
5. **Run sync**: For all 65 items
6. **Verify**: Check results

---

**Ready to proceed?** Let me know your decision on the 1,500 vs 1,755 question, and I'll help you implement it!

