-- ============================================================================
-- LEDGER STOCK HISTORY vs db_items STOCK DISCREPANCY REPORT
-- ============================================================================

SELECT '===== LEDGER vs db_items DISCREPANCY REPORT =====' as title;
SELECT CONCAT('Generated: ', NOW()) as timestamp;
SELECT '' as blank_line;

-- ============================================================================
-- MAIN REPORT: Compare Ledger vs db_items
-- ============================================================================

SELECT
  i.id,
  i.item_code,
  i.item_name,
  u.unit_name,
  i.stock as db_items_stock,
  
  -- Calculate ledger total
  (
    COALESCE((SELECT SUM(purchase_qty) FROM db_purchaseitems pi JOIN db_purchase p ON p.id = pi.purchase_id WHERE pi.item_id = i.id AND p.status = 1), 0)
    - COALESCE((SELECT SUM(sales_qty) FROM db_salesitems si JOIN db_sales s ON s.id = si.sales_id WHERE si.item_id = i.id AND s.status = 1), 0)
    + COALESCE((SELECT SUM(return_qty) FROM db_salesitemsreturn sr JOIN db_salesreturn sr2 ON sr2.id = sr.return_id WHERE sr.item_id = i.id AND sr2.status = 1), 0)
    - COALESCE((SELECT SUM(return_qty) FROM db_purchaseitemsreturn pr JOIN db_purchasereturn pr2 ON pr2.id = pr.return_id WHERE pr.item_id = i.id AND pr2.status = 1), 0)
  ) as ledger_stock,
  
  -- Discrepancy
  (
    i.stock - (
      COALESCE((SELECT SUM(purchase_qty) FROM db_purchaseitems pi JOIN db_purchase p ON p.id = pi.purchase_id WHERE pi.item_id = i.id AND p.status = 1), 0)
      - COALESCE((SELECT SUM(sales_qty) FROM db_salesitems si JOIN db_sales s ON s.id = si.sales_id WHERE si.item_id = i.id AND s.status = 1), 0)
      + COALESCE((SELECT SUM(return_qty) FROM db_salesitemsreturn sr JOIN db_salesreturn sr2 ON sr2.id = sr.return_id WHERE sr.item_id = i.id AND sr2.status = 1), 0)
      - COALESCE((SELECT SUM(return_qty) FROM db_purchaseitemsreturn pr JOIN db_purchasereturn pr2 ON pr2.id = pr.return_id WHERE pr.item_id = i.id AND pr2.status = 1), 0)
    )
  ) as discrepancy,
  
  -- Absolute discrepancy
  ABS(
    i.stock - (
      COALESCE((SELECT SUM(purchase_qty) FROM db_purchaseitems pi JOIN db_purchase p ON p.id = pi.purchase_id WHERE pi.item_id = i.id AND p.status = 1), 0)
      - COALESCE((SELECT SUM(sales_qty) FROM db_salesitems si JOIN db_sales s ON s.id = si.sales_id WHERE si.item_id = i.id AND s.status = 1), 0)
      + COALESCE((SELECT SUM(return_qty) FROM db_salesitemsreturn sr JOIN db_salesreturn sr2 ON sr2.id = sr.return_id WHERE sr.item_id = i.id AND sr2.status = 1), 0)
      - COALESCE((SELECT SUM(return_qty) FROM db_purchaseitemsreturn pr JOIN db_purchasereturn pr2 ON pr2.id = pr.return_id WHERE pr.item_id = i.id AND pr2.status = 1), 0)
    )
  ) as abs_discrepancy,
  
  -- Status
  CASE
    WHEN ABS(
      i.stock - (
        COALESCE((SELECT SUM(purchase_qty) FROM db_purchaseitems pi JOIN db_purchase p ON p.id = pi.purchase_id WHERE pi.item_id = i.id AND p.status = 1), 0)
        - COALESCE((SELECT SUM(sales_qty) FROM db_salesitems si JOIN db_sales s ON s.id = si.sales_id WHERE si.item_id = i.id AND s.status = 1), 0)
        + COALESCE((SELECT SUM(return_qty) FROM db_salesitemsreturn sr JOIN db_salesreturn sr2 ON sr2.id = sr.return_id WHERE sr.item_id = i.id AND sr2.status = 1), 0)
        - COALESCE((SELECT SUM(return_qty) FROM db_purchaseitemsreturn pr JOIN db_purchasereturn pr2 ON pr2.id = pr.return_id WHERE pr.item_id = i.id AND pr2.status = 1), 0)
      )
    ) > 0.01 THEN 'MISMATCH'
    ELSE 'OK'
  END as status

FROM db_items i
LEFT JOIN db_units u ON i.unit_id = u.id

WHERE ABS(
  i.stock - (
    COALESCE((SELECT SUM(purchase_qty) FROM db_purchaseitems pi JOIN db_purchase p ON p.id = pi.purchase_id WHERE pi.item_id = i.id AND p.status = 1), 0)
    - COALESCE((SELECT SUM(sales_qty) FROM db_salesitems si JOIN db_sales s ON s.id = si.sales_id WHERE si.item_id = i.id AND s.status = 1), 0)
    + COALESCE((SELECT SUM(return_qty) FROM db_salesitemsreturn sr JOIN db_salesreturn sr2 ON sr2.id = sr.return_id WHERE sr.item_id = i.id AND sr2.status = 1), 0)
    - COALESCE((SELECT SUM(return_qty) FROM db_purchaseitemsreturn pr JOIN db_purchasereturn pr2 ON pr2.id = pr.return_id WHERE pr.item_id = i.id AND pr2.status = 1), 0)
  )
) > 0.01

ORDER BY abs_discrepancy DESC;

