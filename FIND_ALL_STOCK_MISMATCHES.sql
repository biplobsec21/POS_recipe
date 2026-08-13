-- ============================================================================
-- STOCK MISMATCH DIAGNOSTIC SCRIPT
-- ============================================================================
-- Purpose: Find all items with stock discrepancies between:
--   - db_items.stock (snapshot/cache)
--   - db_stockentry SUM(qty) (source of truth for manual stock entries)
--   - stock_history calculation (complete ledger with all transaction types)
--
-- This will identify items like IT0135 (Jhal patis) that have data entry errors
-- ============================================================================

-- ============================================================================
-- SECTION 1: ITEMS WITH BASIC db_stockentry MISMATCH
-- ============================================================================

SELECT '============================================================' as section;
SELECT 'SECTION 1: db_stockentry vs db_items.stock Mismatch' as title;
SELECT '============================================================' as divider;

SELECT
  i.id,
  i.item_code,
  i.item_name,
  i.stock as db_items_stock,
  COALESCE(SUM(se.qty), 0) as stockentry_total,
  (COALESCE(SUM(se.qty), 0) - i.stock) as discrepancy,
  ABS((COALESCE(SUM(se.qty), 0) - i.stock)) as abs_discrepancy,
  CASE 
    WHEN (COALESCE(SUM(se.qty), 0) - i.stock) > 0 THEN 'OVERSTATEMENT'
    WHEN (COALESCE(SUM(se.qty), 0) - i.stock) < 0 THEN 'UNDERSTATEMENT'
    ELSE 'MATCH'
  END as mismatch_type,
  u.unit_name
FROM db_items i
LEFT JOIN db_stockentry se ON i.id = se.item_id AND se.status = 1
LEFT JOIN db_units u ON i.unit_id = u.id
GROUP BY i.id
HAVING ABS((COALESCE(SUM(se.qty), 0) - i.stock)) > 0.01
ORDER BY abs_discrepancy DESC;

-- ============================================================================
-- SECTION 2: BREAKDOWN BY SEVERITY
-- ============================================================================

SELECT '============================================================' as section;
SELECT 'SECTION 2: Mismatches by Severity Level' as title;
SELECT '============================================================' as divider;

SELECT
  CASE 
    WHEN ABS((COALESCE(SUM(se.qty), 0) - i.stock)) >= 1000 THEN 'CRITICAL (≥1000 units)'
    WHEN ABS((COALESCE(SUM(se.qty), 0) - i.stock)) >= 100 THEN 'HIGH (100-999 units)'
    WHEN ABS((COALESCE(SUM(se.qty), 0) - i.stock)) >= 10 THEN 'MEDIUM (10-99 units)'
    ELSE 'LOW (<10 units)'
  END as severity,
  COUNT(*) as item_count,
  SUM(ABS((COALESCE(SUM(se.qty), 0) - i.stock))) as total_discrepancy
FROM db_items i
LEFT JOIN db_stockentry se ON i.id = se.item_id AND se.status = 1
GROUP BY severity
HAVING COUNT(*) > 0
ORDER BY total_discrepancy DESC;

-- ============================================================================
-- SECTION 3: ITEMS WITH PRODUCTION-RELATED MISMATCHES
-- ============================================================================

SELECT '============================================================' as section;
SELECT 'SECTION 3: Items with Production Stock Entries' as title;
SELECT '============================================================' as divider;

SELECT
  i.id,
  i.item_code,
  i.item_name,
  i.stock as db_items_stock,
  COALESCE(SUM(CASE WHEN se.note LIKE '%Production Output%' THEN se.qty ELSE 0 END), 0) as production_output,
  COALESCE(SUM(CASE WHEN se.note LIKE '%Production%' THEN se.qty ELSE 0 END), 0) as all_production_qty,
  COALESCE(SUM(se.qty), 0) as total_stockentry,
  (COALESCE(SUM(se.qty), 0) - i.stock) as discrepancy
FROM db_items i
LEFT JOIN db_stockentry se ON i.id = se.item_id AND se.status = 1
GROUP BY i.id
HAVING 
  COALESCE(SUM(CASE WHEN se.note LIKE '%Production Output%' THEN se.qty ELSE 0 END), 0) > 0
  AND ABS((COALESCE(SUM(se.qty), 0) - i.stock)) > 0.01
