$(function() {
    'use strict';
    var currentId = 0;
    const url = '/bookings';
    var tableRecords = null;
    var timeZone = Intl.DateTimeFormat().resolvedOptions().timeZone;

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

    function onChangePrice() {
        if (currentId) {
            $('body').removeClass('timer-alert');
            swal({
                title: "Notificación",
                text: "¿Está seguro que desea cambiar el precio, si cambia el precio ya no sera posible calcularlo dinamicamente?",
                type: "warning",
                showCancelButton: true,
                confirmButtonText: 'Continuar',
                cancelButtonText: 'Cancelar'
            }, function() {
                $.ajax({
                    url: url+'/'+currentId+"/customprice",
                    type: 'PUT',
                    data: {
                        "price": $("#price").val(),
                        "_token": window.Laravel.csrfToken,
                    },
                }).done(function() {
                    tableRecords.ajax.reload();
                    $('#frmRegister')[0].reset();
                    $('#modalCreate').modal('hide');
                    $.growl.notice({ title: "Cambio", message: "Se modifico su precio correctamente" });
                }).fail(function() {
                    $.growl.error({ message: "Hubo un error al intentar cambiar su precio, por favor intente más tarde" });
                });
            });
        } else {
            $('body').removeClass('timer-alert');
            swal({
                title: "Notificación",
                text: "Por favor seleccione un registro a eliminar",
                type: "warning",
                showCancelButton: true,
                cancelButtonText: 'Cancelar'
            });
        }
    }

    function onDelete(id) {
        if (id) {
            $('body').removeClass('timer-alert');
            swal({
                title: "Notificación",
                text: "¿Está seguro que desea eliminar su registro?",
                type: "warning",
                showCancelButton: true,
                confirmButtonText: 'Continuar',
                cancelButtonText: 'Cancelar'
            }, function() {
                $.ajax({
                    url: url+'/'+id,
                    type: 'DELETE',
                    data: {
                        "_token": window.Laravel.csrfToken,
                    },
                }).done(function() {
                    tableRecords.ajax.reload();
                    $.growl.notice({ title: "Eliminar", message: "Se eliminó su registro correctamente" });
                }).fail(function() {
                    $.growl.error({ message: "Hubo un error al intentar eliminar, por favor intente más tarde" });
                });
            });
        } else {
            $('body').removeClass('timer-alert');
            swal({
                title: "Notificación",
                text: "Por favor seleccione un registro a eliminar",
                type: "warning",
                showCancelButton: true,
                cancelButtonText: 'Cancelar'
            });
        }
    }

    function onBaggage(id) {
        if (id) {
            $('body').removeClass('timer-alert');
            $.ajax({
                url: url+'/'+id+'/baggage',
                type: 'PUT',
                data: {
                    "_token": window.Laravel.csrfToken,
                },
            }).done(function() {
                let btnBaggage = $("#btnBaggage_"+id)

                if (btnBaggage.hasClass("btn-success")) {
                    btnBaggage.removeClass("btn-success");
                    btnBaggage.addClass('btn-warning');
                    $.growl.notice({ title: "Equipaje", message: "Se terminó de recoger el equipaje" });
                } else {
                    btnBaggage.removeClass('btn-warning');
                    btnBaggage.addClass("btn-success");
                    $.growl.notice({ title: "Equipaje", message: "Se marco como recogiendo equipaje" });
                }

            }).fail(function() {
                $.growl.error({ message: "Hubo un error al intentar marcar equipaje, por favor intente más tarde" });
            });
        } else {
            $('body').removeClass('timer-alert');
            swal({
                title: "Notificación",
                text: "Por favor seleccione un registro",
                type: "warning",
                showCancelButton: true,
                cancelButtonText: 'Cancelar'
            });
        }
    }

    function activeActions() {
        setTimeout(() => {
            $(".command-delete").off("click");
            $(".command-delete").on("click", function(e)
            {
                onDelete($(this).data("row-id"));
            });

            $(".command-baggage").off("click");
            $(".command-baggage").on("click", function(e)
            {
                onBaggage($(this).data("row-id"));
            });

            $(".command-price").off("click");
            $(".command-price").on("click", function(e)
            {
                currentId = $(this).data("row-id");
                $('#frmRegister')[0].reset();
                $('#modalCreate').modal('show');
            });

            $(".command-edit").off("click");
            $(".command-edit").on("click", function(e)
            {
                var url = window.location.origin + "/bookings/"+$(this).data("row-id")+"/edit";
                window.location.replace(url);
            });
        }, 500);
    }

    function initial() {
        $("#divModalError").hide();

        tableRecords = $('#tables-bookings').on('processing.dt', function () {
            activeActions();
        }).DataTable({
            order: [[ 7, "desc" ]],
            lengthMenu: [
                [ 10, 25, 50, -1 ],
                [ '10 rows', '25 rows', '50 rows', 'Show all' ]
            ],
            language: {
                url: 'https://cdn.datatables.net/plug-ins/1.11.3/i18n/es_es.json',
                searchPlaceholder: 'Búsqueda...',
            },
            processing: true,
            serverSide: true,
			ajax: {
                url: url + "/get?timeZone="+timeZone,
                type: 'POST',
                data:{
                    '_token':window.Laravel.csrfToken,
               }
		    },
		    columns: [
                { data: 'company' },
                { data: 'area' },
                { data: 'full_name' },
                { data: 'phone' },
                { data: 'full_name_pickup' },
                { data: 'full_name_delivery' },
                { data: 'licenseplate' },
                { data: 'time_reservation' },
                { data: 'time_delivery' },
                { data: 'created_at' },
                {
                    data: 'actions',
                    orderable: false,
                    render: function (data, type, row, meta) {
                        return "<button id=\"btnBaggage_" + row.id + "\" type=\"button\" class=\"btn btn-xs btn-warning command-baggage\" data-row-id=\"" + row.id + "\"><span class=\"fa fa-suitcase\"></span></button> " +
                               "<button type=\"button\" class=\"btn btn-xs btn-success command-edit\" data-row-id=\"" + row.id + "\"><span class=\"fa fa-pencil\"></span></button> " +
                               "<a href=\"/" + row.id + "/bookings/history\" class=\"btn btn-xs btn-primary\"><span class=\"fa fa-eye\"></span></a> " +
                               "<button type=\"button\" class=\"btn btn-xs btn-primary command-price\" data-row-id=\"" + row.id + "\"><span class=\"fa fa-money\"></span></button> " +
                               "<button type=\"button\" class=\"btn btn-xs btn-danger command-delete\" data-row-id=\"" + row.id + "\"><span class=\"fa fa-trash-o\"></span></button> ";
                    }
                },
		    ]
        });

        $('#frmRegister').validator().on('submit', function (e) {
            if (e.isDefaultPrevented()) {
            // handle the invalid form...
            } else {
                e.preventDefault();
                onChangePrice();
            }
        });

        setInterval(function() {
            tableRecords.ajax.reload();
        }, 120000);
    }

    $(document).ready(initial);
});
