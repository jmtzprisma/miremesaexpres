<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $general->siteName($pageTitle ?? '') }}</title>
    <link rel="shortcut icon" type="image/png" href="{{ getImage(getFilePath('logoIcon') . '/favicon.png') }}">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets/global/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/admin/css/vendor/bootstrap-toggle.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/global/css/all.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/global/css/line-awesome.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/global/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/global/css/slick.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/global/css/datepicker.min.css') }}">

    @stack('style-lib')

    <link rel="stylesheet" href="{{ asset('assets/admin/css/app.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/admin/css/custom.css') }}">
    <link rel="stylesheet" href="{{ asset('css/style_new.css') }}">

    @stack('style')
    <style>
        .login-form .form-control {
            color: #000
        }
        .login-area::after
        {
            border: none;
        }

        @media screen and (max-width: 991px)
        {
            .d-xs-none{
                display: none;
            }
        }
        .sidebar[class*="bg--"] .sidebar__menu .sidebar__menu-header {
            color: #000000;
        }

        .list-group-item .w-75
        {
            max-width: 75%;
        }
        /* .list-group-item .form-group
        {
            max-width: inherit;
        } */

        .slick-next{
            position: absolute;
            top: calc(50% - 10px);
            right: 0;
            z-index: 999;
            background-color: #8080806e;
            border-radius: 50%;
            width: 35px;
            height: 35px;
        }
        .slick-prev{
            position: absolute;
            top: calc(50% - 10px);
            left: 0;
            z-index: 999;
            background-color: #8080806e;
            border-radius: 50%;
            width: 35px;
            height: 35px;
        }
        tbody, td, tfoot, th, thead, tr {
            border-color: blue;
            border-style: solid;
            border-width: 0px 0px 2px 0px;
        }
    </style>
</head>

<body>
    @yield('content')



    <script src="{{ asset('assets/global/js/jquery-3.6.0.min.js') }}"></script>
    <script src="{{ asset('assets/global/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('assets/admin/js/vendor/bootstrap-toggle.min.js') }}"></script>

    <script src="{{ asset('assets/global/js/jquery.slimscroll.min.js') }}"></script>


    @include('partials.notify')
    @stack('script-lib')

    <script src="{{ asset('assets/admin/js/nicEdit.js') }}"></script>

    <script src="{{ asset('assets/global/js/select2.min.js') }}"></script>
    <script src="{{ asset('assets/global/js/slick.min.js') }}"></script>
    <script src="{{ asset('assets/admin/js/app.js') }}"></script>
    <script src="{{ asset('assets/admin/js/cuModal.js') }}"></script>

    <script src="{{ asset('assets/global/js/cryptojs-aes.min.js') }}"></script>
    <script src="{{ asset('assets/global/js/cryptojs-aes-format.js') }}"></script>

    <script>
        "use strict";
        bkLib.onDomLoaded(function() {
            $(".nicEdit").each(function(index) {
                $(this).attr("id", "nicEditor" + index);
                new nicEditor({
                    fullPanel: true
                }).panelInstance('nicEditor' + index, {
                    hasPanel: true
                });
            });
        });
        (function($) {
            $(document).on('mouseover ', '.nicEdit-main,.nicEdit-panelContain', function() {
                $('.nicEdit-main').focus();
            });
        
            
            $(document).on('keypress', '.rule_iban', function(e) {
                var charCode = (e.which) ? e.which : e.keyCode;
                if (charCode > 31 && (charCode < 48 || charCode > 57)) {
                    return false;
                }
            });

            $(document).on('keyup', '.rule_iban', function(e) {
                var lngt = $('.rule_iban').val().length;
                $(".rule_iban_length").html(lngt + "/20")
            });

            $(document).on('blur', '.rule_iban', function() {
                var lngt = $(".rule_iban").val().length;
                if(lngt < 20)
                {
                    $(".rule_iban").focus();
                    notify('error', 'El campo requiere un valor de 20 dígitos.');
                }
            });
        })(jQuery);

        $("#value_tipo_cambio").on("blur", function(){
            //alert()
            $.ajax({
                type: 'POST',
                url: '{{ route('admin.country.update.rate') }}',
                data:{
                    '_token': '{{ csrf_token() }}',
                    rate: $("#value_tipo_cambio").val()
                },
                success: function(data) {
                    notify('success', 'Tipo de cambio actualizado')
                },
                error: function() {
                    notify('error', 'Server error');
                }
            });
        });
    </script>

    @stack('script')


</body>

</html>
