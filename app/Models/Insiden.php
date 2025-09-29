<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Insiden extends Model
{
    use HasFactory, SoftDeletes;
    protected $primaryKey = 'id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id',
        'puskesmas_id',
        'tahapan_id',
        'status_id',
        'reported_by',
        'reported_date',
        'insiden',
        'waktu_kejadian',
        'detail_kejadian',
        'nama_korban',
        'tindak_lanjut',
        'dokumentasi',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'integer',
        'tahapan_id' => 'integer',
        'status_id' => 'integer',
        'reported_by' => 'integer',
        'reported_date' => 'date',
        'waktu_kejadian' => 'date',
    ];

    /**
     * Get the puskesmas that owns the Insiden
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function puskesmas(): BelongsTo
    {
        return $this->belongsTo(Puskesmas::class, 'puskesmas_id', 'id');
    }

    /**
     * Get the tahapan that owns the Insiden
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function tahapan(): BelongsTo
    {
        return $this->belongsTo(Tahapan::class, 'tahapan_id', 'id');
    }

    /**
     * Get the status that owns the Insiden
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function status(): BelongsTo
    {
        return $this->belongsTo(StatusInsiden::class, 'status_id', 'id');
    }

    /**
     * Get the user that owns the Insiden
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function reporter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reported_by', 'id');
    }

}
