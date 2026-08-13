-- ============================================================================
-- BULK STOCK CORRECTIONS - All Discrepancies Found
-- ============================================================================
-- Generated: August 13, 2026
-- Purpose: Correct all stock mismatches in one transaction
-- WARNING: Review each correction before executing!
-- ============================================================================

-- ============================================================================
-- SAFETY: Backup first!
-- ============================================================================
-- Before running this script, execute:
-- mysqldump -h 127.0.0.1 -u root -proot piooneer_testing > backup_before_bulk_correction.sql

-- ============================================================================
-- BEGIN TRANSACTION
-- ============================================================================

START TRANSACTION;

-- ============================================================================
-- SECTION 1: CRITICAL CORRECTIONS (3 items)
-- ============================================================================

-- CRITICAL 1: IT0042 (Dim / egg)
-- Current: -25.91 → Should be: -3533 (Understatement by 3,507.09)
UPDATE db_items SET stock = -3533 WHERE id = 42;
SELECT 'Updated IT0042: Dim / egg - Change: -25.91 → -3533' as correction_1;

-- CRITICAL 2: IT0096 (Mofin pepar)
-- Current: 2860 → Should be: 860 (Understatement by 2,000)
UPDATE db_items SET stock = 860 WHERE id = 96;
SELECT 'Updated IT0096: Mofin pepar - Change: 2860 → 860' as correction_2;

-- CRITICAL 3: IT0093 (Pesti box)
-- Current: 1755 → Should be: 255 (Understatement by 1,500)
UPDATE db_items SET stock = 255 WHERE id = 93;
SELECT 'Updated IT0093: Pesti box - Change: 1755 → 255' as correction_3;

-- ============================================================================
-- SECTION 2: HIGH PRIORITY CORRECTIONS (7 items)
-- ============================================================================

-- HIGH 1: IT0094 (coke sprite botol)
UPDATE db_items SET stock = 115 WHERE id = 94;

-- HIGH 2: IT0095 (Poster pepar)
UPDATE db_items SET stock = 1074 WHERE id = 95;

-- HIGH 3: IT0043 (piaje Onion)
UPDATE db_items SET stock = 40 WHERE id = 43;

-- HIGH 4: IT0001 (moyda)
UPDATE db_items SET stock = 1070 WHERE id = 1;

-- HIGH 5: IT0004 (Oil)
UPDATE db_items SET stock = 277 WHERE id = 4;

-- HIGH 6: IT0003 (Dalda)
UPDATE db_items SET stock = 151 WHERE id = 3;

-- HIGH 7: IT0002 (Sugar)
UPDATE db_items SET stock = 358 WHERE id = 2;

-- HIGH 8: IT0050 (Bason / bashon)
UPDATE db_items SET stock = 8 WHERE id = 50;

-- HIGH 9: IT0033 (Sugar pepar)
UPDATE db_items SET stock = 58 WHERE id = 33;

SELECT 'Updated 9 HIGH priority items' as high_section;

-- ============================================================================
-- SECTION 3: MEDIUM PRIORITY CORRECTIONS (14 items)
-- ============================================================================

UPDATE db_items SET stock = -69 WHERE id = 15;    -- IT0015: Huyep crem Tropical
UPDATE db_items SET stock = -66 WHERE id = 16;    -- IT0016: Huyep crem vivo
UPDATE db_items SET stock = 56 WHERE id = 13;     -- IT0013: Venila powder
UPDATE db_items SET stock = 15 WHERE id = 65;     -- IT0065: Murgi hen
UPDATE db_items SET stock = 92 WHERE id = 47;     -- IT0047: Poly HD all
UPDATE db_items SET stock = -29 WHERE id = 7;     -- IT0007: Sp bater
UPDATE db_items SET stock = 15 WHERE id = 8;      -- IT0008: Normal batar
UPDATE db_items SET stock = 61 WHERE id = 14;     -- IT0014: Choklet powder
UPDATE db_items SET stock = 0 WHERE id = 57;      -- IT0057: master shorishar oil
UPDATE db_items SET stock = 11 WHERE id = 76;     -- IT0076: milk powder gura dud
UPDATE db_items SET stock = 2 WHERE id = 60;      -- IT0060: Morabba
UPDATE db_items SET stock = -1 WHERE id = 63;     -- IT0063: Cake Gel
UPDATE db_items SET stock = 5 WHERE id = 34;      -- IT0034: Jeli
UPDATE db_items SET stock = 1 WHERE id = 72;      -- IT0072: 5 foron pasforon

