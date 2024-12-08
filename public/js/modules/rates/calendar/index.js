$(function() {
    'use strict';
    const url = '/rates/calendar';
    var args = null;
    var dTable = null;
    var arrayData = [];
    var dTableDates = null;

    function clearDataForm() {
        $("#btnClear").attr("disabled", "disabled")
        args = null;
        arrayData = [];
        $("#rate_id").val(0).trigger('change');
        dTable
            .clear()
            .draw();
    }

    function clearDataFormDates() {
        $("#btnClearDates").attr("disabled", "disabled")
        args = null;
        arrayData = [];
        $("#dates_rate_id").val(0).trigger('change');
        dTableDates
            .clear()
            .draw();
    }

    function saveRates(e) {
        e.preventDefault();
        var dataPost = [];
        let totalPercentage = 0;

        arrayData.map(el => {
            dataPost.push({
                company_id: company_id,
                rate_id: el.rate_id,
                title: el.rate_description,
                initial_percentage:el.startPercentage,
                end_percentage:el.endPercentage,
                start_date: args.startStr,
                end_date: args.endStr,
                all_day: args.allDay
            });

            let percentage = el.startPercentage == 100 ? el.startPercentage : parseFloat(el.endPercentage) - parseFloat(el.startPercentage) ;
            totalPercentage += percentage;
        });

        if (totalPercentage < 100) {
            $.growl.error({
                title: "Error",
                message: "La suma de sus porcentajes debe de ser total a 100%"
            });
        }

        arrayData.map(el => {
            calendar.addEvent({
                title: el.rate_description,
                start: args.start,
                end: args.end,
                allDay: args.allDay
            })
        });

        calendar.unselect()


        var post = {
            _token: window.Laravel.csrfToken,
            parking_id: $("#parking_id").val(),
            data: dataPost
        }

        $.post(url, post)
            .done(function(data) {
                $("#modalAddRates").modal("hide");
            })
            .fail(function(xhr) {
                $.growl.error({
                    title: "Error",
                    message: "Existe un error por favor intente mas tarde"
                });
            });
    }

    function saveRatesDates(e) {
        e.preventDefault();
        var dataPost = [];

        arrayData.map(el => {
            dataPost.push({
                company_id: company_id,
                rate_id: el.rate_id,
                title: el.rate_description,
                initial_percentage:el.startPercentage,
                end_percentage:el.endPercentage,
                start_date: $("#star_time").val(),
                end_date: $("#end_date").val(),
                all_day: true
            });
        });

        if ($("#star_date").val() == "" && $("#end_date").val() == "") {
            $.growl.error({
                title: "Error",
                message: "Por favor ingresa la fecha de inicio y fin para tu vigencia"
            });
        }

        arrayData.map(el => {
            calendar.addEvent({
                title: el.rate_description,
                start: $("#star_time").val(),
                end: $("#end_date").val(),
                allDay: true
            })
        });

        calendar.unselect()

        var post = {
            _token: window.Laravel.csrfToken,
            parking_id: $("#dates_parking_id").val(),
            data: dataPost
        }

        $.post(url, post)
            .done(function(data) {
                $("#modalAddRatesDates").modal("hide");
            })
            .fail(function(xhr) {
                $.growl.error({
                    title: "Error",
                    message: "Existe un error por favor intente mas tarde"
                });
            });
    }

    function addRatesDates(e) {
        e.preventDefault();
        $("#btnClearDates").removeAttr("disabled");
        const rate = $("#dates_rate_id");

        if (rate.val() == "" || rate.val() == "Seleccione su tarifa") {
            $.growl.error({ message: "Por favor seleccione su tarifa" });
        }

        var rateSelect = rates.find(element => element.id.toString() === rate.val());

        if (rateSelect.type == "Tarifa") {
            const ratesFilter = rates.filter(element => element.type === rateSelect.type
                && element.id >= parseInt(rate.val()));
            if (ratesFilter.length > 15) {
                var i = 0;
                var percentage = 100/15;
                var end_percentage = 0;
                var initial_percentage = 0;

                ratesFilter.map(element => {
                    if (element.id >= rateSelect.id && i < 15) {
                        end_percentage += percentage;
                        end_percentage = parseInt(end_percentage);
                        i++;
                        if (i > 14) end_percentage = 100;

                        const rowObj = {
                            rate_id: element.id,
                            company_id: company_id,
                            endPercentage: end_percentage,
                            startPercentage: initial_percentage,
                            rate_description: element.name+" %"+initial_percentage+"-"+end_percentage,
                        };

                        const rowT = [
                            element.name+" %"+initial_percentage+"-"+end_percentage,
                            initial_percentage,
                            end_percentage,
                            element.id,
                        ];

                        initial_percentage += percentage;
                        initial_percentage = parseInt(initial_percentage);
                        arrayData.push(rowObj);
                        dTableDates.row.add(rowT).draw(false);
                    }
                })
            } else {
                var i = 0;
                var percentage = 100/ratesFilter.length;
                var end_percentage = 0;
                var initial_percentage = 0;
                var endRecords = ratesFilter.length;

                ratesFilter.map(element => {
                    if (element.id >= rateSelect.id && i <= endRecords) {
                        end_percentage += percentage;
                        end_percentage = parseInt(end_percentage);
                        i++;
                        if (i == endRecords) end_percentage = 100;

                        const rowObj = {
                            rate_id: element.id,
                            company_id: company_id,
                            endPercentage: end_percentage,
                            startPercentage: initial_percentage,
                            rate_description: element.name+" %"+initial_percentage+"-"+end_percentage,
                        };

                        const rowT = [
                            element.name+" %"+initial_percentage+"-"+end_percentage,
                            initial_percentage,
                            end_percentage,
                            element.id,
                        ];

                        initial_percentage += percentage;
                        initial_percentage = parseInt(initial_percentage);
                        arrayData.push(rowObj);
                        dTableDates.row.add(rowT).draw(false);
                    }
                })
            }
        } else {
            const rowObj = {
                rate_id: rateSelect.id,
                company_id: company_id,
                endPercentage: 0,
                startPercentage: 100,
                rate_description: rateSelect.name+" %0-100",
            };

            const rowT = [
                rateSelect.name+" %0-100",
                0,
                100,
                rateSelect.id,
            ];

            arrayData.push(rowObj);
            dTableDates.row.add(rowT).draw(false);
        }
    }

    function addRates(e) {
        e.preventDefault();
        $("#btnClear").removeAttr("disabled");
        const rate = $("#rate_id");

        if (rate.val() == "" || rate.val() == "Seleccione su tarifa") {
            $.growl.error({ message: "Por favor seleccione su tarifa" });
        }

        var rateSelect = rates.find(element => element.id.toString() === rate.val());

        if (rateSelect.type == "Tarifa") {
            const ratesFilter = rates.filter(element => element.type === rateSelect.type
                && element.id >= parseInt(rate.val()));
            console.log(ratesFilter);
            if (ratesFilter.length > 15) {
                var i = 0;
                var percentage = 100/15;
                var end_percentage = 0;
                var initial_percentage = 0;

                ratesFilter.map(element => {
                    if (element.id >= rateSelect.id && i < 15) {
                        end_percentage += percentage;
                        end_percentage = parseInt(end_percentage);
                        i++;
                        if (i > 14) end_percentage = 100;

                        const rowObj = {
                            rate_id: element.id,
                            company_id: company_id,
                            endPercentage: end_percentage,
                            startPercentage: initial_percentage,
                            rate_description: element.name+" %"+initial_percentage+"-"+end_percentage,
                        };

                        const rowT = [
                            element.name+" %"+initial_percentage+"-"+end_percentage,
                            initial_percentage,
                            end_percentage,
                            element.id,
                        ];

                        initial_percentage += percentage;
                        initial_percentage = parseInt(initial_percentage);
                        console.log(rowObj, rowT);
                        arrayData.push(rowObj);
                        dTable.row.add(rowT).draw(false);
                    }
                })
            } else {
                var i = 0;
                var percentage = 100/ratesFilter.length;
                var end_percentage = 0;
                var initial_percentage = 0;
                var endRecords = ratesFilter.length;

                ratesFilter.map(element => {
                    if (element.id >= rateSelect.id && i <= endRecords) {
                        end_percentage += percentage;
                        end_percentage = parseInt(end_percentage);
                        i++;
                        if (i == endRecords) end_percentage = 100;

                        const rowObj = {
                            rate_id: element.id,
                            company_id: company_id,
                            endPercentage: end_percentage,
                            startPercentage: initial_percentage,
                            rate_description: element.name+" %"+initial_percentage+"-"+end_percentage,
                        };

                        const rowT = [
                            element.name+" %"+initial_percentage+"-"+end_percentage,
                            initial_percentage,
                            end_percentage,
                            element.id,
                        ];

                        initial_percentage += percentage;
                        initial_percentage = parseInt(initial_percentage);
                        console.log(rowObj, rowT);
                        arrayData.push(rowObj);
                        dTable.row.add(rowT).draw(false);
                    }
                })
            }
        } else {
            const rowObj = {
                rate_id: rateSelect.id,
                company_id: company_id,
                endPercentage: 0,
                startPercentage: 100,
                rate_description: rateSelect.name+" %0-100",
            };

            const rowT = [
                rateSelect.name+" %0-100",
                0,
                100,
                rateSelect.id,
            ];

            arrayData.push(rowObj);
            dTable.row.add(rowT).draw(false);
        }
    }

    function initial() {
        setTimeout(() => {
            var parking_id = $('#parkings').val();
            $.get('/parkings/company/'+parking_id+'/calendar')
                .done(function(data) {
                    calendar.removeAllEvents();
                    data.data.map(el => {
                        calendar.addEvent(el);
                    });
                })
                .fail(function(xhr) {
                    console.log(xhr);
                });
        }, 500)

        $("#parkings").change(() => {
            var parking_id = $('#parkings').val();
            $.get('/parkings/company/'+parking_id+'/calendar')
                .done(function(data) {
                    calendar.removeAllEvents();
                    data.data.map(el => {
                        calendar.addEvent(el);
                    });
                })
                .fail(function(xhr) {
                    console.log(xhr);
                });

        });

        var calendarEl = document.getElementById('calendar2');
        calendar = new FullCalendar.Calendar(calendarEl, {
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth'
            },
            defaultView: 'month',
            navLinks: false,
            businessHours: false,
            editable: false,
            selectable: true,
            selectMirror: false,
            droppable: false,
            select: function(arg) {
                clearDataForm();
                args = arg;
                arrayData = [];
                $("#modalAddRates").modal("show");
            },
            eventClick: function(arg) {
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
                        url: url+'/'+arg.event.extendedProps.id_company_rate,
                        type: 'DELETE',
                        data: {
                            "_token": window.Laravel.csrfToken,
                        },
                    }).done(function() {
                        arg.event.remove()
                        $.growl.notice({ title: "Eliminar", message: "Se eliminó su registro correctamente" });
                    }).fail(function() {
                        $.growl.error({ message: "Hubo un error al intentar eliminar, por favor intente más tarde" });
                    });
                });
            },
            editable: false,
            dayMaxEvents: false, // allow "more" link when too many events
            events: [
            ]
        });

        calendar.render();

        dTable = $('#tables-rates').DataTable({
            "searching": false,
            "paging":   false,
            "ordering": false,
            "info":     false
        });

        dTableDates = $('#tables-rates-dates').DataTable({
            "searching": false,
            "paging":   false,
            "ordering": false,
            "info":     false
        });

        $('.select2').select2({
            minimumResultsForSearch: Infinity
        });

        $("#btnClear").attr("disabled", "disabled")
        $("#btnAdd").click(addRates);
        $("#btnClear").click(clearDataForm);
        $("#saveRate").click(saveRates);

        $("#btnClearDates").attr("disabled", "disabled")
        $("#btnAddDates").click(addRatesDates);
        $("#btnClearDates").click(clearDataFormDates);
        $("#saveRateDates").click(saveRatesDates);
    }

    $(document).ready(initial);
});
