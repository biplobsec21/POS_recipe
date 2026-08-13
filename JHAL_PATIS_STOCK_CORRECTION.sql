-- ============================================================================
-- JHAL PATIS (IT0135) STOCK CORRECTION SQL
-- ============================================================================
-- Item: Jhal patis Regular (IT0135)
-- Current Stock: 3,503.00 units (INCORRECT)
-- Correct Stock: 1,609.73 units
-- Difference: -1,893.27 units
-- Root Cause: db_stockentry quantities are 2.176x too high
-- Date: August 2026
-- ============================================================================

-- STEP 1: BACKUP (Run this FIRST to create a restore point)
-- ============================================================================
-- Run this in a separate terminal to backup before making changes:
-- mysqldump -h 127.0.0.1 -u root -proot piooneer_testing > backup_$(date +%Y%m%d_%H%M%S).sql

-- ============================================================================
-- STEP 2: BEGIN TRANSACTION (Safety check - can ROLLBACK if needed)
-- ============================================================================
START TRANSACTION;

-- ============================================================================
-- STEP 3: VERIFY CURRENT STATE (Check BEFORE correction)
-- ============================================================================

-- Check current db_stockentry records for IT0135
SELECT 'BEFORE: db_stockentry records' as checkpoint;
SELECT id, item_id, qty, note, entry_date, status 
FROM db_stockentry 
WHERE item_id = 135 
AND note LIKE '%Production Output%'
AND status = 1
ORDER BY entry_date DESC;

-- Check current db_items stock
SELECT 'BEFORE: db_items.stock' as checkpoint;
SELECT id, item_code, item_name, stock, unit_id 
FROM db_items 
WHERE id = 135;

-- Calculate total from db_stockentry
SELECT 'BEFORE: Total from db_stockentry' as checkpoint;
SELECT SUM(qty) as total_stock_entry 
FROM db_stockentry 
WHERE item_id = 135 
AND status = 1;

-- ============================================================================
-- STEP 4: APPLY CORRECTIONS
-- ============================================================================

-- Update PROD-20260809120944: 1088 → 499.97
-- This production batch yielded 499.97 units, not 1088
UPDATE db_stockentry 
SET qty = 499.97 
WHERE id = 1598 
AND item_id = 135 
AND qty = 1088;

-- Verify first update worked
SELECT 'After Update 1' as checkpoint;
SELECT id, qty, note FROM db_stockentry WHERE id = 1598;

-- ============================================================================

-- Update PROD-20260810095002: 1175 → 539.92
-- This production batch yielded 539.92 units, not 1175
UPDATE db_stockentry 
SET qty = 539.92 
WHERE id = 1415 
AND item_id = 135 
AND qty = 1175;

-- Verify second update worked
SELECT 'After Update 2' as checkpoint;
SELECT id, qty, note FROM db_stockentry WHERE id = 1415;

-- ============================================================================

-- Update PROD-20260811182606: 1240 → 569.84
-- This production batch yielded 569.84 units, not 1240
UPDATE db_stockentry 
SET qty = 569.84 
WHERE id = 1125 
AND item_id = 135 
AND qty = 1240;

-- Verify third update worked
SELECT 'After Update 3' as checkpoint;
SELECT id, qty, note FROM db_stockentry WHERE id = 1125;

-- ============================================================================

-- Update db_items stock to match corrected total
-- New total: 499.97 + 539.92 + 569.84 = 1,609.73
UPDATE db_items 
SET stock = 1609.73 
WHERE id = 135;

-- Verify db_items update worked
SELECT 'After db_items update' as checkpoint;
SELECT id, item_code, item_name, stock 
FROM db_items 
WHERE id = 135;

-- ============================================================================
-- STEP 5: VERIFY CORRECTIONS (Check AFTER correction)
-- ============================================================================

-- Check corrected db_stockentry records
SELECT 'AFTER: db_stockentry records' as checkpoint;
SELECT id, item_id, qty, note, entry_date, status 
FROM db_stockentry 
WHERE item_id = 135 
AND note LIKE '%Production Output%'
AND status = 1
ORDER BY entry_date DESC;

-- Check corrected db_items stock
SELECT 'AFTER: db_items.stock' as checkpoint;
SELECT id, item_code, item_name, stock 
FROM db_items 
WHERE id = 135;

-- Calculate new total from db_stockentry
SELECT 'AFTER: Total from db_stockentry' as checkpoint;
SELECT SUM(qty) as total_stock_entry 
FROM db_stockentry 
WHERE item_id = 135 
AND status = 1;

