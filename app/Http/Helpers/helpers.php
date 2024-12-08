<?php

use App\Constants\Status;
use App\Lib\GoogleAuthenticator;
use App\Models\Extension;
use App\Models\Frontend;
use App\Models\LogNotify;
use App\Models\GeneralSetting;
use Carbon\Carbon;
use App\Lib\Captcha;
use App\Lib\ClientInfo;
use App\Lib\CurlRequest;
use App\Lib\FileManager;
use App\Models\Transaction;
use App\Notify\Notify;
use Illuminate\Support\Str;

function systemDetails() {
    $system['name'] = 'AndresTeLoCambia';
    $system['version'] = '1.0';
    $system['build_version'] = '1.0.0';
    return $system;
}

function slug($string) {
    return Illuminate\Support\Str::slug($string);
}

function verificationCode($length) {
    if ($length == 0) return 0;
    $min = pow(10, $length - 1);
    $max = (int) ($min - 1) . '9';
    return random_int($min, $max);
}

function getNumber($length = 8) {
    $characters = '1234567890';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}

function activeTemplate($asset = false) {
    $general = gs();
    $template = $general->active_template;
    if ($asset) return 'assets/templates/' . $template . '/';
    return 'templates.' . $template . '.';
}

function activeTemplateName() {
    $general = gs();
    $template = $general->active_template;
    return $template;
}

function loadReCaptcha() {
    return Captcha::reCaptcha();
}

function loadCustomCaptcha($width = '100%', $height = 50, $bgColor = '#003') {
    return Captcha::customCaptcha($width, $height, $bgColor);
}

function verifyCaptcha() {
    return Captcha::verify();
}

function loadExtension($key) {
    $extension = Extension::where('act', $key)->where('status', Status::ENABLE)->first();
    return $extension ? $extension->generateScript() : '';
}

function getTrx($length = 12) {
    $characters = 'ABCDEFGHJKMNOPQRSTUVWXYZ123456789';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}

function getAmount($amount, $length = 2) {
    $amount = round($amount ?? 0, $length);
    return $amount + 0;
}

function showAmount($amount, $decimal = 2, $separate = true, $exceptZeros = false) {
    $separator = '';
    if ($separate) {
        $separator = ',';
    }
    $printAmount = number_format($amount, $decimal, '.', $separator);
    if ($exceptZeros) {
        $exp = explode('.', $printAmount);
        if ($exp[1] * 1 == 0) {
            $printAmount = $exp[0];
        } else {
            $printAmount = rtrim($printAmount, '0');
        }
    }
    return $printAmount;
}


function removeElement($array, $value) {
    return array_diff($array, (is_array($value) ? $value : array($value)));
}

function cryptoQR($wallet) {
    return "https://chart.googleapis.com/chart?chs=300x300&cht=qr&chl=$wallet&choe=UTF-8";
}


function keyToTitle($text) {
    return ucfirst(preg_replace("/[^A-Za-z0-9 ]/", ' ', $text));
}


function titleToKey($text) {
    return strtolower(str_replace(' ', '_', $text));
}


function strLimit($title = null, $length = 10) {
    return Str::limit($title, $length);
}


function getIpInfo() {
    $ipInfo = ClientInfo::ipInfo();
    return $ipInfo;
}


function osBrowser() {
    $osBrowser = ClientInfo::osBrowser();
    return $osBrowser;
}


function getTemplates() {
    return null;
}


function getPageSections($arr = false) {
    $jsonUrl = resource_path('views/') . str_replace('.', '/', activeTemplate()) . 'sections.json';
    $sections = json_decode(file_get_contents($jsonUrl));
    if ($arr) {
        $sections = json_decode(file_get_contents($jsonUrl), true);
        ksort($sections);
    }
    return $sections;
}


function getImage($image, $size = null, $avatar = false) {
    $clean = '';
    if (file_exists($image) && is_file($image)) {
        return asset($image) . $clean;
    }
    if ($size) {
        return route('placeholder.image', $size);
    }
    if(!$avatar)
        return asset('assets/images/default.png');
    else
        return asset('assets/admin/images/avatar.jpg');
}

