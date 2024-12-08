@extends('admin.layouts.app')

@section('panel')
    <div class="card b-radius--10 mb-3">
        <div class="card-body">
            @if ($countries->count())
                <form action="" method="POST">
                    @csrf
                    <div class="row">
                        @foreach ($countries as $item)
                            @php
                                $rate = $rates->where('to_country', $item->id)->first();
                                $value = $rate->rate ?? null;
                            @endphp
                            <div class="col-lg-6 col-xl-3">
                                <div class="form-group">
                                    <label>{{ $country->currency }} @lang('to') {{ $item->currency }}</label>
                                    <input name="data[{{ $loop->index }}][from_country]" type="hidden" value="{{ $country->id }}">
                                    <input name="data[{{ $loop->index }}][to_country]" type="hidden" value="{{ $item->id }}">
                                    <div class="input-group">
                                        <span class="input-group-text">1 {{ $country->currency }} = </span>
                                        <input class="form-control" name="data[{{ $loop->index }}][rate]" step="any" type="number" value="{{ $value }}">
                                        <span class="input-group-text">{{ $item->currency }} </span>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                        <div class="col-md-12">
                            <button class="btn w-100 btn--primary h-45" type="submit">@lang('Submit')</button>
                        </div>
                    </div>
                </form>
            @else
                <div class="text-center">
                    <h3 class="text--danger">@lang('Add more countries')</h3>
                </div>
            @endif
        </div>
    </div>
@endsection

@push('breadcrumb-plugins')
    <x-back route="{{ route('admin.country.index') }}"></x-back>
@endpush
