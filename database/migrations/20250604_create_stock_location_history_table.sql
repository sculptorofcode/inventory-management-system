-- Stock Location History Table
-- This table tracks the history of stock location changes

CREATE TABLE IF NOT EXISTS `tbl_stock_location_history` (
  `history_id` int(11) NOT NULL AUTO_INCREMENT,
  `stock_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `batch_number` varchar(50) DEFAULT NULL,
  `old_warehouse_id` int(11) DEFAULT NULL,
  `new_warehouse_id` int(11) NOT NULL,
  `old_location_id` int(11) DEFAULT NULL,
  `new_location_id` int(11) DEFAULT NULL,
  `moved_by` int(11) NOT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`history_id`),
  KEY `stock_location_product_id` (`product_id`),
  KEY `stock_location_stock_id` (`stock_id`),
  KEY `stock_location_old_warehouse` (`old_warehouse_id`),
  KEY `stock_location_new_warehouse` (`new_warehouse_id`),
  KEY `stock_location_old_location` (`old_location_id`),
  KEY `stock_location_new_location` (`new_location_id`),
  KEY `stock_location_moved_by` (`moved_by`),
  CONSTRAINT `stock_location_product_id` FOREIGN KEY (`product_id`) REFERENCES `tbl_products` (`product_id`) ON DELETE CASCADE,
  CONSTRAINT `stock_location_stock_id` FOREIGN KEY (`stock_id`) REFERENCES `tbl_stock` (`stock_id`) ON DELETE CASCADE,
  CONSTRAINT `stock_location_old_warehouse` FOREIGN KEY (`old_warehouse_id`) REFERENCES `tbl_warehouse` (`warehouse_id`) ON DELETE SET NULL,
  CONSTRAINT `stock_location_new_warehouse` FOREIGN KEY (`new_warehouse_id`) REFERENCES `tbl_warehouse` (`warehouse_id`),
  CONSTRAINT `stock_location_old_location` FOREIGN KEY (`old_location_id`) REFERENCES `tbl_warehouse_location` (`location_id`) ON DELETE SET NULL,
  CONSTRAINT `stock_location_new_location` FOREIGN KEY (`new_location_id`) REFERENCES `tbl_warehouse_location` (`location_id`) ON DELETE SET NULL,
  CONSTRAINT `stock_location_moved_by` FOREIGN KEY (`moved_by`) REFERENCES `tbl_customers` (`customer_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
