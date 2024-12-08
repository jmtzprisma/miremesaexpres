@extends('admin.layouts.app')
@section('panel')
    <div class="row">
        <div class="col-lg-12">
            <div class="card b-radius--10">
                <div class="card-body p-0">
                    <div class="table-responsive--sm table-responsive">
                        <table class="table table-responsive-md">
                            <thead>
                                <tr>
                                    <th>@lang('Name')</th>
                                    <th>@lang('Currency')</th>
                                    <th>@lang('Balance')</th>
                                    <th>@lang('Titular')</th>
                                    <th>@lang('Tasa promedio')</th>
                                    <th>@lang('Tipo')</th>
                                    <th class="w-85"></th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($banks as $bank)
                                    <tr>
                                        <td><span class="text--muted fw-bold">{{ $bank->name }}</span></td>
                                        <td><span class="small">{{ $bank->currency }}</span></td>
                                        <td><span class="small">{{ showAmount($bank->balance) }}</span></td>
                                        <td><span class="small">{{ $bank->name_account }}</span></td>
                                        <td><span class="small">{{ showAmount($bank->average_rate) }}</span></td>
                                        <td>
                                            @if($bank->recibe)
                                            <span class="badge badge--info">RECIBE</span>
                                            @endif
                                            @if($bank->envia)
                                            <span class="badge badge--success">ENVIA</span>
                                            @endif
                                        </td>
                                        <td class="d-flex">
                                            <a class="btn btn-sm btn-outline--primary me-2" href="{{route('admin.bank.edit', $bank->id)}}">
                                                <i class="las la-edit"></i>
                                            </a>
                                            <button class="btn btn-sm btn-outline--primary btn-deposit mx-2" data-id="{{ $bank->id }}">
                                                <i class="las la-plus"></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline--primary btn-out mx-2" data-id="{{ $bank->id }}">
                                                <i class="las la-minus"></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline--primary btn-convert-currency mx-2" data-id="{{ $bank->id }}">
                                                <i class="las la-shopping-cart"></i>
                                            </button>
                                            @if($bank->active)
                                            <a href="{{ route('admin.bank.inactive', $bank->id) }}" class="btn btn-sm btn-outline--danger ms-2">
                                                <i class="las la-trash"></i>
                                            </a>
                                            @else
                                            <a href="{{ route('admin.bank.active', $bank->id) }}" class="btn btn-sm btn-outline--success ms-2">
                                                <i class="las la-check"></i>
                                            </a>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td class="text-muted text-center" colspan="100%">{{ __($emptyMessage) }}</td>
                                    </tr>
                                @endforelse

                            </tbody>
                        </table><!-- table end -->
                    </div>
                </div>
                @if ($banks->hasPages())
                    <div class="card-footer py-4">
                        {{ paginateLinks($banks) }}
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div class="modal fade" id="depositModal" role="dialog" tabindex="-1">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">@lang('Deposit')</h5>
                    <button aria-label="Close" class="close" data-bs-dismiss="modal" type="button">
                        <i class="las la-times"></i>
                    </button>
                </div>
                <form action="{{ route('admin.bank.deposit') }}" method="POST">
                    @csrf
                    <input type="hidden" name="bankId" id="bankIdDeposito">
                    <input type="hidden" name="reason" value="OTRO">
                    
                    <div class="modal-body">
                        <div class="form-group">
                            <label class="fw-bold mt-2">@lang('Title')</label>
                            <input class="form-control form--control" name="title" required type="text">
                        </div>
                        <div class="form-group">
                            <label class="fw-bold mt-2">@lang('Description')</label>
                            <input class="form-control form--control" name="description" required type="text">
                        </div>
                        <div class="form-group">
                            <label class="fw-bold mt-2">@lang('Amount')</label>
                            <input class="form-control form--control text--right convert-amount" min="0" name="amount_currency_local" placeholder="0.00" required step="any" type="number">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn--dark" data-bs-dismiss="modal" type="button">@lang('No')</button>
                        <button class="btn btn--primary" type="submit">@lang('Yes')</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="modal fade" id="retiroModal" role="dialog" tabindex="-1">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">@lang('Retiro')</h5>
                    <button aria-label="Close" class="close" data-bs-dismiss="modal" type="button">
                        <i class="las la-times"></i>
                    </button>
                </div>
                <form action="{{ route('admin.bank.retiro') }}" method="POST">
                    @csrf
                    <input type="hidden" name="bankId" id="bankIdRetiro">
                    <input type="hidden" name="reason" value="OTRO">
                    
                    <div class="modal-body">
                        <div class="form-group">
                            <label class="fw-bold mt-2">@lang('Description')</label>
                            <input class="form-control form--control" name="description" required type="text">
                        </div>
                        <div class="form-group">
                            <label class="fw-bold mt-2">@lang('Amount')</label>
                            <input class="form-control form--control text--right convert-amount" min="0" name="amount_currency_local" placeholder="0.00" required step="any" type="number">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn--dark" data-bs-dismiss="modal" type="button">@lang('No')</button>
                        <button class="btn btn--primary" type="submit">@lang('Yes')</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="modal fade" id="convertCurrencyModal" role="dialog" tabindex="-1">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">@lang('Convert Currency')</h5>
                    <button aria-label="Close" class="close" data-bs-dismiss="modal" type="button">
                        <i class="las la-times"></i>
                    </button>
                </div>
                <form action="{{ route('admin.bank.convert_currency') }}" method="POST">
                    @csrf
                    <input type="hidden" name="bankId" id="bankIdCart">
                    <input type="hidden" name="reason" value="COMPRA MONEDA">
                    
                    <div class="modal-body">
                        <div class="form-group">
                            <label class="fw-bold mt-2">@lang('Amount convert')</label>
                            <input class="form-control form--control text--right convert-amount" min="0" name="amount_currency_local" id="amount" placeholder="0.00" required step="any" type="number">
                        </div>
                        <div class="form-group">
                            
                            <label class="fw-bold mt-2">@lang('Seleccione un banco')</label>
                            <select name="bankIdOutput" id="bankIdOutput" class="select2-basic" required>
                                <option value="">Seleccione un banco</option>
                                @foreach (\App\Models\Bank::where('envia', true)->get() as $itm)
                                <option value="{{ $itm->id }}" >{{ $itm->name . ' (' . $itm->currency . ')' }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="fw-bold mt-2">@lang('Amount converted')</label>
                            <input class="form-control form--control text--right convert-amount" min="0" name="amount_currency_convert" id="amount_convert" placeholder="0.00" required step="any" type="number">
                        </div>
                        <div class="form-group">
                            <label class="fw-bold mt-2" id="rate">@lang('Tasa')</label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn--dark" data-bs-dismiss="modal" type="button">@lang('No')</button>
                        <button class="btn btn--primary" type="submit">@lang('Yes')</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('breadcrumb-plugins')
    <a class="btn btn-outline--primary h-45" href="{{ route('admin.bank.add') }}"><i class="las la-plus"></i>@lang('Add New')</a>
    <x-search-form placeholder="Nombre" />
@endpush

@push('script')
<script>
    $(document).ready(function() {
        $(".btn-deposit").on("click", function(){
            bankId = $(this).data('id');

            $("#bankIdDeposito").val(bankId);
            $("#depositModal").modal("show");
        });

        $(".btn-out").on("click", function(){
            bankId = $(this).data('id');

            $("#bankIdRetiro").val(bankId);
            $("#retiroModal").modal("show");
        });

        $(".btn-convert-currency").on("click", function(){
            bankId = $(this).data('id');

            $("#bankIdCart").val(bankId);
            $("#convertCurrencyModal").modal("show");
        });

        $(".convert-amount").on("blur", function(){
            $("#rate").html('@lang('Tasa')');
            
            if($("#amount").val() > 0 && $("#amount_convert").val() > 0 )
            {
                $("#rate").html('@lang('Tasa'): ' + parseFloat($("#amount_convert").val()) / parseFloat($("#amount").val()));
            }
        })

        $(".select2-basic").select2({
            dropdownParent: $("#convertCurrencyModal")
        });
    });
</script>
@endpush