function notify($user, $templateName, $shortCodes = null, $sendVia = null, $createLog = true) {
    $general = gs();
    $globalShortCodes = [
        'site_name' => $general->site_name,
        'site_currency' => $general->cur_text,
        'currency_symbol' => $general->cur_sym,
    ];

    if (gettype($user) == 'array') {
        $user = (object) $user;
    }

    $shortCodes = array_merge($shortCodes ?? [], $globalShortCodes);

    $notify_md = new LogNotify;
    $notify_md->send_via = json_encode($sendVia);
    $notify_md->template_name = $templateName;
    $notify_md->short_codes = json_encode($shortCodes);
    $notify_md->user_id = isset($user->id) ? $user->id : $user->user_id;
    $notify_md->create_log = $createLog;
    $notify_md->user_column = isset($user->id) ? $user->getForeignKey() : 'user_id';
    $notify_md->save();

    $notify = new Notify($sendVia);
    $notify->templateName = $templateName;
    $notify->shortCodes = $shortCodes;
    $notify->user = $user;
    $notify->createLog = $createLog;
    $notify->userColumn = isset($user->id) ? $user->getForeignKey() : 'user_id';
    $response = $notify->send();

    $notify_md->status = $response;
    $notify_md->save();
}

function getColumnName($user) {
    $array = explode("\\", get_class($user));
    return strtolower(end($array)) . '_id';
}

function getPaginate($paginate = 20) {
    return $paginate;
}

function paginateLinks($data) {
    return $data->appends(request()->all())->links();
}

function menuActive($routeName, $type = null) {
    if ($type == 3) {
        $class = 'side-menu--open';
    } elseif ($type == 2) {
        $class = 'sidebar-submenu__open';
    } else {
        $class = 'active';
    }
    if (is_array($routeName)) {
        foreach ($routeName as $key => $value) {
            if (request()->routeIs($value)) {
                return $class;
            }
        }
    } elseif (request()->routeIs($routeName)) {
        return $class;
    }
}

function fileUploader($file, $location, $size = null, $old = null, $thumb = null) {
    $fileManager = new FileManager($file);
    $fileManager->path = $location;
    $fileManager->size = $size;
    $fileManager->old = $old;
    $fileManager->thumb = $thumb;
    $fileManager->upload();
    return $fileManager->filename;
}

function fileManager() {
    return new FileManager();
}

function getFilePath($key) {
    return fileManager()->$key()->path;
}

function getFileSize($key) {
    return fileManager()->$key()->size;
}

function getFileExt($key) {
    return fileManager()->$key()->extensions;
}

function diffForHumans($date) {
    $lang = session()->get('lang');
    Carbon::setlocale($lang);
    return Carbon::parse($date)->diffForHumans();
}

function showDateTime($date, $format = 'Y-m-d h:i A') {
    $lang = session()->get('lang');
    Carbon::setlocale($lang);
    return Carbon::parse($date)->translatedFormat($format);
}

function getContent($dataKeys, $singleQuery = false, $limit = null, $orderById = false) {
    if ($singleQuery) {
        $content = Frontend::where('data_keys', $dataKeys)->orderBy('id', 'desc')->first();
    } else {
        $article = Frontend::query();
        $article->when($limit != null, function ($q) use ($limit) {
            return $q->limit($limit);
        });
        if ($orderById) {
            $content = $article->where('data_keys', $dataKeys)->orderBy('id')->get();
        } else {
            $content = $article->where('data_keys', $dataKeys)->orderBy('id', 'desc')->get();
        }
    }
    return $content;
}


function gatewayRedirectUrl($type = false) {
    if (auth()->user()) {
        return 'user.send.money.history';
    } else if (authAgent()) {
        return 'agent.deposit.history';
    }
    return 'home';
}

function verifyG2fa($user, $code, $secret = null) {
    $authenticator = new GoogleAuthenticator();
    if (!$secret) {
        $secret = $user->tsc;
    }
    $oneCode = $authenticator->getCode($secret);
    $userCode = $code;
    if ($oneCode == $userCode) {
        $user->tv = 1;
        $user->save();
        return true;
    } else {
        return false;
    }
}


function urlPath($routeName, $routeParam = null) {
    if ($routeParam == null) {
        $url = route($routeName);
    } else {
        $url = route($routeName, $routeParam);
    }
    $basePath = route('home');
    $path = str_replace($basePath, '', $url);
    return $path;
}


function showMobileNumber($number) {
    $length = strlen($number);
    return substr_replace($number, '***', 2, $length - 4);
}

function showEmailAddress($email) {
    $endPosition = strpos($email, '@') - 1;
    return substr_replace($email, '***', 1, $endPosition);
}


