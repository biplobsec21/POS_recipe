<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

ERROR - 2025-11-26 13:26:50 --> Query error: You have an error in your SQL syntax; check the manual that corresponds to your MariaDB server version for the right syntax to use near '// Always negative for consumption
        ABS(im.qty) as absolute_quantity, ...' at line 1 - Invalid query: SELECT 'Production Consume' as type, -ABS(im.qty) as quantity_change, // Always negative for consumption
        ABS(im.qty) as absolute_quantity, im.created_at as transaction_date, CONCAT('PROD-', COALESCE(pb.batch_code, im.reference_id)) as reference_no, CONCAT('Batch: ', COALESCE(pb.batch_code, 'N/A')) as customer_supplier_info, im.id as source_id, 'production_consume' as source_table, im.id as detail_id, im.created_at as sort_date
FROM `inventory_movements` `im`
LEFT JOIN `production_batches` `pb` ON `pb`.`id` = `im`.`reference_id`
WHERE `im`.`item_id` = '518'
AND `im`.`type` = 'PRODUCTION_CONSUME'
ORDER BY `im`.`created_at` DESC, `im`.`id` DESC
ERROR - 2025-11-26 13:27:19 --> Query error: You have an error in your SQL syntax; check the manual that corresponds to your MariaDB server version for the right syntax to use near '// Always negative for consumption
        ABS(im.qty) as absolute_quantity, ...' at line 1 - Invalid query: SELECT 'Production Consume' as type, -ABS(im.qty) as quantity_change, // Always negative for consumption
        ABS(im.qty) as absolute_quantity, im.created_at as transaction_date, CONCAT('PROD-', COALESCE(pb.batch_code, im.reference_id)) as reference_no, CONCAT('Batch: ', COALESCE(pb.batch_code, 'N/A')) as customer_supplier_info, im.id as source_id, 'production_consume' as source_table, im.id as detail_id, im.created_at as sort_date
FROM `inventory_movements` `im`
LEFT JOIN `production_batches` `pb` ON `pb`.`id` = `im`.`reference_id`
WHERE `im`.`item_id` = '517'
AND `im`.`type` = 'PRODUCTION_CONSUME'
ORDER BY `im`.`created_at` DESC, `im`.`id` DESC
ERROR - 2025-11-26 13:59:52 --> Query error: You have an error in your SQL syntax; check the manual that corresponds to your MariaDB server version for the right syntax to use near '// Ensure negative value
        ABS(im.qty) as absolute_quantity, im.created...' at line 1 - Invalid query: SELECT 'Production Consume' as type, -ABS(im.qty) as quantity_change, // Ensure negative value
        ABS(im.qty) as absolute_quantity, im.created_at as transaction_date, CONCAT('PROD-', COALESCE(pb.batch_code, im.reference_id)) as reference_no, CONCAT('Batch: ', COALESCE(pb.batch_code, 'N/A')) as customer_supplier_info, im.id as source_id, 'production_consume' as source_table, im.id as detail_id, im.created_at as sort_date
FROM `inventory_movements` `im`
LEFT JOIN `production_batches` `pb` ON `pb`.`id` = `im`.`reference_id`
WHERE `im`.`item_id` = '2'
AND `im`.`type` = 'PRODUCTION_CONSUME'
ORDER BY `im`.`created_at` DESC, `im`.`id` DESC
ERROR - 2025-11-26 14:00:17 --> Query error: You have an error in your SQL syntax; check the manual that corresponds to your MariaDB server version for the right syntax to use near '// Ensure negative value
        ABS(im.qty) as absolute_quantity, im.created...' at line 1 - Invalid query: SELECT 'Production Consume' as type, -ABS(im.qty) as quantity_change, // Ensure negative value
        ABS(im.qty) as absolute_quantity, im.created_at as transaction_date, CONCAT('PROD-', COALESCE(pb.batch_code, im.reference_id)) as reference_no, CONCAT('Batch: ', COALESCE(pb.batch_code, 'N/A')) as customer_supplier_info, im.id as source_id, 'production_consume' as source_table, im.id as detail_id, im.created_at as sort_date
