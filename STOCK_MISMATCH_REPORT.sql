-- ============================================================================
-- STOCK MISMATCH DIAGNOSTIC - WORKING VERSION
-- ============================================================================
-- Purpose: Find all items with stock discrepancies
-- ============================================================================

-- ============================================================================
-- MAIN REPORT: Items with Stock Mismatches
-- ============================================================================

SELECT '===== STOCK MISMATCH DIAGNOSTIC REPORT =====' as header;
SELECT CONCAT('Report Generated: ', DATE_FORMAT(NOW(), '%Y-%m-%d %H:%i:%s')) as timestamp;
SELECT '' as blank_line;

SELECT
  i.id,
  i.item_code,
  i.item_name,
  i.stock as db_items_stock,
  COALESCE(SUM(se.qty), 0) as stockentry_total,
  (COALESCE(SUM(se.qty), 0) - i.stock) as discrepancy,
  ABS((COALESCE(SUM(se.qty), 0) - i.stock)) as abs_discrepancy,
  CASE 
    WHEN (COALESCE(SUM(se.qty), 0) - i.stock) > 0.01 THEN 'OVERSTATEMENT'
    WHEN (COALESCE(SUM(se.qty), 0) - i.stock) < -0.01 THEN 'UNDERSTATEMENT'
    ELSE 'MATCH'
  END as mismatch_type,
  u.unit_name,
  CASE 
    WHEN ABS((COALESCE(SUM(se.qty), 0) - i.stock)) >= 1000 THEN 'CRITICAL'
    WHEN ABS((COALESCE(SUM(se.qty), 0) - i.stock)) >= 100 THEN 'HIGH'
    WHEN ABS((COALESCE(SUM(se.qty), 0) - i.stock)) >= 10 THEN 'MEDIUM'
    WHEN ABS((COALESCE(SUM(se.qty), 0) - i.stock)) > 0.01 THEN 'LOW'
    ELSE 'OK'
  END as severity
FROM db_items i
LEFT JOIN db_stockentry se ON i.id = se.item_id AND se.status = 1
LEFT JOIN db_units u ON i.unit_id = u.id
GROUP BY i.id
HAVING ABS((COALESCE(SUM(se.qty), 0) - i.stock)) > 0.01
ORDER BY abs_discrepancy DESC;

-- ============================================================================
-- SUMMARY BY SEVERITY
-- ============================================================================

SELECT '' as blank_line;
SELECT '===== SUMMARY BY SEVERITY LEVEL =====' as summary;
SELECT '' as blank_line;

SELECT 'CRITICAL Issues (≥1000 units)' as severity_level;
SELECT COUNT(*) as item_count FROM (
  SELECT i.id
  FROM db_items i
  LEFT JOIN db_stockentry se ON i.id = se.item_id AND se.status = 1
  GROUP BY i.id
  HAVING ABS((COALESCE(SUM(se.qty), 0) - i.stock)) >= 1000
) t;

SELECT 'HIGH Issues (100-999 units)' as severity_level;
SELECT COUNT(*) as item_count FROM (
  SELECT i.id
  FROM db_items i
  LEFT JOIN db_stockentry se ON i.id = se.item_id AND se.status = 1
  GROUP BY i.id
  HAVING ABS((COALESCE(SUM(se.qty), 0) - i.stock)) >= 100
  AND ABS((COALESCE(SUM(se.qty), 0) - i.stock)) < 1000
) t;

SELECT 'MEDIUM Issues (10-99 units)' as severity_level;
SELECT COUNT(*) as item_count FROM (
  SELECT i.id
  FROM db_items i
  LEFT JOIN db_stockentry se ON i.id = se.item_id AND se.status = 1
  GROUP BY i.id
  HAVING ABS((COALESCE(SUM(se.qty), 0) - i.stock)) >= 10
  AND ABS((COALESCE(SUM(se.qty), 0) - i.stock)) < 100
) t;

SELECT 'LOW Issues (<10 units)' as severity_level;
SELECT COUNT(*) as item_count FROM (
  SELECT i.id
  FROM db_items i
  LEFT JOIN db_stockentry se ON i.id = se.item_id AND se.status = 1
  GROUP BY i.id
  HAVING ABS((COALESCE(SUM(se.qty), 0) - i.stock)) > 0.01
  AND ABS((COALESCE(SUM(se.qty), 0) - i.stock)) < 10
) t;

-- ============================================================================
-- CRITICAL ISSUES ONLY
-- ============================================================================

SELECT '' as blank_line;
SELECT '===== CRITICAL ISSUES (≥1000 units difference) =====' as critical_section;
SELECT '' as blank_line;

SELECT
  i.id,
  i.item_code,
  i.item_name,
  i.stock as db_items_stock,
  COALESCE(SUM(se.qty), 0) as stockentry_total,
  (COALESCE(SUM(se.qty), 0) - i.stock) as discrepancy,
  ABS((COALESCE(SUM(se.qty), 0) - i.stock)) as abs_discrepancy
FROM db_items i
LEFT JOIN db_stockentry se ON i.id = se.item_id AND se.status = 1
GROUP BY i.id
HAVING ABS((COALESCE(SUM(se.qty), 0) - i.stock)) >= 1000
ORDER BY abs_discrepancy DESC;

