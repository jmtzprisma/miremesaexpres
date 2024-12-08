$(function() {
    var pakages = [];
    var pathURL = "/bookings";

    function showErrorsVehicle(errors) {
        var errorMsg = "";
        var xComa = "";

        for(var indx in errors) {
            var error = errors[indx];
            console.log(error);
            errorMsg += xComa + error[0];
            xComa = ", ";
        }

        divModalErrorShowUsers(errorMsg);
    }

    function divModalErrorShowVehicle(errorMsg) {
        $("#divModalErrorVehicle").html(errorMsg);
        $("#divModalErrorVehicle").show(500);

        $("#frmRegisterVehicle").scrollTop(0);

        setTimeout(function() {
            $("#divModalErrorVehicle").hide(500);
        }, 10000);
    }

    function showErrorsUsers(errors) {
        var errorMsg = "";
        var xComa = "";

        for(var indx in errors) {
            var error = errors[indx];
            console.log(error);
            errorMsg += xComa + error[0];
            xComa = ", ";
        }

        divModalErrorShowUsers(errorMsg);
    }

    function divModalErrorShowUsers(errorMsg) {
        $("#divModalErrorUser").html(errorMsg);
        $("#divModalErrorUser").show(500);

        $("#frmRegisterUserAndVehicle").scrollTop(0);

        setTimeout(function() {
            $("#divModalErrorUser").hide(500);
        }, 10000);
    }

    function showErrors(errors) {
        var errorMsg = "";
        var xComa = "";

        for(var indx in errors) {
            var error = errors[indx];
            console.log(error);
            errorMsg += xComa + error[0];
            xComa = ", ";
        }

        divModalErrorShow(errorMsg);
    }

    function divModalErrorShow(errorMsg) {
        $("#divModalError").html(errorMsg);
        $("#divModalError").show(500);

        $("#frmRegister").scrollTop(0);

        setTimeout(function() {
            $("#divModalError").hide(500);
        }, 10000);
    }

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

    function getPakage() {
        const url = "/companies/" + $("#company_id").val() + "/pakages";
        $("#pakage_id").empty();
        var newOption = new Option("Seleccione su Vehículo", "", true, true);
        $('#pakage_id').append(newOption);
        pakages = [];

        $.get(url, function(data) {
            pakages = data.data;
            data.data.map(element => {
                const description = element.title + ' - ' + element.price + '€';
                var newOption = new Option(description, element.id, false, false);
                $('#pakage_id').append(newOption);
            });

            if (booking) {
                $('#pakage_id').val(booking.pakage_id);
            }

            $('#pakage_id').trigger('change')
        });
    }

    function getAreas() {
        const url = "/companies/" + $("#company_id").val() + "/areas";
        $("#area_pickup_id").empty();
        $("#area_delivery_id").empty();
        var newOption = new Option("Seleccione su Area", "", true, true);
        var newOption2 = new Option("Seleccione su Area", "", true, true);
        $('#area_pickup_id').append(newOption);
        $('#area_delivery_id').append(newOption2);

        $.get(url, function(data) {
            data.data.map(element => {
                const description = element.name;
                var newOption = new Option(description, element.id, false, false);
                var newOption2 = new Option(description, element.id, false, false);
                $('#area_pickup_id').append(newOption);
                $('#area_delivery_id').append(newOption2);
            });

            if (booking) {
                $('#area_pickup_id').val(booking.area_pickup_id);
                $('#area_delivery_id').val(booking.area_delivery_id);
            }

            $('#area_pickup_id').trigger('change')
            $('#area_delivery_id').trigger('change')
        });
    }

    function getInsurance() {
        const url = "/companies/"+$("#company_id").val()+'/insurances';
        $("#insurance_id").empty();
        var newOption = new Option("Seleccione su Vehículo", "", true, true);
        $('#insurance_id').append(newOption);
        insurances = [];

        $.get(url, function(data) {
            insurances = data.data;
            data.data.map(element => {
                const description = element.title + ' - ' + element.price + '€';
                var newOption = new Option(description, element.id, false, false);
                $('#insurance_id').append(newOption);
            });

            if (booking) {
                $('#insurance_id').val(booking.insurance_id);
            }

            $('#insurance_id').trigger('change')
        });
    }

    function getVehicles() {
        if ($("#user_id").val()) {
            const url = "/users/"+$("#user_id").val()+'/vehicles';
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

                if (booking) {
                    $('#vehicle_id').val(booking.vehicle_id);
                }

                $('#vehicle_id').trigger('change')
            });
        }
    }

    function getPaymentMethods() {
        if ($("#user_id").val()) {
            const url = "/users/"+$("#user_id").val()+'/payments/methods';

            $("#payment_m").empty();
            var newOption = new Option("Seleccione su método de pago", "", true, true);
            $('#payment_method_id').append(newOption);

            $.get(url, function(data) {
                data.data.map(element => {
                    const description = element.alias + (element.card_number != null ? (' - ' + element.card_number) : "");
                    var icon = element.icon;

                    if (element.icon == null) {
                        switch(element.brand) {
                            case "AmericanExpress":
                                icon = "/images/payments/americanexpress-dark.svg";
                                break;
                            case "Diners":
                                icon = "/images/payments/dinersclub.svg";
                                break;
                            case "Discover":
                                icon = "/images/payments/discover.svg";
                                break;
                            case "MasterCard":
                                icon = "/images/payments/mastercard.svg";
                                break;
                            case "Visa":
                                icon = "/images/payments/visa.svg";
                                break;
                        }
                    }
                     
                    var option = $('<option/>');
                    option.attr({ 'value': element.id, 'data-icon' : icon }).text(description);
                    $('#payment_method_id').append(option);
                });

                if (booking) {
                    $('#payment_method_id').val(booking.payment_method_id);
                }

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

                if (booking) {
                    $('#parking_type_id').val(booking.parking_type_id);
                }

                $('#parking_type_id').trigger('change')
            });
        }
    }

    function getServices() {
        if ($("#area_pickup_id").val()) {
            const url = "/areas/"+$("#area_pickup_id").val()+'/extra/services';

            $("#div-services-extra").empty();

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
                });
            });
        }
    }

    function updatRecord() {
        showLoading();
        var post = $('#frmRegister').serializeArray();
        var timeZone = Intl.DateTimeFormat().resolvedOptions().timeZone;
        var timeZonePost = { name: "timeZone", value: timeZone };
        post.push(timeZonePost);

        $.post(pathURL+"/"+booking.id, post, function(data) {
            var url = window.location.origin + "/bookings";
            window.location.replace(url);
        }).fail(function(data) {
            if (data.responseJSON.msg !== undefined) {
                divModalErrorShow(data.responseJSON.msg);
            } else {
                showErrors(data.responseJSON.errors);
            }
            hideLoading();
        });
    }

    function storeRecord() {
        showLoading();
        var post = $('#frmRegister').serializeArray();
        console.log(post);
        var timeZone = Intl.DateTimeFormat().resolvedOptions().timeZone;
        var timeZonePost = { name: "timeZone", value: timeZone };
        post.push(timeZonePost);

        $.post(pathURL, post, function(data) {
            var url = window.location.origin + "/bookings";
            window.location.replace(url);
        }).fail(function(data) {
            if (data.responseJSON.msg !== undefined) {
                divModalErrorShow(data.responseJSON.msg);
            } else {
                showErrors(data.responseJSON.errors);
            }
            hideLoading();
        });
    }

    function storeVehicle() {
        showLoading();
        var post = $('#frmRegisterVehicle').serializeArray();

        var url = pathURL+"/users/"+$("#user_id").val()+"/vehicles";

        if (booking) {
            url = pathURL+"/users/"+booking.user_id+"/vehicles"
        }

        $.post(url, post, function(data) {
            $('#modaldemo9').modal('toggle');

            notif({
				msg: "<b>Registro:</b> Se registro su unidad correctamente",
				type: "success"
			});

            getVehicles();

            hideLoading();
        }).fail(function(data) {
            if (data.responseJSON.msg !== undefined) {
                divModalErrorShowVehicle(data.responseJSON.msg);
            } else {
                showErrorsVehicle(data.responseJSON.errors);
            }
            hideLoading();
        });
    }

    function storeUserAndVehicle() {
        showLoading();
        var post = $('#frmRegisterUserAndVehicle').serializeArray();
        $.post(pathURL+"/users", post, function(data) {
            $('#modaldemo8').modal('toggle');

            notif({
				msg: "<b>Registro:</b> Se registro su usuario correctamente",
				type: "success"
			});

            hideLoading();
        }).fail(function(data) {
            if (data.responseJSON.msg !== undefined) {
                divModalErrorShowUsers(data.responseJSON.msg);
            } else {
                showErrorsUsers(data.responseJSON.errors);
            }
            hideLoading();
        });
    }

    function getPrice() {
        if ($("#time_reservation").val() != "" && $("#time_return").val() != "") {
            showLoading();
            var post = $('#frmRegister').serializeArray();

            var item = post.find(element => element.name == "_method");

            var index = post.indexOf(item);
            if (index !== -1) {
                post.splice(index, 1);
            }

            var url = pathURL+"/cost/calculate";
            if (booking) {
                post.push({name: 'company_id', value: booking.company_id});
                url = pathURL+"/cost/calculate/"+booking.id;
            }

            $.post(url, post, function(data) {
                var cost = data.estimated_cost;
                cost = cost.toString().replace(".", ",");
                $('#price').html("Precio Estimado: "+cost);
                hideLoading();
            }).fail(function(data) {
                console.log(data);
                hideLoading();
            });
        }
    }

    function initial() {
        $("#divModalError").hide();
        $("#divModalErrorUser").hide();
        $("[name='divNaranja']").hide();
        $("#divModalErrorVehicle").hide();
        $("#passager_pickup").prop('disabled', true);
        $("#passager_delivery").prop('disabled', true);

        if (booking) {
            $("#user_id").prop('disabled', true);
            $("#company_id").prop('disabled', true);

            getAreas();

            if  (booking.company_id == 6) {
                getPakage();
                getInsurance();
                $("[name='divNaranja']").show(500);
            } else {
                $("#pakage_id").empty();
                $("#insurance_id").empty();
                $("[name='divNaranja']").hide(500);
            }
        }


        $('.select2').select2({
            minimumResultsForSearch: Infinity
        });

        $('#user_id').select2({
            minimumResultsForSearch: Infinity,
            minimumInputLength: 5,
            ajax: {
                url: function (params) {
                    return '/users/search/' + params.term;
                },
                dataType: 'json'
            }
        });

        $('#brand_id').select2({
            minimumInputLength: 2,
            templateResult: formatBrand,
            dropdownParent: $('#modaldemo8'),
            minimumResultsForSearch: Infinity,
            ajax: {
                url: function (params) {
                    return '/brands/search/' + params.term;
                },
                dataType: 'json'
            },
        });

        $('#payment_method_id').select2({
            minimumResultsForSearch: Infinity,
            templateResult: formatState
        });

        $("#time_reservation").change(() => {
            getPrice();
        });

        $("#time_return").change(() => {
            getPrice();
        });

        $("#user_id").change(() => {
            getVehicles();
            getPaymentMethods();
        });

        if (booking) {
            getVehicles();
            getPaymentMethods();
        }

        $("#passager_pickup").change(() => {
            getPrice();
        });

        $("#passager_delivery").change(() => {
            getPrice();
        });

        $("#insurance_id").change(() => {
            getPrice();
        });

        $("#pakage_id").change(() => {
            if (pakages.length > 0) {
                var pakage = pakages.find(element =>  element.id == $("#pakage_id").val());

                if (pakage) { 
                    if (pakage.pickup) {
                        $("#passager_pickup").prop('disabled', false);
                    }

                    if (pakage.delivery) {
                        $("#passager_delivery").prop('disabled', false);
                    }
                    getPrice();
                }
            }
        });

        $("#company_id").change(() => {
            getAreas();

            if  ($("#company_id").val() == 6) {
                getPakage();
                getInsurance();
                $("[name='divNaranja']").show(500);
            } else {
                $("#pakage_id").empty();
                $("#insurance_id").empty();
                $("[name='divNaranja']").hide(500);
            }
        });
        
        $("#area_pickup_id").change(() => {
            getServices();
            getParkingTypes();
        });

        $("#showPassword").click(() => {
            var type = $("#password").attr('type');

            if (type == "password") {
                $('#password').attr('type', 'text');
            } else {
                $('#password').attr('type', 'password');
            }

            return false;
        });

        $('#frmRegister').validator().on('submit', function (e) {
            if (e.isDefaultPrevented()) {
            // handle the invalid form...
            } else {
                e.preventDefault();
                if (booking) {
                    updatRecord();
                } else {
                    storeRecord();
                }
            }
        });

        $('#frmRegisterUserAndVehicle').validator().on('submit', function (e) {
            if (e.isDefaultPrevented()) {
            // handle the invalid form...
            } else {
                e.preventDefault();
                storeUserAndVehicle();
            }
        });

        $('#frmRegisterVehicle').validator().on('submit', function (e) {
            if (e.isDefaultPrevented()) {
            // handle the invalid form...
            } else {
                e.preventDefault();

                if ($("#user_id").val() != "0") {
                    storeVehicle();
                } else {
                    divModalErrorShowVehicle("Antes de agregar un vehiculo por favor seleccione un usuario");
                }
            }
        });
    }


    if (booking) {
        getPrice();
    }


    $(document).ready(initial);
});

function formatBrand(element) {
    if (!element.id) {
        return element.text;
    }

    const description = element.text;
    var icon = element.icon;

    var $option = $(
        '<span><img src="' + icon + '" class="img-flag" />  ' + description + '</span>'
    );

    return $option;
};

function formatState(element) {
    if (!element.id) {
        return element.text;
    }

    const description = element.text;
    var icon = $(element.element).data("icon");

    var $option = $(
        '<span><img src="' + icon + '" class="img-flag" />  ' + description + '</span>'
    );

    return $option;
};
