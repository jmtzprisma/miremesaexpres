$(function() {
    'use strict';

    //For Card Number formatted input
    var cardNum = document.getElementById('card_number');
    cardNum.onkeyup = function (e) {
        if (this.value == this.lastValue) return;
        var caretPosition = this.selectionStart;
        var sanitizedValue = this.value.replace(/[^0-9]/gi, '');
        var parts = [];

        for (var i = 0, len = sanitizedValue.length; i < len; i +=4) { parts.push(sanitizedValue.substring(i, i + 4)); } for (var i=caretPosition - 1; i>= 0; i--) {
            var c = this.value[i];
            if (c < '0' || c> '9') {
                caretPosition--;
            }
        }
        caretPosition += Math.floor(caretPosition / 4);

        this.value = this.lastValue = parts.join('-');
        this.selectionStart = this.selectionEnd = caretPosition;
    }

    // Radio button
    $('.radio-group .radio').click(function(){
        $(this).parent().find('.radio').removeClass('selected');
        $(this).addClass('selected');
    });

    function activeFunctions() {
        $("button[data-action='delete']").off("click");
        $("button[data-action='delete']").click(recordDelete);
    }

    function addRecord(e)  {
        e.preventDefault();

        if (e.target.checkValidity() === false) {
            e.stopPropagation();
        } else {
            $.post("/users/"+idUser+"/payments/methods/store", $("#formPaymentMethod").serialize())
                .done(function(data) {
                    console.log(data);
                    const record = data.data;

                    if ($('#payment_methods_id').val()) {
                        $('#payment_methods_'+$('#payment_methods_id').val()).remove();
                    }

                    if ($('.odd')[0]) {
                        $('.odd').remove();
                    }

                    $("#formPaymentMethod")[0].reset();

                    const rowStatus = (record.status) ? '<span class="badge rounded-pill bg-primary me-1 mb-1 mt-1">En uso</span>'
                        : '<span class="badge rounded-pill bg-warning me-1 mb-1 mt-1">Sin uso</span>';

                    const rowPaymentMethod = '<tr id="payment_methods_' + record.id + '">'+
                        '<td>' + rowStatus + '</td>'+
                        '<td>' + record.alias + '</td>' +
                        '<td>' + record.card_number + '</td>' +
                        '<td>Stripe</td>' +
                        '<td>' + record.tax + '</td>' +
                        '<td>' + record.created_at + '</td>' +
                        '<td>' + record.updated_at + '</td>' +
                        '<td>' +
                        '<div class="btn-group align-top">' +
                        '<button class="btn btn-sm btn-danger badge" data-action="delete" data-id="' + record.id + '" type="button"><i class="fa fa-trash"></i></button>' +
                        '</div>' +
                        '</td>' +
                        '</tr>';

                    $("#tables-payments-methods tbody").append(rowPaymentMethod);
                    $('#modalAddPaymentMethod').modal('hide')
                    $.growl.notice({ title: "Agregar", message: "Se agregó su registro correctamente" });

                    $('#tables-payments-methods').DataTable();

                    activeFunctions();
                })
                .fail(function(xhr) {
                    console.log(xhr);
                    if (JSON.parse(xhr.responseText).errors.licenseplate) {
                        $("#validationServerLinceseplate").html(JSON.parse(xhr.responseText).errors.licenseplate[0]);
                    }
                });
        } 

        e.target.classList.add('was-validated');
    }

    function recordDelete(e) {
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
                    url: "/users/"+idUser+"/payments/methods/"+id,
                    type: 'DELETE',
                    data: {
                        "_token": window.Laravel.csrfToken,
                    },
                }).done(function() {
                    $('#payment_methods_'+id).remove();
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
        $('#formPaymentMethod').on('submit', addRecord);

        $('#method_type').change(() => {
            $('#alias').val($('#method_type').val());
        });

        $('#tables-payments-methods').DataTable({
            language: {
                url: 'https://cdn.datatables.net/plug-ins/1.11.3/i18n/es_es.json',
                searchPlaceholder: 'Búsqueda...',
            }
        });

        activeFunctions();
    }

    $(document).ready(initial);
});
