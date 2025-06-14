<?php
require_once 'includes/config/database.php';
// Check if postal code is set
if (isset($_POST['check_postal_code'])) {
    $postal_code = filtervar($_POST['check_postal_code']);
    // Validate postal code length
    if (strlen($postal_code) === 6) {
        $data = json_decode(file_get_contents("https://api.postalpincode.in/pincode/$postal_code"), true);
        // Check if API response is successful
        if ($data[0]['Status'] === 'Success') {
            $city = $data[0]['PostOffice'][0]['District'];
            $state = $data[0]['PostOffice'][0]['State'];
            $country = $data[0]['PostOffice'][0]['Country'];
            $res = ['status' => 'success', 'city' => $city, 'state' => $state, 'country' => $country];
        } else {
            $res = ['status' => 'error', 'message' => 'Invalid Postal Code'];
        }
    }
    echo json_encode($res);
    exit();
}

// Get locations by warehouse
if (isset($_POST['action']) && $_POST['action'] === 'get_locations_by_warehouse') {
    $warehouse_id = filtervar($_POST['warehouse_id']);
    $locations = getLocationsByWarehouse($warehouse_id);
    
    $html = '<option value="" disabled selected>Select Location</option>';
    
    foreach ($locations as $location) {
        $location_label = $location['name'] . ' (' . $location['type'] . ')';
        $html .= '<option value="' . $location['location_id'] . '">' . $location_label . '</option>';
    }
    
    echo $html;
    exit;
}