FROM `inventory_movements` `im`
LEFT JOIN `production_batches` `pb` ON `pb`.`id` = `im`.`reference_id`
WHERE `im`.`item_id` = '518'
AND `im`.`type` = 'PRODUCTION_CONSUME'
ORDER BY `im`.`created_at` DESC, `im`.`id` DESC
ERROR - 2025-11-26 14:00:24 --> Query error: You have an error in your SQL syntax; check the manual that corresponds to your MariaDB server version for the right syntax to use near '// Ensure negative value
        ABS(im.qty) as absolute_quantity, im.created...' at line 1 - Invalid query: SELECT 'Production Consume' as type, -ABS(im.qty) as quantity_change, // Ensure negative value
        ABS(im.qty) as absolute_quantity, im.created_at as transaction_date, CONCAT('PROD-', COALESCE(pb.batch_code, im.reference_id)) as reference_no, CONCAT('Batch: ', COALESCE(pb.batch_code, 'N/A')) as customer_supplier_info, im.id as source_id, 'production_consume' as source_table, im.id as detail_id, im.created_at as sort_date
FROM `inventory_movements` `im`
LEFT JOIN `production_batches` `pb` ON `pb`.`id` = `im`.`reference_id`
WHERE `im`.`item_id` = '1'
AND `im`.`type` = 'PRODUCTION_CONSUME'
ORDER BY `im`.`created_at` DESC, `im`.`id` DESC
ERROR - 2025-11-26 14:01:27 --> Query error: You have an error in your SQL syntax; check the manual that corresponds to your MariaDB server version for the right syntax to use near '// Ensure negative value
        ABS(im.qty) as absolute_quantity, im.created...' at line 1 - Invalid query: SELECT 'Production Consume' as type, -ABS(im.qty) as quantity_change, // Ensure negative value
        ABS(im.qty) as absolute_quantity, im.created_at as transaction_date, CONCAT('PROD-', COALESCE(pb.batch_code, im.reference_id)) as reference_no, CONCAT('Batch: ', COALESCE(pb.batch_code, 'N/A')) as customer_supplier_info, im.id as source_id, 'production_consume' as source_table, im.id as detail_id, im.created_at as sort_date
FROM `inventory_movements` `im`
LEFT JOIN `production_batches` `pb` ON `pb`.`id` = `im`.`reference_id`
WHERE `im`.`item_id` = '1'
AND `im`.`type` = 'PRODUCTION_CONSUME'
ORDER BY `im`.`created_at` DESC, `im`.`id` DESC
ERROR - 2025-11-26 14:01:59 --> Query error: You have an error in your SQL syntax; check the manual that corresponds to your MariaDB server version for the right syntax to use near '// Ensure negative value
        ABS(im.qty) as absolute_quantity, im.created...' at line 1 - Invalid query: SELECT 'Production Consume' as type, -ABS(im.qty) as quantity_change, // Ensure negative value
        ABS(im.qty) as absolute_quantity, im.created_at as transaction_date, CONCAT('PROD-', COALESCE(pb.batch_code, im.reference_id)) as reference_no, CONCAT('Batch: ', COALESCE(pb.batch_code, 'N/A')) as customer_supplier_info, im.id as source_id, 'production_consume' as source_table, im.id as detail_id, im.created_at as sort_date
FROM `inventory_movements` `im`
LEFT JOIN `production_batches` `pb` ON `pb`.`id` = `im`.`reference_id`
WHERE `im`.`item_id` = '516'
AND `im`.`type` = 'PRODUCTION_CONSUME'
ORDER BY `im`.`created_at` DESC, `im`.`id` DESC
ERROR - 2025-11-26 14:05:41 --> Query error: Unknown column 'se.created_time' in 'SELECT' - Invalid query: SELECT CASE 
            WHEN se.qty > 0 THEN 'Stock In'
            ELSE 'Stock Out'
        END as type, se.qty as quantity_change, ABS(se.qty) as absolute_quantity, se.entry_date as transaction_date, CONCAT('STK-', se.id) as reference_no, COALESCE(se.note, 'Stock Adjustment') as customer_supplier_info, se.id as source_id, 'stock_entry' as source_table, se.id as detail_id, CONCAT(se.entry_date, ' ', COALESCE(se.created_time, '00:00:00')) as sort_date
