-- ============================================================================
-- STOCK LEDGER vs db_items DISCREPANCY ANALYSIS
-- ============================================================================
-- Purpose: Compare stock_history ledger calculation vs db_items.stock
-- Shows which items have mismatches and by how much
-- ============================================================================

-- ============================================================================
-- SECTION 1: COMPLETE DISCREPANCY REPORT
-- ============================================================================

SELECT '===== COMPLETE STOCK DISCREPANCY ANALYSIS =====' as header;
SELECT CONCAT('Report Date: ', NOW()) as timestamp;
SELECT '' as blank_line;

SELECT
  i.id,
  i.item_code,
  i.item_name,
  u.unit_name,
  i.stock as db_items_stock,
  -- Calculate stock from ledger sources
  (
    COALESCE(SUM(CASE WHEN pi_table.id IS NOT NULL THEN pi_table.purchase_qty ELSE 0 END), 0)
    - COALESCE(SUM(CASE WHEN si_table.id IS NOT NULL THEN si_table.sales_qty ELSE 0 END), 0)
    + COALESCE(SUM(CASE WHEN sr_table.id IS NOT NULL THEN sr_table.return_qty ELSE 0 END), 0)
    - COALESCE(SUM(CASE WHEN pr_table.id IS NOT NULL THEN pr_table.return_qty ELSE 0 END), 0)
  ) as ledger_calculated_stock,
  -- Calculate discrepancy
  (
    i.stock - (
      COALESCE(SUM(CASE WHEN pi_table.id IS NOT NULL THEN pi_table.purchase_qty ELSE 0 END), 0)
      - COALESCE(SUM(CASE WHEN si_table.id IS NOT NULL THEN si_table.sales_qty ELSE 0 END), 0)
      + COALESCE(SUM(CASE WHEN sr_table.id IS NOT NULL THEN sr_table.return_qty ELSE 0 END), 0)
      - COALESCE(SUM(CASE WHEN pr_table.id IS NOT NULL THEN pr_table.return_qty ELSE 0 END), 0)
    )
  ) as discrepancy,
  ABS(
    i.stock - (
      COALESCE(SUM(CASE WHEN pi_table.id IS NOT NULL THEN pi_table.purchase_qty ELSE 0 END), 0)
      - COALESCE(SUM(CASE WHEN si_table.id IS NOT NULL THEN si_table.sales_qty ELSE 0 END), 0)
      + COALESCE(SUM(CASE WHEN sr_table.id IS NOT NULL THEN sr_table.return_qty ELSE 0 END), 0)
      - COALESCE(SUM(CASE WHEN pr_table.id IS NOT NULL THEN pr_table.return_qty ELSE 0 END), 0)
    )
  ) as abs_discrepancy,
  CASE
    WHEN ABS(
      i.stock - (
        COALESCE(SUM(CASE WHEN pi_table.id IS NOT NULL THEN pi_table.purchase_qty ELSE 0 END), 0)
        - COALESCE(SUM(CASE WHEN si_table.id IS NOT NULL THEN si_table.sales_qty ELSE 0 END), 0)
        + COALESCE(SUM(CASE WHEN sr_table.id IS NOT NULL THEN sr_table.return_qty ELSE 0 END), 0)
        - COALESCE(SUM(CASE WHEN pr_table.id IS NOT NULL THEN pr_table.return_qty ELSE 0 END), 0)
      )
    ) > 0.01 THEN 'MISMATCH'
    ELSE 'OK'
  END as status

FROM db_items i
LEFT JOIN db_units u ON i.unit_id = u.id

-- Purchase Items Join
LEFT JOIN (
  SELECT pi.item_id, pi.purchase_qty, pi.purchase_id
  FROM db_purchaseitems pi
  JOIN db_purchase p ON p.id = pi.purchase_id AND p.status = 1
) pi_table ON i.id = pi_table.item_id

-- Sales Items Join
LEFT JOIN (
  SELECT si.item_id, si.sales_qty, si.sales_id
  FROM db_salesitems si
  JOIN db_sales s ON s.id = si.sales_id AND s.status = 1
) si_table ON i.id = si_table.item_id

-- Sales Returns Join
LEFT JOIN (
  SELECT sr.item_id, sr.return_qty, sr.return_id
  FROM db_salesitemsreturn sr
  JOIN db_salesreturn s ON s.id = sr.return_id AND s.status = 1
) sr_table ON i.id = sr_table.item_id

