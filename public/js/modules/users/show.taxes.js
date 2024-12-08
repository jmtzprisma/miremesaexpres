$(function() {
    'use strict';
    const url = 'taxes';

    function addActions() {
        $("button[data-action='delete']").off("click");
        $("button[data-action='edit']").off("click");
        $("button[data-action='delete']").click(onDelete);
        $("button[data-action='edit']").click(onEdit);
    }

    function odAdd(e)  {
        e.preventDefault();

        $.post("/users/"+idUser+"/"+url+"/store", $("#formTaxes").serialize())
            .done(function(data) {
                const tax = data.data;
                console.log(tax);

                if ($('#tax_id').val()) {
                    $('#tax_'+$('#tax_id').val()).remove();
                }
                
                $("#formTaxes")[0].reset();

                const rowObj = '<tr id="tax_' + tax.id + '">'+
                    '<td>' + tax.name + '</td>' +
                    '<td>' + tax.vat + '</td>' +
                    '<td>' +
                    '<div class="btn-group align-top">' +
                    '<button class="btn btn-sm btn-primary badge" data-action="edit" data-data="' + tax + '" type="button"><i class="fa fa-pencil"></i></button>' + 
                    '<button class="btn btn-sm btn-danger badge" data-action="delete" data-id="' + tax.id + '" type="button"><i class="fa fa-trash"></i></button>' +
                    '</div>' +
                    '</td>' +
                    '</tr>';

                $("#tables-taxes tbody").append(rowObj);
                $('#modalAddTaxes').modal('hide')
                $.growl.notice({ title: "Agregar", message: "Se agregó su registro correctamente" });

                addActions();
            })
            .fail(function(data) {
                console.error(data);
            });
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
                    url: "/users/"+idUser+"/"+url+"/"+id,
                    type: 'DELETE',
                    data: {
                        "_token": window.Laravel.csrfToken,
                    },
                }).done(function() {
                    $('#tax_'+id).remove();
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

    function onEdit(e) {
        const  tax = $(e.target).data('data');

        if (tax) {
            $('#vat').val(tax.vat);
            $('#name').val(tax.name);
            $('#city').val(tax.city);
            $('#tax_id').val(tax.id);
            $('#address').val(tax.address);
            $('#postal_code').val(tax.postal_code);
            $('#modalAddTaxes').modal('show')
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
        $('#formTaxes').on('submit', odAdd);

        $('#tables-taxes').DataTable({
            language: {
                url: 'https://cdn.datatables.net/plug-ins/1.11.3/i18n/es_es.json',
                searchPlaceholder: 'Búsqueda...',
            },
            "searching": false,
            "paging":   false,
            "ordering": false,
            "info":     false
        });

        addActions();
    }

    $(document).ready(initial);
});
