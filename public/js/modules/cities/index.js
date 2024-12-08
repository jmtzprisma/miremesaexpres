$(function() {
    'use strict';
    const url = '/cities';

    function addActions() {
        $("button[data-action='delete']").off("click");
        $("button[data-action='delete']").click(onDelete);
    }

    function onDelete(e) {
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
                    url: url+'/'+id,
                    type: 'DELETE',
                    data: {
                        "_token": window.Laravel.csrfToken,
                    },
                }).done(function() {
                    $('#city_'+id).remove();
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

    function initial() {
        $('#tables-cities').DataTable({
            language: {
                url: 'https://cdn.datatables.net/plug-ins/1.11.3/i18n/es_es.json',
                searchPlaceholder: 'Búsqueda...',
            },
        });

        addActions();
    }

    $(document).ready(initial);
});
