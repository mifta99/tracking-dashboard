<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Puskesmas extends Model
{
    use HasFactory;
    protected $primaryKey = 'id';
    protected $keyType = 'string';
    public $incrementing = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id',
        'district_id',
        'name',
        'pic',
        'kepala',
        'pic_dinkes_prov',
        'pic_dinkes_kab',
    'no_hp',
    'no_hp_alternatif',
        'created_by',
        'updated_by',
    ];

    /**
     * Get the district that owns the Puskesmas
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function district(): BelongsTo
    {
        return $this->belongsTo(District::class, 'district_id', 'id');
    }

    /**
     * Get all of the user for the Puskesmas
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function user(): HasMany
    {
        return $this->hasMany(User::class, 'puskesmas_id', 'id');
    }

    /**
     * Get the pengiriman associated with the Puskesmas
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function pengiriman(): HasOne
    {
        return $this->hasOne(Pengiriman::class, 'puskesmas_id', 'id');
    }

    /**
     * Get the ujiFungsi associated with the Puskesmas
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function ujiFungsi(): HasOne
    {
        return $this->hasOne(UjiFungsi::class, 'puskesmas_id', 'id');
    }

    /**
     * Get the document associated with the Puskesmas
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function document(): HasOne
    {
        return $this->hasOne(Document::class, 'puskesmas_id', 'id');
    }

    /**
     * Get all of the insiden for the Puskesmas
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function insiden(): HasMany
    {
        return $this->hasMany(Insiden::class, 'puskesmas_id', 'id');
    }

}
