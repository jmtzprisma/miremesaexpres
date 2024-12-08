<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Lib\FormProcessor;
use App\Models\Country;
use App\Models\CountryDeliveryMethod;
use App\Models\Service;
use App\Models\DeliveryMethod;
use Illuminate\Http\Request;

class ServiceController extends Controller {
    public function index($id = null) {
        $countries = Country::whereHas('countryDeliveryMethods')->orderBy('name')->get();
        $services  = Service::searchable(['name', 'countryDeliveryMethod.deliveryMethod:name']);
        if (request()->country_id) {
            $services->filter(['countryDeliveryMethod:country_id']);
        }
        $services = $services->with('countryDeliveryMethod.deliveryMethod', 'countryDeliveryMethod.country:id,name')->latest()->paginate(getPaginate());
        $pageTitle  = 'All Services';
        return view('admin.country.service.index', compact('pageTitle', 'services', 'countries'));
    }

    public function add() {
        $deliveryMethods = DeliveryMethod::active()->get();
        $pageTitle       = 'Add New Service';
        $countries       = Country::all();
        return view('admin.country.service.form', compact('pageTitle', 'deliveryMethods', 'countries'));
    }

    public function edit($id) {
        $pageTitle       = 'Update Service';
        $service         = Service::with('countryDeliveryMethod', 'form')->findOrFail($id);
        $deliveryMethods = DeliveryMethod::active()->get();
        $countries       = Country::all();
        return view('admin.country.service.form', compact('pageTitle', 'service', 'deliveryMethods', 'countries'));
    }

    public function addService(Request $request, $id = 0) {
        $formProcessor     = new FormProcessor();
        $this->validation($request, $formProcessor);
        $country           = Country::findOrFail($request->country_id);

        if ($id) {
            $countryService = Service::findOrFail($id);
            $form           = $formProcessor->generate('service_form', true, 'id', $countryService->form_id);
        } else {
            $countryService = new Service();
            $form           = $formProcessor->generate('service_form');
        }

        $countryDeliveryMethod = CountryDeliveryMethod::where('country_id', $country->id)->where('delivery_method_id', $request->delivery_method_id)->first('id');

        if (!$countryDeliveryMethod) {
            $countryDeliveryMethod = new CountryDeliveryMethod();
            $countryDeliveryMethod->country_id = $country->id;
            $countryDeliveryMethod->delivery_method_id = $request->delivery_method_id;
            $countryDeliveryMethod->save();
        }

        $countryService->country_delivery_method_id = $countryDeliveryMethod->id;
        $countryService->name    = $request->name;
        $countryService->form_id = $form->id ?? 0;
        $countryService->save();
        $notify[] = ['success', 'Service added successfully'];
        return back()->withNotify($notify);
    }

    private function validation($request, $formProcessor) {
        $validation = [
            'delivery_method_id' => 'required|exists:delivery_methods,id',
            'country_id' => 'required|integer',
            'name' => 'required|string|max:255'
        ];

        $generatorValidation = $formProcessor->generatorValidation();
        $validation = array_merge($validation, $generatorValidation['rules']);
        $request->validate($validation, $generatorValidation['messages']);
    }

    public function updateServiceStatus($id) {
        return Service::changeStatus($id);
    }
}
