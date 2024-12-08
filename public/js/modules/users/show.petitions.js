$(function() {
    'use strict';
    const url = 'petitions';

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

    function initial() {
        $('#formTaxes').on('submit', odAdd);
    }

    $(document).ready(initial);
});
