<?php
namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class OwnerAuthController extends Controller
{
    public function showLogin()
    {
        $cust = session('bw_customer');
        if ($cust && !empty($cust['owner_type'] ?? null)) {
            return redirect('/owner/dashboard');
        }
        return view('frontend.owner.auth.login');
    }

    public function showRegister()
    {
        $cust = session('bw_customer');
        if ($cust && !empty($cust['owner_type'] ?? null)) {
            return redirect('/owner/dashboard');
        }
        return view('frontend.owner.register');
    }

    public function register(Request $request)
    {
        $isAjax = $request->wantsJson() || $request->ajax();

        $rules = [
            'name'         => 'required|string|max:100',
            'email'        => 'required|email|unique:customers,email',
            'password'     => 'required|min:6|confirmed',
            'mobile'       => 'required|string|max:15',
            'owner_type'   => 'required|in:seller,builder',
            'city'         => 'required|string|max:100',
        ];

        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            if ($isAjax) {
                return response()->json(['error' => true, 'message' => $validator->errors()->first()]);
            }
            return back()->withErrors($validator)->withInput();
        }

        $slug = Str::slug($request->name) . '-' . Str::random(5);

        $customer = Customer::create([
            'name'              => $request->name,
            'email'             => $request->email,
            'password'          => Hash::make($request->password),
            'mobile'            => $request->mobile,
            'country_code'      => $request->country_code ?? '+91',
            'owner_type'        => $request->owner_type,
            'company_name'      => $request->company_name,
            'city'              => $request->city,
            'state'             => $request->state,
            'slug_id'           => $slug,
            'address'           => '',
            'is_email_verified' => 1,
            'isActive'          => 1,
            'logintype'         => 'manual',
        ]);

        session(['bw_customer' => self::safeCustomerSession($customer)]);

        if ($isAjax) {
            return response()->json([
                'success'  => true,
                'message'  => 'Account created successfully!',
                'redirect' => '/owner/profile-setup',
            ]);
        }

        return redirect('/owner/profile-setup')
            ->with('success', 'Welcome to BigWein! Complete your profile to get started.');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        $customer = Customer::where('email', $request->email)->first();

        if (!$customer || !Hash::check($request->password, $customer->password)) {
            return back()->withErrors(['email' => 'Invalid email or password.'])->withInput();
        }
        if (empty($customer->owner_type)) {
            return back()->withErrors(['email' => 'This account is not registered as a property owner.'])->withInput();
        }
        if (!$customer->isActive) {
            return back()->withErrors(['email' => 'Your account has been deactivated.'])->withInput();
        }

        session(['bw_customer' => self::safeCustomerSession($customer)]);

        // If profile incomplete, redirect to setup
        if (empty($customer->about_me) && empty($customer->state)) {
            return redirect('/owner/profile-setup')
                ->with('success', 'Welcome back, ' . $customer->name . '!');
        }

        return redirect('/owner/dashboard')
            ->with('success', 'Welcome back, ' . $customer->name . '!');
    }

    public function logout()
    {
        session()->forget('bw_customer');
        return redirect('/owner/login')->with('success', 'Logged out successfully.');
    }

    /**
     * Build a safe session array without triggering heavy model accessors.
     */
    private static function safeCustomerSession($customer): array
    {
        return [
            'id'               => $customer->id,
            'name'             => $customer->getRawOriginal('name') ?? $customer->name,
            'email'            => $customer->email,
            'mobile'           => $customer->getRawOriginal('mobile'),
            'country_code'     => $customer->country_code,
            'profile'          => $customer->getRawOriginal('profile'),
            'owner_type'       => $customer->owner_type,
            'company_name'     => $customer->company_name,
            'phone_alt'        => $customer->phone_alt,
            'city'             => $customer->city,
            'state'            => $customer->state,
            'country'          => $customer->country,
            'about_me'         => $customer->about_me,
            'slug_id'          => $customer->slug_id,
            'is_email_verified'=> $customer->is_email_verified,
            'isActive'         => $customer->isActive,
        ];
    }
}
