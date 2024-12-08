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

    function showPreview() {
        const file = $("#icon")[0].files[0];

        if (file) {
            $("#imgPreview").attr("src", URL.createObjectURL(file));
        }
    }

    $("#is_manager_valet_div").hide();


    $("#rol").change(() => {
        if ($("#rol").val() == 3) {
            $("#is_manager_valet_div").show();
        } else {
            $("#is_manager_valet_div").hide();
        }
    });
    
    $("#show_hide_password a").on('click', function(event) {
        event.preventDefault();
        if($('#show_hide_password input').attr("type") == "text"){
            $('#show_hide_password input').attr('type', 'password');
            $('#show_hide_password i').addClass( "fa-eye-slash" );
            $('#show_hide_password i').removeClass( "fa-eye" );
        }else if($('#show_hide_password input').attr("type") == "password"){
            $('#show_hide_password input').attr('type', 'text');
            $('#show_hide_password i').removeClass( "fa-eye-slash" );
            $('#show_hide_password i').addClass( "fa-eye" );
        }
    });

    $("#icon").change(showPreview);
})();
