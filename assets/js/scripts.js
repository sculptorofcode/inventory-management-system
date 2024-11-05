function htmlDecode(input) {
    let doc = new DOMParser().parseFromString(input, "text/html");
    return doc.documentElement.textContent;
}

function htmlEncode(input) {
    let doc = new DOMParser().parseFromString(input, "text/html");
    return doc.documentElement.innerHTML;
}

$(document).keydown(function(event) {
    if (event.key === "Escape" || event.keyCode === 27) {
        $(".jconfirm-closeIcon").click();
    }
});




$("#postal_code").on('input', function () {
    $this = $(this);
    let postal_code = $this.val();
    if (postal_code && postal_code.length === 6) {
        $this.addClass('loading');
        $.post('ajax', {
            check_postal_code: postal_code
        }, function (response) {
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

if ($('.datepicker').length > 0) {
    $('.datepicker').flatpickr({
        dateFormat: 'Y-m-d',
        altInput: true,
        altFormat: 'd-m-Y',
    });
}

if ($('.timepicker').length > 0) {
    $('.timepicker').flatpickr({
        enableTime: true,
        noCalendar: true,
        dateFormat: "H:i",
        time_24hr: true
    });
}

if ($('.datetimepicker').length > 0) {
    $('.datetimepicker').flatpickr({
        enableTime: true,
        dateFormat: "Y-m-d H:i",
        time_24hr: true,
        altInput: true,
        altFormat: "d-m-Y H:i",
    });
}

if ($('.selectize').length > 0) {
    $('.selectize').selectize();
}