-- Purchase Returns Join
LEFT JOIN (
  SELECT pr.item_id, pr.return_qty, pr.return_id
  FROM db_purchaseitemsreturn pr
  JOIN db_purchasereturn p ON p.id = pr.return_id AND p.status = 1
) pr_table ON i.id = pr_table.item_id

GROUP BY i.id
HAVING ABS(
  i.stock - (
    COALESCE(SUM(CASE WHEN pi_table.id IS NOT NULL THEN pi_table.purchase_qty ELSE 0 END), 0)
    - COALESCE(SUM(CASE WHEN si_table.id IS NOT NULL THEN si_table.sales_qty ELSE 0 END), 0)
    + COALESCE(SUM(CASE WHEN sr_table.id IS NOT NULL THEN sr_table.return_qty ELSE 0 END), 0)
    - COALESCE(SUM(CASE WHEN pr_table.id IS NOT NULL THEN pr_table.return_qty ELSE 0 END), 0)
  )
) > 0.01
ORDER BY abs_discrepancy DESC;

-- ============================================================================
-- SECTION 2: BREAKDOWN FOR EACH TRANSACTION TYPE
-- ============================================================================

SELECT '' as blank_line;
SELECT '===== TRANSACTION BREAKDOWN FOR MISMATCHED ITEMS =====' as section;
SELECT '' as blank_line;

-- For each item with mismatch, show the components
SELECT
  i.id,
  i.item_code,
  i.item_name,
  'Purchases' as transaction_type,
  COALESCE(SUM(pi.purchase_qty), 0) as ledger_amount,
  0 as db_items_amount,
  COALESCE(SUM(pi.purchase_qty), 0) as contribution_to_total
FROM db_items i
LEFT JOIN db_purchaseitems pi ON i.id = pi.item_id
LEFT JOIN db_purchase p ON p.id = pi.purchase_id AND p.status = 1
WHERE i.id IN (
  SELECT i.id FROM db_items i
  LEFT JOIN db_purchaseitems pi ON i.id = pi.item_id
  LEFT JOIN db_purchase p ON p.id = pi.purchase_id AND p.status = 1
  LEFT JOIN db_salesitems si ON i.id = si.item_id
  LEFT JOIN db_sales s ON s.id = si.sales_id AND s.status = 1
  LEFT JOIN db_salesitemsreturn sr ON i.id = sr.item_id
  LEFT JOIN db_purchaseitemsreturn pr ON i.id = pr.item_id
  GROUP BY i.id
  HAVING ABS(
    i.stock - (
      COALESCE(SUM(CASE WHEN p.id IS NOT NULL THEN pi.purchase_qty ELSE 0 END), 0)
      - COALESCE(SUM(CASE WHEN s.id IS NOT NULL THEN si.sales_qty ELSE 0 END), 0)
      + COALESCE(SUM(sr.return_qty), 0)
      - COALESCE(SUM(pr.return_qty), 0)
    )
  ) > 0.01
)
GROUP BY i.id

UNION ALL

SELECT
  i.id,
  i.item_code,
  i.item_name,
  'Sales' as transaction_type,
  0 as ledger_amount,
  0 as db_items_amount,
  -COALESCE(SUM(si.sales_qty), 0) as contribution_to_total
FROM db_items i
LEFT JOIN db_salesitems si ON i.id = si.item_id
LEFT JOIN db_sales s ON s.id = si.sales_id AND s.status = 1
WHERE i.id IN (
  SELECT i.id FROM db_items i
  LEFT JOIN db_purchaseitems pi ON i.id = pi.item_id
  LEFT JOIN db_purchase p ON p.id = pi.purchase_id AND p.status = 1
  LEFT JOIN db_salesitems si ON i.id = si.item_id
  LEFT JOIN db_sales s ON s.id = si.sales_id AND s.status = 1
  LEFT JOIN db_salesitemsreturn sr ON i.id = sr.item_id
  LEFT JOIN db_purchaseitemsreturn pr ON i.id = pr.item_id
  GROUP BY i.id
  HAVING ABS(
    i.stock - (
      COALESCE(SUM(CASE WHEN p.id IS NOT NULL THEN pi.purchase_qty ELSE 0 END), 0)
      - COALESCE(SUM(CASE WHEN s.id IS NOT NULL THEN si.sales_qty ELSE 0 END), 0)
      + COALESCE(SUM(sr.return_qty), 0)
      - COALESCE(SUM(pr.return_qty), 0)
    )
  ) > 0.01
)
GROUP BY i.id

UNION ALL

