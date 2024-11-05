<?php
require_once 'includes/config/after-login.php';
$title = 'Register New User';
?>
<!DOCTYPE html>
<html lang="en" class="light-style layout-menu-fixed" dir="ltr" data-theme="theme-default" data-assets-path="assets/"
      data-template="vertical-menu-template-free">

<head>
    <meta charset="utf-8" />
    <meta name="viewport"
          content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />
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
                        <div class="card-header">
                            <h4 class="card-title">Register New User</h4>
                            <p class="card-subtitle">Create a new user account by filling in the details below.</p>
                        </div>
                        <div class="card-body">
                            <div class="progress-bar d-flex mb-3">
                                <div class="progress-step active">Personal</div>
                                <div class="progress-step">Address</div>
                                <div class="progress-step">Account</div>
                                <div class="progress-step">Preferences</div>
                            </div>
                            <form id="registerForm" class="form" data-reset="true">
                                <!-- Step 1: Personal Information -->
                                <div class="form-step active">
                                    <div class="row">
                                        <div class="mb-3 col-md-6">
                                            <label for="first_name" class="form-label">First Name</label>
                                            <input type="text" class="form-control nameVerify" id="first_name" name="first_name"
                                                   placeholder="Enter First Name" required>
                                            <div class="invalid-feedback"></div>
                                        </div>
                                        <div class="mb-3 col-md-6">
                                            <label for="last_name" class="form-label">Last Name</label>
                                            <input type="text" class="form-control nameVerify" id="last_name" name="last_name"
                                                   placeholder="Enter Last Name" required>
                                            <div class="invalid-feedback"></div>
                                        </div>
                                        <div class="mb-3 col-md-6">
                                            <label for="email" class="form-label">Email address</label>
                                            <div class="input-group">
                                                <input type="email" class="form-control emailVerify" id="email" name="email"
                                                       placeholder="Enter Email" required>
                                                <button class="btn btn-outline-primary verify-button" type="button" id="verifyEmail">Verify</button>
                                            </div>
                                            <div class="invalid-feedback"></div>
                                        </div>
                                        <div class="mb-3 col-md-6">
                                            <label for="phone" class="form-label">Phone Number</label>
                                            <div class="input-group">
                                                <input type="text" class="form-control numInput numberVerify" id="phone"
                                                       name="phone" maxlength="10" minlength="10"
                                                       placeholder="Enter Phone Number" required>
                                                <button class="btn btn-outline-primary verify-button" type="button" id="verifyPhone">Verify</button>
                                            </div>
                                            <div class="invalid-feedback"></div>
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
                                            <div class="invalid-feedback"></div>
                                        </div>
                                        <div class="mb-3 col-md-6">
                                            <label for="postal_code" class="form-label">Postal Code</label>
                                            <input type="text" class="form-control numberVerify" length="6" minlength="6"
                                                   maxlength="6" id="postal_code" name="postal_code" placeholder="Enter Postal Code"
                                                   required>
                                            <div class="invalid-feedback"></div>
                                        </div>
                                        <div class="mb-3 col-md-6">
                                            <label for="city" class="form-label">City</label>
                                            <input type="text" class="form-control" id="city" name="city" placeholder="Enter City"
                                                   required>
                                            <div class="invalid-feedback"></div>
                                        </div>
                                        <div class="mb-3 col-md-6">
                                            <label for="state_province" class="form-label">State/Province</label>
                                            <input type="text" class="form-control" id="state_province" name="state_province"
                                                   placeholder="Enter State/Province" required>
                                            <div class="invalid-feedback"></div>
                                        </div>
                                        <div class="mb-3 col-md-6">
                                            <label for="country" class="form-label">Country</label>
                                            <input type="text" class="form-control" id="country" name="country"
                                                   placeholder="Enter Country" required>
                                            <div class="invalid-feedback"></div>
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
                                            <div class="invalid-feedback"></div>
                                        </div>
                                        <div class="mb-3 col-md-6">
                                            <label for="password" class="form-label">Password</label>
                                            <div class="input-group">
                                                <input type="password" class="form-control" id="password" name="password"
                                                       placeholder="●●●●●●●●" required>
                                                <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                                    <i class="bx bx-hide"></i>
                                                </button>
                                            </div>
                                            <div class="invalid-feedback"></div>
                                        </div>
                                        <div class="mb-3 col-md-6">
                                            <label for="company_name" class="form-label">Company Name</label>
                                            <input type="text" class="form-control" id="company_name" name="company_name"
                                                   placeholder="Enter Company Name" required>
                                            <div class="invalid-feedback"></div>
                                        </div>
                                        <div class="mb-3 col-md-6">
                                            <label for="tax_identification_number" class="form-label">Tax Identification Number</label>
                                            <input type="text" class="form-control" id="tax_identification_number"
                                                   name="tax_identification_number" placeholder="Enter TIN">
                                            <div class="invalid-feedback"></div>
                                        </div>
                                        <div class="mb-3 col-md-6">
                                            <label for="business_type" class="form-label">Business Type</label>
                                            <input type="text" class="form-control" id="business_type" name="business_type"
                                                   placeholder="Enter Business Type" required>
                                            <div class="invalid-feedback"></div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Step 4: Preferences and Security -->
                                <div class="form-step">
                                    <div class="row">
                                        <div class="mb-3 col-md-6">
                                            <label for="preferred_contact_method" class="form-label">Preferred Contact Method</label>
                                            <select class="form-select" id="preferred_contact_method" name="preferred_contact_method" required>
                                                <option value="email">Email</option>
                                                <option value="phone">Phone</option>
                                            </select>
                                            <div class="invalid-feedback"></div>
                                        </div>
                                        <div class="mb-3 col-md-6">
                                            <label for="referral_source" class="form-label">Referral Source</label>
                                            <input type="text" class="form-control" id="referral_source" name="referral_source"
                                                   placeholder="Enter Referral Source" required>
                                            <div class="invalid-feedback"></div>
                                        </div>
                                        <div class="mb-3 col-md-6">
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" id="newsletter_subscription"
                                                       name="newsletter_subscription" value="1">
                                                <label class="form-check-label" for="newsletter_subscription">
                                                    Receive newsletters
                                                </label>
                                            </div>
                                        </div>
                                        <div class="mb-3 col-md-6">
                                            <label for="security_question" class="form-label">Security Question</label>
                                            <input type="text" class="form-control" id="security_question" name="security_question"
                                                   placeholder="Enter Security Question" required>
                                            <div class="invalid-feedback"></div>
                                        </div>
                                        <div class="mb-3 col-md-6">
                                            <label for="security_answer" class="form-label">Security Answer</label>
                                            <input type="text" class="form-control" id="security_answer" name="security_answer"
                                                   placeholder="Enter Security Answer" required>
                                            <div class="invalid-feedback"></div>
                                        </div>
                                        <div class="mb-3 col-md-12">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="agreed_to_terms"
                                                       name="agreed_to_terms" required>
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
                                    <button type="button" class="btn btn-secondary prev-btn" style="display: none;">Previous</button>
                                    <button type="button" class="btn btn-primary next-btn">Next</button>
                                    <button type="submit" name="register" class="btn btn-success" id="registerBtn"
                                            style="display: none;">Register</button>
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

    // Initialize form steps
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
        let isValid = true;

        for (let input of inputs) {
            if (input.hasAttribute('required') && !input.value) {
                let sibling = input.nextElementSibling;
                sibling.textContent = 'This field is required';
                sibling.style.display = 'block';
                isValid = false;
            }
            if (input.classList.contains('is-invalid')) {
                isValid = false;
            }
        }
        return isValid;
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

    // Email verification
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

    // Phone number verification
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

    // Email verification button
    $("#verifyEmail").on('click', function() {
        $this = $(this);
        let email = $('#email').val();
        if (email && $('#email').is(':valid')) {
            // Triggering loading spinner on button
            $('#verifyEmail').prop('disabled', true).html('<i class="bx bx-loader bx-spin"></i>');

            // Make POST request to send verification email
            $.post('register', {
                verify_email: email
            }, function(response) {
                response = JSON.parse(response);

                // If the email is valid and successfully processed, show OTP modal
                if (response.status === 'info') {
                    toastr.info(response.message, 'Info');
                    $("#email").prop('readonly', true);
                    $("#username").val(email).prop('readonly', true);
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
                                            $("#username").val(email).prop('readonly', true);
                                            toastr.success(otpResponse.message, 'Success');
                                        } else {
                                            toastr.error(otpResponse.message, 'Error');
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

    // Phone verification button
    $("#verifyPhone").on('click', function() {
        $this = $(this);
        let number = $('#phone').val();
        if (number && $('#phone').is(':valid')) {
            // Triggering loading spinner on button
            $('#verifyPhone').prop('disabled', true).html('<i class="bx bx-loader bx-spin"></i>');

            // Make POST request to send verification email
            $.post('register', {
                verify_phone: number
            }, function(response) {
                response = JSON.parse(response);

                // If the phone number is valid and successfully processed, show OTP modal
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
                                            toastr.success(otpResponse.message, 'Success');
                                        } else {
                                            toastr.error(otpResponse.message, 'Error');
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
    });

    // Toggle password visibility
    const togglePassword = document.getElementById('togglePassword');
    const passwordInput = document.getElementById('password');

    togglePassword.addEventListener('click', function() {
        const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
        passwordInput.setAttribute('type', type);
        this.querySelector('i').classList.toggle('bx-show');
    });
</script>
</body>

</html>
