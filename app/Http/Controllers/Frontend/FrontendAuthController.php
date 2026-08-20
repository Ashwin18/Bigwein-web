<?php
namespace App\Http\Controllers\Frontend;

use Exception;
use App\Models\Customer;
use App\Models\NumberOtp;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class FrontendAuthController extends Controller
{
    /* ── Pages ─────────────────────────────────────────────────────────────── */
    public function showLogin()
    {
        if (session('bw_customer')) return redirect('/');
        return view('frontend.auth.login', ['tab' => 'login']);
    }

    public function showRegister()
    {
        if (session('bw_customer')) return redirect('/');
        return view('frontend.auth.login', ['tab' => 'register']);
    }

    /* ── JSON: LOGIN ────────────────────────────────────────────────────────── */
    public function login(Request $request)
    {
        $v = Validator::make($request->all(), [
            'email'    => 'required|email',
            'password' => 'required|min:6',
        ]);
        if ($v->fails()) return response()->json(['error'=>true,'message'=>$v->errors()->first()]);

        $user = Customer::where('email', $request->email)
            ->where('logintype', 3)->first();

        if (!$user)
            return response()->json(['error'=>true,'message'=>'No account found with this email.']);

        if (!Hash::check($request->password, $user->password))
            return response()->json(['error'=>true,'message'=>'Incorrect password. Please try again.']);

        if (!$user->is_email_verified)
            return response()->json(['error'=>true,'message'=>'Please verify your email first.','key'=>'notVerified']);

        if (!$user->isActive)
            return response()->json(['error'=>true,'message'=>'Your account has been deactivated.']);

        // Store in session
        $data = [
            'id'      => $user->id,
            'name'    => $user->name,
            'email'   => $user->email,
            'mobile'  => $user->mobile,
            'profile' => $user->getRawOriginal('profile'),
        ];
        session(['bw_customer' => $data]);

        return response()->json(['error'=>false,'message'=>'Login successful!','data'=>$data]);
    }

    /* ── JSON: REGISTER ─────────────────────────────────────────────────────── */
    public function register(Request $request)
    {
        $v = Validator::make($request->all(), [
            'name'     => 'required|string|max:100',
            'email'    => 'required|email|unique:customers,email',
            'password' => 'required|min:6',
            'mobile'   => 'nullable|string|max:20',
        ], ['email.unique' => 'This email is already registered.']);
        if ($v->fails()) return response()->json(['error'=>true,'message'=>$v->errors()->first()]);

        try {
            // customers.address is NOT NULL — set empty string for web registrations
            $otp = '123456'; // Default OTP — email verification bypassed
            $customerId = DB::table('customers')->insertGetId([
                'name'             => $request->name,
                'email'            => $request->email,
                'password'         => Hash::make($request->password),
                'mobile'           => $request->input('mobile',''),
                'country_code'     => $request->input('country_code','+91'),
                'auth_id'          => uniqid('bw_', true),
                'slug_id'          => $this->makeSlug($request->name),
                'logintype'        => '3',
                'is_email_verified'=> 1, // Auto-verified — no OTP needed
                'isActive'         => 1,
                'notification'     => 1,
                'address'          => '',
                'created_at'       => now(),
                'updated_at'       => now(),
            ]);

            // Save default OTP (123456) so OTP step still works if user reaches it
            DB::table('number_otps')->updateOrInsert(
                ['email' => $request->email],
                ['otp' => $otp, 'expire_at' => now()->addMinutes(60),
                 'created_at' => now(), 'updated_at' => now()]
            );

            // Auto-login after registration
            $customer = DB::table('customers')->where('id', $customerId)->first();
            session(['bw_customer' => (array) $customer]);

            return response()->json(['error'=>false,'message'=>'Account created successfully! Welcome to BigWein.', 'auto_login'=>true]);
        } catch (Exception $e) {
            Log::error('Register: '.$e->getMessage());
            return response()->json(['error'=>true,'message'=>'Registration failed. Please try again.'],500);
        }
    }

    /* ── JSON: SEND OTP ─────────────────────────────────────────────────────── */
    public function sendOtp(Request $request)
    {
        $v = Validator::make($request->all(), ['email'=>'required|email|exists:customers,email']);
        if ($v->fails()) return response()->json(['error'=>true,'message'=>$v->errors()->first()]);

        $otp = rand(100000, 999999);
        DB::table('number_otps')->updateOrInsert(
            ['email' => $request->email],
            ['otp' => $otp, 'expire_at' => now()->addMinutes(10), 'updated_at' => now()]
        );
        $this->sendOtpEmail($request->email, $otp);

        return response()->json(['error'=>false,'message'=>'OTP sent to your email!']);
    }

    /* ── JSON: VERIFY OTP ───────────────────────────────────────────────────── */
    public function verifyOtp(Request $request)
    {
        $v = Validator::make($request->all(), [
            'email' => 'required|email',
            'otp'   => 'required',
        ]);
        if ($v->fails()) return response()->json(['error'=>true,'message'=>$v->errors()->first()]);

        $rec = DB::table('number_otps')->where('email', $request->email)->first();
        if (!$rec)           return response()->json(['error'=>true,'message'=>'OTP not found. Please request a new one.']);
        if ($rec->otp != $request->otp && $request->otp !== '123456') return response()->json(['error'=>true,'message'=>'Incorrect OTP. Try 123456 if you did not receive an email.']);
        if (now()->isAfter($rec->expire_at)) return response()->json(['error'=>true,'message'=>'OTP has expired.']);

        DB::table('customers')->where('email', $request->email)->update(['is_email_verified'=>1,'updated_at'=>now()]);
        DB::table('number_otps')->where('email', $request->email)->delete();

        return response()->json(['error'=>false,'message'=>'Email verified successfully!']);
    }

    /* ── JSON: LOGOUT ───────────────────────────────────────────────────────── */
    public function logout()
    {
        session()->forget('bw_customer');
        return response()->json(['error'=>false,'message'=>'Logged out successfully.']);
    }

    /* ── Helpers ────────────────────────────────────────────────────────────── */
    private function makeSlug(string $name): string
    {
        $slug = strtolower(preg_replace('/[^a-z0-9]+/i','-',trim($name))).'-'.substr(uniqid(),0,5);
        return substr($slug, 0, 50);
    }

    private function sendOtpEmail(string $email, int $otp): void
    {
        try {
            $template = system_setting('verify_mail_template') ?: "Your OTP is: {$otp}";
            $template = str_replace(['{app_name}','{otp}'], ['Bigwein', $otp], strip_tags($template));
            \App\Services\HelperService::sendMail([
                'email_template' => $template,
                'email'          => $email,
                'title'          => 'Verify your email — Bigwein',
            ], false, true);
        } catch (Exception $e) {
            Log::warning('OTP email failed: '.$e->getMessage());
        }
    }
}
