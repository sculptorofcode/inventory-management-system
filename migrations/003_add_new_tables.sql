ALTER TABLE `tbl_stock`
ADD COLUMN `batch_number` VARCHAR(50) AFTER `product_id`;
ALTER TABLE `tbl_purchase_orders`
ADD `supplier_id` INT NOT NULL,
ADD `delivery_date` DATE DEFAULT NULL,
ADD `payment_status` ENUM('pending', 'completed', 'failed') DEFAULT 'pending',
ADD `shipping_address` VARCHAR(255) DEFAULT NULL;

ALTER TABLE `tbl_purchase_orders`
ADD CONSTRAINT fk_supplier_id FOREIGN KEY (`supplier_id`) REFERENCES `tbl_suppliers` (`supplier_id`) ON DELETE CASCADE;

CREATE TABLE `ims`.`tbl_purchase_orders` (`order_id` INT NOT NULL AUTO_INCREMENT , `product_id` INT NOT NULL , `supplier_id` INT NOT NULL , `quantity` INT NOT NULL , `unit_price` DECIMAL(10,2) NOT NULL , `order_date` DATE NOT NULL , `total_amount` DECIMAL(10,2) NOT NULL , `delivery_date` DATE NOT NULL , `shipping_address` TEXT NOT NULL , `status` ENUM('pending','confirmed','shipped','delivered','cancelled') NOT NULL DEFAULT 'pending' , PRIMARY KEY (`order_id`)) ENGINE = InnoDB;