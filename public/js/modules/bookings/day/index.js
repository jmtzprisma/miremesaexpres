$(function() {
    'use strict';
    const url = '/bookings';
    var tableRecords = null;
    var timeZone = Intl.DateTimeFormat().resolvedOptions().timeZone;

    function initial() {
        $("#divModalError").hide();

        tableRecords = $('#tables-bookings').DataTable({
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
                url: url + "/get/day?timeZone="+timeZone,
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
                    data: 'status',
                    render: function (data, type, row, meta) {
                        switch(row.status) {
                            case "Esperando":
                                return '<p class="text-info">'+row.status+'</p>';
                                break;
                            case "Devolucion":
                                return '<p class="text-primary">'+row.status+'</p>';
                                break;
                            case "Cancelada":
                                return '<p class="text-danger">'+row.status+'</p>';
                                break;
                            case "Terminada":
                                return '<p class="text-success">'+row.status+'</p>';
                                break;
                            default:
                                return '<p class="text-secondary">'+row.status+'</p>';
                                break;
                        }
                    }
                },
                {
                    data: 'actions',
                    orderable: false,
                    render: function (data, type, row, meta) {
                        return "<a class=\"btn btn-xs btn-primary command-show\" href=\"bookings/history/"+row.id+"\"><span class=\"fa fa-eye\"></span></a> ";
                    }
                },
		    ]
        });

        setInterval(function() {
            tableRecords.ajax.reload();
        }, 120000);
    }

    $(document).ready(initial);
});
