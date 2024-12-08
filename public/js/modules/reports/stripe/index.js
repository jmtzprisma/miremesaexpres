$(function() {
    'use strict';
    const url = '/reports/stripe';
    var timeZone = Intl.DateTimeFormat().resolvedOptions().timeZone;

    function initial() {
        tableRecords = $('#tables-users-old').DataTable({
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
                { data: 'company' },
                { data: 'full_name' },
                { data: 'email' },
                { data: 'phone' },
                { data: 'price_total' },
                { data: 'stripe_id_payment' },
                { data: 'card_number' },
                { data: 'time_delivery' },
		    ]
        });
    }

    $(document).ready(initial);
});
