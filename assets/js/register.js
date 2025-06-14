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

progressSteps.forEach((step, index) => {
    step.addEventListener('click', () => {
        showStep(index);
    });
})

function validateStep(stepIndex) {
    const inputs = steps[stepIndex].getElementsByTagName('input');
    let isValid = true;

    for (let input of inputs) {
        let sibling = $(input).siblings('.invalid-feedback')[0] || $(input).parent().siblings('.invalid-feedback')[0];
        if (input.hasAttribute('required') && !input.value) {
            sibling.textContent = 'This field is required';
            sibling.style.display = 'block';
            isValid = false;
        } else {
            sibling.textContent = '';
            sibling.style.display = 'none';
        }
        if (input.classList.contains('is-invalid')) {
            isValid = false;
        }
    }
    return isValid;
}

nextButton.addEventListener('click', function () {
    if (validateStep(currentStep)) {
        showStep(currentStep + 1);
    }
});

prevButton.addEventListener('click', function () {
    showStep(currentStep - 1);
});

showStep(0);

// Email verification
$(".emailVerify").on('input', function () {
    $this = $(this);
    let email = $this.val();
    if (email) {
        if ($this.is(':valid')) {
            $.post('register', {
                check_email: email
            }, function (response) {
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
$('#phone').on('input', function () {
    $this = $(this);
    let number = $this.val();
    if (number) {
        if ($this.is(':valid')) {
            $.post('register', {
                check_number: number
            }, function (response) {
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
$("#verifyEmail").on('click', function () {
    let $this = $(this);
    let $email = $('#email');
    let email = $email.val();
    if (email && $email.is(':valid')) {
        // Triggering loading spinner on button
        $('#verifyEmail').prop('disabled', true).html('<i class="bx bx-loader bx-spin"></i>');

        // Make POST request to send verification email
        $.post('register', {
            verify_email: email
        }, function (response) {
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
                    title: 'OTP Verification', content: otpForm('Email Address'), buttons: {
                        verify: {
                            text: 'Verify OTP', btnClass: 'btn-blue', action: function () {
                                var otp = this.$content.find('.otpInput').val();
                                if (!otp) {
                                    $.alert('Please enter a valid OTP');
                                    return false;
                                }

                                // Verify OTP through backend
                                $.post('register', {
                                    verify_email_otp: otp, email: email,
                                }, function (otpResponse) {
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
                        }, cancel: function () {
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
$("#verifyPhone").on('click', function () {
    let $this = $(this);
    let $phone = $('#phone');
    let number = $phone.val();
    if (number && $phone.is(':valid')) {
        // Triggering loading spinner on button
        $('#verifyPhone').prop('disabled', true).html('<i class="bx bx-loader bx-spin"></i>');

        // Make POST request to send verification email
        $.post('register', {
            verify_phone: number
        }, function (response) {
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
                    title: 'OTP Verification', content: otpForm('Phone Number'), buttons: {
                        verify: {
                            text: 'Verify OTP', btnClass: 'btn-blue', action: function () {
                                var otp = this.$content.find('.otpInput').val();
                                if (!otp) {
                                    $.alert('Please enter a valid OTP');
                                    return false;
                                }

                                // Verify OTP through backend
                                $.post('register', {
                                    verify_phone_otp: otp, number: number,
                                }, function (otpResponse) {
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
                        }, cancel: function () {
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

if (togglePassword) {
    togglePassword.addEventListener('click', function () {
        const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
        passwordInput.setAttribute('type', type);
        this.querySelector('i').classList.toggle('bx-show');
    });
}