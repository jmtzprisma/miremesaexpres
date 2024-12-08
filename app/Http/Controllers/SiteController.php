<?php

namespace App\Http\Controllers;

use App\Constants\Status;
use App\Models\AdminNotification;
use App\Models\Country;
use App\Models\CountryDeliveryMethod;
use App\Models\DeliveryMethod;
use App\Models\Service;
use App\Models\Frontend;
use App\Models\Language;
use App\Models\Page;
use App\Models\SupportMessage;
use App\Models\SupportTicket;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;

class SiteController extends Controller {
    public function index() {
        if (request()->reference) {
            session()->put('reference', request()->reference);
        }

        $pageTitle          = 'Home';
        $sections           = Page::where('tempname', $this->activeTemplate)->where('slug', '/')->first();
        $hideBreadcrumb     = true;
        $sendingCountries   = Country::active()->sending()->with('conversionRates')->get();
        $receivingCountries = Country::receivableCountries()->get();
        $recipientCountryId = @$receivingCountries->where('currency', 'VEF')->first()->id;
        return view($this->activeTemplate . 'home', compact('pageTitle', 'sections', 'hideBreadcrumb', 'sendingCountries', 'receivingCountries', 'recipientCountryId'));
    }

    public function pages($slug) {
        $page = Page::where('tempname', $this->activeTemplate)->where('slug', $slug)->firstOrFail();
        $pageTitle = $page->name;
        $sections = $page->secs;
        return view($this->activeTemplate . 'pages', compact('pageTitle', 'sections'));
    }


    public function contact() {
        $pageTitle = "Contact Us";
        $sections  = Page::where('tempname', $this->activeTemplate)->where('slug', 'contact')->first();
        return view($this->activeTemplate . 'contact', compact('pageTitle', 'sections'));
    }

    public function contactSubmit(Request $request) {
        $user = auth()->user();
        $validation = $user ? 'nullable' : 'required';

        $this->validate($request, [
            'name' => "$validation|string",
            'email' => "$validation|string",
            'subject' => 'required|string|max:255',
            'message' => 'required',
        ]);

        if (!verifyCaptcha()) {
            $notify[] = ['error', 'Captcha no válido proporcionado'];
            return back()->withNotify($notify);
        }

        $request->session()->regenerateToken();

        $random = getNumber();

        $ticket = new SupportTicket();
        $ticket->user_id = $user->id ?? 0;
        $ticket->name = $user->name ?? $request->name;
        $ticket->email = $user->email ?? $request->email;
        $ticket->priority = Status::PRIORITY_MEDIUM;


        $ticket->ticket = $random;
        $ticket->subject = $request->subject;
        $ticket->last_reply = Carbon::now();
        $ticket->status = Status::TICKET_OPEN;
        $ticket->save();

        $adminNotification = new AdminNotification();
        $adminNotification->user_id = $user ? $user->id : 0;
        $adminNotification->title = trans('A new support ticket has opened');
        $adminNotification->click_url = urlPath('admin.ticket.view', $ticket->id);
        $adminNotification->save();

        $message = new SupportMessage();
        $message->support_ticket_id = $ticket->id;
        $message->message = $request->message;
        $message->save();

        $notify[] = ['success', 'Ticket created successfully!'];

        return to_route('ticket.view', [$ticket->ticket])->withNotify($notify);
    }

    public function policyPages($slug, $id) {
        $policy = Frontend::where('id', $id)->where('data_keys', 'policy_pages.element')->firstOrFail();
        $pageTitle = $policy->data_values->title;
        return view($this->activeTemplate . 'policy', compact('policy', 'pageTitle'));
    }

    public function changeLanguage($lang = null) {
        $language = Language::where('code', $lang)->first();
        if (!$language) $lang = 'en';
        session()->put('lang', $lang);
        return back();
    }
    public function blogs() {
        $count = Page::where('tempname', $this->activeTemplate)->where('slug', 'blog')->count();
        $pageTitle = 'Blog';
        $sections  = Page::where('tempname', $this->activeTemplate)->where('slug', 'blog')->first();
        $blogs     = Frontend::where('data_keys', 'blog.element')->latest()->paginate(getPaginate(9));
        return view($this->activeTemplate . 'blog', compact('pageTitle', 'sections', 'blogs'));
    }
    public function blogDetails($slug, $id) {
        $blog = Frontend::where('id', $id)->where('data_keys', 'blog.element')->firstOrFail();
        $pageTitle = 'Blog Details';
        $recents = Frontend::latest()->where('data_keys', 'blog.element')->whereNotIn('id', [$id])->take(7)->get();
        return view($this->activeTemplate . 'blog_details', compact('blog', 'pageTitle', 'recents'));
    }


