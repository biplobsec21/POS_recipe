-- Damage Management Schema for piooneer_testing
-- Compatible with the current MariaDB/MySQL structure in this project.
-- This script adds a damage header/detail model and links it to the existing stock movement table.

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- -----------------------------------------------------------------------------
-- 1) Damage header table
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `db_damage` (
  `id` INT(50) NOT NULL AUTO_INCREMENT,
  `damage_code` VARCHAR(50) NOT NULL,
  `damage_date` DATE NOT NULL,
  `warehouse_id` INT(5) DEFAULT NULL,
  `company_id` INT(5) DEFAULT NULL,
  `damage_type` VARCHAR(50) NOT NULL DEFAULT 'general' COMMENT 'broken, expired, defective, spoiled, lost',
  `reason` VARCHAR(255) DEFAULT NULL,
  `note` TEXT DEFAULT NULL,
  `status` VARCHAR(20) NOT NULL DEFAULT 'draft' COMMENT 'draft,pending,approved,rejected,cancelled',
  `total_qty` DECIMAL(20,2) NOT NULL DEFAULT 0.00,
  `total_value` DECIMAL(20,2) NOT NULL DEFAULT 0.00,
  `created_date` DATE DEFAULT NULL,
  `created_time` VARCHAR(50) DEFAULT NULL,
  `created_by` INT(5) DEFAULT NULL,
  `approved_by` INT(5) DEFAULT NULL,
  `approved_at` DATETIME DEFAULT NULL,
  `system_ip` VARCHAR(100) DEFAULT NULL,
  `system_name` VARCHAR(100) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_damage_code` (`damage_code`),
  KEY `idx_damage_date` (`damage_date`),
  KEY `idx_damage_status` (`status`),
  KEY `idx_damage_company` (`company_id`),
  CONSTRAINT `fk_damage_warehouse`
    FOREIGN KEY (`warehouse_id`) REFERENCES `db_warehouse` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_damage_company`
    FOREIGN KEY (`company_id`) REFERENCES `db_company` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_damage_created_by`
    FOREIGN KEY (`created_by`) REFERENCES `db_users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_damage_approved_by`
    FOREIGN KEY (`approved_by`) REFERENCES `db_users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- 2) Damage detail table
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `db_damageitems` (
  `id` INT(50) NOT NULL AUTO_INCREMENT,
  `damage_id` INT(50) NOT NULL,
  `item_id` INT(50) NOT NULL,
  `purchase_id` INT(50) DEFAULT NULL,
  `sales_id` INT(50) DEFAULT NULL,
  `stock_before` DECIMAL(20,2) NOT NULL DEFAULT 0.00,
  `damage_qty` DECIMAL(20,2) NOT NULL DEFAULT 0.00,
  `unit_cost` DECIMAL(20,2) NOT NULL DEFAULT 0.00,
  `total_value` DECIMAL(20,2) NOT NULL DEFAULT 0.00,
  `reason` VARCHAR(255) DEFAULT NULL,
  `note` TEXT DEFAULT NULL,
  `status` VARCHAR(20) NOT NULL DEFAULT 'draft' COMMENT 'draft,pending,approved,rejected',
  `created_at` DATETIME DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_damageitems_damage_id` (`damage_id`),
  KEY `idx_damageitems_item_id` (`item_id`),
  CONSTRAINT `fk_damageitems_damage`
    FOREIGN KEY (`damage_id`) REFERENCES `db_damage` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_damageitems_item`
    FOREIGN KEY (`item_id`) REFERENCES `db_items` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `fk_damageitems_purchase`
    FOREIGN KEY (`purchase_id`) REFERENCES `db_purchase` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_damageitems_sales`
    FOREIGN KEY (`sales_id`) REFERENCES `db_sales` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- 3) Link damage to existing stock movement history
--    This allows the current inventory_movements table to track damage events.
-- -----------------------------------------------------------------------------
ALTER TABLE `inventory_movements`
  ADD COLUMN IF NOT EXISTS `damage_id` INT(50) NULL AFTER `reference_id`,
  ADD KEY `idx_inventory_movements_damage_id` (`damage_id`),
  ADD CONSTRAINT `fk_inventory_movements_damage`
    FOREIGN KEY (`damage_id`) REFERENCES `db_damage` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

SET FOREIGN_KEY_CHECKS = 1;

-- -----------------------------------------------------------------------------
-- Notes for the application layer
-- -----------------------------------------------------------------------------
-- 1. When a damage entry is approved, the app should:
--    - reduce db_items.stock by damage_qty for each damage item
--    - insert a new row into inventory_movements with:
--        type = 'damage'
--        item_id = db_damageitems.item_id
--        qty = -damage_qty
--        reference_id = NULL (or optional original purchase/sales id)
--        damage_id = db_damage.id
--    - update db_damage.total_qty and db_damage.total_value
-- 2. The current stock check trigger in db_salesitems should remain intact.
-- 3. If you want to expose this in the UI later, create modules for:
--    - damage entry
--    - approval workflow
--    - damage report by warehouse / item / date