-- ============================================================================
-- SUMMARY
-- ============================================================================

SELECT '' as blank_line;
SELECT '===== SUMMARY =====' as summary;
SELECT '' as blank_line;

SELECT
  COUNT(*) as total_items_with_discrepancy,
  ROUND(SUM(ABS(
    i.stock - (
      COALESCE((SELECT SUM(purchase_qty) FROM db_purchaseitems pi JOIN db_purchase p ON p.id = pi.purchase_id WHERE pi.item_id = i.id AND p.status = 1), 0)
      - COALESCE((SELECT SUM(sales_qty) FROM db_salesitems si JOIN db_sales s ON s.id = si.sales_id WHERE si.item_id = i.id AND s.status = 1), 0)
      + COALESCE((SELECT SUM(return_qty) FROM db_salesitemsreturn sr JOIN db_salesreturn sr2 ON sr2.id = sr.return_id WHERE sr.item_id = i.id AND sr2.status = 1), 0)
      - COALESCE((SELECT SUM(return_qty) FROM db_purchaseitemsreturn pr JOIN db_purchasereturn pr2 ON pr2.id = pr.return_id WHERE pr.item_id = i.id AND pr2.status = 1), 0)
    )
  )), 2) as total_discrepancy_amount
FROM db_items i
WHERE ABS(
  i.stock - (
    COALESCE((SELECT SUM(purchase_qty) FROM db_purchaseitems pi JOIN db_purchase p ON p.id = pi.purchase_id WHERE pi.item_id = i.id AND p.status = 1), 0)
    - COALESCE((SELECT SUM(sales_qty) FROM db_salesitems si JOIN db_sales s ON s.id = si.sales_id WHERE si.item_id = i.id AND s.status = 1), 0)
    + COALESCE((SELECT SUM(return_qty) FROM db_salesitemsreturn sr JOIN db_salesreturn sr2 ON sr2.id = sr.return_id WHERE sr.item_id = i.id AND sr2.status = 1), 0)
    - COALESCE((SELECT SUM(return_qty) FROM db_purchaseitemsreturn pr JOIN db_purchasereturn pr2 ON pr2.id = pr.return_id WHERE pr.item_id = i.id AND pr2.status = 1), 0)
  )
) > 0.01;

-- ============================================================================
-- BREAKDOWN BY DIRECTION
-- ============================================================================

SELECT '' as blank_line;
SELECT '===== BREAKDOWN BY DIRECTION =====' as breakdown;
SELECT '' as blank_line;

SELECT
  CASE
    WHEN i.stock > (
      COALESCE((SELECT SUM(purchase_qty) FROM db_purchaseitems pi JOIN db_purchase p ON p.id = pi.purchase_id WHERE pi.item_id = i.id AND p.status = 1), 0)
      - COALESCE((SELECT SUM(sales_qty) FROM db_salesitems si JOIN db_sales s ON s.id = si.sales_id WHERE si.item_id = i.id AND s.status = 1), 0)
      + COALESCE((SELECT SUM(return_qty) FROM db_salesitemsreturn sr JOIN db_salesreturn sr2 ON sr2.id = sr.return_id WHERE sr.item_id = i.id AND sr2.status = 1), 0)
      - COALESCE((SELECT SUM(return_qty) FROM db_purchaseitemsreturn pr JOIN db_purchasereturn pr2 ON pr2.id = pr.return_id WHERE pr.item_id = i.id AND pr2.status = 1), 0)
    ) THEN 'OVERSTATED'
    ELSE 'UNDERSTATED'
  END as direction,
  COUNT(*) as item_count,
  ROUND(SUM(ABS(
    i.stock - (
      COALESCE((SELECT SUM(purchase_qty) FROM db_purchaseitems pi JOIN db_purchase p ON p.id = pi.purchase_id WHERE pi.item_id = i.id AND p.status = 1), 0)
      - COALESCE((SELECT SUM(sales_qty) FROM db_salesitems si JOIN db_sales s ON s.id = si.sales_id WHERE si.item_id = i.id AND s.status = 1), 0)
      + COALESCE((SELECT SUM(return_qty) FROM db_salesitemsreturn sr JOIN db_salesreturn sr2 ON sr2.id = sr.return_id WHERE sr.item_id = i.id AND sr2.status = 1), 0)
      - COALESCE((SELECT SUM(return_qty) FROM db_purchaseitemsreturn pr JOIN db_purchasereturn pr2 ON pr2.id = pr.return_id WHERE pr.item_id = i.id AND pr2.status = 1), 0)
    )
  )), 2) as total_amount
FROM db_items i
WHERE ABS(
  i.stock - (
    COALESCE((SELECT SUM(purchase_qty) FROM db_purchaseitems pi JOIN db_purchase p ON p.id = pi.purchase_id WHERE pi.item_id = i.id AND p.status = 1), 0)
    - COALESCE((SELECT SUM(sales_qty) FROM db_salesitems si JOIN db_sales s ON s.id = si.sales_id WHERE si.item_id = i.id AND s.status = 1), 0)
    + COALESCE((SELECT SUM(return_qty) FROM db_salesitemsreturn sr JOIN db_salesreturn sr2 ON sr2.id = sr.return_id WHERE sr.item_id = i.id AND sr2.status = 1), 0)
    - COALESCE((SELECT SUM(return_qty) FROM db_purchaseitemsreturn pr JOIN db_purchasereturn pr2 ON pr2.id = pr.return_id WHERE pr.item_id = i.id AND pr2.status = 1), 0)
  )
) > 0.01
GROUP BY direction;

-- ============================================================================
-- END REPORT
-- ============================================================================

SELECT '' as blank_line;
SELECT '===== REPORT COMPLETE =====' as completion;
