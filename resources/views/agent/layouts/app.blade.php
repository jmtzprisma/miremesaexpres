@extends('agent.layouts.master')

@section('content')
    <!-- page-wrapper start -->
    <div class="agent-dashboard">
        @include('agent.partials.sidenav')
        @include('agent.partials.topnav')

        <div class="agent-dashboard__body">
            @yield('panel')
        </div>
    </div>
@endsection
