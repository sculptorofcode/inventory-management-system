<?php
require_once 'includes/config/after-login.php';
$title = 'Profile';
if (isset($_POST['update_profile'])) {
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $organization = $_POST['organization'];
    $address = $_POST['address'];
    $state = $_POST['state'];
    $zip_code = $_POST['zip_code'];
    $country = $_POST['country'];
    $update_profile = update_profile([
        'first_name' => $first_name,
        'last_name' => $last_name,
        'company_name' => $organization,
        'street_address' => $address,
        'state_province' => $state,
        'postal_code' => $zip_code,
        'country' => $country
    ], $userdata['customer_id']);
    if ($update_profile) {
        $res = ['status' => 'success', 'message' => 'Profile updated successfully', 'redirect' => 'profile'];
    } else {
        $res = ['status' => 'error', 'message' => 'Failed to update profile'];
    }

    echo json_encode($res);
    exit;
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

                        <div class="row">
                            <div class="col-md-12">
                                <ul class="nav nav-pills flex-column flex-md-row mb-3 d-none">
                                    <li class="nav-item">
                                        <a class="nav-link active" href=""><i class="bx bx-user me-1"></i> Account</a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" href="pages-account-settings-notifications.html"><i
                                                class="bx bx-bell me-1"></i> Notifications</a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" href="pages-account-settings-connections.html"><i
                                                class="bx bx-link-alt me-1"></i> Connections</a>
                                    </li>
                                </ul>
                                <div class="card mb-4">
                                    <h5 class="card-header">Profile Details</h5>
                                    <!-- Account -->
                                    <div class="card-body d-none">
                                        <div class="d-flex align-items-start align-items-sm-center gap-4">
                                            <img src="<?= APP_LOGO_ICON ?>" alt="user-avatar" class="d-block rounded"
                                                height="100" width="100" id="uploadedAvatar" />
                                            <div class="button-wrapper">
                                                <label for="upload" class="btn btn-primary me-2 mb-4" tabindex="0">
                                                    <span class="d-none d-sm-block">Upload new photo</span>
                                                    <i class="bx bx-upload d-block d-sm-none"></i>
                                                    <input type="file" id="upload" class="account-file-input" hidden
                                                        accept="image/png, image/jpeg" />
                                                </label>
                                                <button type="button"
                                                    class="btn btn-outline-secondary account-image-reset mb-4">
                                                    <i class="bx bx-reset d-block d-sm-none"></i>
                                                    <span class="d-none d-sm-block">Reset</span>
                                                </button>

                                                <p class="text-muted mb-0">Allowed JPG, GIF or PNG. Max size of 800K</p>
                                            </div>
                                        </div>
                                    </div>
                                    <hr class="my-0" />
                                    <div class="card-body">
                                        <form id="formAccountSettings" method="POST" class="form">
                                            <div class="row">
                                                <div class="mb-3 col-md-6">
                                                    <label for="firstName" class="form-label">First Name</label>
                                                    <input class="form-control" type="text" id="firstName"
                                                        name="first_name" value="<?= $userdata['first_name'] ?>"
                                                        placeholder="First Name" />
                                                </div>
                                                <div class="mb-3 col-md-6">
                                                    <label for="lastName" class="form-label">Last Name</label>
                                                    <input class="form-control" type="text" id="lastName"
                                                        name="last_name" value="<?= $userdata['last_name'] ?>"
                                                        placeholder="Last Name" />
                                                </div>
                                                <div class="mb-3 col-md-6">
                                                    <label for="email" class="form-label">E-mail</label>
                                                    <input class="form-control" type="email" id="email" name="email"
                                                        placeholder="Email" value="<?= $userdata['email'] ?>"
                                                        readonly />
                                                </div>
                                                <div class="mb-3 col-md-6">
                                                    <label for="organization" class="form-label">Organization</label>
                                                    <input class="form-control" type="text" id="organization"
                                                        name="organization" value="<?= $userdata['company_name'] ?>"
                                                        placeholder="Organization" />
                                                </div>
                                                <div class="mb-3 col-md-6">
                                                    <label class="form-label" for="phoneNumber">Phone Number</label>
                                                    <div class="input-group input-group-merge">
                                                        <span class="input-group-text">India (+91)</span>
                                                        <input type="text" id="phoneNumber" name="phone_number"
                                                            class="form-control ps-2" placeholder="+91 98653 215487"
                                                            value="<?= $userdata['phone'] ?>" readonly />
                                                    </div>
                                                </div>
                                                <div class="mb-3 col-md-6">
                                                    <label for="address" class="form-label">Address</label>
                                                    <input type="text" class="form-control" id="address" name="address"
                                                        value="<?= $userdata['street_address'] ?>"
                                                        placeholder="Address" />
                                                </div>
                                                <div class="mb-3 col-md-6">
                                                    <label for="state" class="form-label">State</label>
                                                    <input class="form-control" type="text" id="state" name="state"
                                                        value="<?= $userdata['state_province'] ?>"
                                                        placeholder="Enter State" />
                                                </div>
                                                <div class="mb-3 col-md-6">
                                                    <label for="zipCode" class="form-label">Zip Code</label>
                                                    <input type="text" class="form-control" id="zipCode" name="zip_code"
                                                        value="<?= $userdata['postal_code'] ?>"
                                                        placeholder="Enter Pin Code" maxlength="6" />
                                                </div>
                                                <div class="mb-3 col-md-6">
                                                    <label class="form-label" for="country">Country</label>
                                                    <select id="country" name="country" class="select2 form-select">
                                                        <option value="">Select</option>

                                                        <?php
                                                        $countries = get_countries();
                                                        foreach ($countries as $country) {
                                                            $selected = $country['country_name'] == $userdata['country'] ? 'selected' : '';
                                                            echo "<option value='{$country['country_name']}' $selected>{$country['country_name']}</option>";
                                                        }
                                                        ?>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="mt-2">
                                                <button type="submit" class="btn btn-primary me-2"
                                                    name="update_profile">Save changes</button>
                                            </div>
                                        </form>

                                    </div>
                                    <!-- /Account -->
                                </div>
                                <div class="card d-none">
                                    <h5 class="card-header">Delete Account</h5>
                                    <div class="card-body">
                                        <div class="mb-3 col-12 mb-0">
                                            <div class="alert alert-warning">
                                                <h6 class="alert-heading fw-bold mb-1">Are you sure you want to delete
                                                    your account?</h6>
                                                <p class="mb-0">Once you delete your account, there is no going back.
                                                    Please be certain.</p>
                                            </div>
                                        </div>
                                        <form id="formAccountDeactivation" onsubmit="return false">
                                            <div class="form-check mb-3">
                                                <input class="form-check-input" type="checkbox" name="accountActivation"
                                                    id="accountActivation" />
                                                <label class="form-check-label" for="accountActivation">I confirm my
                                                    account deactivation</label>
                                            </div>
                                            <button type="submit" class="btn btn-danger deactivate-account">Deactivate
                                                Account</button>
                                        </form>
                                    </div>
                                </div>
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
</body>

</html>