FROM `db_stockentry` `se`
WHERE `se`.`item_id` = '1'
AND `se`.`status` = 1
AND `se`.`note` NOT LIKE '%Production Consumption - Batch:%'
ERROR - 2025-11-26 14:06:07 --> Query error: Unknown column 'se.created_time' in 'SELECT' - Invalid query: SELECT CASE 
            WHEN se.qty > 0 THEN 'Stock In'
            ELSE 'Stock Out'
        END as type, se.qty as quantity_change, ABS(se.qty) as absolute_quantity, se.entry_date as transaction_date, CONCAT('STK-', se.id) as reference_no, COALESCE(se.note, 'Stock Adjustment') as customer_supplier_info, se.id as source_id, 'stock_entry' as source_table, se.id as detail_id, CONCAT(se.entry_date, ' ', COALESCE(se.created_time, '00:00:00')) as sort_date
FROM `db_stockentry` `se`
WHERE `se`.`item_id` = '1'
AND `se`.`status` = 1
AND `se`.`note` NOT LIKE '%Production Consumption - Batch:%'
ERROR - 2025-11-26 14:06:28 --> Query error: You have an error in your SQL syntax; check the manual that corresponds to your MariaDB server version for the right syntax to use near '// Ensure negative value
        ABS(im.qty) as absolute_quantity, im.created...' at line 1 - Invalid query: SELECT 'Production Consume' as type, -ABS(im.qty) as quantity_change, // Ensure negative value
        ABS(im.qty) as absolute_quantity, im.created_at as transaction_date, CONCAT('PROD-', COALESCE(pb.batch_code, im.reference_id)) as reference_no, CONCAT('Batch: ', COALESCE(pb.batch_code, 'N/A')) as customer_supplier_info, im.id as source_id, 'production_consume' as source_table, im.id as detail_id, im.created_at as sort_date
FROM `inventory_movements` `im`
LEFT JOIN `production_batches` `pb` ON `pb`.`id` = `im`.`reference_id`
WHERE `im`.`item_id` = '2'
AND `im`.`type` = 'PRODUCTION_CONSUME'
ORDER BY `im`.`created_at` DESC, `im`.`id` DESC
ERROR - 2025-11-26 14:06:45 --> Query error: Unknown column 'se.created_time' in 'SELECT' - Invalid query: SELECT CASE 
            WHEN se.qty > 0 THEN 'Stock In'
            ELSE 'Stock Out'
        END as type, se.qty as quantity_change, ABS(se.qty) as absolute_quantity, se.entry_date as transaction_date, CONCAT('STK-', se.id) as reference_no, COALESCE(se.note, 'Stock Adjustment') as customer_supplier_info, se.id as source_id, 'stock_entry' as source_table, se.id as detail_id, CONCAT(se.entry_date, ' ', COALESCE(se.created_time, '00:00:00')) as sort_date
FROM `db_stockentry` `se`
WHERE `se`.`item_id` = '2'
AND `se`.`status` = 1
AND `se`.`note` NOT LIKE '%Production Consumption - Batch:%'
ERROR - 2025-11-26 14:07:02 --> Query error: You have an error in your SQL syntax; check the manual that corresponds to your MariaDB server version for the right syntax to use near '// Ensure negative value
        ABS(im.qty) as absolute_quantity, im.created...' at line 1 - Invalid query: SELECT 'Production Consume' as type, -ABS(im.qty) as quantity_change, // Ensure negative value
        ABS(im.qty) as absolute_quantity, im.created_at as transaction_date, CONCAT('PROD-', COALESCE(pb.batch_code, im.reference_id)) as reference_no, CONCAT('Batch: ', COALESCE(pb.batch_code, 'N/A')) as customer_supplier_info, im.id as source_id, 'production_consume' as source_table, im.id as detail_id, im.created_at as sort_date
