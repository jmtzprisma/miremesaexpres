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
                            @if ($user->kyc_data)
                                <ul class="list-group list-group-flush">
                                    @foreach ($user->kyc_data as $val)
                                        @continue(!$val->value)
                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                            {{ __($val->name) }}
                                            <span>
                                                @if ($val->type == 'checkbox')
                                                    {{ implode(',', $val->value) }}
                                                @elseif($val->type == 'file')
                                                    <a href="{{ route('user.attachment.download', encrypt(getFilePath('verify') . '/' . $val->value)) }}" class="t-link">
                                                        <span class="d-inline-block me-2">
                                                            <span class="fa fa-file"></span>
                                                        </span> @lang('Attachment')
                                                    </a>
                                                @else
                                                    <p class="m-0">{{ __($val->value) }}</p>
                                                @endif
                                            </span>
                                        </li>
                                    @endforeach
                                </ul>
                            @else
                                <h5 class="text-center">@lang('Tus datos se encuentran en proceso de validación, en breve tendremos una respuesta.')</h5>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
