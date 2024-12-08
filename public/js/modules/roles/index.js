$(function() {
    'use strict';
    const url = '/permissions';
    var tableRecords = null;
    var timeZone = Intl.DateTimeFormat().resolvedOptions().timeZone;

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

    function activeActions() {
        setTimeout(() => {
            $(".command-delete").off("click");
            $(".command-delete").on("click", function(e)
            {
                onDelete($(this).data("row-id"));
            });
        }, 500);
    }

    function initial() {
        tableRecords = $('#tables-permissions').on('processing.dt', function () {
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
                url: url + "/get?timeZone="+timeZone,
                type: 'POST',
                data:{
                    '_token':window.Laravel.csrfToken,
               }
		    },
		    columns: [
                { data: 'guard_name' },
                { data: 'alias' },
                { data: 'description' },
                {
                    data: 'is_company',
                    render: function (data, type, row, meta) {
                        return (row.is_company) ? "Si" : "No";
                    }
                },
                {
                    data: 'actions',
                    orderable: false,
                    render: function (data, type, row, meta) {
                        return "<a href=\"/permissions/" + row.id + "/edit\" class=\"btn btn-xs btn-success \" data-row-id=\"" + row.id + "\"><span class=\"fa fa-pencil\"></span></a> " +
                                "<button type=\"button\" class=\"btn btn-xs btn-danger command-delete\" data-row-id=\"" + row.id + "\"><span class=\"fa fa-trash-o\"></span></button> ";
                    }
                },
		    ]
        });
    }

    $(document).ready(initial);
});