FROM `inventory_movements` `im`
LEFT JOIN `production_batches` `pb` ON `pb`.`id` = `im`.`reference_id`
WHERE `im`.`item_id` = '1'
AND `im`.`type` = 'PRODUCTION_CONSUME'
ORDER BY `im`.`created_at` DESC, `im`.`id` DESC
ERROR - 2025-11-26 14:09:24 --> Query error: Unknown column 'se.created_time' in 'SELECT' - Invalid query: SELECT CASE 
            WHEN se.qty > 0 THEN 'Stock In'
            ELSE 'Stock Out'
        END as type, se.qty as quantity_change, ABS(se.qty) as absolute_quantity, se.entry_date as transaction_date, CONCAT('STK-', se.id) as reference_no, COALESCE(se.note, 'Stock Adjustment') as customer_supplier_info, se.id as source_id, 'stock_entry' as source_table, se.id as detail_id, CONCAT(se.entry_date, ' ', COALESCE(se.created_time, '00:00:00')) as sort_date
FROM `db_stockentry` `se`
WHERE `se`.`item_id` = '1'
AND `se`.`status` = 1
AND (`se`.`note` NOT LIKE '%Production Consumption - Batch:%' OR `se`.`note` IS NULL)
ERROR - 2025-11-26 14:09:45 --> Query error: Unknown column 'se.created_time' in 'SELECT' - Invalid query: SELECT CASE 
            WHEN se.qty > 0 THEN 'Stock In'
            ELSE 'Stock Out'
        END as type, se.qty as quantity_change, ABS(se.qty) as absolute_quantity, se.entry_date as transaction_date, CONCAT('STK-', se.id) as reference_no, COALESCE(se.note, 'Stock Adjustment') as customer_supplier_info, se.id as source_id, 'stock_entry' as source_table, se.id as detail_id, CONCAT(se.entry_date, ' ', COALESCE(se.created_time, '00:00:00')) as sort_date
FROM `db_stockentry` `se`
WHERE `se`.`item_id` = '1'
AND `se`.`status` = 1
AND (`se`.`note` NOT LIKE '%Production Consumption - Batch:%' OR `se`.`note` IS NULL)
ERROR - 2025-11-26 14:10:11 --> Query error: Unknown column 'se.created_time' in 'SELECT' - Invalid query: SELECT CASE 
            WHEN se.qty > 0 THEN 'Stock In'
            ELSE 'Stock Out'
        END as type, se.qty as quantity_change, ABS(se.qty) as absolute_quantity, se.entry_date as transaction_date, CONCAT('STK-', se.id) as reference_no, COALESCE(se.note, 'Stock Adjustment') as customer_supplier_info, se.id as source_id, 'stock_entry' as source_table, se.id as detail_id, CONCAT(se.entry_date, ' ', COALESCE(se.created_time, '00:00:00')) as sort_date
FROM `db_stockentry` `se`
WHERE `se`.`item_id` = '2'
AND `se`.`status` = 1
AND (`se`.`note` NOT LIKE '%Production Consumption - Batch:%' OR `se`.`note` IS NULL)
ERROR - 2025-11-26 14:10:37 --> Query error: Unknown column 'se.created_time' in 'SELECT' - Invalid query: SELECT CASE 
            WHEN se.qty > 0 THEN 'Stock In'
            ELSE 'Stock Out'
        END as type, se.qty as quantity_change, ABS(se.qty) as absolute_quantity, se.entry_date as transaction_date, CONCAT('STK-', se.id) as reference_no, COALESCE(se.note, 'Stock Adjustment') as customer_supplier_info, se.id as source_id, 'stock_entry' as source_table, se.id as detail_id, CONCAT(se.entry_date, ' ', COALESCE(se.created_time, '00:00:00')) as sort_date
