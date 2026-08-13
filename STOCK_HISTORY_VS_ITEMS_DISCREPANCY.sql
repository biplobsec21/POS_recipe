-- ============================================================================
-- STOCK HISTORY PAGE vs ITEMS TABLE DISCREPANCY
-- ============================================================================
-- Compare:
-- 1. Stock History "Current Stock" (from get_stock_summary)
-- 2. Items Table "stock" field (db_items.stock)
-- ============================================================================

SELECT '===== STOCK HISTORY vs ITEMS TABLE DISCREPANCY =====' as title;
SELECT CONCAT('Report Date: ', NOW()) as timestamp;
SELECT '' as blank_line;

SELECT
  i.id,
  i.item_code,
  i.item_name,
  u.unit_name,
  i.stock as items_table_stock,
  
  -- Get last transaction balance (what Stock History page shows as Current Stock)
  (
    SELECT new_quantity FROM (
      -- Get all transactions for this item sorted by date
      SELECT 
        new_quantity,
        ROW_NUMBER() OVER (ORDER BY transaction_date DESC, id DESC) as rn
      FROM (
        -- All transactions combined
        SELECT CONCAT(s.sales_date, ' ', COALESCE(s.created_time, '00:00:00')) as transaction_date,
               0 - si.sales_qty as new_quantity,
               si.id
        FROM db_salesitems si
        JOIN db_sales s ON s.id = si.sales_id
        WHERE si.item_id = i.id AND s.status = 1
        
        UNION ALL
        
        SELECT CONCAT(p.purchase_date, ' ', COALESCE(p.created_time, '00:00:00')),
               pi.purchase_qty,
               pi.id
        FROM db_purchaseitems pi
        JOIN db_purchase p ON p.id = pi.purchase_id
        WHERE pi.item_id = i.id AND p.status = 1
        
        UNION ALL
        
        SELECT CONCAT(sr2.created_date, ' ', COALESCE(sr2.created_time, '00:00:00')),
               sr.return_qty,
               sr.id
        FROM db_salesitemsreturn sr
        JOIN db_salesreturn sr2 ON sr2.id = sr.return_id
        WHERE sr.item_id = i.id AND sr2.status = 1
        
        UNION ALL
        
        SELECT CONCAT(pr2.created_date, ' ', COALESCE(pr2.created_time, '00:00:00')),
               0 - pr.return_qty,
               pr.id
        FROM db_purchaseitemsreturn pr
        JOIN db_purchasereturn pr2 ON pr2.id = pr.return_id
        WHERE pr.item_id = i.id AND pr2.status = 1
      ) all_transactions
    ) ranked
    WHERE rn = 1
  ) as stock_history_current_stock,
  
  -- Calculate discrepancy
  (
    i.stock - (
      SELECT new_quantity FROM (
        SELECT 
          new_quantity,
          ROW_NUMBER() OVER (ORDER BY transaction_date DESC, id DESC) as rn
        FROM (
          SELECT CONCAT(s.sales_date, ' ', COALESCE(s.created_time, '00:00:00')) as transaction_date,
                 0 - si.sales_qty as new_quantity,
                 si.id
          FROM db_salesitems si
          JOIN db_sales s ON s.id = si.sales_id
          WHERE si.item_id = i.id AND s.status = 1
          
          UNION ALL
          
          SELECT CONCAT(p.purchase_date, ' ', COALESCE(p.created_time, '00:00:00')),
                 pi.purchase_qty,
                 pi.id
          FROM db_purchaseitems pi
          JOIN db_purchase p ON p.id = pi.purchase_id
          WHERE pi.item_id = i.id AND p.status = 1
          
          UNION ALL
          
          SELECT CONCAT(sr2.created_date, ' ', COALESCE(sr2.created_time, '00:00:00')),
                 sr.return_qty,
                 sr.id
          FROM db_salesitemsreturn sr
          JOIN db_salesreturn sr2 ON sr2.id = sr.return_id
          WHERE sr.item_id = i.id AND sr2.status = 1
          
          UNION ALL
          
          SELECT CONCAT(pr2.created_date, ' ', COALESCE(pr2.created_time, '00:00:00')),
                 0 - pr.return_qty,
                 pr.id
          FROM db_purchaseitemsreturn pr
          JOIN db_purchasereturn pr2 ON pr2.id = pr.return_id
          WHERE pr.item_id = i.id AND pr2.status = 1
        ) all_transactions
      ) ranked
      WHERE rn = 1
    )
  ) as discrepancy

FROM db_items i
LEFT JOIN db_units u ON i.unit_id = u.id

