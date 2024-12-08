@extends($activeTemplate . 'layouts.app')
@section('panel')
    @include($activeTemplate . 'partials.user_header')

    @yield('content')

    @include($activeTemplate . 'partials.footer')
@endsection
