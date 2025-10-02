<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DokumentasiKeluhan extends Model
{
    use HasFactory;
    protected $primaryKey = 'id';
    protected $table = 'dokumentasi_keluhans';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id',
        'keluhan_id',
        'link_foto',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'integer',
        'keluhan_id' => 'integer',
    ];

    /**
     * Get the keluhan that owns the DokumentasiKeluhan
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function keluhan(): BelongsTo
    {
        return $this->belongsTo(Keluhan::class, 'keluhan_id', 'id');
    }
}
