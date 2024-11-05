<?php
include_once 'includes/config/database.php';
$title = "Register";
if (isset($_POST['check_email'])) {
    $email = filtervar($_POST['check_email']);
    if (isEmailRegistered($email)) {
        echo 'taken';
    } else {
        echo 'available';
    }
    exit();
}
if (isset($_POST['check_number'])) {
    $number = filtervar($_POST['check_number']);
    if (isMobileRegistered($number)) {
        echo 'taken';
    } else {
        echo 'available';
    }
    exit();
}

if (isset($_POST['verify_email'])) {
    $email = filtervar($_POST['verify_email']);
    if (isEmailRegistered($email)) {
        $res = ['status' => 'error', 'message' => 'Email is already taken'];
    } else {
        if (isset($_SESSION['register_props']['email']) && $_SESSION['register_props']['email'] === $email) {
            $res = ['status' => 'info', 'message' => 'Email already verified'];
        } else {
            $to = $email;
            $subject = 'Email Verification';
            $otp = $_SESSION['email_otp'] = generateOTP(6);
            $body = email_otp_template($otp);

            if ($mailer->sendMail($to, $subject, $body)) {
                if (DEBUG_MODE) {
                    $res = ['status' => 'success', 'otp' => $otp, 'message' => 'OTP sent successfully'];
                } else {
                    $res = ['status' => 'success', 'message' => 'OTP sent successfully'];
                }
            } else {
                $res = ['status' => 'error', 'message' => 'Failed to send OTP'];
            }
        }
    }
    echo json_encode($res);
    exit();
}

if (isset($_POST['verify_email_otp'])) {
    $otp = filtervar($_POST['verify_email_otp']);
    $email = filtervar($_POST['email']);
    if ($otp === $_SESSION['email_otp']) {
        $_SESSION['register_props']['email'] = $email;
        $res = ['status' => 'success', 'message' => 'Email verified successfully'];
    } else {
        $res = ['status' => 'error', 'message' => 'Invalid OTP'];
    }
    echo json_encode($res);
    exit();
}

if (isset($_POST['verify_phone'])) {
    $number = filtervar($_POST['verify_phone']);
    if (isMobileRegistered($number)) {
        $res = ['status' => 'error', 'message' => 'Number is already taken'];
    } else {
        if (isset($_SESSION['register_props']['phone']) && $_SESSION['register_props']['phone'] === $number) {
            $res = ['status' => 'info', 'message' => 'Phone number already verified'];
        } else {
            $to = $number;
            $otp = $_SESSION['phone_otp'] = generateOTP(6);
            $message = "Your OTP for phone verification is: $otp";
            $response = $sms->sendOTP($to, 0, $otp);
            if ($response['status'] === 'success') {
                if (DEBUG_MODE) {
                    $res = ['status' => 'success', 'otp' => $otp, 'message' => 'OTP sent successfully'];
                } else {
                    $res = ['status' => 'success', 'message' => 'OTP sent successfully'];
                }
            } else {
                $res = ['status' => 'error', 'message' => 'Failed to send OTP'];
            }
        }
    }
    echo json_encode($res);
    exit();
}

if (isset($_POST['verify_phone_otp'])) {
    $otp = filtervar($_POST['verify_phone_otp']);
    $number = filtervar($_POST['number']);
    if ($otp === $_SESSION['phone_otp']) {
        $_SESSION['register_props']['phone'] = $number;
        $res = ['status' => 'success', 'message' => 'Phone number verified successfully'];
    } else {
        $res = ['status' => 'error', 'message' => 'Invalid OTP'];
    }
    echo json_encode($res);
    exit();
}

