<?php
require_once 'includes/config/database.php';
if (isset($_POST['check_postal_code'])) {
    $postal_code = filtervar($_POST['check_postal_code']);
    if (strlen($postal_code) === 6) {
        $data = json_decode(file_get_contents("https://api.postalpincode.in/pincode/$postal_code"), true);
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