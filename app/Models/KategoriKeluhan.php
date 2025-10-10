<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class KategoriKeluhan extends Model
{
    use HasFactory;
    protected $primaryKey = 'id';
    protected $table = 'kategori_keluhan';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id',
        'kategori',
        'description',
        'max_response_time',
        'max_technical_time',
        'max_resolution_time',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'integer',
        'max_response_time' => 'integer',
        'max_technical_time' => 'integer',
        'max_resolution_time' => 'integer',
    ];

    /**
     * Get all of the keluhan for the KategoriKeluhan
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function keluhan(): HasMany
    {
        return $this->hasMany(Keluhan::class, 'kategori_id', 'id');
    }

    /**
     * Get all of the opsiKeluhan for the KategoriKeluhan
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function opsiKeluhan(): HasMany
    {
        return $this->hasMany(OpsiKeluhan::class, 'kategori_keluhan_id', 'id');
    }
}
