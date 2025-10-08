<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PuskesmasEmailVerification extends Model
{
    use HasFactory;
    
    protected $table = 'puskesmas_email_verifications';
    
    protected $fillable = [
        'user_id',
        'email',
        'token',
        'kode_verifikasi',
        'confirmed_at'
    ];
    
    protected $casts = [
        'confirmed_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    /**
     * Get the user that owns the verification record
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
