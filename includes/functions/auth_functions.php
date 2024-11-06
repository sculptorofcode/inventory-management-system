<?php
function registerCustomer($firstName, $lastName, $email, $username, $password)
{
    global $conn, $table_customers;

    $passwordHash = password_hash($password, PASSWORD_BCRYPT);

    $stmt = $conn->prepare("INSERT INTO $table_customers (first_name, last_name, email, username, password_hash) 
                            VALUES (:first_name, :last_name, :email, :username, :password_hash)");
    $stmt->bindParam(':first_name', $firstName);
    $stmt->bindParam(':last_name', $lastName);
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':username', $username);
    $stmt->bindParam(':password_hash', $passwordHash);

    return $stmt->execute();
}

function loginCustomer($usernameOrEmail, $password)
{
    global $conn, $table_customers;

    $stmt = $conn->prepare("SELECT * FROM $table_customers WHERE `username` = :username OR `email` = :email LIMIT 1");
    $stmt->bindParam(':username', $usernameOrEmail);
    $stmt->bindParam(':email', $usernameOrEmail);
    $stmt->execute();

    $customer = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($customer && password_verify($password, $customer['password_hash'])) {
        $_SESSION['customer_id'] = $customer['customer_id'];
        $_SESSION['customer_username'] = $customer['username'];

        return true;
    }

    return false;
}

function isLoggedIn()
{
    return isset($_SESSION['customer_id']);
}

function logoutCustomer()
{
    session_destroy();
    return true;
}

function updateCustomerPassword($customerId, $newPassword)
{
    global $conn, $table_customers;

    $newPasswordHash = password_hash($newPassword, PASSWORD_BCRYPT);

    $stmt = $conn->prepare("UPDATE $table_customers SET `password_hash` = :password_hash WHERE `customer_id` = :customer_id");
    $stmt->bindParam(':password_hash', $newPasswordHash);
    $stmt->bindParam(':customer_id', $customerId);

    return $stmt->execute();
}

function isEmailRegistered($email)
{
    global $conn, $table_customers;

    $stmt = $conn->prepare("SELECT COUNT(*) FROM $table_customers WHERE `email` = :email");
    $stmt->bindParam(':email', $email);
    $stmt->execute();

    return $stmt->fetchColumn() > 0;
}

function isMobileRegistered($mobile)
{
    global $conn, $table_customers;

    $stmt = $conn->prepare("SELECT COUNT(*) FROM $table_customers WHERE `phone` = :mobile");
    $stmt->bindParam(':mobile', $mobile);
    $stmt->execute();

    return $stmt->fetchColumn() > 0;
}

function getCustomerById($customerId)
{
    global $conn, $table_customers;

    $stmt = $conn->prepare("SELECT * FROM $table_customers WHERE `customer_id` = :customer_id LIMIT 1");
    $stmt->bindParam(':customer_id', $customerId);
    $stmt->execute();

    return $stmt->fetch(PDO::FETCH_ASSOC);
}

if(!function_exists('getAllCustomers')){
    function getAllCustomers()
    {
        global $conn, $table_customers;

        $stmt = $conn->prepare("SELECT * FROM $table_customers WHERE `user_type` = 'customer'");
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

function getCustomersCount()
{
    global $conn, $table_customers;

    $stmt = $conn->prepare("SELECT COUNT(*) FROM $table_customers WHERE `user_type` = 'customer'");
    $stmt->execute();

    return $stmt->fetchColumn();
}

function update_profile($data, $customer_id)
{
    global $conn, $table_customers, $session;

    $current_customer = getCustomerById($customer_id);

    if(empty($data['first_name'])){
        $data['first_name'] = $current_customer['first_name'];
    }else{
        $data['first_name'] = ucwords(strtolower($data['first_name']));
    }

    if(empty($data['last_name'])){
        $data['last_name'] = $current_customer['last_name'];
    }else{
        $data['last_name'] = ucwords(strtolower($data['last_name']));
    }

    if(empty($data['email'])){
        $data['email'] = $current_customer['email'];
    }

    if(empty($data['phone'])){
        $data['phone'] = $current_customer['phone'];
    }

    if(empty($data['company_name'])){
        $data['company_name'] = $current_customer['company_name'];
    }

    if(empty($data['street_address'])){
        $data['street_address'] = $current_customer['street_address'];
    }

    if(empty($data['state_province'])){
        $data['state_province'] = $current_customer['state_province'];
    }

    if(empty($data['postal_code'])){
        $data['postal_code'] = $current_customer['postal_code'];
    }

    if(empty($data['country'])){
        $data['country'] = $current_customer['country'];
    }

    $full_name = $data['first_name'] . ' ' . $data['last_name'];
    
    $stmt = $conn->prepare("UPDATE $table_customers SET 
                            `first_name` = :first_name, 
                            `last_name` = :last_name, 
                            `full_name` = :full_name,
                            `email` = :email, 
                            `phone` = :phone, 
                            `company_name` = :company_name, 
                            `street_address` = :street_address, 
                            `state_province` = :state_province, 
                            `postal_code` = :postal_code, 
                            `country` = :country 
                            WHERE `customer_id` = :customer_id");

    $stmt->bindParam(':first_name', $data['first_name']);
    $stmt->bindParam(':last_name', $data['last_name']);
    $stmt->bindParam(':full_name', $full_name);
    $stmt->bindParam(':email', $data['email']);
    $stmt->bindParam(':phone', $data['phone']);
    $stmt->bindParam(':company_name', $data['company_name']);
    $stmt->bindParam(':street_address', $data['street_address']);
    $stmt->bindParam(':state_province', $data['state_province']);
    $stmt->bindParam(':postal_code', $data['postal_code']);
    $stmt->bindParam(':country', $data['country']);
    $stmt->bindParam(':customer_id', $customer_id);

    $res = $stmt->execute();

    $userdata = getCustomerById($customer_id);

    $session->set('userdata', $userdata);
    
    return $res;
}