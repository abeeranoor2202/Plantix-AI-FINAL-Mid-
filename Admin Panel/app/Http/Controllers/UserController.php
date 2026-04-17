<?php

namespace App\Http\Controllers;

use App\Mail\DynamicEmail;
use App\Models\User;
use App\Models\Role;
use App\Models\Vendor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use App\Services\Security\PermissionService;
use Illuminate\Support\Facades\Validator;
use PaypalPayoutsSDK\Core\PayPalHttpClient;
use PaypalPayoutsSDK\Core\SandboxEnvironment;
use PaypalPayoutsSDK\Core\ProductionEnvironment;
use PaypalPayoutsSDK\Payouts\PayoutsPostRequest;
use Razorpay\Api\Api;

class UserController extends Controller
{

    public function __construct()
    {
        // $this->middleware('auth'); // Removed to avoid guard conflicts
    }


    public function index()
    {
        $users = User::where('role', 'user')
                     ->orderByDesc('created_at')
                     ->get();
        return view("admin.settings.users.index", compact('users'));
    }

    public function updateUserProfile(Request $request, $id)
    {
        $actor = $this->currentActor();
        if ($actor->id !== (int) $id) {
            $this->ensureAnyPermission(['users.edit', 'admin.users.edit']);
        }

        $request->validate([
            'first_name'    => 'nullable|string|max:255',
            'last_name'     => 'nullable|string|max:255',
            'phone_number'  => 'nullable|string|max:50',
            'is_active'     => 'nullable|boolean',
            'profile_picture'=> 'nullable|image|max:2048',

            'address_label' => 'nullable|string|max:60',
            'address_line1' => 'nullable|string|max:255|required_with:address_line2,city,state,zip,country,address_label',
            'address_line2' => 'nullable|string|max:255',
            'city'          => 'nullable|string|max:100|required_with:address_line1',
            'state'         => 'nullable|string|max:100',
            'zip'           => 'nullable|string|max:20',
            'country'       => 'nullable|string|max:60|required_with:address_line1',
        ]);

        $user = User::findOrFail($id);

        $firstName = trim((string) $request->input('first_name', ''));
        $lastName  = trim((string) $request->input('last_name', ''));
        if ($firstName !== '' || $lastName !== '') {
            $user->name = trim($firstName . ' ' . $lastName);
        }

        if ($request->filled('phone_number')) {
            $user->phone = $request->input('phone_number');
        }

        if ($request->has('is_active')) {
            $user->active = $request->boolean('is_active');
        }

        if ($request->hasFile('profile_picture')) {
            $path = $request->file('profile_picture')->store('profiles', 'public');
            $user->profile_photo = $path;
        }

        $user->save();

        $hasAddressPayload = $request->filled('address_line1')
            || $request->filled('address_line2')
            || $request->filled('city')
            || $request->filled('state')
            || $request->filled('zip')
            || $request->filled('country')
            || $request->filled('address_label');

        if ($hasAddressPayload) {
            $defaultAddress = $user->addresses()->where('is_default', true)->first();

            if ($defaultAddress || $request->filled('address_line1')) {
                $addressPayload = [
                    'label'         => $request->input('address_label', $defaultAddress?->label ?? 'Home'),
                    'address_line1' => $request->input('address_line1', $defaultAddress?->address_line1),
                    'address_line2' => $request->input('address_line2', $defaultAddress?->address_line2),
                    'city'          => $request->input('city', $defaultAddress?->city),
                    'state'         => $request->input('state', $defaultAddress?->state),
                    'zip'           => $request->input('zip', $defaultAddress?->zip),
                    'country'       => $request->input('country', $defaultAddress?->country ?? 'Pakistan'),
                    'is_default'    => true,
                ];

                if ($defaultAddress) {
                    $defaultAddress->update($addressPayload);
                } else {
                    $user->addresses()->create($addressPayload);
                }
            }
        }

        return response()->json(['success' => true]);
    }

    public function sendPasswordReset(Request $request, $id)
    {
        $this->ensureAnyPermission(['users.edit', 'admin.users.edit']);

        $user = User::findOrFail($id);
        $status = Password::broker()->sendResetLink(['email' => $user->email]);
        if ($status === Password::RESET_LINK_SENT) {
            return response()->json(['success' => true]);
        }
        return response()->json(['success' => false, 'message' => __($status)], 422);
    }

