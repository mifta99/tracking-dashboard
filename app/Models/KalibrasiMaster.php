<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class KalibrasiMaster extends Model
{
    use HasFactory;
    protected $primaryKey = 'id';
    protected $table = 'kalibrasi_master';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id',
        'kuartal',
        'start_period',
        'end_period',
        'description',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'integer',
        'kuartal' => 'integer',
        'start_period' => 'date',
        'end_period' => 'date',
    ];

    /**
     * Get all of the kalibrasi for the KalibrasiMaster
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function kalibrasi(): HasMany
    {
        return $this->hasMany(Kalibrasi::class, 'kalibrasi_master_id', 'id');
    }
}