-- ============================================================================
-- HIGH ISSUES ONLY
-- ============================================================================

SELECT '' as blank_line;
SELECT '===== HIGH PRIORITY ISSUES (100-999 units) =====' as high_section;
SELECT '' as blank_line;

SELECT
  i.id,
  i.item_code,
  i.item_name,
  i.stock as db_items_stock,
  COALESCE(SUM(se.qty), 0) as stockentry_total,
  (COALESCE(SUM(se.qty), 0) - i.stock) as discrepancy,
  ABS((COALESCE(SUM(se.qty), 0) - i.stock)) as abs_discrepancy
FROM db_items i
LEFT JOIN db_stockentry se ON i.id = se.item_id AND se.status = 1
GROUP BY i.id
HAVING ABS((COALESCE(SUM(se.qty), 0) - i.stock)) >= 100
AND ABS((COALESCE(SUM(se.qty), 0) - i.stock)) < 1000
ORDER BY abs_discrepancy DESC;

-- ============================================================================
-- PRODUCTION-RELATED ITEMS WITH MISMATCHES
-- ============================================================================

SELECT '' as blank_line;
SELECT '===== ITEMS WITH PRODUCTION OUTPUT DISCREPANCIES =====' as production_section;
SELECT '' as blank_line;

SELECT
  i.id,
  i.item_code,
  i.item_name,
  i.stock as db_items_stock,
  COALESCE(SUM(CASE WHEN se.note LIKE '%Production Output%' THEN se.qty ELSE 0 END), 0) as production_output_qty,
  COALESCE(SUM(se.qty), 0) as total_stockentry,
  (COALESCE(SUM(se.qty), 0) - i.stock) as discrepancy,
  ABS((COALESCE(SUM(se.qty), 0) - i.stock)) as abs_discrepancy
FROM db_items i
LEFT JOIN db_stockentry se ON i.id = se.item_id AND se.status = 1
GROUP BY i.id
HAVING 
  COALESCE(SUM(CASE WHEN se.note LIKE '%Production Output%' THEN se.qty ELSE 0 END), 0) > 0
  AND ABS((COALESCE(SUM(se.qty), 0) - i.stock)) > 0.01
ORDER BY abs_discrepancy DESC;

-- ============================================================================
-- CORRECTION CANDIDATES (Items most likely affected by data entry errors)
-- ============================================================================

SELECT '' as blank_line;
SELECT '===== TOP 10 CORRECTION CANDIDATES =====' as candidates_section;
SELECT '(Items with largest discrepancies - review these first)' as note;
SELECT '' as blank_line;

SELECT
  i.id,
  i.item_code,
  i.item_name,
  i.stock as current_stock,
  COALESCE(SUM(se.qty), 0) as correct_stock,
  (COALESCE(SUM(se.qty), 0) - i.stock) as correction_needed,
  CONCAT('UPDATE db_items SET stock = ', ROUND(COALESCE(SUM(se.qty), 0), 2), ' WHERE id = ', i.id, ';') as sql_fix
FROM db_items i
LEFT JOIN db_stockentry se ON i.id = se.item_id AND se.status = 1
GROUP BY i.id
HAVING ABS((COALESCE(SUM(se.qty), 0) - i.stock)) > 0.01
ORDER BY ABS((COALESCE(SUM(se.qty), 0) - i.stock)) DESC
LIMIT 10;

-- ============================================================================
-- OVERALL STATISTICS
-- ============================================================================

SELECT '' as blank_line;
SELECT '===== OVERALL STATISTICS =====' as stats_section;
SELECT '' as blank_line;

SELECT 'Total Items in System' as metric, COUNT(*) as value FROM db_items
UNION ALL
SELECT 'Items with Discrepancies', (
  SELECT COUNT(DISTINCT i.id)
  FROM db_items i
  LEFT JOIN db_stockentry se ON i.id = se.item_id AND se.status = 1
  GROUP BY i.id
  HAVING ABS((COALESCE(SUM(se.qty), 0) - i.stock)) > 0.01
)
UNION ALL
SELECT 'Items with No Issues', (
  SELECT COUNT(DISTINCT i.id)
  FROM db_items i
  LEFT JOIN db_stockentry se ON i.id = se.item_id AND se.status = 1
  GROUP BY i.id
  HAVING ABS((COALESCE(SUM(se.qty), 0) - i.stock)) <= 0.01
)
UNION ALL
SELECT 'Total Discrepancy Amount', (
  SELECT ROUND(SUM(ABS((COALESCE(SUM(se.qty), 0) - i.stock))), 2)
  FROM db_items i
  LEFT JOIN db_stockentry se ON i.id = se.item_id AND se.status = 1
  GROUP BY i.id
  HAVING ABS((COALESCE(SUM(se.qty), 0) - i.stock)) > 0.01
);

-- ============================================================================
-- END OF REPORT
-- ============================================================================

SELECT '' as blank_line;
SELECT '===== REPORT COMPLETE =====' as footer;
SELECT 'Use the SQL fixes above to correct individual items' as instruction;
