-- ============================================================================
-- SIMPLE: Stock History Current Stock vs Items Table Stock
-- ============================================================================
-- What Stock History page shows vs what Items table has
-- ============================================================================

SELECT '===== STOCK HISTORY vs ITEMS TABLE COMPARISON =====' as title;
SELECT CONCAT('Generated: ', NOW()) as timestamp;
SELECT '' as blank_line;

SELECT
  i.id,
  i.item_code,
  i.item_name,
  u.unit_name,
  i.stock as items_table_stock,
  
  -- Stock History current stock = sum of all transactions
  ROUND(
    COALESCE(SUM(CASE WHEN type='Purchase' THEN qty ELSE 0 END), 0) +
    COALESCE(SUM(CASE WHEN type='Sell' THEN qty ELSE 0 END), 0) +
    COALESCE(SUM(CASE WHEN type='Sell Return' THEN qty ELSE 0 END), 0) +
    COALESCE(SUM(CASE WHEN type='Purchase Return' THEN qty ELSE 0 END), 0),
    2
  ) as stock_history_current_stock,
  
  -- Discrepancy
  ROUND(
    i.stock - (
      COALESCE(SUM(CASE WHEN type='Purchase' THEN qty ELSE 0 END), 0) +
      COALESCE(SUM(CASE WHEN type='Sell' THEN qty ELSE 0 END), 0) +
      COALESCE(SUM(CASE WHEN type='Sell Return' THEN qty ELSE 0 END), 0) +
      COALESCE(SUM(CASE WHEN type='Purchase Return' THEN qty ELSE 0 END), 0)
    ),
    2
  ) as discrepancy

FROM db_items i
LEFT JOIN db_units u ON i.unit_id = u.id

-- Get all transactions for each item (simplified)
LEFT JOIN (
  SELECT item_id, purchase_qty as qty, 'Purchase' as type FROM db_purchaseitems pi
  JOIN db_purchase p ON p.id = pi.purchase_id WHERE p.status = 1
  UNION ALL
  SELECT item_id, 0 - sales_qty as qty, 'Sell' FROM db_salesitems si
  JOIN db_sales s ON s.id = si.sales_id WHERE s.status = 1
  UNION ALL
  SELECT item_id, return_qty as qty, 'Sell Return' FROM db_salesitemsreturn sr
  UNION ALL
  SELECT item_id, 0 - return_qty as qty, 'Purchase Return' FROM db_purchaseitemsreturn pr
) transactions ON i.id = transactions.item_id

GROUP BY i.id

HAVING ABS(
  i.stock - (
    COALESCE(SUM(CASE WHEN type='Purchase' THEN qty ELSE 0 END), 0) +
    COALESCE(SUM(CASE WHEN type='Sell' THEN qty ELSE 0 END), 0) +
    COALESCE(SUM(CASE WHEN type='Sell Return' THEN qty ELSE 0 END), 0) +
    COALESCE(SUM(CASE WHEN type='Purchase Return' THEN qty ELSE 0 END), 0)
  )
) > 0.01

ORDER BY ABS(
  i.stock - (
    COALESCE(SUM(CASE WHEN type='Purchase' THEN qty ELSE 0 END), 0) +
    COALESCE(SUM(CASE WHEN type='Sell' THEN qty ELSE 0 END), 0) +
    COALESCE(SUM(CASE WHEN type='Sell Return' THEN qty ELSE 0 END), 0) +
    COALESCE(SUM(CASE WHEN type='Purchase Return' THEN qty ELSE 0 END), 0)
  )
) DESC;

-- ============================================================================
-- SUMMARY
-- ============================================================================

SELECT '' as blank_line;
SELECT '===== SUMMARY =====' as summary;
SELECT '' as blank_line;

SELECT COUNT(*) as items_with_discrepancy,
       ROUND(SUM(ABS(
         i.stock - (
           COALESCE(SUM(CASE WHEN type='Purchase' THEN qty ELSE 0 END), 0) +
           COALESCE(SUM(CASE WHEN type='Sell' THEN qty ELSE 0 END), 0) +
           COALESCE(SUM(CASE WHEN type='Sell Return' THEN qty ELSE 0 END), 0) +
           COALESCE(SUM(CASE WHEN type='Purchase Return' THEN qty ELSE 0 END), 0)
         )
       )), 2) as total_discrepancy

FROM db_items i
LEFT JOIN (
  SELECT item_id, purchase_qty as qty, 'Purchase' as type FROM db_purchaseitems pi
  JOIN db_purchase p ON p.id = pi.purchase_id WHERE p.status = 1
  UNION ALL
  SELECT item_id, 0 - sales_qty as qty, 'Sell' FROM db_salesitems si
  JOIN db_sales s ON s.id = si.sales_id WHERE s.status = 1
  UNION ALL
  SELECT item_id, return_qty as qty, 'Sell Return' FROM db_salesitemsreturn sr
  UNION ALL
  SELECT item_id, 0 - return_qty as qty, 'Purchase Return' FROM db_purchaseitemsreturn pr
) transactions ON i.id = transactions.item_id

GROUP BY i.id

HAVING ABS(
  i.stock - (
    COALESCE(SUM(CASE WHEN type='Purchase' THEN qty ELSE 0 END), 0) +
    COALESCE(SUM(CASE WHEN type='Sell' THEN qty ELSE 0 END), 0) +
    COALESCE(SUM(CASE WHEN type='Sell Return' THEN qty ELSE 0 END), 0) +
    COALESCE(SUM(CASE WHEN type='Purchase Return' THEN qty ELSE 0 END), 0)
  )
) > 0.01;

-- ============================================================================
-- END
-- ============================================================================
