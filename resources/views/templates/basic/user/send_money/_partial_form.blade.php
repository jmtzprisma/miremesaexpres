
<div class="mb-3">
    <label class="text--accent sm-text d-block fw-md mb-2" for="recipient_name">@lang('Recipient Name')</label>
    <input class="form-control form--control" id="recipient_name" name="recipient[name]" required type="text" value="{{ old('recipient')['name'] ?? null }}">
</div>
<div class="mb-3 @if(isset($hide_phone)) d-none @endif">
    <label class="text--accent sm-text d-block fw-md mb-2" for="recipient_mobile">@lang('Recipient Mobile No.') (Opcional)</label>
    <div class="input-group">
        <span class="input-group-text recipient-dial-code d-none"></span>
        <input class="form-control form--control" id="recipient_mobile" name="recipient[mobile]" @if(!isset($hide_phone))  @endif  type="number" value="@if(isset($hide_phone)) {{'000000000'}} @else {{ old('recipient')['mobile'] ?? null }} @endif">
    </div>
</div>
<div class="mb-3 @if(isset($hide_email)) d-none @endif">
    <label class="text--accent sm-text d-block fw-md mb-2" for="recipient_email">@lang('Recipient Email') (Opcional)</label>
    <input class="form-control form--control" id="recipient_email" name="recipient[email]" type="email" value="{{ old('recipient')['email'] ?? null }}">
</div>
<div class="mb-3 d-none">
    <label class="text--accent sm-text d-block fw-md mb-2" for="recipient_address">@lang('Recipient Address')</label>
    <input class="form-control form--control-textarea" id="recipient_address" name="recipient[address]" value="{{ old('recipient')['address'] ?? 'address' }}">
</div>

<div class="mb-3">
    <label class="text--accent sm-text d-block fw-md mb-2" for="deliveryMethod">@lang('Delivery Methods')</label>
    <div class="form--select-light">
        <select class="form-select form--select" id="deliveryMethod" name="delivery_method" required>
            <option value="">@lang('Select One')</option>
        </select>
    </div>
</div>
<div class="mb-3 services-div"></div>
<div class="mb-3 mt-4 d-none formData"></div>