    public function edit($id)
    {
        $user           = User::with('addresses')->find($id);
        $totalOrders    = \App\Models\Order::where('user_id', $id)->count();
        [$currencySymbol, $currencyAtRight, $decimalDigits, $placeholderImage] = $this->getCurrencyData();
        return view('admin.settings.users.edit', compact('id', 'user', 'totalOrders', 'currencySymbol', 'currencyAtRight', 'decimalDigits', 'placeholderImage'));
    }

    public function adminUsers()
    {
        $users = User::join('role', 'role.id', '=', 'users.role_id')
            ->select('users.*', 'role.role_name as roleName')->where('users.id', '!=', 1)->get();
        return view('admin.users.index', compact(['users']));
    }

    public function createAdminUsers()
    {
        $roles = Role::all();
        return view('admin.users.create', compact(['roles']));
    }
    public function storeAdminUsers(Request $request)
    {
        $this->ensureAnyPermission(['admin.users.store', 'admin.users.create', 'users.create']);

        $validator = Validator::make($request->all(), [
            'name' => 'required|max:255',
            'email' => 'required|email',
            'password' => 'required|min:8',
            'confirm_password' => 'required|same:password',
        ]);

        if ($validator->fails()) {
            $errorMessage = $validator->errors()->first(); // Get the first error message
            return redirect()->back()->with(['message' => $errorMessage])->withInput();
        }

        User::create([
            'name' => $request->input('name'),
            'email' => $request->input('email'),
            'password' => Hash::make($request->input('password')),
            'role_id' => $request->input('role'),
        ]);

        return redirect('admin-users');
    }

