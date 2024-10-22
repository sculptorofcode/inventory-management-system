<?php
// Function to create a new supplier
function createSupplier($supplierName, $email, $phone, $streetAddress, $city, $stateProvince, $postalCode, $country, $registrationDate) {
    global $conn, $table_suppliers;

    $stmt = $conn->prepare("INSERT INTO $table_suppliers (supplier_name, email, phone, street_address, city, state_province, postal_code, country, registration_date) VALUES (:supplier_name, :email, :phone, :street_address, :city, :state_province, :postal_code, :country, :registration_date)");
    $stmt->bindParam(':supplier_name', $supplierName);
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':phone', $phone);
    $stmt->bindParam(':street_address', $streetAddress);
    $stmt->bindParam(':city', $city);
    $stmt->bindParam(':state_province', $stateProvince);
    $stmt->bindParam(':postal_code', $postalCode);
    $stmt->bindParam(':country', $country);
    $stmt->bindParam(':registration_date', $registrationDate);

    return $stmt->execute();
}

function getSupplierCount() {
    global $conn, $table_suppliers;

    $stmt = $conn->prepare("SELECT COUNT(supplier_id) AS supplier_count FROM $table_suppliers");
    $stmt->execute();

    return $stmt->fetch(PDO::FETCH_ASSOC)['supplier_count'];
}

// Function to get a list of all suppliers
function getSuppliers() {
    global $conn, $table_suppliers;

    $stmt = $conn->prepare("SELECT * FROM $table_suppliers");
    $stmt->execute();
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Function to update a supplier's details
function updateSupplier($supplierId, $supplierName, $email, $phone, $streetAddress, $city, $stateProvince, $postalCode, $country, $registrationDate) {
    global $conn, $table_suppliers;

    $stmt = $conn->prepare("UPDATE $table_suppliers 
                            SET supplier_name = :supplier_name, email = :email, phone = :phone, street_address = :street_address, 
                                city = :city, state_province = :state_province, postal_code = :postal_code, country = :country , registration_date = :registration_date
                            WHERE supplier_id = :supplier_id");
    $stmt->bindParam(':supplier_id', $supplierId);
    $stmt->bindParam(':supplier_name', $supplierName);
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':phone', $phone);
    $stmt->bindParam(':street_address', $streetAddress);
    $stmt->bindParam(':city', $city);
    $stmt->bindParam(':state_province', $stateProvince);
    $stmt->bindParam(':postal_code', $postalCode);
    $stmt->bindParam(':country', $country);
    $stmt->bindParam(':registration_date', $registrationDate);

    return $stmt->execute();
}

// Function to delete a supplier
function deleteSupplier($supplierId) {
    global $conn, $table_suppliers;

    $stmt = $conn->prepare("DELETE FROM $table_suppliers WHERE supplier_id = :supplier_id");
    $stmt->bindParam(':supplier_id', $supplierId);

    return $stmt->execute();
}

// Function to get a single supplier's details by ID
function getSupplierById($supplierId) {
    global $conn, $table_suppliers;

    $stmt = $conn->prepare("SELECT supplier_id, supplier_name, email, phone, street_address, city, state_province, postal_code, country, registration_date 
                            FROM $table_suppliers WHERE supplier_id = :supplier_id");
    $stmt->bindParam(':supplier_id', $supplierId);
    $stmt->execute();

    return $stmt->fetch(PDO::FETCH_ASSOC);
}