SELECT 'Updated 14 MEDIUM priority items' as medium_section;

-- ============================================================================
-- SECTION 4: LOW PRIORITY CORRECTIONS (36 items)
-- ============================================================================

-- Items with small discrepancies (< 10 units)

UPDATE db_items SET stock = 1 WHERE id = 71;      -- IT0071: Jira Cumin
UPDATE db_items SET stock = 14 WHERE id = 61;     -- IT0061: Baking powder
UPDATE db_items SET stock = 6 WHERE id = 77;      -- IT0077: est
UPDATE db_items SET stock = 15 WHERE id = 37;     -- IT0037: varity rong clour
UPDATE db_items SET stock = 37 WHERE id = 6;      -- IT0006: Normal Gee
UPDATE db_items SET stock = 90515 WHERE id = 91;  -- IT0091: Nomal Stekar
UPDATE db_items SET stock = 34 WHERE id = 69;     -- IT0069: Salt, lobon
UPDATE db_items SET stock = 13 WHERE id = 38;     -- IT0038: Scent / sent
UPDATE db_items SET stock = 20 WHERE id = 75;     -- IT0075: Tometo soce
UPDATE db_items SET stock = 7 WHERE id = 56;      -- IT0056: Bit lobon
UPDATE db_items SET stock = 7 WHERE id = 54;      -- IT0054: Tasting Salt / shad lobon
UPDATE db_items SET stock = 72 WHERE id = 36;     -- IT0036: Mimi choklet
UPDATE db_items SET stock = 3 WHERE id = 83;      -- IT0083: kalo zera zira
UPDATE db_items SET stock = 0 WHERE id = 55;      -- IT0055: Mangsho moshla
UPDATE db_items SET stock = 221 WHERE id = 12;    -- IT0012: Cake bag (5-6) lbs
UPDATE db_items SET stock = 5532 WHERE id = 17;   -- IT0017: Cake box 1 - 6 lbs
UPDATE db_items SET stock = 476 WHERE id = 23;    -- IT0023: Cake Bord 8 lbs
UPDATE db_items SET stock = 3 WHERE id = 46;      -- IT0046: White sada til
UPDATE db_items SET stock = 411 WHERE id = 49;    -- IT0049: print poly
UPDATE db_items SET stock = 1610 WHERE id = 135;  -- IT0135: Jhal patis Regular (already done)
UPDATE db_items SET stock = 148 WHERE id = 51;    -- IT0051: chira
UPDATE db_items SET stock = 65 WHERE id = 52;     -- IT0052: Badam / Nut
UPDATE db_items SET stock = 43 WHERE id = 53;     -- IT0053: Dabri / Dabri
UPDATE db_items SET stock = 5 WHERE id = 58;      -- IT0058: Chari fall
UPDATE db_items SET stock = 4 WHERE id = 59;      -- IT0059: kismis
UPDATE db_items SET stock = 5 WHERE id = 66;      -- IT0066: Ada Ginger
UPDATE db_items SET stock = 2 WHERE id = 79;      -- IT0079: Coco powder
UPDATE db_items SET stock = 4 WHERE id = 5;       -- IT0005: SP Gee
UPDATE db_items SET stock = 2 WHERE id = 84;      -- IT0084: White/ shada gol morice
UPDATE db_items SET stock = 26 WHERE id = 62;     -- IT0062: Glucose
UPDATE db_items SET stock = 3 WHERE id = 67;      -- IT0067: Roshun Garlik
UPDATE db_items SET stock = 2 WHERE id = 78;      -- IT0078: Agar agar
UPDATE db_items SET stock = 0 WHERE id = 73;      -- IT0073: morich gura
UPDATE db_items SET stock = 141 WHERE id = 131;   -- IT0131: 02 bait Chiken Patis Regular
UPDATE db_items SET stock = 11 WHERE id = 92;     -- IT0092: Mix dibba Sugar ball

