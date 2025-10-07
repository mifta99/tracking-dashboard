<?php

namespace App\Http\Controllers;

use App\Mail\EmailVerificationMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use App\Models\PuskesmasEmailVerification;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;

class PuskesmasProfileController extends Controller
{
    /**
     * Show the profile edit form for puskesmas users
     */
    public function edit()
    {
        $user = Auth::user();
        
        // Ensure this is a puskesmas user
        if ($user->role_id !== 1) {
            return redirect()->back()->with('error', 'Unauthorized access.');
        }
        
        return view('puskesmas_edit', compact('user'));
    }
    
    /**
     * Update the puskesmas user profile
     */
    public function update(Request $request)
    {
        $user = User::find(Auth::id());
        
        // Ensure this is a puskesmas user
        if ($user->role_id !== 1) {
            return response()->json(['error' => 'Unauthorized access.'], 403);
        }
        
        // Handle immediate email update with OTP verification
        if ($request->has('verify_and_save') && $request->has('otp_code')) {
            return $this->verifyOtpAndSaveProfile($request, $user);
        }
        
        // Check if email has changed and require verification before updating
        $emailChanged = $request->email !== $user->email;
        
        if ($emailChanged) {
            // Email has changed - require verification before allowing profile update
            $verification = PuskesmasEmailVerification::where('user_id', $user->id)
                ->where('email', $request->email)
                ->latest()
                ->first();
                
            if (!$verification) {
                return response()->json([
                    'error' => 'Email belum diverifikasi',
                    'message' => 'Anda harus mengirim dan memverifikasi kode ke email baru terlebih dahulu sebelum dapat mengubah profile.',
                    'require_email_verification' => true
                ], 422);
            }
            
            // Check if the user's current email matches the verification email and is verified
            if ($user->email !== $request->email || !$user->hasVerifiedEmail()) {
                return response()->json([
                    'error' => 'Email belum diverifikasi',
                    'message' => 'Anda harus memasukkan kode verifikasi yang benar sebelum dapat mengubah profile.',
                    'require_email_verification' => true
                ], 422);
            }
        } else {
            // Email hasn't changed, check if current email is verified for new users
            if (!$user->hasVerifiedEmail() && $user->must_change_password) {
                return response()->json([
                    'error' => 'Email belum diverifikasi',
                    'message' => 'Anda harus memverifikasi email terlebih dahulu sebelum dapat melengkapi profile.',
                    'require_email_verification' => true
                ], 422);
            }
        }
        
        // Validation rules
        $validator = Validator::make($request->all(), [
            'email' => ['required', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'name' => 'required|string|max:255',
            'jabatan' => 'required|string|max:255',
            'instansi' => 'required|string|max:255',
            'no_hp' => 'required|string|max:20',
            'password' => 'nullable|string|min:8|confirmed',
        ], [
            'name.required' => 'Nama harus diisi.',
            'jabatan.required' => 'Jabatan harus diisi.',
            'email.required' => 'Email harus diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.max' => 'Email maksimal 255 karakter.',
            'email.unique' => 'Email ini sudah digunakan oleh pengguna lain.',
            'instansi.required' => 'Instansi harus diisi.',
            'no_hp.required' => 'Nomor HP harus diisi.',
            'password.min' => 'Password minimal 8 karakter.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'error' => 'Validation failed',
                'messages' => $validator->errors()
            ], 422);
        }
        
        try {
            // Check if email has changed
            $emailChanged = $request->email !== $user->email;
            
            // Update user profile
            $user->name = $request->name;
            $user->jabatan = $request->jabatan;
            $user->instansi = $request->instansi;
            $user->no_hp = $request->no_hp;
            
            // Update email and handle verification status
            if ($emailChanged) {
                // Email is already updated during verification, just maintain verified status
                // Clean up verification records after successful update
                PuskesmasEmailVerification::where('user_id', $user->id)->delete();
            }
            
            // Update password if provided
            if ($request->filled('password')) {
                $user->password = Hash::make($request->password);
            }
            
            // Mark as profile completed (no longer needs to change password)
            $user->must_change_password = false;
            
            $user->save();
            
            Log::info('Profile updated successfully for user: ' . $user->id);
            
            $message = 'Profile berhasil diperbarui!';
            if ($emailChanged) {
                $message = 'Profile berhasil diperbarui! Email telah diubah dan perlu diverifikasi ulang.';
            }
            
            return response()->json([
                'success' => true,
                'message' => $message,
                'email_changed' => $emailChanged,
                'redirect' => $emailChanged ? null : route('home') // Don't redirect if email changed
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error updating profile for user ' . $user->id . ': ' . $e->getMessage());
            
            return response()->json([
                'error' => 'Terjadi kesalahan saat memperbarui profile. Silakan coba lagi.'
            ], 500);
        }
    }

    /**
     * Verify OTP and save profile immediately
     */
    private function verifyOtpAndSaveProfile(Request $request, User $user)
    {
        try {
            // Validate OTP code
            $validator = Validator::make($request->all(), [
                'otp_code' => ['required', 'string', 'regex:/^\d{6}$/'],
                'email' => ['required', 'email', 'max:255', 'unique:users,email,' . $user->id],
                'name' => 'required|string|max:255',
                'jabatan' => 'required|string|max:255',
                'instansi' => 'required|string|max:255',
                'no_hp' => 'required|string|max:20',
                'password' => 'nullable|string|min:8|confirmed',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'error' => 'Validation failed',
                    'messages' => $validator->errors()
                ], 422);
            }

            // Find verification record
            $verification = PuskesmasEmailVerification::where('user_id', $user->id)
                ->where('email', $request->email)
                ->latest()
                ->first();

            if (!$verification) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak ada permintaan verifikasi email yang ditemukan.'
                ], 422);
            }

