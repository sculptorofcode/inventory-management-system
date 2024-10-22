$(function () {
    const EMAIL_REGEX = /^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,6}$/;
    $('.form').on('submit', function (e) {
        e.preventDefault();
        let form = $(this);
        let submitBtn = form.find('[type="submit"]');
        let submitBtnText = submitBtn.text();
        let formData = new FormData(form[0]);
        let actionUrl = form.attr('action');
        let onSuccess = form.data('on-success');
        let reset = form.data('reset');

        formData.append(submitBtn.attr('name'), 'true');

        if (!form[0].checkValidity()) {
            form.addClass('was-validated');
            return;
        }

        form.find('.invalid-feedback').html('');

        $.ajax({
            url: actionUrl,
            type: 'post',
            data: formData,
            processData: false,
            contentType: false,
            beforeSend: function () {
                submitBtn.html('<i class="fa fa-spin fa-spinner"></i>').attr('disabled', 'disabled');
            },
            success: function (data) {
                try {
                    let res = JSON.parse(data);
                    if (typeof res === 'object') {
                        if (res.status === 'success') {
                            form.removeClass('was-validated');
                            form.find('.is-invalid').removeClass('is-invalid');
                            if (reset === 'true') {
                                form[0].reset();
                            }
                            toastr.success(res.message, 'Success');

                            if (onSuccess) {
                                window[onSuccess](res);
                            }
                            if (res.redirect && res.delay) {
                                setTimeout(function () {
                                    window.location.href = res.redirect;
                                }, res.delay);
                            } else if (res.redirect) {
                                window.location.href = res.redirect;
                            }
                            if (res.reload) {
                                window.location.reload();
                            }
                        } else {
                            toastr.error(res.message, 'Error');
                            if (res.errors) {
                                for (let key in res.errors) {
                                    form.find('[name="' + key + '"]').addClass('is-invalid').siblings('.invalid-feedback').html(res.errors[key]);
                                }
                            }
                        }
                        sessionStorage.setItem(res.status, res.message);
                    } else {
                        toastr.error('Something went wrong, please try again later.', 'Error');
                    }
                } catch (e) {
                    console.error('Error:', e);
                    toastr.error('Something went wrong, please try again later.', 'Error');
                }
            },
            error: function (xhr) {
                toastr.error('An error occurred. Please check your connection and try again.', 'Error');
                console.log('Error:', xhr);
            },
            complete: function () {
                submitBtn.html(submitBtnText).removeAttr('disabled');
            }
        });
    });

    $(".numInput").on('input', function () {
        let number = $(this).val();
        if (typeof number === 'string') {
            number = number.replace(/[^0-9]/g, '');
        }
        $(this).val(number);
    });

    // Email Verify
    $(".emailVerify").on('input', function () {
        let email = $(this).val();
        if (!EMAIL_REGEX.test(email)) {
            $(this).addClass('is-invalid');
        } else {
            $(this).removeClass('is-invalid');
            $(this).siblings('.invalid-feedback').html('');
        }
    });
    // Name Verify
    $(".nameVerify").on('input', function () {
        let name = $(this).val();
        if (name.length < 3) {
            $(this).addClass('is-invalid');
        } else {
            $(this).removeClass('is-invalid');
            $(this).siblings('.invalid-feedback').html('');
        }
    });

    // Number Verify
    $(".numberVerify").on('input', function () {
        let number = $(this).val();
        let length = $(this).attr('length');
        if (length && number.length != length) {
            $(this).addClass('is-invalid');
        } else {
            $(this).removeClass('is-invalid');
            $(this).siblings('.invalid-feedback').html('');
        }
    });

    $(".toggle-password").click(function () {
        $(this).find('i').hasClass('fa-eye') ? $(this).find('i').removeClass('fa-eye').addClass('fa-eye-slash') : $(this).find('i').removeClass('fa-eye-slash').addClass('fa-eye');
        let input = $(this).siblings('input');
        input.attr('type') === 'password' ? input.attr('type', 'text') : input.attr('type', 'password');
    });

    notify();

    function notify() {
        let success = sessionStorage.getItem('success');
        let error = sessionStorage.getItem('error');
        let warning = sessionStorage.getItem('warning');
        let info = sessionStorage.getItem('info');
        if (success) {
            toastr.success(success, 'Success');
            sessionStorage.removeItem('success');
        }
        if (error) {
            toastr.error(error, 'Error');
            sessionStorage.removeItem('error');
        }
        if (warning) {
            toastr.warning(warning, 'Warning');
            sessionStorage.removeItem('warning');
        }
        if (info) {
            toastr.info(info, 'Info');
            sessionStorage.removeItem('info');
        }
    }

    
    $("#postal_code").on('input', function() {
        $this = $(this);
        let postal_code = $this.val();
        if (postal_code && postal_code.length === 6) {
            $this.addClass('loading');
            $.post('ajax', {
                check_postal_code: postal_code
            }, function(response) {
                $this.removeClass('loading');
                response = JSON.parse(response);
                if (response.status === 'success') {
                    $('#city').val(response.city).prop('readonly', true).addClass('valid');
                    $('#state_province').val(response.state).prop('readonly', true).addClass('valid');
                    $('#country').val(response.country).prop('readonly', true).addClass('valid');
                    $this.removeClass('is-invalid');
                    $this.next().text('').hide();
                } else {
                    $this.addClass('is-invalid');
                    $this.siblings('.invalid-feedback').html(response.message).show();
                }
            });
        }
    });

    $('.datepicker').flatpickr({
        dateFormat: 'Y-m-d',
        altInput: true,
        altFormat: 'd-m-Y',
    });
})