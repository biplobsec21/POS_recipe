-- ============================================================================
-- SALES MODULE PERFORMANCE OPTIMIZATION FOR 100K+ RECORDS
-- ============================================================================
-- This script adds essential indexes and optimizations for the sales module
-- to handle large datasets efficiently.
-- ============================================================================

-- STEP 1: Add missing indexes on db_sales table for common queries
-- ============================================================================

-- Index for customer-based filtering (most common)
ALTER TABLE `db_sales` ADD INDEX `idx_sales_customer_id` (`customer_id`);

-- Index for date range queries
ALTER TABLE `db_sales` ADD INDEX `idx_sales_date` (`sales_date`);

-- Index for created_by (user-based filtering)
ALTER TABLE `db_sales` ADD INDEX `idx_sales_created_by` (`created_by`);

-- Composite index for common filter combinations (customer + status + date)
ALTER TABLE `db_sales` ADD INDEX `idx_sales_filters` (`customer_id`, `status`, `created_date`);

-- Index for payment status lookups
ALTER TABLE `db_sales` ADD INDEX `idx_sales_payment_status` (`payment_status`);

-- Index for sales status lookups
ALTER TABLE `db_sales` ADD INDEX `idx_sales_sales_status` (`sales_status`);


-- STEP 2: Add missing indexes on db_salespayments table
-- ============================================================================

-- Index on sales_id (critical for JOIN operations)
ALTER TABLE `db_salespayments` ADD INDEX `idx_salespayments_sales_id` (`sales_id`);

-- Index on payment date for filtering
ALTER TABLE `db_salespayments` ADD INDEX `idx_salespayments_payment_date` (`payment_date`);


-- STEP 3: Add missing indexes on db_customers table (for JOINs)
-- ============================================================================

-- These are already present, but verify:
-- idx_customers_status, idx_customers_mobile, idx_customers_name


-- STEP 4: Add missing indexes on related tables
-- ============================================================================

-- Index on db_salesitems.sales_id for faster lookups
ALTER TABLE `db_salesitems` ADD INDEX `idx_salesitems_sales_id_item` (`sales_id`, `item_id`);

-- Index on db_salesitems.item_id for join performance
ALTER TABLE `db_salesitems` ADD INDEX `idx_salesitems_item_id` (`item_id`);


-- STEP 5: Optimize table collations and storage
-- ============================================================================

-- Convert db_sales to InnoDB if not already (for better performance with large datasets)
-- ALTER TABLE `db_sales` ENGINE=InnoDB;

-- Verify and optimize character sets (already UTF8MB4)
-- ALTER TABLE `db_sales` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;


-- STEP 6: Analyze tables after index creation
-- ============================================================================

ANALYZE TABLE `db_sales`;
ANALYZE TABLE `db_salespayments`;
ANALYZE TABLE `db_customers`;
ANALYZE TABLE `db_salesitems`;

-- ============================================================================
-- INDEX VERIFICATION QUERIES
-- ============================================================================

-- Run these to verify indexes are created:
-- SHOW INDEXES FROM db_sales;
-- SHOW INDEXES FROM db_salespayments;
-- SHOW INDEXES FROM db_customers;
-- SHOW INDEXES FROM db_salesitems;

-- ============================================================================
-- OPTIONAL: Query Statistics (Enable for better optimization)
-- ============================================================================

-- Uncomment these if using MySQL 5.7+ for query optimization stats
-- SET GLOBAL innodb_stats_on_metadata = OFF;
-- SET SESSION innodb_stats_on_metadata = OFF;

-- ============================================================================
-- OPTIONAL: Archive old sales data if table is very large (>500k records)
-- ============================================================================

-- Consider creating archive table for sales older than 2 years:
-- CREATE TABLE `db_sales_archive` LIKE `db_sales`;
-- INSERT INTO `db_sales_archive` SELECT * FROM `db_sales` WHERE `created_date` < DATE_SUB(NOW(), INTERVAL 2 YEAR);
-- DELETE FROM `db_sales` WHERE `created_date` < DATE_SUB(NOW(), INTERVAL 2 YEAR);

-- ============================================================================
-- NOTES:
-- 1. Run these indexes during off-peak hours as they lock tables
-- 2. After applying indexes, clear query cache: FLUSH QUERY_CACHE;
-- 3. Monitor slow query log: SET GLOBAL slow_query_log = 'ON';
-- 4. Run ANALYZE regularly on large tables
-- ============================================================================
