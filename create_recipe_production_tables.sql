-- Table: recipes
CREATE TABLE `recipes` (
    `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `recipe_name` VARCHAR(255) NOT NULL,
    `output_product_id` INT(11) UNSIGNED NOT NULL,
    `yield_quantity` DECIMAL(10,4) NOT NULL,
    `notes` TEXT NULL,
    `created_by` INT(11) UNSIGNED NOT NULL,
    `created_at` DATETIME NOT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Table: recipe_items
CREATE TABLE `recipe_items` (
    `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `recipe_id` INT(11) UNSIGNED NOT NULL,
    `item_id` INT(11) UNSIGNED NOT NULL,
    `quantity` DECIMAL(10,4) NOT NULL,
    `unit` VARCHAR(50) NOT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Table: production_batches
CREATE TABLE `production_batches` (
    `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `recipe_id` INT(11) UNSIGNED NOT NULL,
    `produced_quantity` DECIMAL(10,4) NOT NULL,
    `status` ENUM('draft', 'approved', 'cancelled') DEFAULT 'draft',
    `warehouse_id` INT(11) UNSIGNED NOT NULL,
    `total_cost` DECIMAL(10,4) NOT NULL,
    `cost_per_unit` DECIMAL(10,4) NOT NULL,
    `notes` TEXT NULL,
    `created_by` INT(11) UNSIGNED NOT NULL,
    `approved_by` INT(11) UNSIGNED NULL,
    `created_at` DATETIME NOT NULL,
    `approved_at` DATETIME NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;