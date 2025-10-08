<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DokumentasiInsiden extends Model
{
    use HasFactory;
    protected $primaryKey = 'id';
    protected $table = 'dokumentasi_insidens';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id',
        'insiden_id',
        'link_foto',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'integer',
        'insiden_id' => 'integer',
    ];

    /**
     * Get the insiden that owns the DokumentasiInsiden
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function insiden(): BelongsTo
    {
        return $this->belongsTo(Insiden::class, 'insiden_id', 'id');
    }
}
