<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Lib\FormProcessor;
use App\Lib\GoogleAuthenticator;
use App\Models\Form;
use App\Models\SendMoney;
use App\Models\Transaction;
use Illuminate\Http\Request;

class UserController extends Controller {
    public function home() {
        $pageTitle                      = 'Dashboard';
        $user                           = auth()->user();
        $widget['balance']              = $user->balance;
        $sendMoney                      = SendMoney::filterUser()->with('deposit.gateway', 'recipientCountry', 'countryDeliveryMethod.deliveryMethod');
        $transfers                      = (clone $sendMoney)->latest()->with('deposit')->take(5)->get();
        $widget['send_money_amount']    = (clone $sendMoney)->completed()->sum('base_currency_amount');
        $widget['send_money_pending']   = (clone $sendMoney)->pending()->sum('base_currency_amount');
        $widget['send_money_initiated'] = (clone $sendMoney)->initiated()->sum('base_currency_amount');
        $widget['payment_pending']      = (clone $sendMoney)->paymentPending()->sum('base_currency_amount');
        $widget['payment_rejected']     = (clone $sendMoney)->paymentRejected()->sum('base_currency_amount');

        return view($this->activeTemplate . 'user.dashboard', compact('pageTitle', 'widget', 'transfers'));
    }

    public function show2faForm() {
        $general = gs();
        $ga = new GoogleAuthenticator();
        $user = auth()->user();
        $secret = $ga->createSecret();
        $qrCodeUrl = $ga->getQRCodeGoogleUrl($user->username . '@' . $general->site_name, $secret);
        $pageTitle = '2FA Security';
        return view($this->activeTemplate . 'user.twofactor', compact('pageTitle', 'secret', 'qrCodeUrl'));
    }

    public function create2fa(Request $request) {
        $user = auth()->user();
        $this->validate($request, [
            'key' => 'required',
            'code' => 'required',
        ]);
        $response = verifyG2fa($user, $request->code, $request->key);
        if ($response) {
            $user->tsc = $request->key;
            $user->ts = 1;
            $user->save();
            $notify[] = ['success', 'Google authenticator activated successfully'];
            return back()->withNotify($notify);
        } else {
            $notify[] = ['error', 'Código de verificación incorrecto'];
            return back()->withNotify($notify);
        }
    }

    public function disable2fa(Request $request) {
        $this->validate($request, [
            'code' => 'required',
        ]);

        $user = auth()->user();
        $response = verifyG2fa($user, $request->code);
        if ($response) {
            $user->tsc = null;
            $user->ts = 0;
            $user->save();
            $notify[] = ['success', 'Autenticador de dos factores desactivado exitosamente'];
        } else {
            $notify[] = ['error', 'Código de verificación incorrecto'];
        }
        return back()->withNotify($notify);
    }

    public function transactions() {
        $pageTitle    = 'Transactions';
        $remarks      = Transaction::filterUser()->where('agent_id', 0)->distinct('remark')->orderBy('remark')->get('remark');
        $transactions = Transaction::where('user_id', auth()->id())->searchable(['trx'])->filter(['trx_type', 'remark'])->orderBy('id', 'desc')->paginate(getPaginate());

        return view($this->activeTemplate . 'user.transactions', compact('pageTitle', 'transactions', 'remarks'));
    }


    public function attachmentDownload($fileHash) {
        $filePath = decrypt($fileHash);
        if (!(file_exists($filePath) && is_file($filePath))) {
            $notify[] = ['error', '¡Archivo no encontrado!'];
            return back()->withNotify($notify);
        }
        $extension = pathinfo($filePath, PATHINFO_EXTENSION);
        $general   = gs();
        $title     = slug($general->site_name) . '- attachments.' . $extension;
        $mimetype  = mime_content_type($filePath);
        header('Content-Disposition: attachment; filename="' . $title);
        header("Content-Type: " . $mimetype);
        return readfile($filePath);
    }

    public function userData() {
        $user = auth()->user();
        if ($user->profile_complete == 1) {
            return to_route('user.home');
        }
        $pageTitle = _('Complete Account');
        return view($this->activeTemplate . 'user.user_data', compact('pageTitle', 'user'));
    }

    public function userDataSubmit(Request $request) {
        $user = auth()->user();
        if ($user->profile_complete == 1) {
            return to_route('user.home');
        }
        $request->validate([
            'firstname' => 'required',
            'lastname' => 'required',
            'identification' => 'required'
        ]);
        $user->firstname = $request->firstname;
        $user->lastname = $request->lastname;
        $user->identification = $request->identification;
        $user->address = [
            'country' => @$user->address->country,
            'address' => $request->address,
            'state' => $request->state,
            'zip' => $request->zip,
            'city' => $request->city,
        ];
        $user->profile_complete = 1;
        $user->save();

        $notify[] = ['success', 'Proceso de registro completado exitosamente'];
        return to_route('user.home')->withNotify($notify);
    }
    public function kycForm() {
        $user = auth()->user();
        if ($user->kv == 2) {
            $notify[] = ['error', 'Su KYC está bajo revisión'];
            return to_route('user.kyc.data')->withNotify($notify);
        }
        if ($user->kv == 1) {
            $notify[] = ['error', 'Ya estás verificado por KYC'];
            return to_route('user.home')->withNotify($notify);
        }
        $pageTitle = 'KYC Form';
        $form      = Form::where('act', 'user.kyc')->first();
        return view($this->activeTemplate . 'user.kyc.form', compact('pageTitle', 'form'));
    }

    public function kycData() {
        $user      = auth()->user();
        $pageTitle = 'KYC Data';
        return view($this->activeTemplate . 'user.kyc.info', compact('pageTitle', 'user'));
    }
    public function kycSubmit(Request $request) {
        $form           = Form::where('act', 'user.kyc')->first();
        $formData       = $form->form_data;
        $formProcessor  = new FormProcessor();
        $validationRule = $formProcessor->valueValidation($formData);
        $request->validate($validationRule);
        $userData       = $formProcessor->processFormData($request, $formData);
        $user           = auth()->user();
        $user->kyc_data = $userData;
        $user->kv       = 2;
        $user->save();

        $notify[] = ['success', 'Datos KYC enviados correctamente'];
        return to_route('user.home')->withNotify($notify);
    }

    public function kycTypeKyc()
    {
        $pageTitle = 'Tipo de verificación';
        return view($this->activeTemplate . 'user.kyc.asking', compact('pageTitle'));
    }
}
