# 🏭 Inventory Management System
![Leading Image](https://raw.githubusercontent.com/sculptorofcode/inventory-management-system/refs/heads/master/assets/images/banner.png)

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

## 🚀 Installation

### Prerequisites 📋

Before you begin, ensure you have the following installed on your system:

- 🖥️ **Web Server**: Apache or Nginx
- 🐘 **PHP**: Version 8.0 or higher with the following extensions:
  - PDO (PHP Data Objects)
  - PDO MySQL
  - JSON
  - cURL
  - OpenSSL
- 🗄️ **MySQL/MariaDB**: Version 5.7 or higher
- 🎼 **Composer**: For dependency management

### Step-by-Step Installation 🔧

#### 1. **Clone the Repository** 📥
```bash
git clone https://github.com/sculptorofcode/inventory-management-system.git
cd inventory-management-system
```

#### 2. **Install PHP Dependencies** 📦
```bash
composer install
```

#### 3. **Setup Database** 🗄️

**Option A: Import Complete Database (Recommended)**
1. Create a new MySQL database named `ims`
2. Import the database schema:
   ```bash
   mysql -u root -p ims < database/db.sql
   ```

**Option B: Use Migration System**
1. Create a new MySQL database named `ims`
2. Run the migration script:
   - **Windows**: Double-click `run-migrations.bat`
   - **Linux/Mac**: Execute `./run-migrations.sh`
   - **Manual**: Run `php migrate.php` from the project root

#### 4. **Configure Database Connection** ⚙️

Copy the sample configuration file and update with your settings:

```bash
cp includes/config/config.sample.php includes/config/config.php
```

Then edit the configuration file `includes/config/config.php` with your database credentials:

```php
const DB_HOST = 'localhost';     // Your database host
const DB_USER = 'root';          // Your database username
const DB_PASS = '';              // Your database password
const DB_NAME = 'ims';           // Your database name

const SITE_URL = 'http://localhost/your-project-folder';
const LOGIN_URL = 'http://localhost/your-project-folder/login';
```

> **Note**: The `config.php` file contains sensitive information and is excluded from the Git repository by the `.gitignore` file.

#### 5. **Configure Web Server** 🌐

**For Apache (XAMPP/WAMP):**
1. Copy the project folder to your web server's document root (e.g., `htdocs`)
2. Ensure `.htaccess` files are enabled
3. Start Apache and MySQL services

**For Nginx:**
1. Configure a virtual host pointing to the project directory
2. Ensure PHP-FPM is running
3. Configure appropriate rewrite rules

#### 6. **Set File Permissions** 🔐
```bash
# For Linux/Mac systems
chmod -R 755 assets/
chmod -R 755 logs/
chmod 644 includes/config/config.php
```

#### 7. **Configure Email Settings (Optional)** 📧

For email functionality, update the email settings in `includes/config/config.php`:

```php
const APP_EMAIL = 'your-email@domain.com';
const APP_EMAIL_PASSWORD = 'your-app-password';
```

#### 8. **Run the Application** 🚀

1. Start your web server and MySQL service
2. Open your browser and navigate to: `http://localhost/your-project-folder`
3. You should see the IMS login page

### Default Login Credentials 🔑

After installation, you can log in using:
- **Username**: admin
- **Password**: admin123

⚠️ **Important**: Change the default credentials immediately after first login!

### Contributing to the Repository 🤝

When contributing to this repository, please note the following:

1. **Files Excluded from Version Control** 📁
   
   The following files and directories are ignored by Git (defined in `.gitignore`):
   
   - `/vendor/` directory (installed via Composer)
   - IDE configuration files (`.idea/`, `.vscode/`)
   - Log files (`/logs/` and `*.log`)
   - Configuration files with sensitive information (`/includes/config/config.php`)
   - Database files (`*.sql`, `*.sqlite`)
   - Debug and test files (`debug_*.php`, `test_*.php`)
   
   When you clone the repository, you'll need to create these files/directories as needed.

2. **Configuration Setup** ⚙️
   
   Copy `includes/config/config.sample.php` to `includes/config/config.php` and update with your settings.

### Troubleshooting 🔧

**Common Issues:**

1. **Database Connection Error**
   - Verify database credentials in `config.php`
   - Ensure MySQL service is running
   - Check if the database `ims` exists

2. **Composer Dependencies Error**
   - Run `composer update` to refresh dependencies
   - Ensure PHP version compatibility

3. **Permission Issues**
   - Check file permissions for `assets/` and `logs/` directories
   - Ensure web server has read/write access

4. **Email Not Working**
   - Verify SMTP settings in configuration
   - Check if less secure app access is enabled (for Gmail)

### Post-Installation Setup ✅

1. **Change Default Passwords**: Update admin credentials
2. **Configure System Settings**: Set up company information
3. **Add Initial Data**: Create categories, suppliers, and products
4. **Test Features**: Verify all modules are working correctly
5. **Enable Backups**: Set up regular database backups

## 📖 Usage

- 🌐 **Access the application** via your browser at `http://localhost/inventory-management-system`.
- 📱 **Features** are accessible through the main navigation.

## ✅ Changeable Checklist

The following features can be added or enhanced to improve the Inventory Management System:

- [x] **Inventory Reports**
   - [x] Stock Report
   - [x] Sales Report
   - [x] Purchase Report
   - [x] Inventory Valuation Report

- [x] **Inventory Adjustments**
   - [x] Stock Adjustment
   - [x] Stock Transfer
   - [x] Partial Quantity Movement

- [ ] **Pricing Management**
   - [ ] Price Lists
   - [ ] Discounts and Promotions
   - [ ] Pricing History

- [ ] **Supplier Management Enhancements**
   - [x] Supplier Payments
   - [ ] Supplier Ratings and Reviews

- [ ] **Product Return Management**
   - [ ] Product Returns
   - [ ] Return Requests

- [x] **Warehouse/Location Management**
   - [x] Multiple Warehouses
   - [x] Location Tracking
   - [x] Location History

- [ ] **Purchase and Sale Order Enhancements**
   - [x] Order Invoices
   - [ ] Order Status Tracking
   - [ ] Order Returns and Cancellations

- [ ] **User Roles and Permissions**
   - [ ] Role-Based Access Control

- [ ] **Integration with Accounting Software**

- [ ] **Barcode and QR Code Integration**
   - [ ] Barcode Scanning
   - [ ] Barcode Label Generation

- [ ] **Communication Features**
   - [ ] Email/SMS Notifications

- [ ] **Audit Logs**
   - [ ] Audit Trail

- [ ] **Import/Export Functionality**
   - [ ] CSV Import/Export

- [x] **Database Migration System**
   - [x] Automated Schema Updates
   - [x] Migration Tracking
   - [x] CLI and Web Interface

- [ ] **Product Expiry Management (for Perishable Goods)**
   - [ ] Expiry Tracking

## 🤝 Contributing

Contributions are welcome! Please fork the repository and create a pull request for any enhancements or bug fixes.

## 📄 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## 🙏 Acknowledgments

- Thanks to all contributors and open-source libraries that made this project possible.