FROM `db_stockentry` `se`
WHERE `se`.`item_id` = '518'
AND `se`.`status` = 1
AND (`se`.`note` NOT LIKE '%Production Consumption - Batch:%' OR `se`.`note` IS NULL)
ERROR - 2025-11-26 14:13:33 --> Query error: Unknown column 'se.created_time' in 'SELECT' - Invalid query: SELECT CASE 
            WHEN se.note LIKE '%Opening Stock%' OR se.note = '' OR se.note IS NULL THEN 'Opening Stock'
            WHEN se.qty > 0 THEN 'Stock In'
            ELSE 'Stock Out'
        END as type, se.qty as quantity_change, ABS(se.qty) as absolute_quantity, se.entry_date as transaction_date, CONCAT('STK-', se.id) as reference_no, COALESCE(se.note, 'Opening Stock') as customer_supplier_info, se.id as source_id, 'stock_entry' as source_table, se.id as detail_id, CONCAT(se.entry_date, ' ', COALESCE(se.created_time, '00:00:00')) as sort_date
FROM `db_stockentry` `se`
WHERE `se`.`item_id` = '2'
AND `se`.`status` = 1
AND (`se`.`note` NOT LIKE '%Production Consumption - Batch:%' OR `se`.`note` IS NULL)
ERROR - 2025-11-26 14:13:38 --> Query error: Unknown column 'se.created_time' in 'SELECT' - Invalid query: SELECT CASE 
            WHEN se.note LIKE '%Opening Stock%' OR se.note = '' OR se.note IS NULL THEN 'Opening Stock'
            WHEN se.qty > 0 THEN 'Stock In'
            ELSE 'Stock Out'
        END as type, se.qty as quantity_change, ABS(se.qty) as absolute_quantity, se.entry_date as transaction_date, CONCAT('STK-', se.id) as reference_no, COALESCE(se.note, 'Opening Stock') as customer_supplier_info, se.id as source_id, 'stock_entry' as source_table, se.id as detail_id, CONCAT(se.entry_date, ' ', COALESCE(se.created_time, '00:00:00')) as sort_date
FROM `db_stockentry` `se`
WHERE `se`.`item_id` = '2'
AND `se`.`status` = 1
AND (`se`.`note` NOT LIKE '%Production Consumption - Batch:%' OR `se`.`note` IS NULL)
ERROR - 2025-11-26 14:13:40 --> Query error: Unknown column 'se.created_time' in 'SELECT' - Invalid query: SELECT CASE 
            WHEN se.note LIKE '%Opening Stock%' OR se.note = '' OR se.note IS NULL THEN 'Opening Stock'
            WHEN se.qty > 0 THEN 'Stock In'
            ELSE 'Stock Out'
        END as type, se.qty as quantity_change, ABS(se.qty) as absolute_quantity, se.entry_date as transaction_date, CONCAT('STK-', se.id) as reference_no, COALESCE(se.note, 'Opening Stock') as customer_supplier_info, se.id as source_id, 'stock_entry' as source_table, se.id as detail_id, CONCAT(se.entry_date, ' ', COALESCE(se.created_time, '00:00:00')) as sort_date
