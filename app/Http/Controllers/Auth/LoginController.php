<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\EmailVerificationMail;
use App\Models\Puskesmas;
use App\Models\PuskesmasEmailVerification;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::HOME;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    function login() {
        return view('auth.login');
    }
    protected function username()
    {
        return 'username'; 
    }
     public function loginProcess(Request $request){

        $credentials =  $request->only('email','password');
        $validate = Validator::make($credentials,[
            'email'=>'required',
            'password'=>'required'
        ]);

        if($validate->fails()){

            return back()->withErrors($validate)->withInput();
        }

        if(Auth::attempt($credentials)){
            return redirect()->intended('/')->with('success','Successfully Login');
        }
            $existPuskesmasUser = User::where('email', $request->email)->first();
            $loginCheck = Puskesmas::select('puskesmas.*', 'puskesmas.id as puskesmas_id')
                ->join('districts', 'districts.id', '=', 'puskesmas.district_id')
                ->where(function($query) use ($request) {
                    $query->where('puskesmas.id', $request->email)
                        ->whereRaw('LOWER(puskesmas.id) = ?', [strtolower($request->password)]);
                })
                ->orWhere(function($query) use ($request) {
                    $query->where('puskesmas.id', $request->email)
                        ->whereRaw('LOWER(districts.name) = ?', [strtolower($request->password)]);
                })
                ->first();

            if($loginCheck){
                if(!$existPuskesmasUser){
                    $newUser = new \App\Models\User();
                    $newUser->name = 'Admin ' . $loginCheck->name;
                    $newUser->instansi = 'Puskesmas ' . $loginCheck->name;
                    $newUser->email = $loginCheck->id; 
                    $newUser->password = bcrypt(strtolower($loginCheck->id)); 
                    $newUser->role_id = 1; 
                    $newUser->puskesmas_id = $loginCheck->id;
                    $newUser->must_change_password = 1;
                    $newUser->save();
                }
                if(Auth::attempt(['email' => $request->email, 'password' => strtolower($loginCheck->id)])){
                    return redirect()->intended('/')->with('success','Successfully Login');
                }
                
                
            }
            $userByPuskesmasId = User::where('puskesmas_id', $request->email)->first();
            if($userByPuskesmasId){
                if(Auth::attempt(['email' => $userByPuskesmasId->email, 'password' => $request->password])){
                    return redirect()->intended('/')->with('success','Successfully Login');
                }
            }
        return redirect('login')->withInput()->withErrors(['login_message'=>'Email atau Password Salah !']);
      }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        return redirect('/login')->with('success', 'Successfully logged out');
    }

    public function resetPassword()
    {
        return view('auth.passwords.reset');
    }

    public function sendEmail(Request $request)
    {
        // Validate input including reCAPTCHA
        $request->validate([
            'email' => 'required|email',
            'g-recaptcha-response' => 'required'
        ], [
            'email.required' => 'Email harus diisi.',
            'email.email' => 'Format email tidak valid.',
            'g-recaptcha-response.required' => 'Silakan verifikasi reCAPTCHA.'
        ]);

        // Verify reCAPTCHA with Google
        if (!$this->verifyRecaptcha($request->input('g-recaptcha-response'))) {
            return response()->json([
                'message' => 'Verifikasi reCAPTCHA gagal. Silakan coba lagi.',
                'errors' => ['g-recaptcha-response' => ['Verifikasi reCAPTCHA gagal.']]
            ], 422);
        }

        $user = User::where('email', $request->email)->first();
        
        if (!$user) {
            return response()->json([
                'message' => 'Email tidak ditemukan dalam sistem.',
                'errors' => ['email' => ['Email tidak terdaftar.']]
            ], 422);
        }

        try {
            // Clean up existing verification records for this user
            PuskesmasEmailVerification::where('user_id', $user->id)->delete();

            $token = \Illuminate\Support\Str::uuid()->toString();
            $verificationUrl = url('/password/reset/' . $token);
            $recipientName = $user->name ?? 'Bapak / Ibu';
            $verificationCode = str_pad(random_int(100000, 999999), 6, '0', STR_PAD_LEFT);
            $emailTitle = "Reset Password - T-Piece Dashboard";
            $isResetPassword = true;

            // Create verification record
            PuskesmasEmailVerification::create([
                'user_id' => $user->id,
                'email' => $user->email,
                'token' => $token,
                'kode_verifikasi' => $verificationCode,
            ]);

            // Send email
            Mail::mailer(config('mail.default'))
                ->to($request->email)
                ->send(new EmailVerificationMail(
                    $verificationCode,
                    $verificationUrl,
                    $recipientName,
                    $emailTitle,
                    $isResetPassword
                ));

            return response()->json([
                'status' => 'Link reset password telah dikirim ke email Anda! Silakan cek kotak masuk atau folder spam.',
                'success' => true
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to send password reset email: ' . $e->getMessage());
            
            return response()->json([
                'message' => 'Gagal mengirim email reset password. Silakan coba lagi.',
                'errors' => ['email' => ['Terjadi kesalahan sistem.']]
            ], 500);
        }
    }

    /**
     * Verify reCAPTCHA response with Google servers
     */
    private function verifyRecaptcha($recaptchaResponse)
    {
        // Skip reCAPTCHA verification in local development
        if (app()->environment('local')) {
            return true;
        }

        if (empty($recaptchaResponse)) {
            return false;
        }

        // Use test keys for development, replace with real keys for production
        $secretKey = env('RECAPTCHA_SECRET_KEY', '6LdQjOIrAAAAAG2ppySge5SGhSl-BCDcx56Vqj5R');
        $verifyUrl = 'https://www.google.com/recaptcha/api/siteverify';
        
        $data = [
            'secret' => $secretKey,
            'response' => $recaptchaResponse,
            'remoteip' => request()->ip()
        ];

        try {
            $context = stream_context_create([
                'http' => [
                    'method' => 'POST',
                    'header' => 'Content-Type: application/x-www-form-urlencoded',
                    'content' => http_build_query($data),
                    'timeout' => 10
                ]
            ]);

            $verifyResponse = file_get_contents($verifyUrl, false, $context);
            
            if ($verifyResponse === false) {
                Log::error('reCAPTCHA verification failed: Unable to connect to Google servers');
                return false;
            }

            $responseData = json_decode($verifyResponse, true);
            
            if (!isset($responseData['success'])) {
                Log::error('reCAPTCHA verification failed: Invalid response format', ['response' => $verifyResponse]);
                return false;
            }

            if (!$responseData['success']) {
                Log::warning('reCAPTCHA verification failed', [
                    'errors' => $responseData['error-codes'] ?? [],
                    'ip' => request()->ip()
                ]);
                return false;
            }

            return true;
            
        } catch (\Exception $e) {
            Log::error('reCAPTCHA verification exception: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Verify OTP code from email
     */
    public function verifyOTP(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'otp_code' => 'required|string|size:6'
        ], [
            'email.required' => 'Email harus diisi.',
            'email.email' => 'Format email tidak valid.',
            'otp_code.required' => 'Kode OTP harus diisi.',
            'otp_code.size' => 'Kode OTP harus 6 digit.'
        ]);

        try {
            // Find user by email
            $user = User::where('email', $request->email)->first();
            
            if (!$user) {
                return response()->json([
                    'message' => 'Email tidak ditemukan dalam sistem.',
                    'errors' => ['email' => ['Email tidak terdaftar.']]
                ], 422);
            }

            // Find verification record
            $verification = PuskesmasEmailVerification::where('user_id', $user->id)
                ->where('kode_verifikasi', $request->otp_code)
                ->where('created_at', '>=', now()->subHour()) // Valid for 1 hour
                ->first();

            if (!$verification) {
                return response()->json([
                    'message' => 'Kode OTP tidak valid atau telah kedaluwarsa.',
                    'errors' => ['otp_code' => ['Kode OTP tidak valid.']]
                ], 422);
            }

            // Mark verification as confirmed
            $verification->update([
                'confirmed_at' => now()
            ]);

            return response()->json([
                'status' => 'Kode OTP berhasil diverifikasi.',
                'success' => true
            ]);

        } catch (\Exception $e) {
            Log::error('OTP verification failed: ' . $e->getMessage());
            
            return response()->json([
                'message' => 'Gagal memverifikasi kode OTP. Silakan coba lagi.',
                'errors' => ['otp_code' => ['Terjadi kesalahan sistem.']]
            ], 500);
        }
    }

    /**
     * Complete password reset with new password
     */
    public function completePasswordReset(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string|min:8|confirmed',
            'password_confirmation' => 'required|string|min:8'
        ], [
            'email.required' => 'Email harus diisi.',
            'email.email' => 'Format email tidak valid.',
            'password.required' => 'Password baru harus diisi.',
            'password.min' => 'Password minimal 8 karakter.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
            'password_confirmation.required' => 'Konfirmasi password harus diisi.',
            'password_confirmation.min' => 'Konfirmasi password minimal 8 karakter.'
        ]);

        try {
            // Find user by email
            $user = User::where('email', $request->email)->first();
            
            if (!$user) {
                return response()->json([
                    'message' => 'Email tidak ditemukan dalam sistem.',
                    'errors' => ['email' => ['Email tidak terdaftar.']]
                ], 422);
            }

            // Find confirmed verification record
            $verification = PuskesmasEmailVerification::where('user_id', $user->id)
                ->whereNotNull('confirmed_at')
                ->where('confirmed_at', '>=', now()->subHour()) // Confirmed within last hour
                ->first();

            if (!$verification) {
                return response()->json([
                    'message' => 'Sesi reset password tidak valid atau telah kedaluwarsa. Silakan mulai ulang proses reset password.',
                    'errors' => ['session' => ['Sesi tidak valid.']]
                ], 422);
            }

            // Update user password
            $user->update([
                'password' => bcrypt($request->password),
                'must_change_password' => 0 // Reset flag if exists
            ]);

            // Clean up verification record
            PuskesmasEmailVerification::where('user_id', $user->id)->delete();

            return response()->json([
                'status' => 'Password berhasil direset. Anda sekarang dapat login dengan password baru.',
                'success' => true
            ]);

        } catch (\Exception $e) {
            Log::error('Password reset completion failed: ' . $e->getMessage());
            
            return response()->json([
                'message' => 'Gagal mereset password. Silakan coba lagi.',
                'errors' => ['password' => ['Terjadi kesalahan sistem.']]
            ], 500);
        }
    }

}