SELECT
  i.id,
  i.item_code,
  i.item_name,
  'Sales Returns' as transaction_type,
  0 as ledger_amount,
  0 as db_items_amount,
  COALESCE(SUM(sr.return_qty), 0) as contribution_to_total
FROM db_items i
LEFT JOIN db_salesitemsreturn sr ON i.id = sr.item_id
LEFT JOIN db_salesreturn s ON s.id = sr.return_id AND s.status = 1
WHERE i.id IN (
  SELECT i.id FROM db_items i
  LEFT JOIN db_purchaseitems pi ON i.id = pi.item_id
  LEFT JOIN db_purchase p ON p.id = pi.purchase_id AND p.status = 1
  LEFT JOIN db_salesitems si ON i.id = si.item_id
  LEFT JOIN db_sales s ON s.id = si.sales_id AND s.status = 1
  LEFT JOIN db_salesitemsreturn sr ON i.id = sr.item_id
  LEFT JOIN db_purchaseitemsreturn pr ON i.id = pr.item_id
  GROUP BY i.id
  HAVING ABS(
    i.stock - (
      COALESCE(SUM(CASE WHEN p.id IS NOT NULL THEN pi.purchase_qty ELSE 0 END), 0)
      - COALESCE(SUM(CASE WHEN s.id IS NOT NULL THEN si.sales_qty ELSE 0 END), 0)
      + COALESCE(SUM(sr.return_qty), 0)
      - COALESCE(SUM(pr.return_qty), 0)
    )
  ) > 0.01
)
GROUP BY i.id

UNION ALL

SELECT
  i.id,
  i.item_code,
  i.item_name,
  'Purchase Returns' as transaction_type,
  0 as ledger_amount,
  0 as db_items_amount,
  -COALESCE(SUM(pr.return_qty), 0) as contribution_to_total
FROM db_items i
LEFT JOIN db_purchaseitemsreturn pr ON i.id = pr.item_id
LEFT JOIN db_purchasereturn p ON p.id = pr.return_id AND p.status = 1
WHERE i.id IN (
  SELECT i.id FROM db_items i
  LEFT JOIN db_purchaseitems pi ON i.id = pi.item_id
  LEFT JOIN db_purchase p ON p.id = pi.purchase_id AND p.status = 1
  LEFT JOIN db_salesitems si ON i.id = si.item_id
  LEFT JOIN db_sales s ON s.id = si.sales_id AND s.status = 1
  LEFT JOIN db_salesitemsreturn sr ON i.id = sr.item_id
  LEFT JOIN db_purchaseitemsreturn pr ON i.id = pr.item_id
  GROUP BY i.id
  HAVING ABS(
    i.stock - (
      COALESCE(SUM(CASE WHEN p.id IS NOT NULL THEN pi.purchase_qty ELSE 0 END), 0)
      - COALESCE(SUM(CASE WHEN s.id IS NOT NULL THEN si.sales_qty ELSE 0 END), 0)
      + COALESCE(SUM(sr.return_qty), 0)
      - COALESCE(SUM(pr.return_qty), 0)
    )
  ) > 0.01
)
GROUP BY i.id;

-- ============================================================================
-- SECTION 3: SUMMARY STATISTICS
-- ============================================================================

SELECT '' as blank_line;
SELECT '===== SUMMARY =====' as summary;
SELECT '' as blank_line;

SELECT
  'Total items analyzed' as metric,
  COUNT(*) as count,
  '' as note
FROM db_items

UNION ALL

SELECT
  'Items with discrepancies',
  COUNT(DISTINCT i.id),
  '(ledger ≠ db_items)'
FROM db_items i
LEFT JOIN db_purchaseitems pi ON i.id = pi.item_id
LEFT JOIN db_purchase p ON p.id = pi.purchase_id AND p.status = 1
LEFT JOIN db_salesitems si ON i.id = si.item_id
LEFT JOIN db_sales s ON s.id = si.sales_id AND s.status = 1
LEFT JOIN db_salesitemsreturn sr ON i.id = sr.item_id
LEFT JOIN db_purchaseitemsreturn pr ON i.id = pr.item_id
GROUP BY i.id
HAVING ABS(
  i.stock - (
    COALESCE(SUM(CASE WHEN p.id IS NOT NULL THEN pi.purchase_qty ELSE 0 END), 0)
    - COALESCE(SUM(CASE WHEN s.id IS NOT NULL THEN si.sales_qty ELSE 0 END), 0)
    + COALESCE(SUM(sr.return_qty), 0)
    - COALESCE(SUM(pr.return_qty), 0)
  )
) > 0.01

UNION ALL

