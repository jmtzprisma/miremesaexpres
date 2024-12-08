@extends($activeTemplate . 'layouts.master')
@section('content')
    <div class="section section--xl">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="card custom--card">
                        <div class="card-header">
                            <h5 class="card-title">
                                {{ __($pageTitle) }}
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-6">
                                    <a href="{{ route('user.send.money.video_valid', ['only_kyc' => 'true']) }}">
                                    <div class="card custom--card">
                                        <div class="card-body">
                                            Soy residente de la Unión Europea
                                        </div>
                                    </div>
                                    </a>
                                </div>
                                <div class="col-6">
                                    <a href="{{route('user.kyc.form')}}">
                                    <div class="card custom--card">
                                        <div class="card-body">
                                            No soy residente europeo
                                        </div>
                                    </div>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
