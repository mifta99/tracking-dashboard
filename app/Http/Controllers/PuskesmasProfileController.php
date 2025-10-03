<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use App\Models\User;

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
        
        // Validation rules
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'jabatan' => 'required|string|max:255',
            'instansi' => 'required|string|max:255',
            'no_hp' => 'required|string|max:20',
            'password' => 'nullable|string|min:8|confirmed',
        ], [
            'name.required' => 'Nama harus diisi.',
            'jabatan.required' => 'Jabatan harus diisi.',
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
            // Update user profile
            $user->name = $request->name;
            $user->jabatan = $request->jabatan;
            $user->instansi = $request->instansi;
            $user->no_hp = $request->no_hp;
            
            // Update password if provided
            if ($request->filled('password')) {
                $user->password = Hash::make($request->password);
            }
            
            // Mark as profile completed (no longer needs to change password)
            $user->must_change_password = false;
            
            $user->save();
            
            Log::info('Profile updated successfully for user: ' . $user->id);
            
            return response()->json([
                'success' => true,
                'message' => 'Profile berhasil diperbarui!',
                'redirect' => route('home')
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error updating profile for user ' . $user->id . ': ' . $e->getMessage());
            
            return response()->json([
                'error' => 'Terjadi kesalahan saat memperbarui profile. Silakan coba lagi.'
            ], 500);
        }
    }
}