$(function() {
    'use strict';
    var tableRecords = null;
    var tableRecordsHistory = null;
    const url = '/archingsvalets';
    var timeZone = Intl.DateTimeFormat().resolvedOptions().timeZone;

    function storeArching(id) {
        if (id) {
            $('body').removeClass('timer-alert');
            swal({
                title: "Notificación",
                text: "¿Está seguro que deseas finalizar el Arqueo?",
                type: "warning",
                showCancelButton: true,
                confirmButtonText: 'Continuar',
                cancelButtonText: 'Cancelar'
            }, function() {
                $.ajax({
                    url: url+'/'+id,
                    type: 'POST',
                    data: {
                        "_token": window.Laravel.csrfToken,
                    },
                }).done(function() {
                    tableRecords.ajax.reload();
                    tableRecordsHistory.ajax.reload();
                    $.growl.notice({ title: "Agregar", message: "Se cerro su arqueo correctamente" });
                }).fail(function() {
                    $.growl.error({ message: "Hubo un error al intentar cerrar su arqueo, por favor intente más tarde" });
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
                storeArching($(this).data("row-id"));
            });
        }, 500);
    }

    function initial() {
        tableRecords = $('#tables-users-old').on('processing.dt', function () {
            activeActions();
        }).DataTable({
            order: [[ 0, "desc" ]],
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
                { data: 'full_name' },
                { data: 'amount' },
                {
                    data: 'actions',
                    orderable: false,
                    render: function (data, type, row, meta) {
                        return "<button type=\"button\" class=\"btn btn-xs btn-success command-store\" data-row-id=\"" + row.id + "\"><span class=\"fa fa-check-circle\"></span></button> ";
                    }
                },
		    ]
        });

        tableRecordsHistory = $('#tables-archings-history').DataTable({
            order: [[ 0, "desc" ]],
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
                url: url + "/history/get?timeZone="+timeZone,
                type: 'POST',
                data:{
                    '_token':window.Laravel.csrfToken,
               }
		    },
		    columns: [
                { data: 'company' },
                { data: 'full_name' },
                { data: 'amount' },
                { data: 'created_at' },
		    ]
        });

        $("#tabs").tabs();
    }

    $(document).ready(initial);
});