if (isset($_POST['register'])) {
    $first_name = filtervar($_POST['first_name']);
    $last_name = filtervar($_POST['last_name']);
    $full_name = ucwords(strtolower($first_name)) . ' ' . ucwords(strtolower($last_name));
    $email = filtervar($_POST['email']);
    $phone = filtervar($_POST['phone']);
    $street_address = filtervar($_POST['street_address']);
    $postal_code = filtervar($_POST['postal_code']);
    $city = filtervar($_POST['city']);
    $state_province = filtervar($_POST['state_province']);
    $country = filtervar($_POST['country']);
    $username = filtervar($_POST['username']);
    $password = filtervar($_POST['password']);
    $password_hash = password_hash($password, PASSWORD_BCRYPT);
    $company_name = filtervar($_POST['company_name']);
    $tax_identification_number = filtervar($_POST['tax_identification_number']);
    $business_type = filtervar($_POST['business_type']);
    $preferred_contact_method = filtervar($_POST['preferred_contact_method']);
    $referral_source = filtervar($_POST['referral_source']);
    $newsletter_subscription = isset($_POST['newsletter_subscription']) ? 1 : 0;
    $security_question = filtervar($_POST['security_question']);
    $security_answer = filtervar($_POST['security_answer']);
    $agreed_to_terms = isset($_POST['agreed_to_terms']) ? 1 : 0;

    if (isset($_SESSION['register_props'])) {
        if ($_SESSION['register_props']['email'] !== $email) {
            $res = ['status' => 'error', 'message' => 'Email not verified'];
            echo json_encode($res);
            exit();
        }
        if ($_SESSION['register_props']['phone'] !== $phone) {
            $res = ['status' => 'error', 'message' => 'Phone number not verified'];
            echo json_encode($res);
            exit();
        }
        $email = $_SESSION['register_props']['email'];
        $phone = $_SESSION['register_props']['phone'];

        $sql = "INSERT INTO $table_customers 
                SET 
                    `first_name` = :first_name,
                    `last_name` = :last_name,
                    `full_name` = :full_name,
                    `email` = :email,
                    `phone` = :phone,
                    `street_address` = :street_address,
                    `postal_code` = :postal_code,
                    `city` = :city,
                    `state_province` = :state_province,
                    `country` = :country,
                    `username` = :username,
                    `password_hash` = :password_hash,
                    `company_name` = :company_name,
                    `tax_identification_number` = :tax_identification_number,
                    `business_type` = :business_type,
                    `preferred_contact_method` = :preferred_contact_method,
                    `referral_source` = :referral_source,
                    `newsletter_subscription` = :newsletter_subscription,
                    `security_question` = :security_question,
                    `security_answer` = :security_answer,
                    `agreed_to_terms` = :agreed_to_terms";

        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':first_name', $first_name);
        $stmt->bindParam(':last_name', $last_name);
        $stmt->bindParam(':full_name', $full_name);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':phone', $phone);
        $stmt->bindParam(':street_address', $street_address);
        $stmt->bindParam(':postal_code', $postal_code);
        $stmt->bindParam(':city', $city);
        $stmt->bindParam(':state_province', $state_province);
        $stmt->bindParam(':country', $country);
        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':password_hash', $password_hash);
        $stmt->bindParam(':company_name', $company_name);
        $stmt->bindParam(':tax_identification_number', $tax_identification_number);
        $stmt->bindParam(':business_type', $business_type);
        $stmt->bindParam(':preferred_contact_method', $preferred_contact_method);
        $stmt->bindParam(':referral_source', $referral_source);
        $stmt->bindParam(':newsletter_subscription', $newsletter_subscription);
        $stmt->bindParam(':security_question', $security_question);
        $stmt->bindParam(':security_answer', $security_answer);
        $stmt->bindParam(':agreed_to_terms', $agreed_to_terms);
        if ($stmt->execute()) {

            try {
                // Send welcome email
                $creation_date = date('Y-m-d H:i:s');
                $welcome_email = welcome_email_template($full_name, $email, $creation_date);
                $subject = 'Welcome to ' . APP_NAME;
                $mailer->sendMail($email, $subject, $welcome_email);
            } catch (Exception $e) {
                if(DEBUG_MODE) {
                    echo $e->getMessage();
                }
            }

            $res = ['status' => 'success', 'message' => 'Account created successfully', 'redirect' => 'login', 'delay' => 3000];
            echo json_encode($res);
            exit();
        } else {
            $res = ['status' => 'error', 'message' => 'Failed to create account'];
            echo json_encode($res);
            exit();
        }
    } else {
        $res = ['status' => 'error', 'message' => 'Email and Phone number not verified'];
        echo json_encode($res);
        exit();
    }
}


