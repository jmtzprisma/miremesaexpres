(function() {
    'use strict';
    // Select2
    $('.select2').select2({
        minimumResultsForSearch: Infinity
    });

    window.addEventListener('load', function() {
        // Fetch all the forms we want to apply custom Bootstrap validation styles to
        var forms = document.getElementsByClassName('needs-validation');
        // Loop over them and prevent submission
        var validation = Array.prototype.filter.call(forms, function(form) {
            form.addEventListener('submit', function(event) {
                if (form.checkValidity() === false) {
                    event.preventDefault();
                    event.stopPropagation();
                }
                form.classList.add('was-validated');
            }, false);
        });
    }, false);

    $("#showPassword").click(() => {
        var type = $("#password").attr('type');
        console.log(type);

        if (type == "password") {
            $('#password').attr('type', 'text');
        } else {
            $('#password').attr('type', 'password');
        }

        return false;
    });

    $("#rol").change(() => {
        if ($("#rol").val() == 3) {
            $("#is_manager_valet_div").show();
        } else {
            $("#is_manager_valet_div").hide();
        }
    });
    
})();
