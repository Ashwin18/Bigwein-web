<?php
namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class OwnerAuthController extends Controller
{
    /** Show combined login + register page */
    public function showLogin()
    {
        if (session('bw_customer') && session('bw_customer')['owner_type']) {
            return redirect('/owner/dashboard');
        }
        return view('frontend.owner.auth.login');
    }

    /** Show owner registration (step 1: type selection) */
    public function showRegister()
    {
        if (session('bw_customer') && session('bw_customer')['owner_type']) {
            return redirect('/owner/dashboard');
        }
        return view('frontend.owner.register');
    }

    /** Process registration */
    public function register(Request $request)
    {
        $request->validate([
            'name'         => 'required|string|max:100',
            'email'        => 'required|email|unique:customers,email',
            'password'     => 'required|min:6|confirmed',
            'mobile'       => 'required|string|max:15',
            'owner_type'   => 'required|in:seller,builder',
            'company_name' => 'nullable|string|max:150',
        ]);

        $slug = Str::slug($request->name) . '-' . Str::random(5);

        $customer = Customer::create([
            'name'             => $request->name,
            'email'            => $request->email,
            'password'         => Hash::make($request->password),
            'mobile'           => $request->mobile,
            'country_code'     => '+91',
            'owner_type'       => $request->owner_type,
            'company_name'     => $request->company_name,
            'slug_id'          => $slug,
            'address'          => '',
            'is_email_verified'=> 1,
            'isActive'         => 1,
            'logintype'        => 'manual',
        ]);

        session(['bw_customer' => $customer->toArray()]);
        return redirect('/owner/dashboard')->with('success', 'Welcome to BigWein! Your owner account is ready.');
    }

    /** Process login */
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
        if (!$customer->owner_type) {
            return back()->withErrors(['email' => 'This account is not registered as an owner.'])->withInput();
        }
        if (!$customer->isActive) {
            return back()->withErrors(['email' => 'Your account has been deactivated.'])->withInput();
        }

        session(['bw_customer' => $customer->toArray()]);
        return redirect('/owner/dashboard')->with('success', 'Welcome back, ' . $customer->name . '!');
    }

    /** Logout */
    public function logout()
    {
        session()->forget('bw_customer');
        return redirect('/owner/login')->with('success', 'Logged out successfully.');
    }
}
