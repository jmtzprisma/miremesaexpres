$(function() {
    window.addEventListener('load', function() {
        // Fetch all the forms we want to apply custom Bootstrap validation styles to
        var forms = document.getElementsByClassName('needs-validation');
        console.log(forms);
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

    function getVehicles() {
        console.log($("#user_booking_id"), "user");
        if ($("#user_booking_id").val()) {
            const url = "/users/"+$("#user_booking_id").val()+'/vehicles';
            $("#vehicle_id").empty();
            var newOption = new Option("Seleccione su Vehículo", "", true, true);
            $('#vehicle_id').append(newOption);

            $.get(url, function(data) {
                data.data.map(element => {
                    const description = element.brand.name + ' ' + element.model +
                        ' (' + element.licenseplate + ')';
                    var newOption = new Option(description, element.id, false, false);
                    $('#vehicle_id').append(newOption);
                });

                $('#vehicle_id').trigger('change')
            });
        }
    }

    function getPaymentMethods() {
        if ($("#user_booking_id").val()) {
            const url = "/users/"+$("#user_booking_id").val()+'/payments/methods';

            $("#payment_m").empty();
            var newOption = new Option("Seleccione su método de pago", "", true, true);
            $('#payment_method_id').append(newOption);

            $.get(url, function(data) {
                data.data.map(element => {
                    console.log(element);
                });

                $('#payment_method_id').trigger('change')
            });
        }
    }

    function getParkingTypes() {
        if ($("#area_pickup_id").val()) {
            const url = "/areas/"+$("#area_pickup_id").val()+'/parkings/types';

            $("#parking_type_id").empty();
            var newOption = new Option("Seleccione su tipo de aparcamiento", "", true, true);
            $('#parking_type_id').append(newOption);

            $.get(url, function(data) {
                data.data.map(element => {
                    var newOption = new Option(element.name, element.id, false, false);
                    $('#parking_type_id').append(newOption);
                });

                $('#parking_type_id').trigger('change')
            });
        }
    }

    function getServices() {
        if ($("#area_pickup_id").val()) {
            const url = "/areas/"+$("#area_pickup_id").val()+'/extra/services';

            $.get(url, function(data) {
                data.data.map(element => {
                    $("#div-services-extra").append(
                        '<div class="col-md-3 mb-1">' +
                            '<div class="mb-0 row justify-content-end">' +
                                '<div class="col-md-9">' +
                                    '<label class="custom-control custom-checkbox">' +
                                        '<input type="checkbox" class="custom-control-input" name="extra_services[]" value="'+element.id+'">' +
                                        '<span class="custom-control-label">'+element.name+'</span>' +
                                    '</label>' + 
                                '</div>' +
                            '</div>' +
                        '</div>' 
                    );
                    console.log(element);
                });
            });
        }
    }

    function initial() {
        $('.select2').select2({
            minimumResultsForSearch: Infinity
        });

        $("#btnAddBooking").click(() => {
            getVehicles();
            getPaymentMethods();
            $('#modalAddBooking').modal('show')
        });

        $("#btnCloseBooking").click(e => e.preventDefault());
        $("#btnHeaderCloseBooking").click(e => e.preventDefault());
        
        $("#area_pickup_id").change(() => {
            getServices();
            getParkingTypes();
        });
    }

    $(document).ready(initial);
});
