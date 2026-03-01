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
                'push_notification_enabled', 'push_fcm_key', 'push_api_key',
                'push_database_url', 'push_storage_bucket', 'push_app_id',
                'push_auth_domain', 'push_project_id', 'push_message_sender_id',
                'push_measurement_id',
            ])
            ->pluck('value', 'key');
        return view('admin.settings.app.notification', compact('settings'));
    }

    public function notificationsSave(Request $request)
    {
        $keys = [
            'push_notification_enabled', 'push_fcm_key', 'push_api_key',
            'push_database_url', 'push_storage_bucket', 'push_app_id',
            'push_auth_domain', 'push_project_id', 'push_message_sender_id',
            'push_measurement_id',
        ];
        $map = [
            'push_notification_enabled' => $request->boolean('isEnabled') ? '1' : '0',
            'push_fcm_key'              => $request->input('fcm_key', ''),
            'push_api_key'              => $request->input('api_key', ''),
            'push_database_url'         => $request->input('database_url', ''),
            'push_storage_bucket'       => $request->input('storage_bucket', ''),
            'push_app_id'               => $request->input('app_id', ''),
            'push_auth_domain'          => $request->input('auth_domain', ''),
            'push_project_id'           => $request->input('project_id', ''),
            'push_message_sender_id'    => $request->input('message_sender_id', ''),
            'push_measurement_id'       => $request->input('measurement_id', ''),
        ];
        foreach ($map as $key => $value) {
            DB::table('settings')->updateOrInsert(['key' => $key], ['value' => $value, 'updated_at' => now()]);
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
        DB::table('settings')->updateOrInsert(
            ['key' => 'cod_enabled'],
            ['value' => $request->boolean('cod_enabled') ? '1' : '0', 'updated_at' => now()]
        );
        return response()->json(['success' => true]);
    }

    public function applePay()
    {
        return view('admin.settings.app.applepay');
    }

    public function stripe()
    {
        $settings = DB::table('settings')
            ->whereIn('key', ['stripe_enabled', 'stripe_key', 'stripe_secret', 'stripe_withdraw_enabled',
                              'cod_enabled'])
            ->pluck('value', 'key');
        return view('admin.settings.app.stripe', compact('settings'));
    }

    public function stripeSave(Request $request)
    {
        $map = [
            'stripe_enabled'           => $request->boolean('stripe_enabled') ? '1' : '0',
            'stripe_key'               => $request->input('stripe_key', ''),
            'stripe_secret'            => $request->input('stripe_secret', ''),
            'stripe_withdraw_enabled'  => $request->boolean('stripe_withdraw_enabled') ? '1' : '0',
        ];
        foreach ($map as $key => $value) {
            DB::table('settings')->updateOrInsert(['key' => $key], ['value' => $value, 'updated_at' => now()]);
        }
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
    public function documentVerification()
    {
        return view('admin.settings.app.documentVerificationSetting');
    }
}