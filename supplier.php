<?php
require_once 'includes/config/after-login.php';
$title = 'Add Supplier';
$form_action = 'add';
if (isset($_POST['supplier'])) {
    $supplier_name = filtervar($_POST['supplier_name']);
    $email = filtervar($_POST['email']);
    $phone = filtervar($_POST['phone']);
    $registration_date = filtervar($_POST['registration_date']);
    $street_address = filtervar($_POST['street_address']);
    $postal_code = filtervar($_POST['postal_code']);
    $city = filtervar($_POST['city']);
    $state_province = filtervar($_POST['state_province']);
    $country = filtervar($_POST['country']);
    $registration_date = date('Y-m-d', strtotime($registration_date));
    $gst_type = filtervar($_POST['gst_type']);
    $gstin = filtervar($_POST['gstin']);
    $pan = filtervar($_POST['pan']);
    $tan = filtervar($_POST['tan']);
    $cin = filtervar($_POST['cin']);

    if (isset($_POST['form_action']) && $_POST['form_action'] == 'add') {
        $res = createSupplier($supplier_name, $email, $phone, $street_address, $city, $state_province, $postal_code, $country, $registration_date, $gst_type, $gstin, $pan, $tan, $cin);
    } else {
        $id = filtervar($_POST['id']);
        $res = updateSupplier($id, $supplier_name, $email, $phone, $street_address, $city, $state_province, $postal_code, $country, $registration_date, $gst_type, $gstin, $pan, $tan, $cin);
    }

    if ($res) {
        if (isset($_POST['form_action']) && $_POST['form_action'] == 'add') {
            $res = ['status' => 'success', 'message' => 'Supplier added successfully', 'redirect' => 'supplier'];
        } else {
            $res = ['status' => 'success', 'message' => 'Supplier updated successfully', 'redirect' => 'supplier-list'];
        }
    } else {
        $res = ['status' => 'error', 'message' => 'Failed to add supplier'];
    }

    echo json_encode($res);
    exit;
}
if (isset($_GET['id'])) {
    $id = filtervar($_GET['id']);
    $row = getSupplierById($id);
    $title = 'Edit Supplier';
    $form_action = 'edit';
}
?>
<!DOCTYPE html>
<html lang="en" class="light-style layout-menu-fixed" dir="ltr" data-theme="theme-default" data-assets-path="assets/"
    data-template="vertical-menu-template-free">

<head>
    <meta charset="utf-8" />
    <meta name="viewport"
        content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />
    <?php include './includes/layouts/styles.php'; ?>
</head>

