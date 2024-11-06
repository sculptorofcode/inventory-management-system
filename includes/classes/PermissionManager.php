<?php

class PermissionManager {
    private $pdo;

    /**
     * Constructor to initialize the database connection.
     *
     * @param PDO $pdo
     */
    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    /**
     * Add a permission directly to a customer.
     *
     * @param int $customerId
     * @param string $permissionName
     */
    public function addPermissionToCustomer(int $customerId, string $permissionName) {
        $stmt = $this->pdo->prepare("
            INSERT INTO table_permissions (customer_id, permission_name)
            VALUES (:customerId, :permissionName)
            ON DUPLICATE KEY UPDATE permission_name = permission_name
        ");
        $stmt->execute([':customerId' => $customerId, ':permissionName' => $permissionName]);
    }

    /**
     * Remove a permission from a customer.
     *
     * @param int $customerId
     * @param string $permissionName
     */
    public function removePermissionFromCustomer(int $customerId, string $permissionName) {
        $stmt = $this->pdo->prepare("
            DELETE FROM table_permissions
            WHERE customer_id = :customerId AND permission_name = :permissionName
        ");
        $stmt->execute([':customerId' => $customerId, ':permissionName' => $permissionName]);
    }

    /**
     * Check if a customer has a specific permission.
     *
     * @param int $customerId
     * @param string $permissionName
     * @return bool
     */
    public function customerHasPermission(int $customerId, string $permissionName): bool {
        $stmt = $this->pdo->prepare("
            SELECT COUNT(*) FROM table_permissions
            WHERE customer_id = :customerId AND permission_name = :permissionName
        ");
        $stmt->execute([':customerId' => $customerId, ':permissionName' => $permissionName]);

        return $stmt->fetchColumn() > 0;
    }

    /**
     * List all permissions assigned to a customer.
     *
     * @param int $customerId
     * @return array
     */
    public function getCustomerPermissions(int $customerId): array {
        $stmt = $this->pdo->prepare("
            SELECT permission_name FROM table_permissions
            WHERE customer_id = :customerId
        ");
        $stmt->execute([':customerId' => $customerId]);

        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
}


/*
CREATE TABLE table_permissions (
    permission_id INT AUTO_INCREMENT PRIMARY KEY,
    customer_id INT NOT NULL,
    permission_name VARCHAR(50) NOT NULL,
    FOREIGN KEY (customer_id) REFERENCES tbl_customers(customer_id) ON DELETE CASCADE,
    UNIQUE KEY unique_permission (customer_id, permission_name) -- Ensure unique permissions per customer
);

// Database connection setup
$dsn = 'mysql:host=localhost;dbname=your_database';
$username = 'your_username';
$password = 'your_password';
$pdo = new PDO($dsn, $username, $password);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$pm = new PermissionManager($pdo);

// Add permissions to customers
$pm->addPermissionToCustomer(1, "create_post");
$pm->addPermissionToCustomer(1, "edit_post");
$pm->addPermissionToCustomer(2, "delete_post");

// Check if a customer has a permission
echo $pm->customerHasPermission(1, "delete_post") ? "Yes" : "No"; // Output: No
echo $pm->customerHasPermission(2, "delete_post") ? "Yes" : "No"; // Output: Yes

// Remove a permission from a customer
$pm->removePermissionFromCustomer(1, "edit_post");

// List all permissions of a customer
$permissions = $pm->getCustomerPermissions(1);
print_r($permissions);

*/