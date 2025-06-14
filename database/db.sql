-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 04, 2025 at 01:06 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.3.13

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

--
-- Database: `ims`
--

-- --------------------------------------------------------

--
-- Table structure for table `tbl_change_logs`
--

CREATE TABLE `tbl_change_logs` (
  `log_id` int(11) NOT NULL,
  `table_name` varchar(255) NOT NULL,
  `primary_key_value` varchar(255) NOT NULL,
  `action` enum('INSERT','UPDATE','DELETE') NOT NULL,
  `changed_data` text DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `change_timestamp` timestamp NOT NULL DEFAULT current_timestamp(),
  `ip_address` varchar(45) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_cities`
--

CREATE TABLE `tbl_cities` (
  `city_id` int(11) NOT NULL,
  `city_name` varchar(100) NOT NULL,
  `state_id` int(11) DEFAULT NULL,
  `postal_code` varchar(10) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_countries`
--

CREATE TABLE `tbl_countries` (
  `country_id` int(11) NOT NULL,
  `country_name` varchar(100) NOT NULL,
  `country_code` varchar(10) NOT NULL,
  `continent` varchar(100) DEFAULT NULL,
  `currency` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_customers`
--

CREATE TABLE `tbl_customers` (
  `customer_id` int(11) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `full_name` varchar(255) DEFAULT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(15) DEFAULT NULL,
  `date_of_birth` date DEFAULT NULL,
  `street_address` varchar(255) NOT NULL,
  `city` varchar(100) NOT NULL,
  `state_province` varchar(100) NOT NULL,
  `postal_code` varchar(20) NOT NULL,
  `country` varchar(100) NOT NULL,
  `username` varchar(50) DEFAULT NULL,
  `password_hash` varchar(255) NOT NULL,
  `company_name` varchar(100) DEFAULT NULL,
  `tax_identification_number` varchar(50) DEFAULT NULL,
  `business_type` varchar(100) DEFAULT NULL,
  `preferred_contact_method` enum('email','phone') DEFAULT NULL,
  `referral_source` varchar(100) DEFAULT NULL,
  `newsletter_subscription` tinyint(1) DEFAULT 0,
  `security_question` varchar(255) DEFAULT NULL,
  `security_answer` varchar(255) DEFAULT NULL,
  `agreed_to_terms` tinyint(1) NOT NULL DEFAULT 0,
  `registration_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `user_type` enum('admin','customer','staff') NOT NULL DEFAULT 'customer',
  `token` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_customer_payments`
--

CREATE TABLE `tbl_customer_payments` (
  `payment_id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `sale_order_id` int(11) DEFAULT NULL,
  `payment_method` varchar(50) NOT NULL,
  `transaction_id` varchar(255) DEFAULT NULL,
  `payment_status` enum('pending','completed','failed','refunded') NOT NULL DEFAULT 'pending',
  `amount` decimal(10,2) NOT NULL,
  `currency` varchar(10) NOT NULL DEFAULT 'INR',
  `payment_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_products`
--

CREATE TABLE `tbl_products` (
  `product_id` int(11) NOT NULL,
  `supplier_id` int(11) DEFAULT NULL,
  `product_name` varchar(100) NOT NULL,
  `category` int(11) NOT NULL,
  `purchase_price` decimal(10,2) NOT NULL,
  `selling_price` decimal(10,2) NOT NULL,
  `quantity` int(11) NOT NULL,
  `stock` int(11) NOT NULL,
  `description` text DEFAULT NULL,
  `gst_type` tinyint(4) NOT NULL,
  `hsn_code` varchar(50) NOT NULL,
  `gst_rate` decimal(10,2) NOT NULL,
  `added_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_product_categories`
--

CREATE TABLE `tbl_product_categories` (
  `category_id` int(11) NOT NULL,
  `category_name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_purchase_order`
--

CREATE TABLE `tbl_purchase_order` (
  `order_id` int(11) NOT NULL,
  `supplier_id` int(11) NOT NULL,
  `inv_number` varchar(50) DEFAULT NULL,
  `total_quantity` int(11) NOT NULL,
  `total_products` int(11) NOT NULL,
  `order_date` date NOT NULL,
  `total_cost_price` decimal(10,2) NOT NULL,
  `total_gst` decimal(10,2) NOT NULL,
  `discount` decimal(10,2) NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `paid_amount` decimal(10,2) NOT NULL,
  `due_amount` decimal(10,2) NOT NULL,
  `pay_mode` varchar(50) NOT NULL,
  `notes` text NOT NULL,
  `delivery_date` date NOT NULL,
  `shipping_address` text NOT NULL,
  `status` enum('pending','confirmed','shipped','delivered','cancelled') NOT NULL DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_purchase_order_details`
--

CREATE TABLE `tbl_purchase_order_details` (
  `order_detail_id` int(11) NOT NULL,
  `purchase_order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `unit_cost_price` decimal(10,2) NOT NULL,
  `gst_type` int(11) NOT NULL,
  `gst_rate` decimal(5,2) NOT NULL,
  `hsn_code` varchar(15) NOT NULL,
  `sub_total` decimal(10,2) NOT NULL,
  `total` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_purchase_order_status_log`
--

CREATE TABLE `tbl_purchase_order_status_log` (
  `log_id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `old_status` varchar(50) NOT NULL,
  `new_status` varchar(50) NOT NULL,
  `remarks` text NOT NULL,
  `changed_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `changed_by` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_sale_order`
--

CREATE TABLE `tbl_sale_order` (
  `order_id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `inv_number` varchar(50) DEFAULT NULL,
  `total_quantity` int(11) NOT NULL,
  `total_products` int(11) NOT NULL,
  `order_date` date NOT NULL,
  `total_cost_price` decimal(10,2) NOT NULL,
  `total_gst` decimal(10,2) NOT NULL,
  `discount` decimal(10,2) NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `paid_amount` decimal(10,2) NOT NULL,
  `due_amount` decimal(10,2) NOT NULL,
  `pay_mode` varchar(50) NOT NULL,
  `notes` text NOT NULL,
  `delivery_date` date NOT NULL,
  `shipping_address` text NOT NULL,
  `status` enum('pending','confirmed','shipped','delivered','cancelled') NOT NULL DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_sale_order_details`
--

CREATE TABLE `tbl_sale_order_details` (
  `order_detail_id` int(11) NOT NULL,
  `sale_order_id` int(11) NOT NULL,
  `product_id` int(11) DEFAULT NULL,
  `quantity` int(11) NOT NULL,
  `unit_cost_price` decimal(10,2) NOT NULL,
  `sale_price` decimal(10,2) NOT NULL,
  `gst_type` int(11) NOT NULL,
  `gst_rate` decimal(5,2) NOT NULL,
  `hsn_code` varchar(15) NOT NULL,
  `gst_amount` decimal(10,2) NOT NULL,
  `sub_total` decimal(10,2) NOT NULL,
  `total` decimal(10,2) NOT NULL,
  `batch_number` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_sale_order_status_log`
--

CREATE TABLE `tbl_sale_order_status_log` (
  `log_id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `old_status` varchar(50) NOT NULL,
  `new_status` varchar(50) NOT NULL,
  `remarks` text NOT NULL,
  `changed_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `changed_by` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_states`
--

CREATE TABLE `tbl_states` (
  `state_id` int(11) NOT NULL,
  `state_name` varchar(100) NOT NULL,
  `state_code` varchar(10) DEFAULT NULL,
  `country_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_stock`
--

CREATE TABLE `tbl_stock` (
  `stock_id` int(11) NOT NULL,
  `product_id` int(11) DEFAULT NULL,
  `batch_number` varchar(50) DEFAULT NULL,
  `quantity` int(11) NOT NULL DEFAULT 0,
  `warehouse_id` int(11) DEFAULT NULL,
  `location_id` int(11) DEFAULT NULL,
  `supplier_id` int(11) DEFAULT NULL,
  `unit_cost_price` decimal(10,2) DEFAULT NULL,
  `added_on` timestamp NOT NULL DEFAULT current_timestamp(),
  `last_updated` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_stock_transactions`
--

CREATE TABLE `tbl_stock_transactions` (
  `transaction_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `stock_id` int(11) DEFAULT NULL,
  `quantity_change` int(11) NOT NULL,
  `previous_quantity` int(11) DEFAULT NULL,
  `transaction_type` enum('in','out') NOT NULL,
  `transaction_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `notes` varchar(255) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `transaction_location` varchar(255) DEFAULT NULL,
  `order_reference` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_suppliers`
--

CREATE TABLE `tbl_suppliers` (
  `supplier_id` int(11) NOT NULL,
  `supplier_name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(15) DEFAULT NULL,
  `street_address` varchar(255) NOT NULL,
  `city` varchar(100) NOT NULL,
  `state_province` varchar(100) NOT NULL,
  `postal_code` varchar(20) NOT NULL,
  `country` varchar(100) NOT NULL,
  `gst_type` varchar(50) DEFAULT NULL,
  `gstin` varchar(50) DEFAULT NULL,
  `pan` varchar(50) DEFAULT NULL,
  `tan` varchar(50) DEFAULT NULL,
  `cin` varchar(50) DEFAULT NULL,
  `registration_date` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_supplier_payments`
--

CREATE TABLE `tbl_supplier_payments` (
  `payment_id` int(11) NOT NULL,
  `supplier_id` int(11) NOT NULL,
  `purchase_order_id` int(11) DEFAULT NULL,
  `payment_method` varchar(50) NOT NULL,
  `transaction_id` varchar(255) DEFAULT NULL,
  `payment_status` enum('pending','completed','failed','refunded') NOT NULL DEFAULT 'pending',
  `amount` decimal(10,2) NOT NULL,
  `currency` varchar(10) NOT NULL DEFAULT 'INR',
  `payment_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_warehouse`
--

CREATE TABLE `tbl_warehouse` (
  `warehouse_id` int(11) NOT NULL,
  `warehouse_name` varchar(255) NOT NULL,
  `location` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_by` int(11) NOT NULL,
  `updated_by` int(11) NOT NULL,
  `deleted_by` int(11) DEFAULT NULL,
  `is_deleted` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_warehouse_location`
--

CREATE TABLE `tbl_warehouse_location` (
  `location_id` int(11) NOT NULL,
  `warehouse_id` int(11) NOT NULL,
  `parent_location_id` int(11) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `type` enum('Zone','Aisle','Rack','Shelf','Bin') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_by` int(11) NOT NULL,
  `updated_by` int(11) NOT NULL,
  `deleted_by` int(11) DEFAULT NULL,
  `is_deleted` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `tbl_change_logs`
--
ALTER TABLE `tbl_change_logs`
  ADD PRIMARY KEY (`log_id`),
  ADD KEY `fk_user_id` (`user_id`);

--
-- Indexes for table `tbl_cities`
--
ALTER TABLE `tbl_cities`
  ADD PRIMARY KEY (`city_id`),
  ADD KEY `state_id` (`state_id`);

--
-- Indexes for table `tbl_countries`
--
ALTER TABLE `tbl_countries`
  ADD PRIMARY KEY (`country_id`),
  ADD UNIQUE KEY `country_code` (`country_code`);

--
-- Indexes for table `tbl_customers`
--
ALTER TABLE `tbl_customers`
  ADD PRIMARY KEY (`customer_id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `tbl_customer_payments`
--
ALTER TABLE `tbl_customer_payments`
  ADD PRIMARY KEY (`payment_id`),
  ADD KEY `gk_customer_id` (`customer_id`),
  ADD KEY `gk_sale_order_id` (`sale_order_id`);

--
-- Indexes for table `tbl_products`
--
ALTER TABLE `tbl_products`
  ADD PRIMARY KEY (`product_id`),
  ADD KEY `supplier_id` (`supplier_id`),
  ADD KEY `category` (`category`);

--
-- Indexes for table `tbl_product_categories`
--
ALTER TABLE `tbl_product_categories`
  ADD PRIMARY KEY (`category_id`);

--
-- Indexes for table `tbl_purchase_order`
--
ALTER TABLE `tbl_purchase_order`
  ADD PRIMARY KEY (`order_id`),
  ADD KEY `fk_supplier_id` (`supplier_id`);

--
-- Indexes for table `tbl_purchase_order_details`
--
ALTER TABLE `tbl_purchase_order_details`
  ADD PRIMARY KEY (`order_detail_id`),
  ADD KEY `fk_purchase_product_id` (`product_id`),
  ADD KEY `fk_purchase_order_id` (`purchase_order_id`) USING BTREE;

--
-- Indexes for table `tbl_purchase_order_status_log`
--
ALTER TABLE `tbl_purchase_order_status_log`
  ADD PRIMARY KEY (`log_id`),
  ADD KEY `fk_order_id` (`order_id`);

--
-- Indexes for table `tbl_sale_order`
--
ALTER TABLE `tbl_sale_order`
  ADD PRIMARY KEY (`order_id`),
  ADD KEY `gk_customer_id_idx` (`customer_id`);

--
-- Indexes for table `tbl_sale_order_details`
--
ALTER TABLE `tbl_sale_order_details`
  ADD PRIMARY KEY (`order_detail_id`) USING BTREE,
  ADD KEY `gk_sale_product_id_idx` (`product_id`),
  ADD KEY `gk_sale_order_id_idx` (`sale_order_id`) USING BTREE,
  ADD KEY `fk_batch_product` (`batch_number`,`product_id`);

--
-- Indexes for table `tbl_sale_order_status_log`
--
ALTER TABLE `tbl_sale_order_status_log`
  ADD PRIMARY KEY (`log_id`),
  ADD KEY `gk_sale_order_id_idx` (`order_id`);

--
-- Indexes for table `tbl_states`
--
ALTER TABLE `tbl_states`
  ADD PRIMARY KEY (`state_id`),
  ADD UNIQUE KEY `state_code` (`state_code`),
  ADD KEY `country_id` (`country_id`);

--
-- Indexes for table `tbl_stock`
--
ALTER TABLE `tbl_stock`
  ADD PRIMARY KEY (`stock_id`),
  ADD UNIQUE KEY `tbl_stock_batch_number_product_id_uindex` (`batch_number`,`product_id`),
  ADD KEY `fk_supplier` (`supplier_id`),
  ADD KEY `idx_batch_product` (`batch_number`,`product_id`),
  ADD KEY `gk_product_id` (`product_id`),
  ADD KEY `tbl_stock_tbl_warehouse_warehouse_id_fk` (`warehouse_id`),
  ADD KEY `tbl_stock_tbl_warehouse_location_location_id_fk` (`location_id`);

--
-- Indexes for table `tbl_stock_transactions`
--
ALTER TABLE `tbl_stock_transactions`
  ADD PRIMARY KEY (`transaction_id`),
  ADD KEY `fk_product_transaction` (`product_id`),
  ADD KEY `fk_stock_id` (`stock_id`);

--
-- Indexes for table `tbl_suppliers`
--
ALTER TABLE `tbl_suppliers`
  ADD PRIMARY KEY (`supplier_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `tbl_supplier_payments`
--
ALTER TABLE `tbl_supplier_payments`
  ADD PRIMARY KEY (`payment_id`),
  ADD KEY `gk_supplier_id` (`supplier_id`),
  ADD KEY `gk_purchase_order_id` (`purchase_order_id`);

--
-- Indexes for table `tbl_warehouse`
--
ALTER TABLE `tbl_warehouse`
  ADD PRIMARY KEY (`warehouse_id`);

--
-- Indexes for table `tbl_warehouse_location`
--
ALTER TABLE `tbl_warehouse_location`
  ADD PRIMARY KEY (`location_id`),
  ADD KEY `warehouse_id` (`warehouse_id`),
  ADD KEY `parent_location_id` (`parent_location_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `tbl_change_logs`
--
ALTER TABLE `tbl_change_logs`
  MODIFY `log_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_cities`
--
ALTER TABLE `tbl_cities`
  MODIFY `city_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_countries`
--
ALTER TABLE `tbl_countries`
  MODIFY `country_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_customers`
--
ALTER TABLE `tbl_customers`
  MODIFY `customer_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_customer_payments`
--
ALTER TABLE `tbl_customer_payments`
  MODIFY `payment_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_products`
--
ALTER TABLE `tbl_products`
  MODIFY `product_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_product_categories`
--
ALTER TABLE `tbl_product_categories`
  MODIFY `category_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_purchase_order`
--
ALTER TABLE `tbl_purchase_order`
  MODIFY `order_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_purchase_order_details`
--
ALTER TABLE `tbl_purchase_order_details`
  MODIFY `order_detail_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_purchase_order_status_log`
--
ALTER TABLE `tbl_purchase_order_status_log`
  MODIFY `log_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_sale_order`
--
ALTER TABLE `tbl_sale_order`
  MODIFY `order_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_sale_order_details`
--
ALTER TABLE `tbl_sale_order_details`
  MODIFY `order_detail_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_sale_order_status_log`
--
ALTER TABLE `tbl_sale_order_status_log`
  MODIFY `log_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_states`
--
ALTER TABLE `tbl_states`
  MODIFY `state_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_stock`
--
ALTER TABLE `tbl_stock`
  MODIFY `stock_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_stock_transactions`
--
ALTER TABLE `tbl_stock_transactions`
  MODIFY `transaction_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_suppliers`
--
ALTER TABLE `tbl_suppliers`
  MODIFY `supplier_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_supplier_payments`
--
ALTER TABLE `tbl_supplier_payments`
  MODIFY `payment_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_warehouse`
--
ALTER TABLE `tbl_warehouse`
  MODIFY `warehouse_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_warehouse_location`
--
ALTER TABLE `tbl_warehouse_location`
  MODIFY `location_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `tbl_change_logs`
--
ALTER TABLE `tbl_change_logs`
  ADD CONSTRAINT `fk_user_id` FOREIGN KEY (`user_id`) REFERENCES `tbl_customers` (`customer_id`) ON DELETE SET NULL;

--
-- Constraints for table `tbl_cities`
--
ALTER TABLE `tbl_cities`
  ADD CONSTRAINT `tbl_cities_ibfk_1` FOREIGN KEY (`state_id`) REFERENCES `tbl_states` (`state_id`) ON DELETE CASCADE;

--
-- Constraints for table `tbl_customer_payments`
--
ALTER TABLE `tbl_customer_payments`
  ADD CONSTRAINT `gk_customer_id` FOREIGN KEY (`customer_id`) REFERENCES `tbl_customers` (`customer_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `gk_sale_order_id` FOREIGN KEY (`sale_order_id`) REFERENCES `tbl_sale_order` (`order_id`) ON DELETE SET NULL;

--
-- Constraints for table `tbl_products`
--
ALTER TABLE `tbl_products`
  ADD CONSTRAINT `tbl_products_ibfk_1` FOREIGN KEY (`supplier_id`) REFERENCES `tbl_suppliers` (`supplier_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `tbl_products_ibfk_2` FOREIGN KEY (`category`) REFERENCES `tbl_product_categories` (`category_id`);

--
-- Constraints for table `tbl_purchase_order`
--
ALTER TABLE `tbl_purchase_order`
  ADD CONSTRAINT `fk_supplier_id` FOREIGN KEY (`supplier_id`) REFERENCES `tbl_suppliers` (`supplier_id`) ON DELETE CASCADE;

--
-- Constraints for table `tbl_purchase_order_details`
--
ALTER TABLE `tbl_purchase_order_details`
  ADD CONSTRAINT `fk_purchase_order_id` FOREIGN KEY (`purchase_order_id`) REFERENCES `tbl_purchase_order` (`order_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_purchase_product_id` FOREIGN KEY (`product_id`) REFERENCES `tbl_products` (`product_id`) ON DELETE CASCADE;

--
-- Constraints for table `tbl_purchase_order_status_log`
--
ALTER TABLE `tbl_purchase_order_status_log`
  ADD CONSTRAINT `fk_order_id` FOREIGN KEY (`order_id`) REFERENCES `tbl_purchase_order` (`order_id`) ON DELETE CASCADE;

--
-- Constraints for table `tbl_sale_order`
--
ALTER TABLE `tbl_sale_order`
  ADD CONSTRAINT `gk_customer_id_uniq` FOREIGN KEY (`customer_id`) REFERENCES `tbl_customers` (`customer_id`) ON DELETE CASCADE;

--
-- Constraints for table `tbl_sale_order_details`
--
ALTER TABLE `tbl_sale_order_details`
  ADD CONSTRAINT `fk_batch_product` FOREIGN KEY (`batch_number`,`product_id`) REFERENCES `tbl_stock` (`batch_number`, `product_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_product_batch` FOREIGN KEY (`batch_number`) REFERENCES `tbl_stock` (`batch_number`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `gk_sale_order_id_uniq` FOREIGN KEY (`sale_order_id`) REFERENCES `tbl_sale_order` (`order_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `gk_sale_product_id_uniq` FOREIGN KEY (`product_id`) REFERENCES `tbl_products` (`product_id`) ON DELETE CASCADE;

--
-- Constraints for table `tbl_sale_order_status_log`
--
ALTER TABLE `tbl_sale_order_status_log`
  ADD CONSTRAINT `gk_sale_order_id_status_uniq` FOREIGN KEY (`order_id`) REFERENCES `tbl_sale_order` (`order_id`) ON DELETE CASCADE;

--
-- Constraints for table `tbl_states`
--
ALTER TABLE `tbl_states`
  ADD CONSTRAINT `tbl_states_ibfk_1` FOREIGN KEY (`country_id`) REFERENCES `tbl_countries` (`country_id`) ON DELETE CASCADE;

--
-- Constraints for table `tbl_stock`
--
ALTER TABLE `tbl_stock`
  ADD CONSTRAINT `fk_product` FOREIGN KEY (`product_id`) REFERENCES `tbl_products` (`product_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_supplier` FOREIGN KEY (`supplier_id`) REFERENCES `tbl_suppliers` (`supplier_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `gk_product_id` FOREIGN KEY (`product_id`) REFERENCES `tbl_products` (`product_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `tbl_stock_tbl_warehouse_location_location_id_fk` FOREIGN KEY (`location_id`) REFERENCES `tbl_warehouse_location` (`location_id`),
  ADD CONSTRAINT `tbl_stock_tbl_warehouse_warehouse_id_fk` FOREIGN KEY (`warehouse_id`) REFERENCES `tbl_warehouse` (`warehouse_id`) ON DELETE SET NULL;

--
-- Constraints for table `tbl_stock_transactions`
--
ALTER TABLE `tbl_stock_transactions`
  ADD CONSTRAINT `fk_product_transaction` FOREIGN KEY (`product_id`) REFERENCES `tbl_products` (`product_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_stock_id` FOREIGN KEY (`stock_id`) REFERENCES `tbl_stock` (`stock_id`);

--
-- Constraints for table `tbl_supplier_payments`
--
ALTER TABLE `tbl_supplier_payments`
  ADD CONSTRAINT `gk_purchase_order_id` FOREIGN KEY (`purchase_order_id`) REFERENCES `tbl_purchase_order` (`order_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `gk_supplier_id` FOREIGN KEY (`supplier_id`) REFERENCES `tbl_suppliers` (`supplier_id`) ON DELETE CASCADE;

--
-- Constraints for table `tbl_warehouse_location`
--
ALTER TABLE `tbl_warehouse_location`
  ADD CONSTRAINT `tbl_warehouse_location_ibfk_1` FOREIGN KEY (`warehouse_id`) REFERENCES `tbl_warehouse` (`warehouse_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `tbl_warehouse_location_ibfk_2` FOREIGN KEY (`parent_location_id`) REFERENCES `tbl_warehouse_location` (`location_id`) ON DELETE SET NULL;
COMMIT;
