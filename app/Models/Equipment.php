<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Equipment extends Model
{
    use HasFactory, SoftDeletes;
    protected $primaryKey = 'id';
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id',
        'puskesmas_id',
        'serial_number',
        'name',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'integer',
    ];

    /**
     * Get the puskesmas that owns the Equipment
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function puskesmas(): BelongsTo
    {
        return $this->belongsTo(Puskesmas::class, 'puskesmas_id', 'id');
    }

    /**
     * Get all of the maintenance for the Equipment
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function maintenance(): HasMany
    {
        return $this->hasMany(Maintenance::class, 'equipment_id', 'id');
    }

    /**
     * Get all of the kalibrasi for the Equipment
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function kalibrasi(): HasMany
    {
        return $this->hasMany(Kalibrasi::class, 'equipment_id', 'id');
    }

}