    public function storeWebUser(Request $request)
    {
        $this->ensureAnyPermission(['users.create', 'admin.users.create', 'admin.users.store']);

        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
            'phone_number' => 'required|string|max:50',
            'is_active' => 'boolean',
            'profile_picture' => 'nullable|image|max:2048',

            'address_label' => 'nullable|string|max:60',
            'address_line1' => 'nullable|string|max:255|required_with:address_line2,city,state,zip,country,address_label',
            'address_line2' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:100|required_with:address_line1',
            'state' => 'nullable|string|max:100',
            'zip' => 'nullable|string|max:20',
            'country' => 'nullable|string|max:60|required_with:address_line1',
        ]);

        $user = new User();
        $user->name = trim($request->input('first_name') . ' ' . $request->input('last_name'));
        $user->email = $request->input('email');
        $user->password = Hash::make($request->input('password'));
        $user->phone = $request->input('phone_number');
        $user->active = $request->boolean('is_active', true);
        $user->role = 'user';

        if ($request->hasFile('profile_picture')) {
            $path = $request->file('profile_picture')->store('users', 'public');
            $user->profile_photo = asset('storage/' . $path);
        }

        $user->save();

        if ($request->filled('address_line1')) {
            $user->addresses()->create([
                'label'         => $request->input('address_label', 'Home'),
                'address_line1' => $request->input('address_line1'),
                'address_line2' => $request->input('address_line2'),
                'city'          => $request->input('city'),
                'state'         => $request->input('state'),
                'zip'           => $request->input('zip'),
                'country'       => $request->input('country', 'Pakistan'),
                'is_default'    => true,
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'User created successfully',
            'data' => $user->load('addresses'),
        ]);
    }
    public function editAdminUsers($id)
    {
        $user = User::join('role', 'role.id', '=', 'users.role_id')->select('users.*', 'role.role_name as roleName')->find($id);
        $roles = Role::all();
        return view('admin.users.edit', compact(['user', 'roles']));
    }
    public function updateAdminUsers(Request $request, $id)
    {
        $this->ensureAnyPermission(['admin.users.update', 'admin.users.edit', 'users.edit']);

        $name = $request->input('name');
        $password = $request->input('password');
        $old_password = $request->input('old_password');
        $email = $request->input('email');
        $role = ($id == 1) ? 1 : $request->input('role');
        if ($password == '') {
            $validator = Validator::make($request->all(), [
                'name' => 'required|max:255',
                'email' => 'required|email'
            ]);
        } else {
            $user = User::find($id);
            if (password_verify($old_password, $user->password)) {
                $validator = Validator::make($request->all(), [
                    'name' => 'required|max:255',
                    'password' => 'required|min:8',
                    'confirm_password' => 'required|same:password',
                    'email' => 'required|email'
                ]);
            } else {
                return Redirect()->back()->with(['message' => "Please enter correct old password"]);
            }
        }

        if ($validator->fails()) {
            $error = $validator->errors()->first();
            return Redirect()->back()->with(['message' => $error]);
        }

        $user = User::find($id);

        if ($user) {

            $user->name = $name;
            $user->email = $email;
            if ($password != '') {
                $user->password = Hash::make($password);
            }
            $user->role_id = $role;
            $user->save();
        }

        return redirect('admin-users');
    }
    public function deleteAdminUsers($id)
    {
        $this->ensureAnyPermission(['users.delete', 'admin.users.delete']);

        $id = json_decode($id);

        if (is_array($id)) {

            for ($i = 0; $i < count($id); $i++) {
                $users = User::find($id[$i]);
                $users->delete();
            }
        } else {
            $user = User::find($id);
            $user->delete();
        }

        return redirect()->back();
    }

    private function currentActor(): User
    {
        $user = auth('admin')->user() ?: auth()->user();
        abort_if(! $user instanceof User, 401, 'Unauthenticated.');

        return $user;
    }

    private function ensureAnyPermission(array $permissions): void
    {
        $actor = $this->currentActor();

        /** @var PermissionService $permissionService */
        $permissionService = app(PermissionService::class);
        abort_if(! $permissionService->checkAnyPermission($actor, $permissions), 403, 'Forbidden.');
    }

    // ── Vendor Management ─────────────────────────────────────────────────────

    public function vendors(Request $request)
    {
        $query = Vendor::with('author', 'category');

        if ($request->filled('search')) {
            $query->where('title', 'like', '%' . $request->search . '%')
                  ->orWhere('phone', 'like', '%' . $request->search . '%');
        }

        if ($request->filled('approval')) {
            if ($request->approval === 'approved') {
                $query->where('is_approved', true);
            } elseif ($request->approval === 'pending') {
                $query->where('is_approved', false);
            }
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active' ? true : false);
        }

        $vendors = $query->latest()->paginate(20);

        return view('admin.vendors.index', compact('vendors'));
    }

    public function vendorView(int $id)
    {
        $vendor = Vendor::with('author', 'category', 'products', 'orders', 'coupons')->findOrFail($id);
        return view('admin.vendors.view', compact('vendor'));
    }

    public function vendorEdit(int $id)
    {
        $vendor = Vendor::with('author', 'category')->findOrFail($id);
        return view('admin.vendors.edit', compact('vendor'));
    }

    public function vendorCreate()
    {
        return view('admin.vendors.create');
    }

    public function vendorStore(Request $request)
    {
        $validated = $request->validate([
            'name'       => ['required', 'string', 'max:255'],
            'email'      => ['required', 'email', 'max:255', 'unique:users,email'],
            'phone'      => ['required', 'string', 'regex:/^(\\+92|0)?3[0-9]{2}[0-9]{7}$/', 'max:30', 'unique:users,phone'],
            'store_name' => ['required', 'string', 'max:255', 'unique:vendors,title'],
            'password'   => ['required', 'string', 'min:8', 'confirmed'],
            'address_line1' => ['nullable', 'string', 'max:255'],
            'address_line2' => ['nullable', 'string', 'max:255'],
            'city'          => ['nullable', 'string', 'max:100'],
            'state'         => ['nullable', 'string', 'max:100'],
            'zip'           => ['nullable', 'string', 'max:20'],
            'country'       => ['nullable', 'string', 'max:100'],
            'image'         => ['nullable', 'image', 'max:2048'],
            'is_active'     => ['nullable', 'boolean'],
        ], [
            'phone.regex'      => 'Please enter a valid Pakistani phone number (e.g., 03001234567).',
            'store_name.unique'=> 'This store name is already registered.',
        ]);

        $isActive = $request->boolean('is_active');

        $addressParts = array_filter([
            $validated['address_line1'] ?? null,
            $validated['address_line2'] ?? null,
            $validated['city'] ?? null,
            $validated['state'] ?? null,
            $validated['zip'] ?? null,
            $validated['country'] ?? null,
        ], fn ($part) => filled($part));

        $vendorAddress = ! empty($addressParts) ? implode(', ', $addressParts) : null;

        $vendorImage = null;
        if ($request->hasFile('image')) {
            $vendorImage = $request->file('image')->store('vendors', 'public');
        }

        DB::transaction(function () use ($validated, $isActive, $vendorAddress, $vendorImage) {
            $user = User::create([
                'name'     => $validated['name'],
                'email'    => $validated['email'],
                'phone'    => $validated['phone'],
                'password' => Hash::make($validated['password']),
                'role'     => 'vendor',
                'active'   => $isActive,
            ]);

            Vendor::create([
                'author_id'   => $user->id,
                'title'       => $validated['store_name'],
                'address'     => $vendorAddress,
                'image'       => $vendorImage,
                'is_active'   => $isActive,
                'is_approved' => false,
            ]);
        });

        return redirect()->route('admin.vendors')->with('success', 'Vendor created successfully. Awaiting approval.');
    }

    public function vendorUpdate(Request $request, int $id)
    {
        $vendor = Vendor::with('author')->findOrFail($id);

        $validated = $request->validate([
            'title'           => ['required', 'string', 'max:255', 'unique:vendors,title,' . $vendor->id],
            'owner_name'      => ['required', 'string', 'max:255'],
            'owner_email'     => ['required', 'email', 'max:255', 'unique:users,email,' . $vendor->author_id],
            'owner_phone'     => ['nullable', 'string', 'max:30'],
            'description'     => ['nullable', 'string', 'max:2000'],
            'address'         => ['nullable', 'string', 'max:500'],
            'phone'           => ['nullable', 'string', 'max:30'],
            'commission_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'delivery_fee'    => ['nullable', 'numeric', 'min:0'],
            'open_time'       => ['nullable', 'date_format:H:i'],
            'close_time'      => ['nullable', 'date_format:H:i'],
            'status'          => ['required', 'in:pending,approved,suspended'],
        ]);

        [$isApproved, $isActive] = match ($validated['status']) {
            'approved'  => [true, true],
            'suspended' => [true, false],
            default     => [false, false],
        };

        DB::transaction(function () use ($vendor, $validated, $isApproved, $isActive) {
            $vendor->update([
                'title'           => $validated['title'],
                'description'     => $validated['description'] ?? null,
                'address'         => $validated['address'] ?? null,
                'phone'           => $validated['phone'] ?? null,
                'commission_rate' => $validated['commission_rate'] ?? $vendor->commission_rate,
                'delivery_fee'    => $validated['delivery_fee'] ?? $vendor->delivery_fee,
                'open_time'       => $validated['open_time'] ?? null,
                'close_time'      => $validated['close_time'] ?? null,
                'is_active'       => $isActive,
                'is_approved'     => $isApproved,
            ]);

            if ($vendor->author) {
                $vendor->author->update([
                    'name'   => $validated['owner_name'],
                    'email'  => $validated['owner_email'],
                    'phone'  => $validated['owner_phone'] ?? $vendor->author->phone,
                    'active' => $isActive,
                ]);
            }
        });

        return back()->with('success', 'Vendor updated successfully.');
    }

    public function vendorDelete(int $id)
    {
        $vendor = Vendor::with('author')->findOrFail($id);

        if ($vendor->orders()->exists()) {
            $vendor->update(['is_active' => false, 'is_approved' => false]);
            if ($vendor->author) {
                $vendor->author->update(['active' => false]);
            }

            return back()->with('success', 'Vendor has existing orders, so it was archived instead of deleted.');
        }

        if ($vendor->author) {
            $vendor->author->delete();
        } else {
            $vendor->delete();
        }

        return back()->with('success', 'Vendor deleted successfully.');
    }

    public function vendorToggle(Request $request, int $id)
    {
        $vendor = Vendor::with('author')->findOrFail($id);

        $action = $request->input('action'); // 'toggle_active' or 'approve'

        if ($action === 'toggle_active') {
            $newState = ! $vendor->is_active;
            $vendor->update([
                'is_active' => $newState,
                'status' => $newState ? 'approved' : 'suspended',
            ]);
            // Keep the users.active flag in sync so login and session middleware
            // correctly block/allow the vendor account.
            $vendor->author->update(['active' => $newState]);
            $message = 'Vendor ' . ($newState ? 'activated' : 'suspended') . ' successfully.';
        } elseif ($action === 'approve') {
            // Approve vendor AND activate their user account
            $vendor->update(['is_approved' => true, 'is_active' => true, 'status' => 'approved']);
            $vendor->author->update(['active' => true, 'status' => 'active']);
            $message = 'Vendor approved successfully! They can now log in and manage their store.';
        } elseif ($action === 'reject') {
            // Reject vendor but keep user account inactive
            $vendor->update(['is_approved' => false, 'is_active' => false, 'status' => 'rejected']);
            $vendor->author->update(['active' => false, 'status' => 'suspended']);
            $message = 'Vendor approval revoked.';
        } else {
            return back()->withErrors(['action' => 'Invalid action.']);
        }

        return back()->with('success', $message);
    }

    public function profile()
    {
        $user = auth('admin')->check() ? auth('admin')->user() : Auth::user();
        return view('admin.settings.users.profile', compact(['user']));
    }

    public function update(Request $request, $id)
    {
        $name = $request->input('name');
        $password = $request->input('password');
        $old_password = $request->input('old_password');
        $email = $request->input('email');
        if ($password == '') {
            $validator = Validator::make($request->all(), [
                'name' => 'required|max:255',
                'email' => 'required|email'
            ]);
        } else {
            $user = auth('admin')->check() ? auth('admin')->user() : Auth::user();
            if (password_verify($old_password, $user->password)) {
                $validator = Validator::make($request->all(), [
                    'name' => 'required|max:255',
                    'password' => 'required|min:8',
                    'confirm_password' => 'required|same:password',
                    'email' => 'required|email'
                ]);
            } else {
                return Redirect()->back()->with(['message' => "Please enter correct old password"]);
            }
        }

        if ($validator->fails()) {
            $error = $validator->errors()->first();
            return Redirect()->back()->with(['message' => $error]);
        }

        $user = User::find($id);
        if ($user) {
            $user->name = $name;
            $user->email = $email;
            
            if ($request->hasFile('profile_photo')) {
                $file = $request->file('profile_photo');
                $ext = $file->getClientOriginalExtension();
                $filename = time() . '.' . $ext;
                $file->move('storage/profiles/', $filename);
                $user->profile_photo = 'profiles/' . $filename;
            }

            if ($password != '') {
                $user->password = Hash::make($password);
            }
            $user->save();
        }

        return redirect()->back()->with('success', 'Profile updated successfully!');
    }

    public function create()
    {
        return view('admin.settings.users.create');
    }

    public function view($id)
    {
        $user = User::with('addresses')->find($id);
        [$currencySymbol, $currencyAtRight, $decimalDigits, $placeholderImage] = $this->getCurrencyData();
        return view('admin.settings.users.view', compact('id', 'user', 'currencySymbol', 'currencyAtRight', 'decimalDigits', 'placeholderImage'));
    }

    private function getCurrencyData(): array
    {
        try {
            $cur  = \App\Models\Setting::where('key', 'default_currency')->first();
            $data = $cur ? (is_string($cur->value) ? json_decode($cur->value, true) : (array) $cur->value) : [];
        } catch (\Throwable) {
            $data = [];
        }
        return [
            $data['symbol']        ?? '$',
            (bool)($data['symbolAtRight'] ?? false),
            (int)($data['decimal_degits'] ?? 2),
            asset('images/placeholder.png'),
        ];
    }

    public function payToUser(Request $request)
    {
        $response = array();
        $encrypt_data =  $request->data;

        if(!empty($encrypt_data)){

            $data = json_decode(base64_decode($encrypt_data),true);
            
            if($data['method'] == "paypal"){
            
                $response = $this->payWithPaypal($data);  
            
            }else if($data['method'] == "stripe"){
            
                $response = $this->payWithStripe($data);  
            
            }else if($data['method'] == "razorpay"){
            
                $response = $this->payWithRazorpay($data);  

            }else if($data['method'] == "flutterwave"){
            
                $response = $this->payWithFlutterwave($data);  
            }
            
        }else{
            $response['success'] = false;
            $response['message'] = 'Payout method setup is not done';
        }
        
        return response()->json($response);
    }

    public function payWithPaypal($data){

        $payout_response = array();

        if(!empty($data['user']['withdrawMethod']['paypal']['email'])){

            $paypal_email = $data['user']['withdrawMethod']['paypal']['email'];

            $isLive = $data['settings']['paypal']['isLive'];
            $clientId = $data['settings']['paypal']['paypalAppId'];
            $clientSecret = $data['settings']['paypal']['paypalSecret'];
            if($isLive){
                $environment = new ProductionEnvironment($clientId, $clientSecret);
            }else{
                $environment = new SandboxEnvironment($clientId, $clientSecret);
            }
            
            $client = new PayPalHttpClient($environment);
            $request = new PayoutsPostRequest();
            $body = [
                "sender_batch_header" => [
                    "sender_batch_id" => "Payouts_".$data["payoutId"],
                    "email_subject" => "You have a payout!",
                    "email_message" => "You have received a payout! Thanks for using our service!",
                ],
                "items" => [
                    [
                        "recipient_type" => "EMAIL",
                        "receiver" => $paypal_email,
                        "note" => "Your $".$data["amount"]." payout",
                        "sender_item_id" => $data["payoutId"],
                        "amount" => [
                            "currency" => "USD",
                            "value" => $data["amount"],
                        ],
                    ],
                ]
            ];
            
            $request->body = $body;

            try {

                $response = $client->execute($request);

                if(isset($response->statusCode) && $response->statusCode == "201"){
                    $payout_response['success'] = true;
                    $payout_response['message'] = 'We successfully processed your payout request';
                    $payout_response['result'] = $response->result;
                    $payout_response['status'] = "In Process";
                }else{
                    $payout_response['success'] = false;
                    $payout_response['message'] = 'Something went wrong to process your payout request';
                }

            }catch(\Throwable $e){
                $payout_response['success'] = false;
                $payout_response['message'] = $e->getMessage();
            }
            
        }else{
            $payout_response['success'] = false;
            $payout_response['message'] = 'User paypal email address is required';
        }
        
        return $payout_response;
    }

    public function payWithStripe($data){

        $payout_response = array();

        if(!empty($data['user']['withdrawMethod']['stripe']['accountId'])){
    
            $accountId = $data['user']['withdrawMethod']['stripe']['accountId'];
            $amount = bcmul($data["amount"], 100);
            
            $stripeSecret = $data['settings']['stripe']['stripeSecret'];
            $stripe = new \Stripe\StripeClient($stripeSecret);

            try {
                
                $response = $stripe->transfers->create([
                    'amount' => $amount,
                    'currency' => 'usd',
                    'destination' => $accountId,
                    'transfer_group' => $data["payoutId"],
                ]);

                $response = json_decode($response,true);

                if(isset($response['id']) && isset($response['balance_transaction'])){
                    $payout_response['success'] = true;
                    $payout_response['message'] = 'We successfully processed your payout request';
                    $payout_response['result'] = $response;
                    $payout_response['status'] = "Success";
                }else{
                    $payout_response['success'] = false;
                    $payout_response['message'] = "No such destination: '".$accountId."'";
                }

            }catch(\Throwable $e){
                $payout_response['success'] = false;
                $payout_response['message'] = $e->getMessage();
            }

        }else{
            $payout_response['success'] = false;
            $payout_response['message'] = 'Stripe accountId is required';
        }
        
        return $payout_response;
    }

    public function payWithRazorpay($data){
        
        $payout_response = array();

        if(!empty($data['user']['withdrawMethod']['razorpay']['accountId'])){
    
            $accountId = $data['user']['withdrawMethod']['razorpay']['accountId'];
            $amount = bcmul($data["amount"], 100);
            
            $api_key = $data['settings']['razorpay']['razorpayKey'];
            $api_secret = $data['settings']['razorpay']['razorpaySecret'];
            $api = new Api($api_key, $api_secret);
            
            try {
               
                $response = $api->transfer->create(array('account' => $accountId, 'amount' => $amount, 'currency' => 'INR'));
                $response = json_decode($response,true);
                
                if(isset($response['status']) && isset($response['id'])){
                    $payout_response['success'] = true;
                    $payout_response['message'] = 'We successfully processed your payout request';
                    $payout_response['result'] = $response;
                    $payout_response['status'] = "In Process";
                }else{
                    $payout_response['success'] = false;
                    $payout_response['message'] = $response['error']['description'];    
                }

            }catch(\Throwable $e){
                $payout_response['success'] = false;
                $payout_response['message'] = $e->getMessage();
            }

        }else{
            $payout_response['success'] = false;
            $payout_response['message'] = 'Razorpay accountId is required';
        }
        
        return $payout_response;
    }

    public function payWithFlutterwave($data){
        
        $payout_response = array();

        if(!empty($data['user']['withdrawMethod']['flutterwave'])){
    
            $bankCode = $data['user']['withdrawMethod']['flutterwave']['bankCode'];
            $accountNumber = $data['user']['withdrawMethod']['flutterwave']['accountNumber'];
            $amount = bcmul($data["amount"],10);
            $secretKey = $data['settings']['flutterwave']['secretKey'];
            
            $fields = [
                "account_bank" => $bankCode,
                "account_number" => $accountNumber,
                "amount" => $amount,
                "narration" => "Payment Request: ".$data["payoutId"]."",
                "currency" => "NGN",
                "reference" => $data["payoutId"],
            ];

            $ch = curl_init();
            curl_setopt($ch,CURLOPT_URL,"https://api.flutterwave.com/v3/transfers");
            curl_setopt($ch,CURLOPT_POST, true);
            curl_setopt($ch,CURLOPT_POSTFIELDS, json_encode($fields));
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                "Authorization: Bearer ".$secretKey,
                "Cache-Control: no-cache",
                "Content-Type: application/json",
            ));
            curl_setopt($ch,CURLOPT_RETURNTRANSFER, true); 
            $result = curl_exec($ch);
            $response = json_decode($result,true);

            if($response['status'] == "success"){
                $payout_response['success'] = true;
                $payout_response['message'] = 'We successfully processed your payout request';
                $payout_response['result'] = $response;
                $payout_response['status'] = "In Process";
            }else{
                $payout_response['success'] = false;
                $payout_response['message'] = $response['message'];    
            }
        
        }else{
            $payout_response['success'] = false;
            $payout_response['message'] = 'Flutterwave account detail is required';
        }
        
        return $payout_response;
    }

    public function checkPayoutStatus(Request $request){
        
        $response = array();
        $encrypt_data =  $request->data;

        if(!empty($encrypt_data)){

            $data = json_decode(base64_decode($encrypt_data),true);
            
            if($data['method'] == "paypal"){
            
                $response = $this->checkStatusPaypal($data);  
            
            }else if($data['method'] == "razorpay"){
            
                $response = $this->checkStatusRazorpay($data);  

            }else if($data['method'] == "flutterwave"){
            
                $response = $this->checkStatusFlutterwave($data);  
            }
            
        }else{
            $response['success'] = false;
            $response['message'] = 'Something went wrong to check status';
        }
        
        return response()->json($response);        
    }

    public function checkStatusPaypal($data){

        $payout_response = array();

        if(isset($data['payoutDetail']['payoutResponse']) && !empty($data['payoutDetail']['payoutResponse'])){

            $payout_batch_id = $data['payoutDetail']['payoutResponse']['batch_header']['payout_batch_id'];

            if(!empty($payout_batch_id)){

                $isLive = $data['settings']['paypal']['isLive'];
                $clientId = $data['settings']['paypal']['paypalAppId'];
                $clientSecret = $data['settings']['paypal']['paypalSecret'];
                if($isLive){
                    $api_url = "https://api-m.paypal.com";
                }else{
                    $api_url = "https://api-m.sandbox.paypal.com";
                }

                //Get access token
                $ch = curl_init();
                curl_setopt($ch,CURLOPT_URL,$api_url."/v1/oauth2/token");
                curl_setopt($ch,CURLOPT_POST, true);
                curl_setopt($ch,CURLOPT_POSTFIELDS,"grant_type=client_credentials");
                curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                    "Authorization: Basic ".base64_encode($clientId.":".$clientSecret),
                    "Content-Type: application/x-www-form-urlencoded"
                ));
                curl_setopt($ch,CURLOPT_RETURNTRANSFER, true); 
                $result = curl_exec($ch);
                $response = json_decode($result,true);

                //Get status
                if($response['access_token']){

                    $ch = curl_init();
                    curl_setopt($ch,CURLOPT_URL,$api_url."/v1/payments/payouts/".$payout_batch_id);
                    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                        "Authorization: Bearer ".$response['access_token'],
                        "Cache-Control: no-cache",
                    ));
                    curl_setopt($ch,CURLOPT_RETURNTRANSFER, true); 
                    $result2 = curl_exec($ch);
                    $response2 = json_decode($result2,true);
                    
                    if(isset($response2['items']) && isset($response2['items'][0]['transaction_status'])){
                        if($response2['items'][0]['transaction_status'] == "SUCCESS"){
                            $payout_response['success'] = true;
                            $payout_response['message'] = "We successfully processed your transaction";
                            $payout_response['result'] = $response2;
                            $payout_response['status'] = "Success";
                        }else{
                            $payout_response['success'] = false;
                            $payout_response['message'] = $response2['items'][0]['errors']['name']." : ".$response2['items'][0]['errors']['message'];
                            $payout_response['result'] = $response2;
                            $payout_response['status'] = "Failed";
                        }
                    }else{
                        $payout_response['success'] = false;
                        $payout_response['message'] = 'Invalid payout transaction';
                    }
                }else{
                    $payout_response['success'] = false;
                    $payout_response['message'] = 'Invalid client credentials';
                }

            }else{
                $payout_response['success'] = false;
                $payout_response['message'] = 'Invalid payout_batch_id';    
            }

        }else{
            $payout_response['success'] = false;
            $payout_response['message'] = 'Invalid payout response';
        }
        
        return $payout_response;
    }

    public function checkStatusRazorpay($data){

        $payout_response = array();

        if(isset($data['payoutDetail']['payoutResponse']) && !empty($data['payoutDetail']['payoutResponse'])){
    
            $transfer_id = $data['payoutDetail']['payoutResponse']['id'];

            if(!empty($transfer_id)){

                $api_key = $data['settings']['razorpay']['razorpayKey'];
                $api_secret = $data['settings']['razorpay']['razorpaySecret'];
                $api = new Api($api_key, $api_secret);
                
                try {
                
                    $response = $api->transfer->fetch($transfer_id);
                    $response = json_decode($response,true);

                    if(isset($response['settlement_status']) && $response['settlement_status'] == "settled"){
                        $payout_response['success'] = true;
                        $payout_response['message'] = 'We successfully processed your transaction';
                        $payout_response['result'] = $response;
                        $payout_response['status'] = "Success";
                    }else{
                        $payout_response['success'] = false;
                        $payout_response['message'] = $response['error']['description'];    
                        $payout_response['status'] = "Failed";
                    }

                }catch(\Throwable $e){
                    $payout_response['success'] = false;
                    $payout_response['message'] = $e->getMessage();
                }

            }else{
                $payout_response['success'] = false;
                $payout_response['message'] = 'Invalid transfer id';    
            }
            
        }else{
            $payout_response['success'] = false;
            $payout_response['message'] = 'Invalid payout response';
        }

        return $payout_response;
    }   

    public function checkStatusFlutterwave($data){

        $payout_response = array();
        
        if(isset($data['payoutDetail']['payoutResponse']) && !empty($data['payoutDetail']['payoutResponse'])){
    
            $transfer_id = $data['payoutDetail']['payoutResponse']['data']['id'];
            
            if(!empty($transfer_id)){

                $secretKey = $data['settings']['flutterwave']['secretKey'];

                $ch = curl_init();
                curl_setopt($ch,CURLOPT_URL,"https://api.flutterwave.com/v3/transfers/".$transfer_id);
                curl_setopt($ch,CURLOPT_CUSTOMREQUEST, "GET"); 
                curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                    "Authorization: Bearer ".$secretKey,
                    "Cache-Control: no-cache",
                    "Content-Type: application/json",
                ));
                curl_setopt($ch,CURLOPT_RETURNTRANSFER, true); 
                $result = curl_exec($ch);
                $response = json_decode($result,true);
                
                if($response['status'] == "success"){
                    $payout_response['success'] = true;
                    $payout_response['message'] = 'We successfully processed your transaction';
                    $payout_response['result'] = $response;
                    $payout_response['status'] = "Success";
                }else{
                    $payout_response['success'] = false;
                    $payout_response['message'] = $response['message'];    
                }

            }else{
                $payout_response['success'] = false;
                $payout_response['message'] = 'Invalid transfer id';    
            }
            
        }else{
            $payout_response['success'] = false;
            $payout_response['message'] = 'Invalid payout response';
        }
        
        return $payout_response;
    }
}
