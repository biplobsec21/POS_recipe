CREATE TABLE `inventory_movements` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `item_id` int(11) NOT NULL,
  `qty` decimal(10,2) NOT NULL,
  `type` enum('PURCHASE','SALE','PRODUCTION_CONSUME','PRODUCTION_OUTPUT','SALE_RETURN','PURCHASE_RETURN') NOT NULL,
  `reference_id` int(11) DEFAULT NULL,
  `warehouse_id` int(11) DEFAULT NULL,
  `created_by` int(11) NOT NULL,
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