-- ============================================================================
-- STEP 6: VERIFICATION QUERY - DO THEY MATCH?
-- ============================================================================

SELECT 'VERIFICATION: Do all sources match?' as checkpoint;

SELECT 
  'db_stockentry total' as source,
  SUM(qty) as quantity
FROM db_stockentry 
WHERE item_id = 135 
AND status = 1

UNION ALL

SELECT 
  'db_items.stock',
  stock
FROM db_items 
WHERE id = 135;

-- Expected output:
-- source                | quantity
-- db_stockentry total   | 1609.73
-- db_items.stock        | 1609.73

-- ============================================================================
-- STEP 7: COMMIT OR ROLLBACK
-- ============================================================================

-- If everything looks good above, run this to COMMIT the changes:
COMMIT;

-- If something went wrong, you can ROLLBACK with:
-- ROLLBACK;

-- ============================================================================
-- STEP 8: FINAL VERIFICATION (Run after COMMIT)
-- ============================================================================

-- Run these queries to confirm the fix is persistent:

SELECT 'FINAL CHECK: db_stockentry corrections' as step;
SELECT id, qty, note 
FROM db_stockentry 
WHERE id IN (1598, 1415, 1125)
ORDER BY id;

-- Expected:
-- id    qty      note
-- 1125  569.84   Production Output - Batch: PROD-20260811182606
-- 1415  539.92   Production Output - Batch: PROD-20260810095002
-- 1598  499.97   Production Output - Batch: PROD-20260809120944

SELECT 'FINAL CHECK: db_items stock' as step;
SELECT id, item_code, item_name, stock 
FROM db_items 
WHERE id = 135;

-- Expected:
-- id    item_code  item_name               stock
-- 135   IT0135     Jhal patis Regular      1609.73

-- ============================================================================
-- OPTIONAL: VIEW STOCK HISTORY IMPACT
-- ============================================================================

-- Check how this affects stock_history calculations:
SELECT 'Stock History Check' as step;
SELECT 
  'Total Purchase' as metric,
  COALESCE(SUM(pi.purchase_qty), 0) as value
FROM db_purchaseitems pi 
JOIN db_purchase p ON p.id = pi.purchase_id 
WHERE pi.item_id = 135 AND p.status = 1

UNION ALL

SELECT 'Total Sold', COALESCE(SUM(si.sales_qty), 0)
FROM db_salesitems si 
JOIN db_sales s ON s.id = si.sales_id 
WHERE si.item_id = 135 AND s.status = 1

UNION ALL

SELECT 'Stock Entry (Manual + Production)', SUM(qty)
FROM db_stockentry 
WHERE item_id = 135 AND status = 1

UNION ALL

SELECT 'Purchase Return', COALESCE(SUM(pr.return_qty), 0)
FROM db_purchaseitemsreturn pr 
WHERE pr.item_id = 135

UNION ALL

SELECT 'Sales Return', COALESCE(SUM(sr.return_qty), 0)
FROM db_salesitemsreturn sr 
WHERE sr.item_id = 135;

-- ============================================================================
-- ROLLBACK SCRIPT (If you need to undo the changes)
-- ============================================================================

-- Uncomment and run ONLY if you need to restore original values:

/*

START TRANSACTION;

-- Restore original db_stockentry values
UPDATE db_stockentry SET qty = 1088 WHERE id = 1598;
UPDATE db_stockentry SET qty = 1175 WHERE id = 1415;
UPDATE db_stockentry SET qty = 1240 WHERE id = 1125;

-- Restore original db_items stock
UPDATE db_items SET stock = 3503 WHERE id = 135;

COMMIT;

-- Verify rollback
SELECT 'ROLLBACK VERIFICATION' as step;
SELECT SUM(qty) FROM db_stockentry WHERE item_id = 135;
SELECT stock FROM db_items WHERE id = 135;

*/

-- ============================================================================
-- END OF CORRECTION SCRIPT
-- ============================================================================

-- SUMMARY OF CHANGES:
-- - db_stockentry id 1598: 1088 → 499.97 units
-- - db_stockentry id 1415: 1175 → 539.92 units
-- - db_stockentry id 1125: 1240 → 569.84 units
-- - db_items stock: 3503 → 1609.73 units
-- - Total reduction: -1,893.27 units
--
-- All changes are within a transaction, so if verification fails,
-- you can ROLLBACK to restore original values.
-- ============================================================================
