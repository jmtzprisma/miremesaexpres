<!DOCTYPE html>
<html lang="{{ config('app.locale') }}" itemscope itemtype="http://schema.org/WebPage">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $general->siteName($pageTitle ?? '') }}</title>
    <link rel="shortcut icon" type="image/png" href="{{ getImage(getFilePath('logoIcon') . '/favicon.png') }}">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets/global/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/global/css/all.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/global/css/line-awesome.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/agent/css/lightcase.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/global/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/global/css/slick.css') }}">
    @stack('style-lib')
    <link rel="stylesheet" href="{{ asset('assets/agent/css/agent.css') }}">
    <link rel="stylesheet" href="{{ asset('css/style_new.css') }}">

    @stack('style')
    <style>
        .login-area::after
        {
            border: none;
        }
        .d-sidebar {
            background-color: #009432;
        }
        .agent-dashboard .dashboard-top-nav {
            background-color: #009432;
        }
        .d-sidebar::after {
            background: none;
        }
        .sidebar-menu__link {
            color: #ffffff;
        }
        .sidebar-menu__link:hover {
            color: #000000;
        }
        .sidebar-menu__item.active .sidebar-menu__link {
            background-color: rgba(255, 255, 255, 0.1);
            color: #16377b;
            border-color: #16377b;
            z-index: 1;
        }
        .sidebar-menu__header::before {
            background-color: #16377b;
        }
        .sidebar-menu__header {
            color: #000000;
        }
    </style>
</head>

<body>
    @yield('content')

    <script src="{{ asset('assets/global/js/jquery-3.6.0.min.js') }}"></script>
    <script src="{{ asset('assets/global/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('assets/global/js/slick.min.js') }}"></script>
    <script src="{{ asset('assets/agent/js/wow.min.js') }}"></script>
    <script src="{{ asset('assets/agent/js/lightcase.min.js') }}"></script>
    <script src="{{ asset('assets/agent/js/jquery.paroller.min.js') }}"></script>
    @include('partials.notify')
    @stack('script-lib')
    <script src="{{ asset('assets/global/js/select2.min.js') }}"></script>
    <script>
        function showAmount(amount, decimal = 8, separate = false, exceptZeros = true) {
            amount *= 1;
            var separator = '';
            if (separate) {
                separator = ',';
            }
            var printAmount = amount.toFixed(decimal).replace(/\B(?=(\d{3})+(?!\d))/g, separator);
            if (exceptZeros) {
                var exp = printAmount.split('.');
                if (exp[1] * 1 == 0) {
                    printAmount = exp[0];
                } else {
                    printAmount = printAmount.replace(/0+$/, '');
                }
            }
            return printAmount;
        }
    </script>
    <script src="{{ asset('assets/agent/js/agent.js') }}"></script>

    <script src="{{ asset('assets/global/js/jquery.slimscroll.min.js') }}"></script>
    <script>
        (function($) {
            "use strict";
            $(".langSel").on("change", function() {
                window.location.href = "{{ route('home') }}/change/" + $(this).val();
            });
            $('.showFilterBtn').on('click', function() {
                $('.responsive-filter-card').slideToggle();
            });

            let headings = $('.table th');
            let rows = $('.table tbody tr');
            let columns
            let dataLabel;
            $.each(rows, function(index, element) {
                columns = element.children;
                if (columns.length == headings.length) {
                    $.each(columns, function(i, td) {
                        dataLabel = headings[i].innerText;
                        $(td).attr('data-label', dataLabel)
                    });
                }
            });

        })(jQuery)
    </script>

    @stack('script')

</body>

</html>
