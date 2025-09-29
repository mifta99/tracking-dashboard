<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tahapan extends Model
{
    use HasFactory;
    protected $primaryKey = 'id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id',
        'tahapan',
        'tahap_ke',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'integer',
        'tahap_ke' => 'integer',
    ];

    /**
     * Get all of the pengiriman for the Tahapan
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function pengiriman(): HasMany
    {
        return $this->hasMany(Pengiriman::class, 'tahapan_id', 'id');
    }

    /**
     * Get all of the insiden for the Tahapan
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function insiden(): HasMany
    {
        return $this->hasMany(Insiden::class, 'tahapan_id', 'id');
    }
}
