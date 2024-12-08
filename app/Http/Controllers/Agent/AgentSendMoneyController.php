<?php

namespace App\Http\Controllers\Agent;

use App\Constants\Status;
use App\Http\Controllers\Controller;
use App\Lib\FormProcessor;
use App\Lib\ProcessSendMoney;
use App\Models\Country;
use App\Models\CountryDeliveryMethod;
use App\Models\Service;
use App\Models\Form;
use App\Models\SendingPurpose;
use App\Models\SendMoney;
use App\Models\SourceOfFund;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class AgentSendMoneyController extends Controller {

    public function transferHistory() {
        $pageTitle = 'Send Money History';
        $transfers = SendMoney::filterAgent()->with('sendingCountry', 'recipientCountry')->where('status', '!=', Status::SEND_MONEY_INITIATED)->searchable(['mtcn_number'])->latest()->paginate(getPaginate());
        return view('agent.send.transfer_history', compact('pageTitle', 'transfers'));
    }

    public function sendMoney() {
        $pageTitle          = 'Send Money';
        $sendingCountry     = Country::where('country_code', authAgent()->country_code)->with('conversionRates')->first();
        $receivingCountries = Country::receivableCountries()->where('id', '!=', @$sendingCountry->id)->get();
        $sources            = SourceOfFund::active()->get();
        $purposes           = SendingPurpose::active()->get();
        $todaySendMoney     = SendMoney::whereIn('status', [Status::SEND_MONEY_PENDING, Status::SEND_MONEY_COMPLETED])->where('agent_id', authAgent()->id)->whereDate('created_at', now())->sum('base_currency_amount');
        $thisMonthSendMoney = SendMoney::whereIn('status', [Status::SEND_MONEY_PENDING, Status::SEND_MONEY_COMPLETED])->where('agent_id', authAgent()->id)->whereMonth('created_at', now()->month)->sum('base_currency_amount');

        return view('agent.send.send_money', compact('pageTitle', 'sendingCountry', 'receivingCountries', 'sources', 'purposes', 'todaySendMoney', 'thisMonthSendMoney'));
    }

    public function sendMoneyInsert(Request $request) {
        $rules = [
            'sending_country'   => 'required|gt:0',
            'sending_amount'    => 'required|numeric|min:0',
            'recipient_country' => 'required|gt:0',
            'source_of_funds'   => 'required|gt:0',
            'sending_purpose'   => 'required|gt:0',
            'recipient'         => 'required|array|min:3',
            'recipient.name'    => 'required',
            'recipient.mobile'  => 'required',
            'recipient.email'   => 'required|email',
            'recipient.address' => 'required',
            'sender'            => 'required|array|min:3',
            'sender.name'       => 'required',
            'sender.mobile'     => 'required',
            'sender.email'      => 'required|email',
            'sender.address'    => 'required',
            'delivery_method'   => 'required|numeric',
            'service'           => 'nullable|required_unless:delivery_method,0|integer'
        ];

        $messages = [
            'recipient.name.required'    => 'Please enter recipient name',
            'recipient.mobile.required'  => 'Please enter recipient mobile number',
            'recipient.email.required'   => 'Please enter recipient email address',
            'recipient.address.required' => 'Please enter recipient address',
            'sender.name.required'       => 'Please enter sender name',
            'sender.mobile.required'     => 'Please enter sender mobile number',
            'sender.email.required'      => 'Please enter sender email address',
            'sender.address.required'    => 'Please enter sender address',
            'service.required_unless'    => 'Service field is required if delivery method is not an agent',
        ];

        $serviceFormData = null;
        if ($request->service) {
            $service        = Service::findOrFail($request->service);
            $form           = Form::where('act', 'service_form')->findOrFail($service->form_id);
            $formData       = $form->form_data;
            $formProcessor  = new FormProcessor();
            $validationRule = $formProcessor->valueValidation($formData);
            $rules          = array_merge($rules, $validationRule);

            $request->validate($rules, $messages);
            $serviceFormData  = $formProcessor->processFormData($request, $formData);
        } else {
            $request->validate($rules, $messages);
        }

        $sendingCountry = Country::active()->sending()->where('country_code', authAgent()->country_code)->exists();
        if (!$sendingCountry) {
            throw ValidationException::withMessages(['error' => 'Sending money from the selected country is not available now']);
        }

        if ($request->delivery_method) {
            $countryDeliveryMethod = CountryDeliveryMethod::where('country_id', $request->recipient_country)->where('delivery_method_id', $request->delivery_method)->exists();
        } else {
            if (gs()->agent_module) {
                $receivingCountry  = Country::findOrFail($request->recipient_country);
                if ($receivingCountry->has_agent) {
                    $countryDeliveryMethod = true;
                } else {
                    $countryDeliveryMethod = false;
                }
            } else {
                $countryDeliveryMethod = false;
            }
        }

        if (!$countryDeliveryMethod) {
            throw ValidationException::withMessages(['error' => 'Invalid delivery method selected']);
        }

        $agent               = authAgent();

        $payment             = new ProcessSendMoney();
        $payment->user       = $agent;
        $payment->columnName = 'agent_id';

        if ($payment->amountWithCharge > $agent->balance) {
            $notify[] = ['error', 'Insufficient Balance.'];
            return back()->withNotify($notify);
        }

        $sendMoney = $payment->createSendMoney($request, $serviceFormData);
        $payment->createTransaction();
        ProcessSendMoney::updateSendMoney($sendMoney, $agent);

        $notify[] = ['success', 'Send money request sent successfully'];
        return to_route('agent.transfer.history')->withNotify($notify);
    }
}
