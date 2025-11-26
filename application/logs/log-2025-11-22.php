<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

ERROR - 2025-11-22 19:25:06 --> Query error: You have an error in your SQL syntax; check the manual that corresponds to your MariaDB server version for the right syntax to use near '// Always negative for consumption
        ABS(im.qty) as absolute_quantity, ...' at line 1 - Invalid query: SELECT 'Production Consume' as type, -ABS(im.qty) as quantity_change, // Always negative for consumption
        ABS(im.qty) as absolute_quantity, im.created_at as transaction_date, CONCAT('PROD-', COALESCE(pb.batch_code, im.reference_id)) as reference_no, CONCAT('Batch: ', COALESCE(pb.batch_code, 'N/A')) as customer_supplier_info, im.id as source_id, 'production_consume' as source_table, im.id as detail_id, im.created_at as sort_date
FROM `inventory_movements` `im`
LEFT JOIN `production_batches` `pb` ON `pb`.`id` = `im`.`reference_id`
WHERE `im`.`item_id` = '1'
AND `im`.`type` = 'PRODUCTION_CONSUME'
ORDER BY `im`.`created_at` DESC, `im`.`id` DESC
ERROR - 2025-11-22 19:28:19 --> Query error: You have an error in your SQL syntax; check the manual that corresponds to your MariaDB server version for the right syntax to use near '// Always negative for consumption
        ABS(im.qty) as absolute_quantity, ...' at line 1 - Invalid query: SELECT 'Production Consume' as type, -ABS(im.qty) as quantity_change, // Always negative for consumption
        ABS(im.qty) as absolute_quantity, im.created_at as transaction_date, CONCAT('PROD-', COALESCE(pb.batch_code, im.reference_id)) as reference_no, CONCAT('Batch: ', COALESCE(pb.batch_code, 'N/A')) as customer_supplier_info, im.id as source_id, 'production_consume' as source_table, im.id as detail_id, im.created_at as sort_date
FROM `inventory_movements` `im`
LEFT JOIN `production_batches` `pb` ON `pb`.`id` = `im`.`reference_id`
WHERE `im`.`item_id` = '1'
AND `im`.`type` = 'PRODUCTION_CONSUME'
ORDER BY `im`.`created_at` DESC, `im`.`id` DESC
