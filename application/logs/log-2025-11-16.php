<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

ERROR - 2025-11-16 07:00:33 --> Severity: Warning --> mysqli::real_connect(): (HY000/2002): Connection refused /var/www/html/system/database/drivers/mysqli/mysqli_driver.php 201
ERROR - 2025-11-16 07:00:33 --> Unable to connect to the database
ERROR - 2025-11-16 07:00:34 --> Severity: Warning --> mysqli::real_connect(): (HY000/2002): Connection refused /var/www/html/system/database/drivers/mysqli/mysqli_driver.php 201
ERROR - 2025-11-16 07:00:34 --> Severity: Warning --> ini_set(): A session is active. You cannot change the session module's ini settings at this time /var/www/html/system/libraries/Session/Session_driver.php 205
ERROR - 2025-11-16 07:00:34 --> Severity: Warning --> session_start(): Failed to initialize storage module: user (path: ) /var/www/html/system/libraries/Session/Session.php 143
ERROR - 2025-11-16 07:00:34 --> Severity: Warning --> mysqli::real_connect(): (HY000/2002): Connection refused /var/www/html/system/database/drivers/mysqli/mysqli_driver.php 201
ERROR - 2025-11-16 07:00:34 --> Unable to connect to the database
ERROR - 2025-11-16 07:00:34 --> Query error: Connection refused - Invalid query: SELECT a.currency_name,a.currency,a.currency_code,a.symbol,b.currency_placement FROM db_currency a,db_sitesettings b WHERE a.id=b.currency_id AND b.id=1
ERROR - 2025-11-16 07:00:34 --> Severity: error --> Exception: Call to a member function row() on bool /var/www/html/application/core/MY_Controller.php 19
ERROR - 2025-11-16 14:34:07 --> Query error: Table 'adoralab_21eb_root.inventory_movements' doesn't exist - Invalid query: 
        SELECT COALESCE(SUM(ABS(im.qty)), 0) as total 
        FROM inventory_movements im 
        WHERE im.item_id = 507 
        AND im.type = 'PRODUCTION_CONSUME'
    
ERROR - 2025-11-16 14:34:07 --> Query error: Table 'adoralab_21eb_root.inventory_movements' doesn't exist - Invalid query: SELECT 'Production Consume' as type, im.qty as quantity_change, ABS(im.qty) as absolute_quantity, im.created_at as transaction_date, CONCAT('PROD-', COALESCE(pb.batch_code, im.reference_id)) as reference_no, CONCAT('Batch: ', COALESCE(pb.batch_code, 'N/A')) as customer_supplier_info, im.id as source_id, 'production_consume' as source_table, im.id as detail_id, im.created_at as sort_date
FROM `inventory_movements` `im`
LEFT JOIN `production_batches` `pb` ON `pb`.`id` = `im`.`reference_id`
WHERE `im`.`item_id` = '507'
AND `im`.`type` = 'PRODUCTION_CONSUME'
ORDER BY `im`.`created_at` DESC, `im`.`id` DESC
ERROR - 2025-11-16 14:34:07 --> Query error: Table 'adoralab_21eb_root.inventory_movements' doesn't exist - Invalid query: SELECT COALESCE(SUM(im.qty), 0) as total
FROM `inventory_movements` `im`
WHERE `im`.`item_id` = '507'
AND `im`.`created_at` < '2025-10-22'
ERROR - 2025-11-16 14:34:07 --> Severity: error --> Exception: Call to a member function row() on bool /var/www/html/application/models/Stock_history_model.php 354
ERROR - 2025-11-16 14:48:18 --> Query error: Table 'adoralab_21eb_root.inventory_movements' doesn't exist - Invalid query: 
        SELECT COALESCE(SUM(ABS(im.qty)), 0) as total 
        FROM inventory_movements im 
        WHERE im.item_id = 629 
        AND im.type = 'PRODUCTION_CONSUME'
    
ERROR - 2025-11-16 14:48:18 --> Query error: Table 'adoralab_21eb_root.inventory_movements' doesn't exist - Invalid query: SELECT 'Production Consume' as type, im.qty as quantity_change, ABS(im.qty) as absolute_quantity, im.created_at as transaction_date, CONCAT('PROD-', COALESCE(pb.batch_code, im.reference_id)) as reference_no, CONCAT('Batch: ', COALESCE(pb.batch_code, 'N/A')) as customer_supplier_info, im.id as source_id, 'production_consume' as source_table, im.id as detail_id, im.created_at as sort_date
FROM `inventory_movements` `im`
LEFT JOIN `production_batches` `pb` ON `pb`.`id` = `im`.`reference_id`
WHERE `im`.`item_id` = '629'
AND `im`.`type` = 'PRODUCTION_CONSUME'
ORDER BY `im`.`created_at` DESC, `im`.`id` DESC
ERROR - 2025-11-16 14:48:18 --> Query error: Table 'adoralab_21eb_root.inventory_movements' doesn't exist - Invalid query: SELECT COALESCE(SUM(im.qty), 0) as total
FROM `inventory_movements` `im`
WHERE `im`.`item_id` = '629'
AND `im`.`created_at` < '2025-11-03'
ERROR - 2025-11-16 14:48:19 --> Severity: error --> Exception: Call to a member function row() on bool /var/www/html/application/models/Stock_history_model.php 354