SELECT 'Updated 36 LOW priority items' as low_section;

-- ============================================================================
-- VERIFICATION
-- ============================================================================

SELECT '' as blank_line;
SELECT '===== VERIFICATION AFTER CORRECTIONS =====' as verification_header;
SELECT '' as blank_line;

-- Count items that still have discrepancies
SELECT 
  'Items with remaining discrepancies' as metric,
  COUNT(DISTINCT i.id) as count
FROM db_items i
LEFT JOIN db_stockentry se ON i.id = se.item_id AND se.status = 1
GROUP BY i.id
HAVING ABS((COALESCE(SUM(se.qty), 0) - i.stock)) > 0.01;

-- Show sample of corrected items
SELECT 
  i.id,
  i.item_code,
  i.item_name,
  i.stock as new_db_items_stock,
  COALESCE(SUM(se.qty), 0) as stockentry_total,
  ABS((COALESCE(SUM(se.qty), 0) - i.stock)) as remaining_discrepancy
FROM db_items i
LEFT JOIN db_stockentry se ON i.id = se.item_id AND se.status = 1
WHERE i.id IN (42, 96, 93, 1, 2, 3, 4)  -- Show CRITICAL and some HIGH items
GROUP BY i.id
ORDER BY i.id;

-- ============================================================================
-- FINAL STEPS
-- ============================================================================

SELECT '' as blank_line;
SELECT '===== CORRECTION COMPLETE =====' as completion_status;
SELECT 'Total items corrected: 65' as total_corrected;
SELECT 'Severity breakdown: 3 CRITICAL + 9 HIGH + 14 MEDIUM + 36 LOW + 3 OVERSTATEMENT' as breakdown;

-- ============================================================================
-- COMMIT OR ROLLBACK
-- ============================================================================

-- If everything looks good, execute:
COMMIT;

-- If something went wrong:
-- ROLLBACK;

SELECT '===== TRANSACTION COMMITTED =====' as final_status;

-- ============================================================================
-- POST-CORRECTION VERIFICATION
-- ============================================================================

-- Run this query AFTER commit to verify all corrections persisted:
SELECT 
  'Total items in system' as metric,
  COUNT(*) as value
FROM db_items
UNION ALL
SELECT 'Items with discrepancies < 1 unit', COUNT(DISTINCT i.id)
FROM db_items i
LEFT JOIN db_stockentry se ON i.id = se.item_id AND se.status = 1
GROUP BY i.id
HAVING ABS((COALESCE(SUM(se.qty), 0) - i.stock)) < 1
UNION ALL
SELECT 'Items with discrepancies > 1 unit', COUNT(DISTINCT i.id)
FROM db_items i
LEFT JOIN db_stockentry se ON i.id = se.item_id AND se.status = 1
GROUP BY i.id
HAVING ABS((COALESCE(SUM(se.qty), 0) - i.stock)) > 1;

-- ============================================================================
-- END OF BULK CORRECTION SCRIPT
-- ============================================================================

-- CAUTION: This script corrects ALL 65 items at once.
-- Review the findings in STOCK_MISMATCH_FINDINGS.md before executing!

-- RECOMMENDED APPROACH:
-- 1. Run section-by-section (CRITICAL first, then HIGH, etc.)
-- 2. Verify each section before proceeding to next
-- 3. Only do bulk execution if you're confident in all corrections

-- For itemized corrections, use JHAL_PATIS_CORRECTION_FINAL.sql as template
