<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Models\EmailTemplate;
use App\Models\Setting;

class SettingsController extends Controller
{

    public function __construct()
    {
        // $this->middleware('auth'); // Removed to avoid guard conflicts
    }

    public function social()
    {
        return view("admin.settings.app.social");
    }

    public function globals()
    {
        $settings = DB::table('settings')->pluck('value', 'key');
        return view("admin.settings.app.global", compact('settings'));
    }

    public function notifications()
    {
        $settings = DB::table('settings')
            ->whereIn('key', [
                'mail_mailer', 'mail_host', 'mail_port', 'mail_username',
                'mail_password', 'mail_encryption', 'mail_from_address',
                'mail_from_name', 'mail_queue_mode',
            ])
            ->pluck('value', 'key');
        return view('admin.settings.app.notification', compact('settings'));
    }

    public function notificationsSave(Request $request)
    {
        $keys = [
            'mail_mailer', 'mail_host', 'mail_port', 'mail_username',
            'mail_password', 'mail_encryption', 'mail_from_address',
            'mail_from_name', 'mail_queue_mode',
        ];
        $map = [
            'mail_mailer'       => $request->input('mail_mailer', 'smtp'),
            'mail_host'         => $request->input('mail_host', ''),
            'mail_port'         => $request->input('mail_port', ''),
            'mail_username'     => $request->input('mail_username', ''),
            'mail_password'     => $request->input('mail_password', ''),
            'mail_encryption'   => $request->input('mail_encryption', ''),
            'mail_from_address' => $request->input('mail_from_address', ''),
            'mail_from_name'    => $request->input('mail_from_name', ''),
            'mail_queue_mode'   => $request->boolean('mail_queue_mode') ? '1' : '0',
        ];
        foreach ($map as $key => $value) {
            Setting::set($key, $value, $key === 'mail_queue_mode' ? 'boolean' : 'string');
        }
        return response()->json(['success' => true]);
    }

    public function cod()
    {
        $codEnabled    = (bool) DB::table('settings')->where('key', 'cod_enabled')->value('value');
        $stripeEnabled = (bool) DB::table('settings')->where('key', 'stripe_enabled')->value('value');
        return view('admin.settings.app.cod', compact('codEnabled', 'stripeEnabled'));
    }

    public function codSave(Request $request)
    {
        Setting::set('cod_enabled', $request->boolean('cod_enabled') ? '1' : '0', 'boolean');
        return response()->json(['success' => true]);
    }

    public function applePay()
    {
        return view('admin.settings.app.applepay');
    }

    public function stripe()
    {
        $settings = DB::table('settings')
            ->whereIn('key', [
                'stripe_enabled', 'stripe_key', 'stripe_secret', 'stripe_webhook_secret',
                'stripe_commission_rate', 'stripe_withdraw_enabled', 'cod_enabled',
            ])
            ->pluck('value', 'key');
        return view('admin.settings.app.stripe', compact('settings'));
    }

    public function stripeSave(Request $request)
    {
        Setting::set('stripe_enabled', $request->boolean('stripe_enabled') ? '1' : '0', 'boolean');
        Setting::set('stripe_key', $request->input('stripe_key', ''));
        Setting::set('stripe_secret', $request->input('stripe_secret', ''));
        Setting::set('stripe_webhook_secret', $request->input('stripe_webhook_secret', ''));
        Setting::set('stripe_commission_rate', $request->input('stripe_commission_rate', ''));
        Setting::set('stripe_withdraw_enabled', $request->boolean('stripe_withdraw_enabled') ? '1' : '0', 'boolean');

        return response()->json(['success' => true]);
    }

    public function mobileGlobals()
    {
        return view('admin.settings.mobile.globals');
    }

    public function razorpay()
    {
        return view('admin.settings.app.razorpay');
    }

    public function paytm()
    {
        return view('admin.settings.app.paytm');
    }

    public function payfast()
    {
        return view('admin.settings.app.payfast');
    }

    public function paypal()
    {
        return view('admin.settings.app.paypal');
    }

    public function orangepay()
    {
        return view('admin.settings.app.orangepay');
    }

    public function xendit()
    {
        return view('admin.settings.app.xendit');
    }

    public function midtrans()
    {
        return view('admin.settings.app.midtrans');
    }

    public function adminCommission()
    {
        return view("admin.settings.app.adminCommission");
    }

    public function radiosConfiguration()
    {
        return view("admin.settings.app.radiosConfiguration");
    }

    public function wallet()
    {
        return view('admin.settings.app.wallet');
    }

    public function bookTable()
    {
        return view('admin.settings.app.bookTable');
    }


    public function paystack()
    {
        return view('admin.settings.app.paystack');
    }

    public function flutterwave()
    {
        return view('admin.settings.app.flutterwave');
    }

    public function mercadopago()
    {
        return view('admin.settings.app.mercadopago');
    }

    public function deliveryCharge()
    {
        return view("admin.settings.app.deliveryCharge");
    }

    public function languages()
    {
        return view('admin.settings.languages.index');
    }

    public function languagesedit($id)
    {
        return view('admin.settings.languages.edit')->with('id', $id);
    }

    public function languagescreate()
    {
        return view('admin.settings.languages.create');
    }

    public function specialOffer()
    {
        return view('admin.settings.app.specialDiscountOffer');
    }

    public function menuItems()
    {
        return view('admin.settings.menu_admin.items.index');
        
    }

    public function menuItemsCreate()
    {
        return view('admin.settings.menu_admin.items.create');

    }

    public function menuItemsEdit($id)
    {
        return view('admin.settings.menu_admin.items.edit')->with('id', $id);

    }

    public function story()
    {
        return view('admin.settings.app.story');

    }

    public function footerTemplate()
    {
        return view('footerTemplate.index');
    }

    public function homepageTemplate()
    {
        return view('homepage_Template.index');
    }

    public function emailTemplatesIndex()
    {
        $templates = EmailTemplate::orderBy('type')->get();
        return view('admin.email-templates.index', compact('templates'));
    }

    public function emailTemplatesSave($id = '')
    {
        $template = ($id !== '') ? EmailTemplate::find($id) : null;
        return view('admin.email-templates.save', compact('id', 'template'));
    }

    public function emailTemplatesDelete($id)
    {
        $template = EmailTemplate::findOrFail($id);
        $template->delete();

        return redirect()
            ->route('admin.email-templates.index')
            ->with('success', 'Email template deleted successfully.');
    }

    public function documentVerification()
    {
        return view('admin.settings.app.documentVerificationSetting');
    }
}