SELECT
  'Items matching perfectly',
  COUNT(DISTINCT i.id),
  '(ledger = db_items)'
FROM db_items i
LEFT JOIN db_purchaseitems pi ON i.id = pi.item_id
LEFT JOIN db_purchase p ON p.id = pi.purchase_id AND p.status = 1
LEFT JOIN db_salesitems si ON i.id = si.item_id
LEFT JOIN db_sales s ON s.id = si.sales_id AND s.status = 1
LEFT JOIN db_salesitemsreturn sr ON i.id = sr.item_id
LEFT JOIN db_purchaseitemsreturn pr ON i.id = pr.item_id
GROUP BY i.id
HAVING ABS(
  i.stock - (
    COALESCE(SUM(CASE WHEN p.id IS NOT NULL THEN pi.purchase_qty ELSE 0 END), 0)
    - COALESCE(SUM(CASE WHEN s.id IS NOT NULL THEN si.sales_qty ELSE 0 END), 0)
    + COALESCE(SUM(sr.return_qty), 0)
    - COALESCE(SUM(pr.return_qty), 0)
  )
) <= 0.01;

-- ============================================================================
-- SECTION 4: TOP 10 WORST DISCREPANCIES
-- ============================================================================

SELECT '' as blank_line;
SELECT '===== TOP 10 WORST DISCREPANCIES =====' as top_10;
SELECT '' as blank_line;

SELECT
  i.id,
  i.item_code,
  i.item_name,
  i.stock as db_items_stock,
  ROUND(
    COALESCE(SUM(CASE WHEN p.id IS NOT NULL THEN pi.purchase_qty ELSE 0 END), 0)
    - COALESCE(SUM(CASE WHEN s.id IS NOT NULL THEN si.sales_qty ELSE 0 END), 0)
    + COALESCE(SUM(sr.return_qty), 0)
    - COALESCE(SUM(pr.return_qty), 0),
    2
  ) as ledger_stock,
  ROUND(
    i.stock - (
      COALESCE(SUM(CASE WHEN p.id IS NOT NULL THEN pi.purchase_qty ELSE 0 END), 0)
      - COALESCE(SUM(CASE WHEN s.id IS NOT NULL THEN si.sales_qty ELSE 0 END), 0)
      + COALESCE(SUM(sr.return_qty), 0)
      - COALESCE(SUM(pr.return_qty), 0)
    ),
    2
  ) as discrepancy,
  CASE
    WHEN i.stock > (
      COALESCE(SUM(CASE WHEN p.id IS NOT NULL THEN pi.purchase_qty ELSE 0 END), 0)
      - COALESCE(SUM(CASE WHEN s.id IS NOT NULL THEN si.sales_qty ELSE 0 END), 0)
      + COALESCE(SUM(sr.return_qty), 0)
      - COALESCE(SUM(pr.return_qty), 0)
    ) THEN 'OVERSTATED'
    ELSE 'UNDERSTATED'
  END as direction

FROM db_items i
LEFT JOIN db_purchaseitems pi ON i.id = pi.item_id
LEFT JOIN db_purchase p ON p.id = pi.purchase_id AND p.status = 1
LEFT JOIN db_salesitems si ON i.id = si.item_id
LEFT JOIN db_sales s ON s.id = si.sales_id AND s.status = 1
LEFT JOIN db_salesitemsreturn sr ON i.id = sr.item_id
LEFT JOIN db_purchaseitemsreturn pr ON i.id = pr.item_id

GROUP BY i.id
HAVING ABS(
  i.stock - (
    COALESCE(SUM(CASE WHEN p.id IS NOT NULL THEN pi.purchase_qty ELSE 0 END), 0)
    - COALESCE(SUM(CASE WHEN s.id IS NOT NULL THEN si.sales_qty ELSE 0 END), 0)
    + COALESCE(SUM(sr.return_qty), 0)
    - COALESCE(SUM(pr.return_qty), 0)
  )
) > 0.01

ORDER BY ABS(
  i.stock - (
    COALESCE(SUM(CASE WHEN p.id IS NOT NULL THEN pi.purchase_qty ELSE 0 END), 0)
    - COALESCE(SUM(CASE WHEN s.id IS NOT NULL THEN si.sales_qty ELSE 0 END), 0)
    + COALESCE(SUM(sr.return_qty), 0)
    - COALESCE(SUM(pr.return_qty), 0)
  )
) DESC
LIMIT 10;

-- ============================================================================
-- END REPORT
-- ============================================================================

SELECT '' as blank_line;
SELECT '===== REPORT COMPLETE =====' as footer;