    public function cookieAccept() {
        $general = gs();
        Cookie::queue('gdpr_cookie', $general->site_name, 43200);
    }

    public function cookiePolicy() {
        $pageTitle = 'Cookie Policy';
        $cookie = Frontend::where('data_keys', 'cookie.data')->first();
        return view($this->activeTemplate . 'cookie', compact('pageTitle', 'cookie'));
    }

    public function placeholderImage($size = null) {
        $imgWidth = explode('x', $size)[0];
        $imgHeight = explode('x', $size)[1];
        $text = $imgWidth . '×' . $imgHeight;
        $fontFile = realpath('assets/font/RobotoMono-Regular.ttf');
        $fontSize = round(($imgWidth - 50) / 8);
        if ($fontSize <= 9) {
            $fontSize = 9;
        }
        if ($imgHeight < 100 && $fontSize > 30) {
            $fontSize = 30;
        }
        $image     = imagecreatetruecolor($imgWidth, $imgHeight);
        $colorFill = imagecolorallocate($image, 100, 100, 100);
        $bgFill    = imagecolorallocate($image, 175, 175, 175);
        imagefill($image, 0, 0, $bgFill);
        $textBox = imagettfbbox($fontSize, 0, $fontFile, $text);
        $textWidth  = abs($textBox[4] - $textBox[0]);
        $textHeight = abs($textBox[5] - $textBox[1]);
        $textX      = ($imgWidth - $textWidth) / 2;
        $textY      = ($imgHeight + $textHeight) / 2;
        header('Content-Type: image/jpeg');
        imagettftext($image, $fontSize, 0, $textX, $textY, $colorFill, $fontFile, $text);
        imagejpeg($image);
        imagedestroy($image);
    }

    public function maintenance() {
        $pageTitle = 'Maintenance Mode';
        $general = gs();
        if ($general->maintenance_mode == Status::DISABLE) {
            return to_route('home');
        }
        $maintenance = Frontend::where('data_keys', 'maintenance.data')->first();
        $title = trans('EL SITIO ESTÁ EN MANTENIMIENTO');
        return view($this->activeTemplate . 'maintenance', compact('pageTitle', 'maintenance', 'title'));
    }

    public function currencyCalculator(Request $request) {
        $request->validate([
            'sending_amount'    => 'required|numeric|gt:0',
            'sending_country'   => 'required|integer',
            'recipient_country' => 'required|integer',
            'delivery_method'   => 'nullable|integer'
        ]);

        Country::active()->sending()->findOrFail($request->sending_country);
        Country::active()->receiving()->findOrFail($request->recipient_country);
        if ($request->delivery_method) {
            DeliveryMethod::active()->findOrFail($request->delivery_method);
        }

        $send_money = [
            'sending_amount'    => $request->sending_amount,
            'sending_country'   => $request->sending_country,
            'recipient_country' => $request->recipient_country,
            'delivery_method'   => $request->delivery_method
        ];

        session()->put('send_money', $send_money);
        return to_route('user.send.money.now');
    }

    public function getServices(Request $request) {

        $services = [];
        if ($request->country_id && $request->delivery_method_id) {
            $countryDeliveryMethod = CountryDeliveryMethod::where('country_id', $request->country_id)->where('delivery_method_id', $request->delivery_method_id)->first();

            if ($countryDeliveryMethod) {
                $services = Service::where('country_delivery_method_id', $countryDeliveryMethod->id)->orderBy('name')->get();
            }
        }

        return response()->json([
            'status' => true,
            'data' => [
                'services' => $services
            ]
        ]);
    }

    public function serviceForm(Request $request) {
        $service = Service::with('form')->find($request->service_id);
        if ($service) {
            $formData = $service->form->form_data;
            $html = view('components.viser-form', compact('formData'))->render();
            return response()->json([
                'html'    => $html,
                'success' => true
            ]);
        }

        return null;
    }
}
