<div class="{{ $class }}">
    <label class="text--accent sm-text d-block fw-md mb-2" for="from">@lang('You Send')</label>
    <div class="input-group">
        <input class="form-control form--control text--right sending-amount" min="0" id="from" name="sending_amount" placeholder="0.00" required step="any" type="number" value="{{ @$sendingAmount }}">
        <span class="sending-parent">
            <select autocomplete="off" class="sending-countries country-picker" name="sending_country" required data-selected="3">
                @foreach ($sendingCountries as $sendingCountry)
                    <option value="{{ $sendingCountry->id }}" {{ $sendingCountry->currency == 'EUR' ? 'selected' : '' }} data-conversion_rates="{{ $sendingCountry->conversionRates }}" data-currency="{{ $sendingCountry->currency }}" data-dial_code="{{ $sendingCountry->dial_code }}" data-id="{{ $sendingCountry->id }}" data-image="{{ getImage(getFilePath('country') . '/' . $sendingCountry->image, getFileSize('country')) }}" data-name="{{ $sendingCountry->name }}" data-rate="{{ $sendingCountry->rate }}">
                        {{ $sendingCountry->currency }}
                    </option>
                @endforeach
            </select>
        </span>
    </div>

    @if ($showLimit)
        <a href="javascript:void(0)" class="showLimit xsm-text text-muted  text-decoration-underline"> <i class="la la-info-circle"></i> @lang('Limit')</a>
        <small class="mb-3 limitMessage d-none text--danger">
            @lang('The amount exceeds the limit')
        </small>
    @endif
</div>

<div class="{{ $class }}">
    <div class="d-flex justify-content-between align-items-center">
        <label class="text--accent sm-text d-block fw-md mb-2" for="you-send">@lang('Recipient Gets')</label>
        <button class="btn p-0 mb-2 reverseCountryBtn" type="button"><i class="la la-exchange-alt"></i></button>
    </div>

    <div class="input-group">
        <input class="form-control form--control text--right recipient-amount" min="0" id="to" name="recipient_amount" placeholder="0.00" required step="any" type="number" value="{{ @$recipientAmount }}">
        <span class="recipient-parent">
            <select class="form-control form--control country-picker recipient-countries" id="recipient-country" name="recipient_country">
                @foreach ($receivingCountries as $receivingCountry)
                    <option value="{{ $receivingCountry->id }}" data-currency="{{ $receivingCountry->currency }}" data-delivery_methods="{{ $receivingCountry->countryDeliveryMethods }}" data-dial_code="{{ $receivingCountry->dial_code }}" data-fixed_charge="{{ $receivingCountry->fixed_charge }}" data-id="{{ $receivingCountry->id }}" data-image="{{ getImage(getFilePath('country') . '/' . $receivingCountry->image, getFileSize('country')) }}" data-name="{{ $receivingCountry->name }}" data-percent_charge="{{ $receivingCountry->percent_charge }}" data-has_agent="{{ $receivingCountry->has_agent }}" data-rate="{{ $receivingCountry->rate }}">
                        {{ $receivingCountry->currency }}
                    </option>
                @endforeach
            </select>
        </span>
    </div>
</div>
