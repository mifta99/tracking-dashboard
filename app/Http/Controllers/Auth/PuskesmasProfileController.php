<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rules\Password;

class PuskesmasProfileController extends Controller
{
    /**
     * Show the profile edit form
     */
    public function edit()
    {
        $user = Auth::user();
        
        // Check if user is puskesmas role
        if ($user->role_id !== 1) {
            abort(403, 'Akses ditolak');
        }
        
        return view('auth.puskesmas_edit', compact('user'));
    }
    
    /**
     * Update the profile
     */
    public function update(Request $request)
    {
        $user = User::find(Auth::id());
        
        // Check if user is puskesmas role
        if ($user->role_id !== 1) {
            abort(403, 'Akses ditolak');
        }
        
        // Validation rules
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'jabatan' => ['required', 'string', 'max:255'],
            'instansi' => ['required', 'string', 'max:255'],
            'no_hp' => ['required', 'string', 'max:20'],
        ];
        
        // If user must change password (first login), password is required
        if ($user->must_change_password) {
            $rules['password'] = ['required', 'confirmed', Password::min(8)];
        } else {
            // Optional password change
            $rules['password'] = ['nullable', 'confirmed', Password::min(8)];
        }
        
        $validated = $request->validate($rules, [
            'name.required' => 'Nama admin puskesmas wajib diisi',
            'name.max' => 'Nama maksimal 255 karakter',
            'jabatan.required' => 'Jabatan wajib diisi',
            'jabatan.max' => 'Jabatan maksimal 255 karakter',
            'instansi.required' => 'Instansi wajib diisi',
            'instansi.max' => 'Instansi maksimal 255 karakter',
            'no_hp.required' => 'No. HP wajib diisi',
            'no_hp.max' => 'No. HP maksimal 20 karakter',
            'password.required' => 'Password wajib diisi untuk aktivasi akun',
            'password.min' => 'Password minimal 8 karakter',
            'password.confirmed' => 'Konfirmasi password tidak cocok',
        ]);
        
        try {
            // Update user data
            $user->name = $validated['name'];
            $user->jabatan = $validated['jabatan'];
            $user->instansi = $validated['instansi'];
            $user->no_hp = $validated['no_hp'];
            
            // Update password if provided
            if (!empty($validated['password'])) {
                $user->password = Hash::make($validated['password']);
            }
            
            // Check if this is first login activation
            $isFirstLogin = $user->must_change_password;
            
            // Mark as completed first login setup
            if ($user->must_change_password) {
                $user->must_change_password = false;
                $user->email_verified_at = now();
            }
            
            $user->save();
            
            // Success message based on context
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Profil berhasil diperbarui',
                    'redirect' => route('dashboard')
                ]);
            }
            
            $message = $isFirstLogin 
                ? 'Akun berhasil diaktivasi! Selamat datang di sistem tracking.' 
                : 'Profil berhasil diperbarui.';
                
            return redirect()->route('dashboard')->with('success', $message);
            
        } catch (\Exception $e) {
            Log::error('Error updating puskesmas profile: ' . $e->getMessage());
            
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Terjadi kesalahan saat menyimpan data'
                ], 500);
            }
            
            return back()->withErrors(['error' => 'Terjadi kesalahan saat menyimpan data']);
        }
    }
    
    /**
     * Check if user needs to complete profile
     */
    public function checkProfileStatus()
    {
        $user = Auth::user();
        
        return response()->json([
            'must_change_password' => $user->must_change_password ?? false,
            'missing_data' => $this->getMissingRequiredData($user),
        ]);
    }
    
    /**
     * Get missing required data for user
     */
    private function getMissingRequiredData($user)
    {
        $required = ['name', 'jabatan', 'instansi', 'no_hp'];
        $missing = [];
        
        foreach ($required as $field) {
            if (empty($user->{$field})) {
                $missing[] = $field;
            }
        }
        
        return $missing;
    }
}