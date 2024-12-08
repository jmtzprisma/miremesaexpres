@extends($activeTemplate . 'layouts.frontend')
@section('content')
    <section class="section">
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <div class="card custom--card">
                        <div class="card-body">
                            @php
                                echo $policy->data_values->details;
                            @endphp
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
