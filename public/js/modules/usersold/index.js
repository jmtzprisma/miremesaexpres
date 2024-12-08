$(function() {
    'use strict';
    const url = '/usersold';
    var tableRecords = null;

    function showErrors(errors) {
        var errorMsg = "";
        var xComa = "";

        for(var indx in errors) {
            var error = errors[indx];
            errorMsg = xComa + error[0];
            xComa = ", ";
        }

        $.growl.error({ message: errorMsg });
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

    function onIntent(id) {
        if (id) {
            $('body').removeClass('timer-alert');

            $.ajax({
                url: url+'/'+id+'',
                type: 'PUT',
                data: {
                    "_token": window.Laravel.csrfToken,
                },
            }).done(function() {
                tableRecords.ajax.reload();
                $.growl.notice({ title: "Intento", message: "Se agrego su intento" });
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

    function onStore(id) {
        if (id) {
            $('body').removeClass('timer-alert');
            swal({
                title: "Notificación",
                text: "¿Está seguro que desea agregar su usuario?",
                type: "warning",
                showCancelButton: true,
                confirmButtonText: 'Continuar',
                cancelButtonText: 'Cancelar'
            }, function() {
                $.ajax({
                    url: url+'/'+id+"/add",
                    type: 'POST',
                    data: {
                        "_token": window.Laravel.csrfToken,
                    },
                }).done(function() {
                    tableRecords.ajax.reload();
                    $.growl.notice({ title: "Agregar", message: "Se agrego su registro correctamente" });
                }).fail(function(data) {
                    showErrors(data.responseJSON.errors);
                    //$.growl.error({ message: "Hubo un error al intentar agregar, por favor intente más tarde" });
                });
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

            $(".command-store").off("click");
            $(".command-store").on("click", function(e)
            {
                onStore($(this).data("row-id"));
            });

            $(".command-intent").off("click");
            $(".command-intent").on("click", function(e)
            {
                onIntent($(this).data("row-id"));
            });
        }, 500);
    }

    function initial() {
        tableRecords = $('#tables-users-old').on('processing.dt', function () {
            activeActions();
        }).DataTable({
            order: [[ 1, "desc" ]],
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
                url: url + "/get",
                type: 'POST',
                data:{
                    '_token':window.Laravel.csrfToken,
               }
		    },
		    columns: [
                { data: 'name' },
                { data: 'last_name' },
                { data: 'email' },
                { data: 'phone' },
                { data: 'bookings' },
                { data: 'vehicles', orderable: false, },
                { data: 'companies', orderable: false, },
                { data: 'intents' },
                { data: 'last_booking' },
                {
                    data: 'actions',
                    orderable: false,
                    render: function (data, type, row, meta) {
                        return "<button type=\"button\" class=\"btn btn-xs btn-warning command-store\" data-row-id=\"" + row.id + "\"><span class=\"fa fa-done\"></span></button> " +
                               "<button type=\"button\" class=\"btn btn-xs btn-success command-intent\" data-row-id=\"" + row.id + "\"><span class=\"fa fa-mobile\"></span></button> " +
                               "<button type=\"button\" class=\"btn btn-xs btn-danger command-delete\" data-row-id=\"" + row.id + "\"><span class=\"fa fa-trash-o\"></span></button> ";
                    }
                },
		    ]
        });
    }

    $(document).ready(initial);
});
