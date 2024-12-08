<?php

namespace App\Http\Controllers\Admin;

use App\Constants\Status;
use App\Http\Controllers\Controller;
use App\Models\CurrencyConversionRate;
use App\Models\Country;
use App\Models\CountryDeliveryMethod;
use App\Models\DeliveryCharge;
use App\Rules\FileTypeValidate;
use Illuminate\Http\Request;

class CountryController extends Controller {
    public function index() {
        $pageTitle   = 'All Countries';
        $countryList = getCountryList();
        $countries   = Country::searchable(['name', 'currency'])->orderBy('id', 'desc')->paginate(getPaginate());
        return view('admin.country.index', compact('pageTitle', 'countries', 'countryList'));
    }

    public function store(Request $request) {
        $this->validation($request);
        $country  = new Country();
        $this->saveCountry($request, $country);
        $notify[] = ['success', 'Country added successfully'];
        return back()->withNotify($notify);
    }

    public function update(Request $request, $id) {
        $this->validation($request, $id);
        $country  = Country::findOrFail($id);
        $this->saveCountry($request, $country);
        $notify[] = ['success', 'Country updated successfully'];
        return back()->withNotify($notify);
    }

    public function saveCountry(Request $request, $country) {
        $countryList             = getCountryList();
        $countryCode             = $request->country_code;
        $country->name           = @$countryList->$countryCode->country;
        $country->country_code   = $countryCode;
        $country->dial_code      = @$countryList->$countryCode->dial_code;
        $country->currency       = $request->currency;
        $country->rate           = $request->rate;
        $country->is_sending     = $request->is_sending ? Status::YES : Status::NO;
        $country->is_receiving   = $request->is_receiving ? Status::YES : Status::NO;
        $country->has_agent      = $request->has_agent ? Status::YES : Status::NO;

        if ($request->hasFile('image')) {
            try {
                $country->image = fileUploader($request->image, getFilePath('country'), getFileSize('country'), @$country->image);
            } catch (\Exception $exp) {
                $notify[] = ['error', 'Image could not be uploaded.'];
                return back()->withNotify($notify);
            }
        }

        $country->save();
    }

    protected function validation($request, $id = 0) {
        $validationRules = [
            'country_code' => 'required|unique:countries,country_code,' . $id,
            'currency'     => 'required',
            'rate'         => 'required|numeric|gt:0',
            'is_sending'   => 'nullable|in:on',
            'is_receiving' => 'nullable|in:on',
            'has_agent'    => 'nullable|in:on'
        ];

        if ($id) {
            $imageValidation = 'nullable';
        } else {
            $imageValidation = 'required';
        }

        $validationRules['image'] = [$imageValidation, 'image', new FileTypeValidate(['jpg', 'jpeg', 'png'])];

        $request->validate($validationRules);
    }

    public function updateStatus($id) {
        return Country::changeStatus($id);
    }

    //currency conversion
    public function currencyConversionRate($id) {
        $country    = Country::with('conversionRates')->findOrFail($id);
        $pageTitle  = "Conversion Rates from $country->currency";
        $countries  = Country::where('id', '!=', $id)->where('status', true)->orderBy('name')->get();
        $rates      = $country->conversionRates;
        return view('admin.country.conversion_rates', compact('countries', 'pageTitle', 'country', 'rates'));
    }

    public function saveCurrencyConversionRates(Request $request, $id) {
        $countryIds = Country::active()->pluck('id')->toArray();
        $request->validate([
            'data' => 'required|array|min:1',
            'data.*.from_country' => 'in:' . implode(',', $countryIds),
            'data.*.to_country' => 'in:' . implode(',', $countryIds),
            'data.*.rate' => 'nullable|numeric|min:0'
        ], [
            'data.*.from_country.in' => 'Invalid from country',
            'data.*.to_country.in' => 'Invalid To country',
            'data.*.rate.numeric' => 'Rate should be numeric value',
            'data.*.rate.min' => 'Rate should be greater than or equal to 0',
        ]);

        $rates = $request->data;

        foreach ($rates as $key => $data) {
            if ($data['rate'] * 1 == 0) {
                unset($rates[$key]);
            }
        }

        $rates = array_values($rates);

        CurrencyConversionRate::where('from_country', $id)->delete();
        CurrencyConversionRate::insert($rates);
        $notify[] = ['success', 'Currency conversion rates updated successfully'];
        return back()->withNotify($notify);
    }

    public function saveCurrencyConversionUsdt(Request $request) {
        $general = gs();
        $general->additional_info = json_encode($request->conversion);
        $general->save();

        $notify[] = ['success', 'Currency conversion usdt updated successfully'];
        return back()->withNotify($notify);
    }
    
    public function setCharges($id) {
        $country = Country::with('countryDeliveryMethods.deliveryMethod', 'countryDeliveryMethods.charge')->findOrFail($id);
        $pageTitle = trans('Set Charges for the Delivery Methods in') . ' ' . $country->name;
        return view('admin.country.set_charges', compact('country', 'pageTitle'));
    }

    public function saveCharges(Request $request, $id) {

        $country = Country::findOrFail($id);

        $request->validate([
            'delivery_method_id' => 'required|exists:delivery_methods,id',
            'fixed_charge'       => 'required|numeric|gte:0',
            'percent_charge'     => 'required|numeric|gte:0'
        ]);

        $countryDeliveryMethod  = CountryDeliveryMethod::where('country_id', $country->id)->where('delivery_method_id', $request->delivery_method_id)->first('id');
        $charge                 = DeliveryCharge::where('country_delivery_method_id', $countryDeliveryMethod->id)->first() ?? new DeliveryCharge();

        $charge->country_delivery_method_id   = $countryDeliveryMethod->id;
        $charge->fixed_charge   = $request->fixed_charge;
        $charge->percent_charge = $request->percent_charge;
        $charge->save();
        $notification = 'Delivery charge updated successfully';
        $notify[] = ['success', $notification];
        return back()->withNotify($notify);
    }

    public function updateRate(Request $request)
    {
        $country = Country::where('currency', 'VEF')->first();
        $country->rate = $request->rate;
        $country->save();

        return true;
    }

}