?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php include_once 'includes/layouts/header.php'; ?>
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
    </style>
</head>

<body class="login-body">
    <section class="d-flex align-items-center justify-content-center">
        <div class="container">
            <div class="login-container">
                <div class="login-header">
                    <a href="index" class="text-decoration-none">
                        <h1><?= APP_NAME ?></h1>
                    </a>
                    <p>Create a new account. Please fill in the details below.</p>
                </div>
                <div class="progress-bar flex-row mb-3">
                    <div class="progress-step active">Personal</div>
                    <div class="progress-step">Address</div>
                    <div class="progress-step">Account</div>
                    <div class="progress-step">Preferences</div>
                </div>
                <form action="" method="POST" id="registerForm" class="form" data-reset="true">
                    <!-- Step 1: Personal Information -->
                    <div class="form-step active">
                        <div class="row">
                            <div class="mb-3 col-md-6">
                                <label for="first_name" class="form-label">First Name</label>
                                <input type="text" class="form-control nameVerify" id="first_name" name="first_name"
                                    placeholder="Enter First Name" required>
                                <span class="invalid-feedback fw-bold"></span>
                            </div>
                            <div class="mb-3 col-md-6">
                                <label for="last_name" class="form-label">Last Name</label>
                                <input type="text" class="form-control nameVerify" id="last_name" name="last_name"
                                    placeholder="Enter Last Name" required>
                                <span class="invalid-feedback fw-bold"></span>
                            </div>
                            <div class="mb-3 col-md-6">
                                <label for="email" class="form-label">Email address</label>
                                <div class="input-group">
                                    <input type="email" class="form-control emailVerify" id="email" name="email"
                                        placeholder="Enter Email" required>
                                    <button class="verify-button" type="button" id="verifyEmail">Verify</button>
                                </div>
                                <span class="invalid-feedback fw-bold"></span>
                            </div>
                            <div class="mb-3 col-md-6">
                                <label for="phone" class="form-label">Phone Number</label>
                                <div class="input-group">
                                    <input type="text" class="form-control numInput numberVerify" id="phone"
                                        name="phone" maxlength="10" minlength="10" length="10"
                                        placeholder="Enter Phone Number" required>
                                    <button class="verify-button" type="button" id="verifyPhone">Verify</button>
                                </div>
                                <span class="invalid-feedback fw-bold"></span>
                            </div>
                        </div>
                    </div>

                    <!-- Step 2: Address Information -->
                    <div class="form-step">
                        <div class="row">
                            <div class="mb-3 col-md-6">
                                <label for="street_address" class="form-label">Street Address</label>
                                <input type="text" class="form-control" id="street_address" name="street_address"
                                    placeholder="Enter Street Address" required>
                                <span class="invalid-feedback fw-bold"></span>
                            </div>
                            <div class="mb-3 col-md-6">
                                <label for="postal_code" class="form-label">Postal Code</label>
                                <input type="text" class="form-control numberVerify" length="6" minlength="6"
                                    maxlength="6" id="postal_code" name="postal_code" placeholder="Enter Postal Code"
                                    required>
                                <span class="invalid-feedback fw-bold"></span>
                            </div>
                            <div class="mb-3 col-md-6">
                                <label for="city" class="form-label">City</label>
                                <input type="text" class="form-control" id="city" name="city" placeholder="Enter City"
                                    required>
                                <span class="invalid-feedback fw-bold"></span>
                            </div>
                            <div class="mb-3 col-md-6">
                                <label for="state_province" class="form-label">State/Province</label>
                                <input type="text" class="form-control" id="state_province" name="state_province"
                                    placeholder="Enter State/Province" required>
                                <span class="invalid-feedback fw-bold"></span>
                            </div>
                            <div class="mb-3 col-md-6">
                                <label for="country" class="form-label">Country</label>
                                <input type="text" class="form-control" id="country" name="country"
                                    placeholder="Enter Country" required>
                                <span class="invalid-feedback fw-bold"></span>
                            </div>
                        </div>
                    </div>

                    <!-- Step 3: Account Information -->
                    <div class="form-step">
                        <div class="row">
                            <div class="mb-3 col-md-6">
                                <label for="username" class="form-label">Username</label>
                                <input type="text" class="form-control" id="username" name="username"
                                    placeholder="Choose a Username" required readonly>
                                <span class="invalid-feedback fw-bold"></span>
                            </div>
                            <div class="mb-3 col-md-6">
                                <label for="password" class="form-label">Password</label>
                                <div class="input-group">
                                    <input type="password" class="form-control" id="password" name="password"
                                        placeholder="●●●●●●●●" required>
                                    <span class="invalid-feedback fw-bold"></span>
                                    <div class="cursor-pointer toggle-password">
                                        <i class="fa fa-eye"></i>
                                    </div>
                                </div>
                            </div>
                            <div class="mb-3 col-md-6">
                                <label for="company_name" class="form-label">Company Name</label>
                                <input type="text" class="form-control" id="company_name" name="company_name"
                                    placeholder="Enter Company Name" required>
                                <span class="invalid-feedback fw-bold"></span>
                            </div>
                            <div class="mb-3 col-md-6">
                                <label for="tax_identification_number" class="form-label">Tax Identification
                                    Number</label>
                                <input type="text" class="form-control" id="tax_identification_number"
                                    name="tax_identification_number" placeholder="Enter TIN">
                                <span class="invalid-feedback fw-bold"></span>
                            </div>
                            <div class="mb-3 col-md-6">
                                <label for="business_type" class="form-label">Business Type</label>
                                <input type="text" class="form-control" id="business_type" name="business_type"
                                    placeholder="Enter Business Type" required>
                                <span class="invalid-feedback fw-bold"></span>
                            </div>
                        </div>
                    </div>

                    <!-- Step 4: Preferences and Security -->
                    <div class="form-step">
                        <div class="row">
                            <div class="mb-3 col-md-6">
                                <label for="preferred_contact_method" class="form-label">Preferred Contact
                                    Method</label>
                                <select class="form-select" id="preferred_contact_method"
                                    name="preferred_contact_method" required>
                                    <option value="email">Email</option>
                                    <option value="phone">Phone</option>
                                </select>
                                <span class="invalid-feedback fw-bold"></span>
                            </div>
                            <div class="mb-3 col-md-6">
                                <label for="referral_source" class="form-label">Referral Source</label>
                                <input type="text" class="form-control" id="referral_source" name="referral_source"
                                    placeholder="Enter Referral Source" required>
                                <span class="invalid-feedback fw-bold"></span>
                            </div>
                            <div class="mb-3 col-md-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="newsletter_subscription"
                                        name="newsletter_subscription" value="1">
                                    <span class="invalid-feedback fw-bold"></span>
                                    <label class="form-check-label" for="newsletter_subscription">
                                        Yes, I want to receive newsletters.
                                    </label>
                                </div>
                            </div>
                            <div class="mb-3 col-md-6">
                                <label for="security_question" class="form-label">Security Question</label>
                                <input type="text" class="form-control" id="security_question" name="security_question"
                                    placeholder="Enter Security Question" required>
                                <span class="invalid-feedback fw-bold"></span>
                            </div>
                            <div class="mb-3 col-md-6">
                                <label for="security_answer" class="form-label">Security Answer</label>
                                <input type="text" class="form-control" id="security_answer" name="security_answer"
                                    placeholder="Enter Security Answer" required>
                                <span class="invalid-feedback fw-bold"></span>
                            </div>
                            <div class="mb-3 col-md-12">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="agreed_to_terms"
                                        name="agreed_to_terms" required>
                                    <span class="invalid-feedback fw-bold"></span>
                                    <label class="form-check-label" for="agreed_to_terms">
                                        I agree to the terms and conditions.
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Navigation Buttons -->
                    <div class="button-container justify-content-center gap-4">
                        <button type="button" class="btn btn-secondary prev-btn">Previous</button>
                        <button type="button" class="btn btn-primary next-btn">Next</button>
                        <button type="submit" name="register" class="btn btn-success" id="registerBtn"
                            style="display: none;">Register</button>
                    </div>
                </form>
                <div class="mt-3 d-flex justify-content-between">
                    <a href="login" class="register">Already have an account? Login</a>
                </div>
            </div>
        </div>
    </section>
    <?php include_once 'includes/layouts/footer.php'; ?>

    <script>
    const otpForm = (otpFor) => {
        return `
        <div class="container">
            <h1>OTP Verification</h1>
            <p>Enter the OTP sent to your ${otpFor}.</p>
            <input type="text" class="form-control otpInput" placeholder="Enter OTP" required>
        </div>
        `;
    };
    const form = document.getElementById('registerForm');
    const nextButton = document.querySelector('.next-btn');
    const prevButton = document.querySelector('.prev-btn');
    const registerButton = document.getElementById('registerBtn');
    const steps = document.querySelectorAll('.form-step');
    const progressSteps = document.querySelectorAll('.progress-step');
    let currentStep = 0;

    function showStep(stepIndex) {
        steps[currentStep].classList.remove('active');
        progressSteps[currentStep].classList.remove('active');
        steps[stepIndex].classList.add('active');
        progressSteps[stepIndex].classList.add('active');
        currentStep = stepIndex;

        prevButton.style.display = currentStep === 0 ? 'none' : 'block';
        nextButton.style.display = currentStep === steps.length - 1 ? 'none' : 'block';
        registerButton.style.display = currentStep === steps.length - 1 ? 'block' : 'none';
    }

    function validateStep(stepIndex) {
        const inputs = steps[stepIndex].getElementsByTagName('input');
        for (let input of inputs) {
            if (input.hasAttribute('required') && !input.value) {
                let sibling = input.nextElementSibling;
                sibling.textContent = 'This field is required';
                sibling.style.display = 'block';
                return false;
            }
            if (input.classList.contains('is-invalid')) {
                return false;
            }
        }
        return true;
    }

    nextButton.addEventListener('click', function() {
        if (validateStep(currentStep)) {
            showStep(currentStep + 1);
        }
    });

    prevButton.addEventListener('click', function() {
        showStep(currentStep - 1);
    });

    showStep(0);
    $(".emailVerify").on('input', function() {
        $this = $(this);
        let email = $this.val();
        if (email) {
            if ($this.is(':valid')) {
                $.post('register', {
                    check_email: email
                }, function(response) {
                    if (response === 'taken') {
                        $this.siblings('.verify-button').hide();
                        $this.addClass('is-invalid');
                        $this.parent().siblings('.invalid-feedback').text('Email is already taken');
                    } else {
                        $this.siblings('.verify-button').css({
                            'display': 'flex'
                        });
                        $this.removeClass('is-invalid');
                        $this.parent().siblings('.invalid-feedback').text('');
                    }
                });
            } else {
                $this.siblings('.verify-button').hide();
            }
        }
    });
    $('#phone').on('input', function() {
        $this = $(this);
        let number = $this.val();
        if (number) {
            if ($this.is(':valid')) {
                $.post('register', {
                    check_number: number
                }, function(response) {
                    if (response === 'taken') {
                        $this.siblings('.verify-button').hide();
                        $this.addClass('is-invalid');
                        $this.parent().siblings('.invalid-feedback').text('Number is already taken');
                    } else {
                        $this.siblings('.verify-button').css({
                            'display': 'flex'
                        });
                        $this.removeClass('is-invalid');
                        $this.parent().siblings('.invalid-feedback').text('');
                    }
                });
            } else {
                $this.siblings('.verify-button').hide();
            }
        }
    });
    $("#verifyEmail").on('click', function() {
        $this = $(this);
        let email = $('#email').val();
        if (email && $('#email').is(':valid')) {
            // Triggering loading spinner on button
            $('#verifyEmail').prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i>');

            // Make POST request to send verification email
            $.post('register', {
                verify_email: email
            }, function(response) {
                response = JSON.parse(response);

                // If the email is valid and successfully processed, show OTP modal
                if (response.status === 'info') {
                    toastr.info(response.message, 'Info');
                    $("#email").prop('readonly', true);
                    $("#username").val(email).prop(
                        'readonly', true);
                    $('#verifyEmail').hide();
                } else if (response.status === 'success') {
                    $('#verifyEmail').prop('disabled', true).html('Verify');

                    // Show the OTP modal using jConfirm
                    $.confirm({
                        title: 'OTP Verification',
                        content: otpForm('Email Address'),
                        buttons: {
                            verify: {
                                text: 'Verify OTP',
                                btnClass: 'btn-blue',
                                action: function() {
                                    var otp = this.$content.find('.otpInput').val();
                                    if (!otp) {
                                        $.alert('Please enter a valid OTP');
                                        return false;
                                    }

                                    // Verify OTP through backend
                                    $.post('register', {
                                        verify_email_otp: otp,
                                        email: email,
                                    }, function(otpResponse) {
                                        otpResponse = JSON.parse(otpResponse);
                                        if (otpResponse.status === 'success') {
                                            $('#verifyEmail').hide();
                                            $('.jconfirm').remove();
                                            $("#email").prop('readonly', true);
                                            $("#username").val(email).prop(
                                                'readonly', true);
                                            toastr.success(otpResponse.message,
                                                'Success');
                                        } else {
                                            toastr.error(otpResponse.message,
                                                'Error');
                                        }
                                    });
                                    return false;
                                }
                            },
                            cancel: function() {
                                $('#verifyEmail').prop('disabled', false).html('Verify');
                            }
                        }
                    });

                } else {
                    toastr.error(response.message, 'Error');
                    $('#verifyEmail').prop('disabled', false).html('Verify');
                }
            });
        }
    });

    $("#verifyPhone").on('click', function() {
        $this = $(this);
        let number = $('#phone').val();
        if (number && $('#phone').is(':valid')) {
            // Triggering loading spinner on button
            $('#verifyPhone').prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i>');

            // Make POST request to send verification email
            $.post('register', {
                verify_phone: number
            }, function(response) {
                response = JSON.parse(response);

                // If the email is valid and successfully processed, show OTP modal
                if (response.status === 'info') {
                    toastr.info(response.message, 'Info');
                    $("#phone").prop('readonly', true);
                    $('#verifyPhone').hide();
                } else if (response.status === 'success') {
                    $('#verifyPhone').prop('disabled', true).html('Verify');

                    // Show the OTP modal using jConfirm
                    $.confirm({
                        title: 'OTP Verification',
                        content: otpForm('Phone Number'),
                        buttons: {
                            verify: {
                                text: 'Verify OTP',
                                btnClass: 'btn-blue',
                                action: function() {
                                    var otp = this.$content.find('.otpInput').val();
                                    if (!otp) {
                                        $.alert('Please enter a valid OTP');
                                        return false;
                                    }

                                    // Verify OTP through backend
                                    $.post('register', {
                                        verify_phone_otp: otp,
                                        number: number,
                                    }, function(otpResponse) {
                                        otpResponse = JSON.parse(otpResponse);
                                        if (otpResponse.status === 'success') {
                                            $('#verifyPhone').hide();
                                            $('.jconfirm').remove();
                                            $("#phone").prop('readonly', true);
                                            toastr.success(otpResponse.message,
                                                'Success');
                                        } else {
                                            toastr.error(otpResponse.message,
                                                'Error');
                                        }
                                    });
                                    return false;
                                }
                            },
                            cancel: function() {
                                $('#verifyPhone').prop('disabled', false).html('Verify');
                            }
                        }
                    });

                } else {
                    toastr.error(response.message, 'Error');
                    $('#verifyPhone').prop('disabled', false).html('Verify');
                }
            });
        }
    })
    </script>
</body>

</html>