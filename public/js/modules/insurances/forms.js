$(function() {
    'use strict';
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

    function initial() {
        $('.select2').select2({
            minimumResultsForSearch: Infinity
        });

        tinymce.init({
            selector: 'textarea#description',
            skin: 'bootstrap',
            plugins: 'lists, link, image, media',
        });

        $("#icon").change(showPreview);
    }

    $(document).ready(initial);
});
