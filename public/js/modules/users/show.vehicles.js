$(function() {
    'use strict';

    function activeFunctions() {
        $("button[data-action='delete']").off("click");
        $("button[data-action='edit']").off("click");
        $("button[data-action='delete']").click(vehicleDelete);
        $("button[data-action='edit']").click(vehicleEdit);
    }

    function addVehicle()  {
        $.post("/users/"+idUser+"/vehicles/store", $("#formVehicle").serialize())
            .done(function(data) {
                const vehicle = data.data;

                if ($('#vehicle_id').val()) {
                    $('#vehicle_'+$('#vehicle_id').val()).remove();
                }

                if ($('.odd')[0]) {
                    $('.odd').remove();
                }

                $("#formVehicle")[0].reset();
                $('#brand_id').val("").trigger('change');

                const rowStatus = (vehicle.status) ? '<span class="badge rounded-pill bg-primary me-1 mb-1 mt-1">Activo</span>'
                    : '<span class="badge rounded-pill bg-warning me-1 mb-1 mt-1">Inactivo</span>';

                const rowVehicle = '<tr id="vehicle_' + vehicle.id + '">'+
                    '<td>' + rowStatus + '</td>'+
                    '<td>' + vehicle.brand.name + ' ' + vehicle.model + '</td>' +
                    '<td>' + vehicle.color + '</td>' +
                    '<td>' + vehicle.licenseplate + '</td>' +
                    '<td>' +
                    '<div class="btn-group align-top">' +
                    '<button class="btn btn-sm btn-primary badge" data-action="edit" data-data="' + vehicle + '" type="button"><i class="fa fa-pencil"></i></button>' + 
                    '<button class="btn btn-sm btn-danger badge" data-action="delete" data-id="' + vehicle.id + '" type="button"><i class="fa fa-trash"></i></button>' +
                    '</div>' +
                    '</td>' +
                    '</tr>';

                $("#tables-vehicles tbody").append(rowVehicle);
                $('#modalAddVehicle').modal('hide')
                $.growl.notice({ title: "Agregar", message: "Se agregó su registro correctamente" });

                $('#tables-vehicles').DataTable();

                activeFunctions();
            })
            .fail(function(xhr) {
                if (JSON.parse(xhr.responseText).errors.licenseplate) {
                    $("#validationServerLinceseplate").html(JSON.parse(xhr.responseText).errors.licenseplate[0]);
                }
            });
    }

    function vehicleDelete(e) {
        const  id = $(e.target).data('id');

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
                    url: "/users/"+idUser+"/vehicles/"+id,
                    type: 'DELETE',
                    data: {
                        "_token": window.Laravel.csrfToken,
                    },
                }).done(function() {
                    $('#vehicle_'+id).remove();
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

    function vehicleEdit(e) {
        const  vehicle = $(e.target).data('vehicle');

        if (vehicle) {
            $('#model').val(vehicle.model);
            $('#color').val(vehicle.color);
            $('#vehicle_id').val(vehicle.id);
            $('#licenseplate').val(vehicle.licenseplate);
            $('#brand_id').val(vehicle.brand_id).trigger('change');

            $('#modalAddVehicle').modal('show')
        } else {
            $('body').removeClass('timer-alert');
            swal({
                title: "Notificación",
                text: "Por favor seleccione un registro a editar",
                type: "warning",
                showCancelButton: true,
                cancelButtonText: 'Cancelar'
            });
        }
    }

    function initial() {
        $('#formVehicle').on('submit', e => {
            e.preventDefault();
            console.log(e, "prevent");

            if (e.target.checkValidity() === false) {
                e.stopPropagation();
            } else {
                addVehicle();
            } 

            e.target.classList.add('was-validated');
        });

        $('#tables-vehicles').DataTable({
            language: {
                url: 'https://cdn.datatables.net/plug-ins/1.11.3/i18n/es_es.json',
                searchPlaceholder: 'Búsqueda...',
            }
        });

        activeFunctions();
    }

    $(document).ready(initial);
});