ORDER BY ABS((COALESCE(SUM(se.qty), 0) - i.stock)) DESC;

-- ============================================================================
-- SECTION 4: PRODUCTION BATCHES WITH POTENTIAL OUTPUT ERRORS
-- ============================================================================

SELECT '============================================================' as section;
SELECT 'SECTION 4: Production Batches - Output vs Stock Entry' as title;
SELECT '============================================================' as divider;

SELECT
  pb.id as batch_id,
  pb.batch_no,
  i.item_code,
  i.item_name as output_product,
  pb.batch_quantity,
  r.yield_quantity,
  (pb.batch_quantity * r.yield_quantity) as expected_output,
  COALESCE(se.qty, 0) as stockentry_qty,
  (pb.batch_quantity * r.yield_quantity - COALESCE(se.qty, 0)) as difference,
  ABS(pb.batch_quantity * r.yield_quantity - COALESCE(se.qty, 0)) as abs_difference,
  pb.status,
  pb.approved_date
FROM production_batches pb
JOIN recipes r ON pb.recipe_id = r.id
JOIN db_items i ON r.output_product_id = i.id
LEFT JOIN db_stockentry se ON 
  i.id = se.item_id 
  AND se.note LIKE CONCAT('%', pb.batch_no, '%')
  AND se.note LIKE '%Production Output%'
WHERE pb.status = 'Approved'
AND ABS(pb.batch_quantity * r.yield_quantity - COALESCE(se.qty, 0)) > 0.01
ORDER BY abs_difference DESC;

-- ============================================================================
-- SECTION 5: ITEMS WITH CONFLICTING INVENTORY_MOVEMENTS
-- ============================================================================

SELECT '============================================================' as section;
SELECT 'SECTION 5: Inventory Movements Mismatches' as title;
SELECT '============================================================' as divider;

SELECT
  i.id,
  i.item_code,
  i.item_name,
  i.stock as db_items_stock,
  COALESCE(SUM(CASE WHEN se.status = 1 THEN se.qty ELSE 0 END), 0) as stockentry_total,
  COALESCE(SUM(im.qty), 0) as inventory_movements_total,
  (COALESCE(SUM(CASE WHEN se.status = 1 THEN se.qty ELSE 0 END), 0) - COALESCE(SUM(im.qty), 0)) as discrepancy
FROM db_items i
LEFT JOIN db_stockentry se ON i.id = se.item_id
LEFT JOIN inventory_movements im ON i.id = im.item_id AND im.type IN ('PRODUCTION_CONSUME', 'PRODUCTION_OUTPUT')
GROUP BY i.id
HAVING 
  (COALESCE(SUM(CASE WHEN se.status = 1 THEN se.qty ELSE 0 END), 0) - COALESCE(SUM(im.qty), 0)) != 0
ORDER BY ABS((COALESCE(SUM(CASE WHEN se.status = 1 THEN se.qty ELSE 0 END), 0) - COALESCE(SUM(im.qty), 0))) DESC;

-- ============================================================================
-- SECTION 6: TOP 20 ITEMS WITH LARGEST DISCREPANCIES
-- ============================================================================

SELECT '============================================================' as section;
SELECT 'SECTION 6: Top 20 Items with Largest Discrepancies' as title;
SELECT '============================================================' as divider;

SELECT
  i.id,
  i.item_code,
  i.item_name,
  i.stock as db_items_stock,
  COALESCE(SUM(se.qty), 0) as stockentry_total,
  (COALESCE(SUM(se.qty), 0) - i.stock) as discrepancy,
  ABS((COALESCE(SUM(se.qty), 0) - i.stock)) as abs_discrepancy,
  ROUND(((COALESCE(SUM(se.qty), 0) - i.stock) / NULLIF(i.stock, 0)) * 100, 2) as percent_error,
  u.unit_name,
  c.category_name,
  b.brand_name
FROM db_items i
LEFT JOIN db_stockentry se ON i.id = se.item_id AND se.status = 1
LEFT JOIN db_units u ON i.unit_id = u.id
LEFT JOIN db_category c ON i.category_id = c.id
LEFT JOIN db_brands b ON i.brand_id = b.id
GROUP BY i.id
HAVING ABS((COALESCE(SUM(se.qty), 0) - i.stock)) > 0.01
ORDER BY abs_discrepancy DESC
LIMIT 20;