FROM `db_stockentry` `se`
WHERE `se`.`item_id` = '2'
AND `se`.`status` = 1
AND (`se`.`note` NOT LIKE '%Production Consumption - Batch:%' OR `se`.`note` IS NULL)
ERROR - 2025-11-26 14:15:24 --> Query error: You have an error in your SQL syntax; check the manual that corresponds to your MariaDB server version for the right syntax to use near 'FROM `inventory_movements` `im`
LEFT JOIN `production_batches` `pb` ON `pb`.`...' at line 2 - Invalid query: SELECT 'Production Consume' as type, CASE WHEN im.qty < 0 THEN im.qty -- If already negative, keep as is ELSE -im.qty -- If positive, make negative END as quantity_change, ABS(im.qty) as absolute_quantity, im.created_at as transaction_date, CONCAT('PROD-', COALESCE(pb.batch_code, im.reference_id)) as reference_no, CONCAT('Batch: ', COALESCE(pb.batch_code, 'N/A')) as customer_supplier_info, im.id as source_id, 'production_consume' as source_table, im.id as detail_id, im.created_at as sort_date
FROM `inventory_movements` `im`
LEFT JOIN `production_batches` `pb` ON `pb`.`id` = `im`.`reference_id`
WHERE `im`.`item_id` = '2'
AND `im`.`type` = 'PRODUCTION_CONSUME'
ORDER BY `im`.`created_at` DESC, `im`.`id` DESC
ERROR - 2025-11-26 14:15:38 --> Query error: You have an error in your SQL syntax; check the manual that corresponds to your MariaDB server version for the right syntax to use near 'FROM `inventory_movements` `im`
LEFT JOIN `production_batches` `pb` ON `pb`.`...' at line 2 - Invalid query: SELECT 'Production Consume' as type, CASE WHEN im.qty < 0 THEN im.qty -- If already negative, keep as is ELSE -im.qty -- If positive, make negative END as quantity_change, ABS(im.qty) as absolute_quantity, im.created_at as transaction_date, CONCAT('PROD-', COALESCE(pb.batch_code, im.reference_id)) as reference_no, CONCAT('Batch: ', COALESCE(pb.batch_code, 'N/A')) as customer_supplier_info, im.id as source_id, 'production_consume' as source_table, im.id as detail_id, im.created_at as sort_date
FROM `inventory_movements` `im`
LEFT JOIN `production_batches` `pb` ON `pb`.`id` = `im`.`reference_id`
WHERE `im`.`item_id` = '1'
AND `im`.`type` = 'PRODUCTION_CONSUME'
ORDER BY `im`.`created_at` DESC, `im`.`id` DESC
ERROR - 2025-11-26 14:15:38 --> Stock calculation mismatch for item 1. DB: 88.00, Calculated: 62
ERROR - 2025-11-26 14:15:55 --> Query error: You have an error in your SQL syntax; check the manual that corresponds to your MariaDB server version for the right syntax to use near 'FROM `inventory_movements` `im`
LEFT JOIN `production_batches` `pb` ON `pb`.`...' at line 2 - Invalid query: SELECT 'Production Consume' as type, CASE WHEN im.qty < 0 THEN im.qty -- If already negative, keep as is ELSE -im.qty -- If positive, make negative END as quantity_change, ABS(im.qty) as absolute_quantity, im.created_at as transaction_date, CONCAT('PROD-', COALESCE(pb.batch_code, im.reference_id)) as reference_no, CONCAT('Batch: ', COALESCE(pb.batch_code, 'N/A')) as customer_supplier_info, im.id as source_id, 'production_consume' as source_table, im.id as detail_id, im.created_at as sort_date
FROM `inventory_movements` `im`
LEFT JOIN `production_batches` `pb` ON `pb`.`id` = `im`.`reference_id`
WHERE `im`.`item_id` = '1'
AND `im`.`type` = 'PRODUCTION_CONSUME'
ORDER BY `im`.`created_at` DESC, `im`.`id` DESC
ERROR - 2025-11-26 14:15:55 --> Stock calculation mismatch for item 1. DB: 88.00, Calculated: 62
ERROR - 2025-11-26 14:16:12 --> Query error: You have an error in your SQL syntax; check the manual that corresponds to your MariaDB server version for the right syntax to use near 'FROM `inventory_movements` `im`
LEFT JOIN `production_batches` `pb` ON `pb`.`...' at line 2 - Invalid query: SELECT 'Production Consume' as type, CASE WHEN im.qty < 0 THEN im.qty -- If already negative, keep as is ELSE -im.qty -- If positive, make negative END as quantity_change, ABS(im.qty) as absolute_quantity, im.created_at as transaction_date, CONCAT('PROD-', COALESCE(pb.batch_code, im.reference_id)) as reference_no, CONCAT('Batch: ', COALESCE(pb.batch_code, 'N/A')) as customer_supplier_info, im.id as source_id, 'production_consume' as source_table, im.id as detail_id, im.created_at as sort_date
FROM `inventory_movements` `im`
LEFT JOIN `production_batches` `pb` ON `pb`.`id` = `im`.`reference_id`
WHERE `im`.`item_id` = '516'
AND `im`.`type` = 'PRODUCTION_CONSUME'
ORDER BY `im`.`created_at` DESC, `im`.`id` DESC
ERROR - 2025-11-26 14:16:12 --> Stock calculation mismatch for item 516. DB: 50.00, Calculated: 0
