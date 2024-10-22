# 🏭 Inventory Management System

An Inventory Management System designed for managing products, suppliers, and stock transactions. This application provides functionalities to manage stock levels, track product information, and log stock movements.

## ✨ Features

- 📦 **Product Management**: Add, update, and delete products.
- 🤝 **Supplier Management**: Manage supplier information.
- 📊 **Stock Management**: Track stock levels for each product, including adding and updating stock quantities.
- 📝 **Stock Transactions**: Log stock movements (in and out) with detailed transaction records.
- 🔍 **Search Functionality**: Search products by name, category, or supplier.

## 🛠️ Technologies Used

- 🐘 PHP
- 🗄️ MySQL
- 🔌 PDO (PHP Data Objects)
- 📧 PHPMailer (for sending emails)
- 🎨 HTML/CSS for front-end

## 💾 Database Schema

### Tables

1. **tbl_products** 📦
   - product_id: INT (Primary Key)
   - product_name: VARCHAR
   - description: TEXT
   - category_id: INT (Foreign Key)
   - supplier_id: INT (Foreign Key)
   - purchase_price: DECIMAL
   - selling_price: DECIMAL
   - quantity: INT
   - status: ENUM ('active', 'inactive')
   - added_date: TIMESTAMP

2. **tbl_suppliers** 🤝
   - supplier_id: INT (Primary Key)
   - supplier_name: VARCHAR
   - email: VARCHAR
   - phone: VARCHAR
   - registration_date: TIMESTAMP
   - street_address: VARCHAR
   - postal_code: VARCHAR
   - city: VARCHAR
   - state_province: VARCHAR
   - country: VARCHAR

3. **tbl_stock** 📊
   - stock_id: INT (Primary Key)
   - product_id: INT (Foreign Key)
   - quantity: INT
   - location: VARCHAR
   - supplier_id: INT (Foreign Key)
   - unit_cost_price: DECIMAL
   - last_updated: TIMESTAMP

4. **tbl_stock_transactions** 📝
   - transaction_id: INT (Primary Key)
   - product_id: INT (Foreign Key)
   - quantity_change: INT
   - previous_quantity: INT
   - transaction_type: ENUM ('in', 'out')
   - transaction_date: TIMESTAMP
   - notes: VARCHAR
   - user_id: INT
   - transaction_location: VARCHAR
   - order_reference: VARCHAR

## 🚀 Installation

1. **Clone the repository** 📥
   ```bash
   git clone https://github.com/CyberSaikat/inventory-management-system.git
   cd inventory-management-system
   ```

2. **Setup Database** 🗄️
   - Create a MySQL database and import the SQL scripts for the tables provided in the `/sql` directory.

3. **Configure Database Connection** ⚙️
   - Edit the database connection details in the `config.php` file:
     ```php
        define('DB_HOST', 'localhost'); // Database host
        define('DB_USER', 'root'); // Database name
        define('DB_PASS', ''); // Database username
        define('DB_NAME', 'ims'); // Database password
     ```

4. **Install Dependencies** 📦
   - Make sure to install PHPMailer using Composer:
     ```bash
     composer require phpmailer/phpmailer
     ```

5. **Run the Application** 🚀
   - You can run the application using a local server setup like XAMPP, WAMP, or any server that supports PHP.

## 📖 Usage

- 🌐 **Access the application** via your browser at `http://localhost/inventory-management-system`.
- 📱 **Features** are accessible through the main navigation.

## 🤝 Contributing

Contributions are welcome! Please fork the repository and create a pull request for any enhancements or bug fixes.

## 📄 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## 🙏 Acknowledgments

- Thanks to all contributors and open-source libraries that made this project possible.