WHERE ABS(
  i.stock - (
    SELECT new_quantity FROM (
      SELECT 
        new_quantity,
        ROW_NUMBER() OVER (ORDER BY transaction_date DESC, id DESC) as rn
      FROM (
        SELECT CONCAT(s.sales_date, ' ', COALESCE(s.created_time, '00:00:00')) as transaction_date,
               0 - si.sales_qty as new_quantity,
               si.id
        FROM db_salesitems si
        JOIN db_sales s ON s.id = si.sales_id
        WHERE si.item_id = i.id AND s.status = 1
        
        UNION ALL
        
        SELECT CONCAT(p.purchase_date, ' ', COALESCE(p.created_time, '00:00:00')),
               pi.purchase_qty,
               pi.id
        FROM db_purchaseitems pi
        JOIN db_purchase p ON p.id = pi.purchase_id
        WHERE pi.item_id = i.id AND p.status = 1
        
        UNION ALL
        
        SELECT CONCAT(sr2.created_date, ' ', COALESCE(sr2.created_time, '00:00:00')),
               sr.return_qty,
               sr.id
        FROM db_salesitemsreturn sr
        JOIN db_salesreturn sr2 ON sr2.id = sr.return_id
        WHERE sr.item_id = i.id AND sr2.status = 1
        
        UNION ALL
        
        SELECT CONCAT(pr2.created_date, ' ', COALESCE(pr2.created_time, '00:00:00')),
               0 - pr.return_qty,
               pr.id
        FROM db_purchaseitemsreturn pr
        JOIN db_purchasereturn pr2 ON pr2.id = pr.return_id
        WHERE pr.item_id = i.id AND pr2.status = 1
      ) all_transactions
    ) ranked
    WHERE rn = 1
  )
) > 0.01

ORDER BY ABS(
  i.stock - (
    SELECT new_quantity FROM (
      SELECT 
        new_quantity,
        ROW_NUMBER() OVER (ORDER BY transaction_date DESC, id DESC) as rn
      FROM (
        SELECT CONCAT(s.sales_date, ' ', COALESCE(s.created_time, '00:00:00')) as transaction_date,
               0 - si.sales_qty as new_quantity,
               si.id
        FROM db_salesitems si
        JOIN db_sales s ON s.id = si.sales_id
        WHERE si.item_id = i.id AND s.status = 1
        
        UNION ALL
        
        SELECT CONCAT(p.purchase_date, ' ', COALESCE(p.created_time, '00:00:00')),
               pi.purchase_qty,
               pi.id
        FROM db_purchaseitems pi
        JOIN db_purchase p ON p.id = pi.purchase_id
        WHERE pi.item_id = i.id AND p.status = 1
        
        UNION ALL
        
        SELECT CONCAT(sr2.created_date, ' ', COALESCE(sr2.created_time, '00:00:00')),
               sr.return_qty,
               sr.id
        FROM db_salesitemsreturn sr
        JOIN db_salesreturn sr2 ON sr2.id = sr.return_id
        WHERE sr.item_id = i.id AND sr2.status = 1
        
        UNION ALL
        
        SELECT CONCAT(pr2.created_date, ' ', COALESCE(pr2.created_time, '00:00:00')),
               0 - pr.return_qty,
               pr.id
        FROM db_purchaseitemsreturn pr
        JOIN db_purchasereturn pr2 ON pr2.id = pr.return_id
        WHERE pr.item_id = i.id AND pr2.status = 1
      ) all_transactions
    ) ranked
    WHERE rn = 1
  )
) DESC;

-- ============================================================================
-- SUMMARY
-- ============================================================================

SELECT '' as blank_line;
SELECT '===== SUMMARY =====' as summary;
SELECT '' as blank_line;

SELECT
  'Items with discrepancy' as metric,
  COUNT(DISTINCT i.id) as count,
  'Between Stock History and Items Table' as description
FROM db_items i
WHERE EXISTS (
  SELECT 1 FROM (
    SELECT 
      new_quantity,
      ROW_NUMBER() OVER (ORDER BY transaction_date DESC, id DESC) as rn
    FROM (
      SELECT CONCAT(s.sales_date, ' ', COALESCE(s.created_time, '00:00:00')) as transaction_date,
             0 - si.sales_qty as new_quantity,
             si.id
      FROM db_salesitems si
      JOIN db_sales s ON s.id = si.sales_id
      WHERE si.item_id = i.id AND s.status = 1
      
      UNION ALL
      
      SELECT CONCAT(p.purchase_date, ' ', COALESCE(p.created_time, '00:00:00')),
             pi.purchase_qty,
             pi.id
      FROM db_purchaseitems pi
      JOIN db_purchase p ON p.id = pi.purchase_id
      WHERE pi.item_id = i.id AND p.status = 1
      
      UNION ALL
      
      SELECT CONCAT(sr2.created_date, ' ', COALESCE(sr2.created_time, '00:00:00')),
             sr.return_qty,
             sr.id
      FROM db_salesitemsreturn sr
      JOIN db_salesreturn sr2 ON sr2.id = sr.return_id
      WHERE sr.item_id = i.id AND sr2.status = 1
      
      UNION ALL
      
      SELECT CONCAT(pr2.created_date, ' ', COALESCE(pr2.created_time, '00:00:00')),
             0 - pr.return_qty,
             pr.id
      FROM db_purchaseitemsreturn pr
      JOIN db_purchasereturn pr2 ON pr2.id = pr.return_id
      WHERE pr.item_id = i.id AND pr2.status = 1
    ) all_transactions
  ) ranked
  WHERE rn = 1
  AND ABS(i.stock - ranked.new_quantity) > 0.01
);

-- ============================================================================
-- END REPORT
-- ============================================================================

SELECT '' as blank_line;
SELECT '===== REPORT COMPLETE =====' as footer;
