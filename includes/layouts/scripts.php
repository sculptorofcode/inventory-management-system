<!-- jQuery -->
<script src="assets/js/jquery-3.7.1.min.js"></script>
<script src="assets/vendor/libs/popper/popper.js"></script>
<script src="assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js"></script>
<script src="assets/vendor/js/bootstrap.js"></script>
<script src="assets/vendor/js/menu.js"></script>
<script src="assets/js/main.js"></script>
<!-- Toastr JS -->
<script src="assets/libs/toastr/toastr.min.js"></script>
<!-- DataTables JS -->
<script src="assets/libs/datatables/datatables.min.js"></script>
<!-- Flatpickr -->
<script src="assets/libs/flatpickr/flatpickr.min.js"></script>
<!-- Selectize -->
<script src="assets/libs/selectize/selectize.min.js"></script>
<!-- Dynamic Row -->
<script src="assets/js/dynamic-row.js"></script>
<!-- jQuery Confirm -->
<script src="assets/libs/jquery-confirm/jquery-confirm.min.js"></script>
<!-- Custom Scripts -->
<script src="assets/js/scripts.js"></script>
<!-- Form Scripts -->
<script src="assets/js/form.js"></script>
<script>
    let filename = '<?= $filename ?>';

    <?php
    if (isset($row['postal_code'])) {
        echo '$("#postal_code").trigger("input");';
    }
    ?>
    $('.menu-link').each(function () {
        if ($(this).attr('href') === filename) {
            $(this).closest('.menu-item').addClass('active');
            $(this).closest('.menu-sub').show();
        }
    });
    window.addEventListener("load", function () {
        document.getElementById("loader").style.display = "none";
    });
</script>