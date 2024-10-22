CREATE TABLE
    tbl_customers (
        customer_id INT AUTO_INCREMENT PRIMARY KEY, -- Unique customer ID
        first_name VARCHAR(50) NOT NULL, -- Customer's first name
        last_name VARCHAR(50) NOT NULL, -- Customer's last name
        email VARCHAR(100) NOT NULL UNIQUE, -- Customer's email (unique for login)
        phone VARCHAR(15), -- Customer's phone number
        date_of_birth DATE, -- Date of birth (optional)
        street_address VARCHAR(255) NOT NULL, -- Customer's street address
        city VARCHAR(100) NOT NULL, -- City of the customer
        state_province VARCHAR(100) NOT NULL, -- State/Province
        postal_code VARCHAR(20) NOT NULL, -- Postal/ZIP code
        country VARCHAR(100) NOT NULL, -- Country
        username VARCHAR(50) UNIQUE, -- Optional username, must be unique if used
        password_hash VARCHAR(255) NOT NULL, -- Hashed password for security
        company_name VARCHAR(100), -- Company name (optional, for business)
        tax_identification_number VARCHAR(50), -- TIN or business identifier (optional)
        business_type VARCHAR(100), -- Business type (optional)
        preferred_contact_method ENUM ('email', 'phone'), -- Preferred contact method
        referral_source VARCHAR(100), -- Referral source (optional)
        newsletter_subscription BOOLEAN DEFAULT FALSE, -- Newsletter subscription flag
        security_question VARCHAR(255), -- Optional security question for recovery
        security_answer VARCHAR(255), -- Optional answer (hashed for security)
        agreed_to_terms BOOLEAN NOT NULL DEFAULT FALSE, -- Agreement to terms and conditions
        registration_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP -- Registration timestamp
    );

CREATE TABLE
    tbl_suppliers (
        supplier_id INT AUTO_INCREMENT PRIMARY KEY, -- Unique supplier ID
        supplier_name VARCHAR(100) NOT NULL, -- Name of the supplier
        email VARCHAR(100) NOT NULL UNIQUE, -- Supplier email
        phone VARCHAR(15), -- Supplier phone number
        street_address VARCHAR(255) NOT NULL, -- Address of the supplier
        city VARCHAR(100) NOT NULL, -- City
        state_province VARCHAR(100) NOT NULL, -- State/Province
        postal_code VARCHAR(20) NOT NULL, -- Postal/ZIP code
        country VARCHAR(100) NOT NULL, -- Country
        registration_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP -- When supplier was added
    );

CREATE TABLE
    tbl_products (
        product_id INT AUTO_INCREMENT PRIMARY KEY, -- Unique product ID
        supplier_id INT, -- Foreign key to supplier
        product_name VARCHAR(100) NOT NULL, -- Name of the product
        category VARCHAR(100) NOT NULL, -- Product category
        purchase_price DECIMAL(10, 2) NOT NULL, -- Purchase price from supplier
        selling_price DECIMAL(10, 2) NOT NULL, -- Selling price to customers
        quantity INT NOT NULL, -- Quantity available
        description TEXT, -- Description of the product (optional)
        added_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP, -- Date the product was added
        FOREIGN KEY (supplier_id) REFERENCES tbl_suppliers (supplier_id) ON DELETE CASCADE
    );

CREATE TABLE
    tbl_purchase_orders (
        order_id INT AUTO_INCREMENT PRIMARY KEY, -- Unique order ID
        customer_id INT, -- Foreign key to customer
        order_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP, -- When the order was placed
        total_amount DECIMAL(10, 2) NOT NULL, -- Total amount of the order
        status ENUM (
            'pending',
            'confirmed',
            'shipped',
            'delivered',
            'cancelled'
        ) DEFAULT 'pending',
        -- Status of the order
        FOREIGN KEY (customer_id) REFERENCES tbl_customers (customer_id) ON DELETE CASCADE
    );

CREATE TABLE
    tbl_order_items (
        order_item_id INT AUTO_INCREMENT PRIMARY KEY, -- Unique order item ID
        order_id INT, -- Foreign key to purchase order
        product_id INT, -- Foreign key to product
        quantity INT NOT NULL, -- Quantity ordered
        price DECIMAL(10, 2) NOT NULL, -- Price per unit at the time of the order
        subtotal DECIMAL(10, 2) NOT NULL, -- Subtotal (price * quantity)
        FOREIGN KEY (order_id) REFERENCES tbl_purchase_orders (order_id) ON DELETE CASCADE,
        FOREIGN KEY (product_id) REFERENCES tbl_products (product_id) ON DELETE CASCADE
    );

CREATE TABLE
    tbl_sales_orders (
        sales_order_id INT AUTO_INCREMENT PRIMARY KEY, -- Unique sales order ID
        order_id INT, -- Foreign key to purchase order
        supplier_id INT, -- Foreign key to supplier
        confirmation_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP, -- Date the order was confirmed
        total_amount DECIMAL(10, 2) NOT NULL, -- Total amount of the sales order
        status ENUM ('confirmed', 'processing', 'shipped', 'delivered') DEFAULT 'confirmed',
        -- Status of the sales order
        FOREIGN KEY (order_id) REFERENCES tbl_purchase_orders (order_id) ON DELETE CASCADE,
        FOREIGN KEY (supplier_id) REFERENCES tbl_suppliers (supplier_id) ON DELETE CASCADE
    );