            // Verify OTP code
            if (!hash_equals((string) $verification->kode_verifikasi, (string) $request->otp_code)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Kode OTP tidak valid.'
                ], 422);
            }

            // Check if OTP is expired (24 hours)
            if ($verification->created_at && $verification->created_at->copy()->addDay()->isPast()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Kode OTP telah kedaluwarsa. Silakan kirim ulang.'
                ], 422);
            }

            // Update user profile with new data
            $user->name = $request->name;
            $user->jabatan = $request->jabatan;
            $user->instansi = $request->instansi;
            $user->no_hp = $request->no_hp;
            $user->email = $request->email; // Update to new email
            $user->email_verified_at = Carbon::now('Asia/Jakarta'); // Mark as verified

            // Update password if provided
            if ($request->filled('password')) {
                $user->password = Hash::make($request->password);
            }

            // Mark as profile completed
            $user->must_change_password = false;
            
            $user->save();

            // Clean up verification records
            PuskesmasEmailVerification::where('user_id', $user->id)->delete();

            Log::info('Profile and email updated via OTP verification for user: ' . $user->id);

            return response()->json([
                'success' => true,
                'message' => 'Profile berhasil diperbarui dan email telah diverifikasi!',
                'redirect' => route('home')
            ]);

        } catch (\Exception $e) {
            Log::error('Error in OTP verification and profile update for user ' . $user->id . ': ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat memverifikasi OTP dan menyimpan profile.'
            ], 500);
        }
    }
        public function sendVerificationMail(Request $request)
    {
        try {
            $validated = $request->validate([
                'email' => 'required|email',
                'request_type' => 'nullable|string|in:email_update,verification'
            ]);
            
            $token = \Illuminate\Support\Str::uuid()->toString();
            $recipient = $validated['email'] ?? 'tpieceverfication@gmail.com';
            $mailerName = $validated['mailer'] ?? config('mail.default');
            $requestType = $validated['request_type'] ?? 'verification';

            $verificationCode = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
            $verificationUrl = 'https://127.0.0.1/email/verify?code=' . $token;

            $expiresAtInput = $validated['expires_at'] ?? null;
            $expiresAt = $expiresAtInput
                ? Carbon::parse($expiresAtInput)->setTimezone('Asia/Jakarta')
                : Carbon::now('Asia/Jakarta')->addDay();

            $recipientName = $validated['name'] ?? null;
            
            // Clean up old verification records before creating new one
            PuskesmasEmailVerification::where('user_id', auth()->id())
                ->where('email', $recipient)
                ->delete();
            
            PuskesmasEmailVerification::create([
                'user_id' => auth()->id(),
                'email' => $recipient,
                'token' => $token,
                'kode_verifikasi' => $verificationCode,
            ]);
            
            // Customize message based on request type
            $emailTitle = $requestType === 'email_update' 
                ? "Verifikasi Perubahan Email - T-Piece Dashboard" 
                : "T-Piece Dashboard";
            
            Mail::mailer($mailerName)
                ->to($recipient)
                ->send(new EmailVerificationMail(
                    $verificationCode,
                    $verificationUrl,
                    $expiresAt,
                    $recipientName,
                    $emailTitle
                ));

            $cfg = config("mail.mailers.$mailerName") ?? [];
            
            $message = $requestType === 'email_update' 
                ? 'Kode OTP untuk perubahan email telah dikirim.' 
                : 'Verification email dispatched.';

            return response()->json([
                'success' => true,
                'message' => $message,
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Throwable $e) {
            Log::error('SMTP test failed: ' . $e->getMessage(), ['exception' => $e]);

            return response()->json([
                'success' => false,
                'message' => 'SMTP test failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function verifyEmailCode(Request $request)
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'regex:/^\d{6}$/'],
        ]);

        $user = $request->user();

        if ($user->email_verified_at) {
            return response()->json([
                'success' => true,
                'message' => 'Email sudah diverifikasi.',
            ]);
        }

        $verification = PuskesmasEmailVerification::where('user_id', $user->id)
            ->latest()
            ->first();

        if (
            !$verification ||
            !hash_equals((string) $verification->kode_verifikasi, (string) $validated['code'])
        ) {
            return response()->json([
                'success' => false,
                'message' => 'Kode verifikasi tidak valid.',
            ], 422);
        }

        if (
            $verification->created_at &&
            $verification->created_at->copy()->addDay()->isPast()
        ) {
            return response()->json([
                'success' => false,
                'message' => 'Kode verifikasi telah kedaluwarsa. Silakan kirim ulang email verifikasi.',
            ], 422);
        }

        // Update user email and verification status
        $user->forceFill([
            'email_verified_at' => Carbon::now('Asia/Jakarta'),
            'email' => $verification->email, // Update email to the verified email
        ])->save();

        // Clear verification records after successful verification
        PuskesmasEmailVerification::where('user_id', $user->id)->delete();

        return response()->json([
            'success' => true,
            'message' => 'Email berhasil diverifikasi.',
        ]);
    }
    public function checkEmail(Request $request)
    {
        try {
            $validated = $request->validate([
                'email' => 'required|email',
                'current_user_id' => 'nullable|integer'
            ]);

            $email = $validated['email'];
            $currentUserId = $validated['current_user_id'] ?? null;

            // Check if email exists in users table
            $query = \App\Models\User::where('email', $email);
            
            // Exclude current user if provided
            if ($currentUserId) {
                $query->where('id', '!=', $currentUserId);
            }

            $emailExists = $query->exists();

            return response()->json([
                'success' => true,
                'available' => !$emailExists,
                'message' => $emailExists ? 'Email sudah digunakan' : 'Email tersedia'
            ]);

        } catch (\Exception $e) {
            Log::error('Email check failed: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'available' => false,
                'message' => 'Gagal mengecek email'
            ], 500);
        }
    }

}