-- ============================================================================
-- SECTION 7: SUMMARY STATISTICS
-- ============================================================================

SELECT '============================================================' as section;
SELECT 'SECTION 7: Summary Statistics' as title;
SELECT '============================================================' as divider;

SELECT
  'Total Items in System' as metric,
  COUNT(*) as value,
  '' as notes
FROM db_items

UNION ALL

SELECT 'Items with Discrepancies', COUNT(*), 'Mismatch detected'
FROM (
  SELECT i.id
  FROM db_items i
  LEFT JOIN db_stockentry se ON i.id = se.item_id AND se.status = 1
  GROUP BY i.id
  HAVING ABS((COALESCE(SUM(se.qty), 0) - i.stock)) > 0.01
) t

UNION ALL

SELECT 'Total Discrepancy Amount', 
  ROUND(SUM(ABS((COALESCE(SUM(se.qty), 0) - i.stock))), 2), 
  'units'
FROM db_items i
LEFT JOIN db_stockentry se ON i.id = se.item_id AND se.status = 1
GROUP BY i.id
HAVING ABS((COALESCE(SUM(se.qty), 0) - i.stock)) > 0.01;

-- ============================================================================
-- SECTION 8: ITEMS THAT MATCH PERFECTLY
-- ============================================================================

SELECT '============================================================' as section;
SELECT 'SECTION 8: Items with Matching Stock (No Issues)' as title;
SELECT '============================================================' as divider;

SELECT
  i.id,
  i.item_code,
  i.item_name,
  i.stock,
  COALESCE(SUM(se.qty), 0) as stockentry_total,
  'OK - MATCH' as status,
  u.unit_name
FROM db_items i
LEFT JOIN db_stockentry se ON i.id = se.item_id AND se.status = 1
LEFT JOIN db_units u ON i.unit_id = u.id
GROUP BY i.id
HAVING ABS((COALESCE(SUM(se.qty), 0) - i.stock)) <= 0.01
ORDER BY i.item_code
LIMIT 50;

-- ============================================================================
-- SECTION 9: DETAILED ANALYSIS FOR FLAGGED ITEMS
-- ============================================================================

SELECT '============================================================' as section;
SELECT 'SECTION 9: Detailed Breakdown for Items with Issues' as title;
SELECT '============================================================' as divider;

-- This query shows exactly what's in each system for problem items
SELECT
  i.id,
  i.item_code,
  i.item_name,
  'Current db_items.stock' as data_source,
  i.stock as value,
  'Snapshot - updated by system' as explanation
FROM db_items i
WHERE EXISTS (
  SELECT 1 FROM db_stockentry se
  WHERE i.id = se.item_id AND se.status = 1
  GROUP BY se.item_id
  HAVING ABS((COALESCE(SUM(se.qty), 0) - i.stock)) > 0.01
)
ORDER BY i.id;

-- ============================================================================
-- SECTION 10: RECOMMENDATIONS FOR TOP MISMATCHES
-- ============================================================================

SELECT '============================================================' as section;
SELECT 'SECTION 10: Recommendations for Top 5 Mismatches' as title;
SELECT '============================================================' as divider;

SELECT
  CONCAT('Item: ', i.item_code, ' - ', i.item_name) as item,
  CONCAT('Current db_items.stock: ', i.stock) as current_value,
  CONCAT('Correct stockentry total: ', COALESCE(SUM(se.qty), 0)) as correct_value,
  CONCAT('Change needed: ', (COALESCE(SUM(se.qty), 0) - i.stock), ' units') as correction,
  CONCAT('Reason: Data entry error in db_stockentry for this item') as analysis,
  CONCAT('Action: Review production batches and manual stock entries for ', i.item_code) as recommendation
FROM db_items i
LEFT JOIN db_stockentry se ON i.id = se.item_id AND se.status = 1
GROUP BY i.id
HAVING ABS((COALESCE(SUM(se.qty), 0) - i.stock)) > 0.01
ORDER BY ABS((COALESCE(SUM(se.qty), 0) - i.stock)) DESC
LIMIT 5;

-- ============================================================================
-- END OF DIAGNOSTIC SCRIPT
-- ============================================================================

SELECT '============================================================' as final;
SELECT 'DIAGNOSTIC COMPLETE - Review results above' as message;
SELECT '============================================================' as end;