CREATE TABLE
    tbl_payments (
        payment_id INT AUTO_INCREMENT PRIMARY KEY, -- Unique payment ID
        order_id INT, -- Foreign key to purchase order
        customer_id INT, -- Foreign key to customer
        payment_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP, -- When the payment was made
        payment_method ENUM ('credit_card', 'bank_transfer', 'paypal') NOT NULL, -- Payment method
        amount_paid DECIMAL(10, 2) NOT NULL, -- Amount paid
        transaction_status ENUM ('pending', 'completed', 'failed') DEFAULT 'pending', -- Status of payment
        FOREIGN KEY (order_id) REFERENCES tbl_purchase_orders (order_id) ON DELETE CASCADE,
        FOREIGN KEY (customer_id) REFERENCES tbl_customers (customer_id) ON DELETE CASCADE
    );

CREATE TABLE
    tbl_reports (
        report_id INT AUTO_INCREMENT PRIMARY KEY, -- Unique report ID
        report_type ENUM (
            'customer',
            'product',
            'purchase_order',
            'sales_order',
            'supplier'
        ) NOT NULL, -- Type of report
        generated_by VARCHAR(100) NOT NULL, -- Name or ID of the import manager who generated the report
        generated_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP, -- Date the report was generated
        report_data TEXT, -- JSON or serialized data of the report
        description TEXT -- Additional information about the report
    );

CREATE TABLE
    `tbl_product_categories` (
        `category_id` INT AUTO_INCREMENT PRIMARY KEY,
        `category_name` VARCHAR(255) NOT NULL,
        `description` TEXT DEFAULT NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    );

    
CREATE TABLE
    tbl_cities (
        city_id INT AUTO_INCREMENT PRIMARY KEY, -- Unique city ID
        city_name VARCHAR(100) NOT NULL, -- City name
        state_id INT, -- Foreign key to the state
        postal_code VARCHAR(10), -- Postal code (optional)
        FOREIGN KEY (state_id) REFERENCES tbl_states (state_id) ON DELETE CASCADE
    );

CREATE TABLE
    tbl_states (
        state_id INT AUTO_INCREMENT PRIMARY KEY, -- Unique ID for each state
        state_name VARCHAR(100) NOT NULL, -- Name of the state/province
        state_code VARCHAR(10) UNIQUE, -- Optional code for the state (e.g., CA for California)
        country_id INT NOT NULL, -- Foreign key to the country
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, -- Timestamp when the entry was created
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP, -- Timestamp for last update
        FOREIGN KEY (country_id) REFERENCES tbl_countries (country_id) ON DELETE CASCADE
    );

CREATE TABLE
    tbl_countries (
        country_id INT AUTO_INCREMENT PRIMARY KEY, -- Unique ID for each country
        country_name VARCHAR(100) NOT NULL, -- Name of the country
        country_code VARCHAR(10) NOT NULL UNIQUE, -- ISO country code (e.g., US for United States)
        continent VARCHAR(100), -- Continent (e.g., Europe, Asia, etc.)
        currency VARCHAR(50), -- Currency (e.g., USD, EUR)
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, -- Timestamp when the entry was created
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP -- Timestamp when the entry was last updated
    );


CREATE TABLE `tbl_stock` (
    `stock_id` INT AUTO_INCREMENT PRIMARY KEY,
    `product_id` INT NOT NULL,
    `quantity` INT NOT NULL DEFAULT 0,
    `location` VARCHAR(100) DEFAULT NULL,
    `supplier_id` INT DEFAULT NULL,
    `unit_cost_price` DECIMAL(10, 2) DEFAULT NULL,
    `last_updated` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_product FOREIGN KEY (`product_id`) REFERENCES `tbl_products` (`product_id`) ON DELETE CASCADE,
    CONSTRAINT fk_supplier FOREIGN KEY (`supplier_id`) REFERENCES `tbl_suppliers` (`supplier_id`) ON DELETE SET NULL
);


CREATE TABLE `tbl_stock_transactions` (
    `transaction_id` INT AUTO_INCREMENT PRIMARY KEY,
    `product_id` INT NOT NULL,
    `quantity_change` INT NOT NULL,
    `previous_quantity` INT DEFAULT NULL,
    `transaction_type` ENUM ('in', 'out') NOT NULL,
    `transaction_date` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `notes` VARCHAR(255),
    `user_id` INT DEFAULT NULL,
    `transaction_location` VARCHAR(255) DEFAULT NULL,
    `order_reference` VARCHAR(100) DEFAULT NULL,
    CONSTRAINT fk_product_transaction FOREIGN KEY (`product_id`) REFERENCES `tbl_products` (`product_id`) ON DELETE CASCADE
);