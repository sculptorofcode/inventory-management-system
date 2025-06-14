<?php
require_once 'includes/config/after-login.php';
$title = 'Register New User';
if (isset($_GET['id'])) {
    $title = 'Edit User';
    $row = getCustomerById(intval($_GET['id']));
    if (!$row) {
        header('Location: customer-list');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en" class="light-style layout-menu-fixed" dir="ltr" data-theme="theme-default" data-assets-path="assets/"
      data-template="vertical-menu-template-free">

<head>
    <meta charset="utf-8"/>
    <meta name="viewport"
          content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0"/>
    <?php include './includes/layouts/styles.php'; ?>
    <style>
        .form-step {
            display: none;
        }

        .form-step.active {
            display: block;
        }

        .progress-step {
            flex: 1;
            text-align: center;
            position: relative;
            padding-bottom: 10px;
            cursor: pointer;
        }

        .progress-step::before {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 0%;
            height: 2px;
            background-color: var(--primary-color);
            transition: width 0.3s ease;
        }

        .progress-step.active::before {
            width: 100%;
        }

        .button-container {
            display: flex;
            justify-content: space-between;
            margin-top: 2rem;
        }

        .btn {
            padding: 0.5rem 1rem;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .verify-button {
            display: none;
        }
    </style>
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
                        <div class="card-header border-bottom p-3">
                            <h4 class="card-title mb-0"><?= $title ?></h4>
                        </div>
                        <div class="card-body pt-4">
                            <div class="row justify-content-center">
                                <div class="col-12 col-md-6">
                                    <div class="progress-bar mb-3 row flex-row  bg-transparent text-primary gap-2 shadow-none">
                                        <div class="col progress-step py-2 bg-transparent active">Personal</div>
                                        <div class="col progress-step py-2 bg-transparent">Address</div>
                                        <div class="col progress-step py-2 bg-transparent">Account</div>
                                        <div class="col progress-step py-2 bg-transparent">Preferences</div>
                                    </div>
                                </div>
                            </div>

                            <form action="register" id="registerForm" class="form" data-reset="true">
                                <input type="hidden" name="form_action" value="<?= isset($_GET['id']) && intval($_GET['id']) > 0 ? 'update' : 'add' ?>">
                                <input type="hidden" name="customer_id" value="<?= $row['customer_id'] ?? '' ?>">
                                <!-- Step 1: Personal Information -->
                                <div class="form-step active">
                                    <div class="row">
                                        <div class="mb-3 col-12 col-md-6 col-lg-4 col-xl-3 col-xxl">
                                            <label for="first_name" class="form-label">First Name</label>
                                            <input type="text" class="form-control nameVerify" id="first_name"
                                                   name="first_name"
                                                   placeholder="Enter First Name" required
                                                   value="<?= $row['first_name'] ?? '' ?>">
                                            <div class="invalid-feedback"></div>
                                        </div>
                                        <div class="mb-3 col-12 col-md-6 col-lg-4 col-xl-3 col-xxl">
                                            <label for="last_name" class="form-label">Last Name</label>
                                            <input type="text" class="form-control nameVerify" id="last_name"
                                                   name="last_name"
                                                   placeholder="Enter Last Name" required
                                                   value="<?= $row['last_name'] ?? '' ?>">
                                            <div class="invalid-feedback"></div>
                                        </div>
                                        <div class="mb-3 col-12 col-md-6 col-lg-4 col-xl-3 col-xxl">
                                            <label for="email" class="form-label">Email address</label>
                                            <div class="input-group">
                                                <input type="email" class="form-control emailVerify" id="email"
                                                       name="email" <?php if (isset($_GET['id'])): ?> readonly <?php endif; ?>
                                                       placeholder="Enter Email" required
                                                       value="<?= $row['email'] ?? '' ?>">
                                                <button class="btn btn-primary verify-button" type="button"
                                                        id="verifyEmail">Verify
                                                </button>
                                            </div>
                                            <div class="invalid-feedback"></div>
                                        </div>
                                        <div class="mb-3 col-12 col-md-6 col-lg-4 col-xl-3 col-xxl">
                                            <label for="phone" class="form-label">Phone Number</label>
                                            <div class="input-group">
                                                <input type="text" class="form-control numInput numberVerify" id="phone"
                                                       name="phone" maxlength="10" minlength="10"
                                                       value="<?= $row['phone'] ?? '' ?>" <?php if (isset($_GET['id'])): ?> readonly <?php endif; ?>
                                                       placeholder="Enter Phone Number" required>
                                                <button class="btn btn-primary verify-button" type="button"
                                                        id="verifyPhone">Verify
                                                </button>
                                            </div>
                                            <div class="invalid-feedback"></div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Step 2: Address Information -->
                                <div class="form-step">
                                    <div class="row">
                                        <div class="mb-3 col-12 col-md-6 col-lg-4 col-xl-3 col-xxl">
                                            <label for="street_address" class="form-label">Street Address</label>
                                            <input type="text" class="form-control" id="street_address"
                                                   name="street_address" value="<?= $row['street_address'] ?? '' ?>"
                                                   placeholder="Enter Street Address" required>
                                            <div class="invalid-feedback"></div>
                                        </div>
                                        <div class="mb-3 col-12 col-md-6 col-lg-4 col-xl-3 col-xxl">
                                            <label for="postal_code" class="form-label">Postal Code</label>
                                            <input type="text" class="form-control numberVerify" length="6"
                                                   minlength="6" value="<?= $row['postal_code'] ?? '' ?>"
                                                   maxlength="6" id="postal_code" name="postal_code"
                                                   placeholder="Enter Postal Code"
                                                   required>
                                            <div class="invalid-feedback"></div>
                                        </div>
                                        <div class="mb-3 col-12 col-md-6 col-lg-4 col-xl-3 col-xxl">
                                            <label for="city" class="form-label">City</label>
                                            <input type="text" class="form-control" id="city" name="city"
                                                   placeholder="Enter City" value="<?= $row['city'] ?? '' ?>"
                                                   required>
                                            <div class="invalid-feedback"></div>
                                        </div>
                                        <div class="mb-3 col-12 col-md-6 col-lg-4 col-xl-3 col-xxl">
                                            <label for="state_province" class="form-label">State/Province</label>
                                            <input type="text" class="form-control" id="state_province"
                                                   name="state_province" value="<?= $row['state_province'] ?? '' ?>"
                                                   placeholder="Enter State/Province" required>
                                            <div class="invalid-feedback"></div>
                                        </div>
                                        <div class="mb-3 col-12 col-md-6 col-lg-4 col-xl-3 col-xxl">
                                            <label for="country" class="form-label">Country</label>
                                            <input type="text" class="form-control" id="country" name="country"
                                                   value="<?= $row['country'] ?? '' ?>"
                                                   placeholder="Enter Country" required>
                                            <div class="invalid-feedback"></div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Step 3: Account Information -->
                                <div class="form-step">
                                    <div class="row">
                                        <div class="mb-3 col-12 col-md-6 col-lg-4 col-xl-3 col-xxl">
                                            <label for="username" class="form-label">Username</label>
                                            <input type="text" class="form-control" id="username" name="username"
                                                   value="<?= $row['username'] ?? '' ?>"
                                                   placeholder="Choose a Username" required readonly>
                                            <div class="invalid-feedback"></div>
                                        </div>
                                        <?php if (!isset($_GET['id'])): ?>
                                            <div class="mb-3 col-12 col-md-6 col-lg-4 col-xl-3 col-xxl">
                                                <label for="password" class="form-label">Password</label>
                                                <div class="input-group">
                                                    <input type="password" class="form-control" id="password"
                                                           value="<?= $row['password'] ?? '' ?>"
                                                           name="password"
                                                           placeholder="●●●●●●●●" required>
                                                    <button class="btn btn-outline-secondary" type="button"
                                                            id="togglePassword">
                                                        <i class="bx bx-hide"></i>
                                                    </button>
                                                </div>
                                                <div class="invalid-feedback"></div>
                                            </div>
                                        <?php endif; ?>
                                        <div class="mb-3 col-12 col-md-6 col-lg-4 col-xl-3 col-xxl">
                                            <label for="company_name" class="form-label">Company Name</label>
                                            <input type="text" class="form-control" id="company_name"
                                                   name="company_name" value="<?= $row['company_name'] ?? '' ?>"
                                                   placeholder="Enter Company Name" required>
                                            <div class="invalid-feedback"></div>
                                        </div>
                                        <div class="mb-3 col-12 col-md-6 col-lg-4 col-xl-3 col-xxl">
                                            <label for="tax_identification_number" class="form-label">Tax Identification
                                                Number</label>
                                            <input type="text" class="form-control" id="tax_identification_number"
                                                   value="<?= $row['tax_identification_number'] ?? '' ?>"
                                                   name="tax_identification_number" placeholder="Enter TIN">
                                            <div class="invalid-feedback"></div>
                                        </div>
                                        <div class="mb-3 col-12 col-md-6 col-lg-4 col-xl-3 col-xxl">
                                            <label for="business_type" class="form-label">Business Type</label>
                                            <input type="text" class="form-control" id="business_type"
                                                   value="<?= $row['business_type'] ?? '' ?>"
                                                   name="business_type"
                                                   placeholder="Enter Business Type" required>
                                            <div class="invalid-feedback"></div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Step 4: Preferences and Security -->
                                <div class="form-step">
                                    <div class="row">
                                        <div class="mb-3 col-12 col-md-6 col-lg-4 col-xl-3 col-xxl">
                                            <label for="preferred_contact_method" class="form-label">Preferred Contact
                                                Method</label>
                                            <select class="form-select" id="preferred_contact_method"
                                                    name="preferred_contact_method" required>
                                                <option value="email" <?= isset($row['preferred_contact_method']) && $row['preferred_contact_method'] == 'email' ? 'selected' : '' ?>>
                                                    Email
                                                </option>
                                                <option value="phone" <?= isset($row['preferred_contact_method']) && $row['preferred_contact_method'] == 'phone' ? 'selected' : '' ?>>
                                                    Phone
                                                </option>
                                            </select>
                                            <div class="invalid-feedback"></div>
                                        </div>
                                        <div class="mb-3 col-12 col-md-6 col-lg-4 col-xl-3 col-xxl">
                                            <label for="referral_source" class="form-label">Referral Source</label>
                                            <input type="text" class="form-control" id="referral_source"
                                                   value="<?= $row['referral_source'] ?? '' ?>"
                                                   name="referral_source"
                                                   placeholder="Enter Referral Source" required>
                                            <div class="invalid-feedback"></div>
                                        </div>
                                        <div class="mb-3 col-12 col-md-6 col-lg-4 col-xl-3 col-xxl">
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox"
                                                       id="newsletter_subscription" <?= isset($row['newsletter_subscription']) && $row['newsletter_subscription'] == 1 ? 'checked' : '' ?>
                                                       name="newsletter_subscription" value="1">
                                                <label class="form-check-label" for="newsletter_subscription">
                                                    Receive newsletters
                                                </label>
                                            </div>
                                        </div>
                                        <div class="mb-3 col-12 col-md-6 col-lg-4 col-xl-3 col-xxl">
                                            <label for="security_question" class="form-label">Security Question</label>
                                            <input type="text" class="form-control" id="security_question"
                                                   name="security_question"
                                                   value="<?= $row['security_question'] ?? '' ?>"
                                                   placeholder="Enter Security Question" required>
                                            <div class="invalid-feedback"></div>
                                        </div>
                                        <div class="mb-3 col-12 col-md-6 col-lg-4 col-xl-3 col-xxl">
                                            <label for="security_answer" class="form-label">Security Answer</label>
                                            <input type="text" class="form-control" id="security_answer"
                                                   name="security_answer" value="<?= $row['security_answer'] ?? '' ?>"
                                                   placeholder="Enter Security Answer" required>
                                            <div class="invalid-feedback"></div>
                                        </div>
                                        <div class="mb-3 col-md-12">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="agreed_to_terms"
                                                       name="agreed_to_terms"
                                                       required <?= isset($row['agreed_to_terms']) && $row['agreed_to_terms'] == 1 ? 'checked' : '' ?>>
                                                <label class="form-check-label" for="agreed_to_terms">
                                                    I agree to the terms and conditions
                                                </label>
                                                <div class="invalid-feedback"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Navigation Buttons -->
                                <div class="button-container justify-content-center gap-4">
                                    <button type="button" class="btn btn-secondary prev-btn" style="display: none;">
                                        Previous
                                    </button>
                                    <button type="button" class="btn btn-primary next-btn">Next</button>
                                    <button type="submit" name="register" class="btn btn-success" id="registerBtn"
                                            style="display: none;">
                                        <?php if (isset($_GET['id'])): ?>
                                            Update
                                        <?php else: ?>
                                            Register
                                        <?php endif; ?>
                                    </button>
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
<script src="assets/js/register.js"></script>
</body>

</html>