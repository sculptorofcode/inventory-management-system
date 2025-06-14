<?php

function logChange($table_name, $primary_key_value, $action, $changed_data = null, $user_id = null, $ip_address = null): bool
{
    global $conn;

    $changed_data_json = !empty($changed_data) ? json_encode($changed_data) : null;

    $sql = "INSERT INTO tbl_change_logs SET table_name = :table_name, primary_key_value = :primary_key_value, action = :action, changed_data = :changed_data, user_id = :user_id, ip_address = :ip_address";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':table_name', $table_name);
    $stmt->bindParam(':primary_key_value', $primary_key_value);
    $stmt->bindParam(':action', $action);
    $stmt->bindParam(':changed_data', $changed_data_json);
    $stmt->bindParam(':user_id', $user_id);
    $stmt->bindParam(':ip_address', $ip_address);

    return $stmt->execute();
}

function logInsert($table_name, $primary_key_value, $user_id = null, $ip_address = null)
{
    return logChange($table_name, $primary_key_value, 'INSERT', null, $user_id, $ip_address);
}

function logUpdate($table_name, $primary_key_value, $old_data, $new_data, $user_id = null, $ip_address = null)
{
    $changed_data = [];
    foreach ($new_data as $key => $value) {
        if (isset($old_data[$key]) && $old_data[$key] != $value) {
            $changed_data[$key] = ['old' => $old_data[$key], 'new' => $value];
        }
    }

    return logChange($table_name, $primary_key_value, 'UPDATE', $changed_data, $user_id, $ip_address);
}

function logDelete($table_name, $primary_key_value, $user_id = null, $ip_address = null)
{
    return logChange($table_name, $primary_key_value, 'DELETE', null, $user_id, $ip_address);
}
