$(function() {
    function onSearchUser(e) {
        e.preventDefault();
        const txtEmail = $('#email');
        if(txtEmail.val()) {
            const user = users.find(element => element.email == txtEmail.val());
            if (user) {
                $('#name').val(user.name);
                $('#last_name').val(user.last_name);
                $('#phone').val(user.phone);
            } else {
                $.growl.error({ message: "No existen coincidencias para su email." });
            }
        }
    }

    function onSearchVehicle(e) {
        e.preventDefault();
        const txtLicenseplate = $('#licenseplate');
        if(txtLicenseplate.val()) {
            const vehicle = vehicles.find(element => element.licenseplate == txtLicenseplate.val());
            if (vehicle) {
                $('#brand_id').val(vehicle.brand_id);
                $('#model').val(vehicle.model);
                $('#color').val(vehicle.color);
            } else {
                $.growl.error({ message: "No existen coincidencias para sus placas." });
            }
        }
    }

    function modelMatcher(params, data) {
        console.log(params)
        console.log(data)
        data.parentText = data.parentText || "";

        // Always return the object if there is nothing to compare
        if ($.trim(params.term) === '') {
            return data;
        }

        // Do a recursive check for options with children
        if (data.children && data.children.length > 0) {
            // Clone the data object if there are children
            // This is required as we modify the object to remove any non-matches
            var match = $.extend(true, {}, data);

            // Check each child of the option
            for (var c = data.children.length - 1; c >= 0; c--) {
                var child = data.children[c];
                child.parentText = data.parentText + " " + data.text;

                var matches = modelMatcher(params, child);

                // If there wasn't a match, remove the object in the array
                if (matches == null) {
                    match.children.splice(c, 1);
                }
            }

            // If any children matched, return the new object
            if (match.children.length > 0) {
                return match;
            }

            // If there were no matching children, check just the plain object
            return modelMatcher(params, match);
        }

        // If the typed-in term matches the text of this term, or the text from any
        // parent term, then it's a match.
        var original = (data.parentText + ' ' + data.text).toUpperCase();
        var term = params.term.toUpperCase();


        // Check if the text contains the term
        if (original.indexOf(term) > -1) {
            return data;
        }

        // If it doesn't contain the term, don't return anything
        return null;
    }


    function initial() {
        $('.select2').select2({
            matcher: modelMatcher,
        });

        $("#searchUserEmail").click(onSearchUser);
        $("#searchUserVehicle").click(onSearchVehicle);

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
    }

    $(document).ready(initial);
});
