$(function() {
    'use strict';

    function initial() {
        $('#tables-rates-hours').DataTable({
            language: {
                url: 'https://cdn.datatables.net/plug-ins/1.11.3/i18n/es_es.json',
                searchPlaceholder: 'Búsqueda...',
            },
        });
    }

    $(document).ready(initial);
});