function getRealIP() {
    $ip = $_SERVER["REMOTE_ADDR"];
    //Deep detect ip
    if (filter_var(@$_SERVER['HTTP_FORWARDED'], FILTER_VALIDATE_IP)) {
        $ip = $_SERVER['HTTP_FORWARDED'];
    }
    if (filter_var(@$_SERVER['HTTP_FORWARDED_FOR'], FILTER_VALIDATE_IP)) {
        $ip = $_SERVER['HTTP_FORWARDED_FOR'];
    }
    if (filter_var(@$_SERVER['HTTP_X_FORWARDED_FOR'], FILTER_VALIDATE_IP)) {
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
    }
    if (filter_var(@$_SERVER['HTTP_CLIENT_IP'], FILTER_VALIDATE_IP)) {
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    }
    if (filter_var(@$_SERVER['HTTP_X_REAL_IP'], FILTER_VALIDATE_IP)) {
        $ip = $_SERVER['HTTP_X_REAL_IP'];
    }
    if (filter_var(@$_SERVER['HTTP_CF_CONNECTING_IP'], FILTER_VALIDATE_IP)) {
        $ip = $_SERVER['HTTP_CF_CONNECTING_IP'];
    }
    if ($ip == '::1') {
        $ip = '127.0.0.1';
    }

    return $ip;
}


function appendQuery($key, $value) {
    return request()->fullUrlWithQuery([$key => $value]);
}


function dateSort($a, $b) {
    return strtotime($a) - strtotime($b);
}

function dateSorting($arr) {
    usort($arr, "dateSort");
    return $arr;
}

function getCountryList() {
    return json_decode(file_get_contents(resource_path('views/partials/country.json')));
}

function authAgent() {
    return auth()->guard('agent')->user();
}

function transactionDuration() {
    $duration = request()->duration;
    if ($duration == 'last-24-hours') {
        $day = 1;
    } elseif ($duration == 'last-week') {
        $day = 7;
    } elseif ($duration == 'last-15-days') {
        $day = 15;
    } elseif ($duration == 'last-month') {
        $day = 30;
    } elseif ($duration == 'last-year') {
        $day = 365;
    } else {
        $day = null;
    }
    return $day;
}
function getInsightDuration($duration) {
    if ($duration == 'today') {
        $info['day']      = 1;
        $info['duration'] = 'Today';
    } elseif ($duration == 'last-week') {
        $info['day']      = 7;
        $info['duration'] = 'Last week';
    } elseif ($duration == 'last-15-days') {
        $info['day']      = 15;
        $info['duration'] = 'Last 15 days';
    } elseif ($duration == 'last-month') {
        $info['day']      = 30;
        $info['duration'] = 'Last month';
    } elseif ($duration == 'last-year') {
        $info['day']      = 365;
        $info['duration'] = 'Last year';
    } else {
        abort(404);
    }
    return $info;
}
function currencyFormatter($number, $precision = 8) {
    $number = round($number, $precision);
    $number = rtrim(number_format($number, $precision), 0);
    $position = strpos($number, '.') + 1;
    if ($position == strlen($number)) {
        $number .= '00';
    }
    return  $number;
}

function gs() {
    $general = Cache::get('GeneralSetting');
    if (!$general) {
        $general = GeneralSetting::first();
        Cache::put('GeneralSetting', $general);
    }
    return $general;
}

function userGuard() {
    if (auth()->check()) {
        $guard = 'user';
        $user  = auth()->user();
    } elseif (auth()->guard('agent')->check()) {
        $guard = 'agent';
        $user  = auth()->guard('agent')->user();
    }

    return [
        'user'  => @$user,
        'guard' => @$guard
    ];
}

function plural($string) {
    return Str::plural($string);
}

function giveReferralCommission($user, $sendMoney) {
    $general = gs();
    $referrer = @$user->referrer;

    if ($referrer && $general->commission_count > $user->total_bonus_given) {
        $user->total_bonus_given += 1;
        $user->save();

        $totalBonus = $sendMoney->base_currency_amount * $general->referral_commission / 100;
        $referrer->balance += $totalBonus;
        $referrer->save();

        $transaction                = new Transaction();
        $transaction->user_id       = $referrer->id;
        $transaction->amount        = $totalBonus;
        $transaction->post_balance  = $referrer->balance;
        $transaction->charge        = 0;
        $transaction->trx_type      = '+';
        $transaction->details       = 'Referral commission from ' . $user->fullname;
        $transaction->remark        = 'referral_commission';
        $transaction->trx           = getTrx();
        $transaction->save();

        notify($referrer, 'REFERRAL_COMMISSION', [
            'user'         => $user->fullname,
            'amount'       => showAmount($totalBonus),
            'trx'          => $transaction->trx,
            'post_balance' => showAmount($transaction->post_balance)
        ]);
    }
}
