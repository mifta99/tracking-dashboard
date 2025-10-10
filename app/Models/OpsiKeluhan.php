<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OpsiKeluhan extends Model
{
    use HasFactory;
    protected $primaryKey = 'id';
    protected $table = 'opsi_keluhan';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id',
        'kategori_keluhan_id',
        'opsi',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'integer',
        'kategori_keluhan_id' => 'integer',
    ];

    /**
     * Get the kategoriKeluhan that owns the OpsiKeluhan
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function kategoriKeluhan(): BelongsTo
    {
        return $this->belongsTo(KategoriKeluhan::class, 'kategori_keluhan_id', 'id');
    }

    /**
     * Get all of the keluhan for the OpsiKeluhan
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function keluhan(): HasMany
    {
        return $this->hasMany(Keluhan::class, 'opsi_keluhan_id', 'id');
    }
}
