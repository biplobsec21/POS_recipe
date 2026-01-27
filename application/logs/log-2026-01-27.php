<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

ERROR - 2026-01-27 07:05:19 --> 404 Page Not Found: Theme/plugins
ERROR - 2026-01-27 07:06:56 --> 404 Page Not Found: Theme/plugins
ERROR - 2026-01-27 07:11:47 --> 404 Page Not Found: Theme/plugins
ERROR - 2026-01-27 07:12:09 --> 404 Page Not Found: Theme/plugins
ERROR - 2026-01-27 07:14:36 --> 404 Page Not Found: Theme/plugins
ERROR - 2026-01-27 07:14:53 --> 404 Page Not Found: Theme/plugins
ERROR - 2026-01-27 07:19:13 --> 404 Page Not Found: Theme/plugins
ERROR - 2026-01-27 07:23:05 --> 404 Page Not Found: Theme/plugins
ERROR - 2026-01-27 07:23:14 --> 404 Page Not Found: Theme/plugins
ERROR - 2026-01-27 07:23:28 --> 404 Page Not Found: Theme/plugins
ERROR - 2026-01-27 07:24:14 --> 404 Page Not Found: Theme/plugins
ERROR - 2026-01-27 07:24:47 --> 404 Page Not Found: Theme/plugins
ERROR - 2026-01-27 07:24:53 --> 404 Page Not Found: Theme/plugins
ERROR - 2026-01-27 07:27:24 --> 404 Page Not Found: Theme/plugins
ERROR - 2026-01-27 07:27:32 --> 404 Page Not Found: Theme/plugins
ERROR - 2026-01-27 07:27:38 --> 404 Page Not Found: Theme/plugins
ERROR - 2026-01-27 07:27:50 --> 404 Page Not Found: Theme/plugins
ERROR - 2026-01-27 07:28:15 --> 404 Page Not Found: Theme/plugins
ERROR - 2026-01-27 07:28:35 --> 404 Page Not Found: Theme/plugins
ERROR - 2026-01-27 08:09:40 --> 404 Page Not Found: Theme/plugins
ERROR - 2026-01-27 08:09:40 --> 404 Page Not Found: Theme/plugins
ERROR - 2026-01-27 14:09:42 --> Query error: Table 'c_attendance.inventory_movements' doesn't exist - Invalid query: 
        SELECT COALESCE(SUM(ABS(im.qty)), 0) as total 
        FROM inventory_movements im 
        WHERE im.item_id = 629 
        AND im.type = 'PRODUCTION_CONSUME'
    
ERROR - 2026-01-27 14:09:42 --> Query error: Table 'c_attendance.inventory_movements' doesn't exist - Invalid query: SELECT 'Production Consume' as type, -ABS(im.qty) as quantity_change, ABS(im.qty) as absolute_quantity, im.created_at as transaction_date, CONCAT('PROD-', COALESCE(pb.batch_code, im.reference_id)) as reference_no, CONCAT('Batch: ', COALESCE(pb.batch_code, 'N/A')) as customer_supplier_info, im.id as source_id, 'production_consume' as source_table, im.id as detail_id, im.created_at as sort_date
FROM `inventory_movements` `im`
LEFT JOIN `production_batches` `pb` ON `pb`.`id` = `im`.`reference_id`
WHERE `im`.`item_id` = '629'
AND `im`.`type` = 'PRODUCTION_CONSUME'
ERROR - 2026-01-27 08:09:42 --> 404 Page Not Found: Theme/plugins
ERROR - 2026-01-27 08:09:47 --> 404 Page Not Found: Theme/plugins
ERROR - 2026-01-27 08:09:49 --> 404 Page Not Found: Theme/plugins
ERROR - 2026-01-27 08:09:59 --> 404 Page Not Found: Theme/plugins
ERROR - 2026-01-27 08:10:19 --> 404 Page Not Found: Theme/plugins
ERROR - 2026-01-27 08:10:31 --> 404 Page Not Found: Theme/plugins
ERROR - 2026-01-27 14:10:31 --> Query error: Table 'c_attendance.recipes' doesn't exist - Invalid query: SELECT `recipes`.*, `i`.`item_name` as `output_product_name`
FROM `recipes`
LEFT JOIN `db_items` as `i` ON `i`.`id` = `recipes`.`output_product_id`
ORDER BY `id` DESC
 LIMIT 10
ERROR - 2026-01-27 14:10:31 --> Severity: error --> Exception: Call to a member function result() on bool /Users/hello/Herd/21eb.ramksofttech.com/application/models/Recipe_model.php 119
ERROR - 2026-01-27 08:10:47 --> 404 Page Not Found: Theme/plugins
ERROR - 2026-01-27 08:10:47 --> 404 Page Not Found: Theme/plugins
ERROR - 2026-01-27 08:11:36 --> 404 Page Not Found: Theme/plugins
ERROR - 2026-01-27 08:11:36 --> 404 Page Not Found: Theme/plugins
ERROR - 2026-01-27 08:11:50 --> 404 Page Not Found: Theme/plugins
ERROR - 2026-01-27 14:11:51 --> Query error: Table 'c_attendance.recipes' doesn't exist - Invalid query: SELECT `recipes`.*, `i`.`item_name` as `output_product_name`
FROM `recipes`
LEFT JOIN `db_items` as `i` ON `i`.`id` = `recipes`.`output_product_id`
ORDER BY `id` DESC
 LIMIT 10
ERROR - 2026-01-27 14:11:51 --> Severity: error --> Exception: Call to a member function result() on bool /Users/hello/Herd/21eb.ramksofttech.com/application/models/Recipe_model.php 119
ERROR - 2026-01-27 08:11:56 --> 404 Page Not Found: Theme/plugins
ERROR - 2026-01-27 08:12:02 --> 404 Page Not Found: Theme/plugins
ERROR - 2026-01-27 14:12:02 --> Query error: Table 'c_attendance.recipes' doesn't exist - Invalid query: SELECT `recipes`.*, `i`.`item_name` as `output_product_name`
FROM `recipes`
LEFT JOIN `db_items` as `i` ON `i`.`id` = `recipes`.`output_product_id`
ORDER BY `id` DESC
 LIMIT 10
ERROR - 2026-01-27 14:12:02 --> Severity: error --> Exception: Call to a member function result() on bool /Users/hello/Herd/21eb.ramksofttech.com/application/models/Recipe_model.php 119