<body>
    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">
            <?php include './includes/layouts/sidebar.php'; ?>
            <div class="layout-page">
                <?php include './includes/layouts/navbar.php'; ?>
                <div class="content-wrapper">
                    <div class="container-fluid flex-grow-1 container-p-y">
                        <div class="card">
                            <div class="card-header p-3 border-bottom">
                                <h4 class="card-title mb-0"><?= $title ?></h4>
                            </div>
                            <div class="card-body py-3">
                                <form action="" method="post" class="form">
                                    <input type="hidden" name="form_action" value="<?= $form_action ?>">
                                    <input type="hidden" name="id"
                                        value="<?= isset($row['supplier_id']) ? $row['supplier_id'] : '' ?>">
                                    <div class="row">
                                        <!-- Supplier Name -->
                                        <div class="form-group col-md-3">
                                            <label for="supplier_name">Supplier Name</label>
                                            <input type="text" class="form-control" id="supplier_name"
                                                name="supplier_name" placeholder="Enter Supplier Name"
                                                value="<?= isset($row['supplier_name']) ? $row['supplier_name'] : '' ?>"
                                                required>
                                        </div>

                                        <!-- Email -->
                                        <div class="form-group col-md-3">
                                            <label for="email">Email</label>
                                            <input type="email" class="form-control emailVerify" id="email" name="email"
                                                value="<?= isset($row['email']) ? $row['email'] : '' ?>"
                                                placeholder="Enter Email" required>
                                        </div>

                                        <!-- Phone -->
                                        <div class="form-group col-md-3">
                                            <label for="phone">Phone</label>
                                            <input type="tel" class="form-control numInput numberVerify" length="10"
                                                minlength="10" maxlength="10" id="phone" name="phone"
                                                value="<?= isset($row['phone']) ? $row['phone'] : '' ?>"
                                                placeholder="Enter Phone Number" required>
                                        </div>

                                        <!-- Registration Date -->
                                        <div class="form-group col-md-3">
                                            <label for="registration_date">Registration Date</label>
                                            <input type="text" class="datepicker" id="registration_date"
                                                value="<?= isset($row['registration_date']) ? $row['registration_date'] : '' ?>"
                                                placeholder="Select Date" name="registration_date" required>
                                        </div>

                                        <!-- Street Address -->
                                        <div class="form-group col-md-6">
                                            <label for="street_address">Street Address</label>
                                            <input type="text" class="form-control" id="street_address"
                                                value="<?= isset($row['street_address']) ? $row['street_address'] : '' ?>"
                                                name="street_address" placeholder="Enter Street Address" required>
                                        </div>

                                        <!-- Postal Code -->
                                        <div class="form-group col-md-3">
                                            <label for="postal_code">Postal Code</label>
                                            <input type="text" class="form-control numberVerify" length="6"
                                                minlength="6" maxlength="6" id="postal_code" name="postal_code"
                                                value="<?= isset($row['postal_code']) ? $row['postal_code'] : '' ?>"
                                                placeholder="Enter Postal Code" required>
                                        </div>

                                        <!-- City -->
                                        <div class="form-group col-md-3">
                                            <label for="city">City</label>
                                            <input type="text" class="form-control" id="city" name="city"
                                                value="<?= isset($row['city']) ? $row['city'] : '' ?>"
                                                placeholder="Enter City" required>
                                        </div>

                                        <!-- State/Province -->
                                        <div class="form-group col-md-3">
                                            <label for="state_province">State/Province</label>
                                            <input type="text" class="form-control" id="state_province"
                                                value="<?= isset($row['state_province']) ? $row['state_province'] : '' ?>"
                                                a name="state_province" placeholder="Enter State/Province" required>
                                        </div>

                                        <!-- Country -->
                                        <div class="form-group col-md-3">
                                            <label for="country">Country</label>
                                            <input type="text" class="form-control" id="country" name="country"
                                                value="<?= isset($row['country']) ? $row['country'] : '' ?>"
                                                placeholder="Enter Country" required>
                                        </div>

                                        <!-- GST Type -->
                                        <div class="form-group col-md-3">
                                            <label for="gst_type">GST Type</label>
                                            <select class="form-control" id="gst_type" name="gst_type" required>
                                                <option value="">Select GST Type</option>
                                                <option value="Regular"
                                                    <?= isset($row['gst_type']) && $row['gst_type'] == 'Regular' ? 'selected' : '' ?>>
                                                    Regular</option>
                                                <option value="Composition"
                                                    <?= isset($row['gst_type']) && $row['gst_type'] == 'Composition' ? 'selected' : '' ?>>
                                                    Composition</option>
                                                <option value="Unregistered"
                                                    <?= isset($row['gst_type']) && $row['gst_type'] == 'Unregistered' ? 'selected' : '' ?>>
                                                    Unregistered</option>
                                            </select>
                                        </div>

                                        <!-- GSTIN -->
                                        <div class="form-group col-md-3 gstin-group">
                                            <label for="gstin">GSTIN</label>
                                            <input type="text" class="form-control" id="gstin" name="gstin"
                                                value="<?= isset($row['gstin']) ? $row['gstin'] : '' ?>"
                                                placeholder="Enter GSTIN">
                                        </div>

                                        <!-- PAN -->
                                        <div class="form-group col-md-3">
                                            <label for="pan">PAN</label>
                                            <input type="text" class="form-control" id="pan" name="pan"
                                                value="<?= isset($row['pan']) ? $row['pan'] : '' ?>"
                                                placeholder="Enter PAN">
                                        </div>

                                        <!-- TAN -->
                                        <div class="form-group col-md-3">
                                            <label for="tan">TAN</label>
                                            <input type="text" class="form-control" id="tan" name="tan"
                                                value="<?= isset($row['tan']) ? $row['tan'] : '' ?>"
                                                placeholder="Enter TAN">
                                        </div>

                                        <!-- CIN -->
                                        <div class="form-group col-md-3">
                                            <label for="cin">CIN</label>
                                            <input type="text" class="form-control" id="cin" name="cin"
                                                value="<?= isset($row['cin']) ? $row['cin'] : '' ?>"
                                                placeholder="Enter CIN">
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="form-group col-md-12">
                                            <button type="submit" name="supplier"
                                                class="btn btn-primary">Submit</button>
                                        </div>
                                    </div>
                                </form>


                            </div>
                        </div>
                    </div>
                    <?php include './includes/layouts/dash-footer.php'; ?>
                    <div class="content-backdrop fade"></div>
                </div>
            </div>
        </div>
        <div class="layout-overlay layout-menu-toggle"></div>
    </div>
    <?php include './includes/layouts/scripts.php'; ?>
    <script>
        $(function() {
            $("#gst_type").change(function() {
                let value = $(this).val();
                if (value == 'Regular' || value == 'Composition') {
                    $('.gstin-group').show().find('input').attr('required', true);
                } else {
                    $('.gstin-group').hide().find('input').removeAttr('required');
                }
            })
        })
    </script>
</body>

</html>