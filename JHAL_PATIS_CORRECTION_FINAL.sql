-- ============================================================================
-- JHAL PATIS (IT0135) STOCK CORRECTION SQL - CORRECTED
-- ============================================================================
-- Item: Jhal patis Regular (IT0135)
-- Current Stock: 3,503.00 units (INCORRECT)
-- Correct Stock: 1,609.73 units
-- Difference: -1,893.27 units
-- Root Cause: db_stockentry quantities are 2.176x too high
-- Date: August 2026
-- Column Fix: Changed 'created_date' to 'entry_date' (correct column name)
-- ============================================================================

START TRANSACTION;

-- ============================================================================
-- VERIFY CURRENT STATE (BEFORE correction)
-- ============================================================================

SELECT '===== BEFORE CORRECTION =====' as status;

SELECT id, item_id, qty, note, entry_date, status 
FROM db_stockentry 
WHERE item_id = 135 
AND note LIKE '%Production Output%'
AND status = 1
ORDER BY entry_date DESC;

-- Current db_items stock
SELECT stock FROM db_items WHERE id = 135;

-- Total from db_stockentry
SELECT CONCAT('Total from db_stockentry: ', SUM(qty)) as total_before
FROM db_stockentry 
WHERE item_id = 135 
AND status = 1;

-- ============================================================================
-- APPLY CORRECTIONS
-- ============================================================================

SELECT '===== APPLYING CORRECTIONS =====' as status;

-- Correction 1: PROD-20260809120944: 1088 → 499.97
UPDATE db_stockentry 
SET qty = 499.97 
WHERE id = 1598 
AND item_id = 135;

SELECT 'Updated id 1598: 1088 → 499.97' as correction_1;

-- Correction 2: PROD-20260810095002: 1175 → 539.92
UPDATE db_stockentry 
SET qty = 539.92 
WHERE id = 1415 
AND item_id = 135;

SELECT 'Updated id 1415: 1175 → 539.92' as correction_2;

-- Correction 3: PROD-20260811182606: 1240 → 569.84
UPDATE db_stockentry 
SET qty = 569.84 
WHERE id = 1125 
AND item_id = 135;

SELECT 'Updated id 1125: 1240 → 569.84' as correction_3;

-- Update db_items stock to corrected total
UPDATE db_items 
SET stock = 1609.73 
WHERE id = 135;

SELECT 'Updated db_items.stock: 3503.00 → 1609.73' as correction_4;

-- ============================================================================
-- VERIFY CORRECTIONS (AFTER)
-- ============================================================================

SELECT '===== AFTER CORRECTION =====' as status;

SELECT id, item_id, qty, note, entry_date, status 
FROM db_stockentry 
WHERE item_id = 135 
AND note LIKE '%Production Output%'
AND status = 1
ORDER BY entry_date DESC;

-- New db_items stock
SELECT stock FROM db_items WHERE id = 135;

-- New total from db_stockentry
SELECT CONCAT('Total from db_stockentry: ', SUM(qty)) as total_after
FROM db_stockentry 
WHERE item_id = 135 
AND status = 1;

-- ============================================================================
-- VERIFICATION: DO ALL SOURCES MATCH?
-- ============================================================================

SELECT '===== VERIFICATION =====' as status;

SELECT 
  'db_stockentry total' as source,
  SUM(qty) as quantity
FROM db_stockentry 
WHERE item_id = 135 
AND status = 1

UNION ALL

SELECT 
  'db_items.stock' as source,
  stock as quantity
FROM db_items 
WHERE id = 135;

-- Expected: Both should be 1609.73

-- ============================================================================
-- COMMIT CHANGES
-- ============================================================================

COMMIT;

SELECT '===== CHANGES COMMITTED =====' as status;

-- ============================================================================
-- FINAL VERIFICATION (Run after COMMIT)
-- ============================================================================

SELECT '===== FINAL CHECK =====' as status;

SELECT 'Corrected db_stockentry records:' as check_1;
SELECT id, qty, note 
FROM db_stockentry 
WHERE id IN (1598, 1415, 1125)
ORDER BY id;

SELECT 'Corrected db_items stock:' as check_2;
SELECT id, item_code, item_name, stock 
FROM db_items 
WHERE id = 135;

-- ============================================================================
-- SUMMARY
-- ============================================================================

SELECT 
  '✓ SUMMARY OF CHANGES' as summary,
  'db_stockentry id 1598: 1088 → 499.97 units' as change_1
UNION ALL SELECT '', 'db_stockentry id 1415: 1175 → 539.92 units'
UNION ALL SELECT '', 'db_stockentry id 1125: 1240 → 569.84 units'
UNION ALL SELECT '', 'db_items.stock: 3503.00 → 1609.73 units'
UNION ALL SELECT '', 'Total reduction: -1,893.27 units';

-- ============================================================================
-- END OF CORRECTION SCRIPT
-- ============================================================================
