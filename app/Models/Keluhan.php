<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Keluhan extends Model
{
    use HasFactory, SoftDeletes;
    protected $primaryKey = 'id';
    protected $table = 'keluhan';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id',
        'puskesmas_id',
        'kategori_id',
        'opsi_keluhan_id',
        'status_id',
        'reported_by',
        'reported_subject',
        'reported_date',
        'reported_issue',
        'proceed_by',
        'proceed_date',
        'resolved_by',
        'resolved_date',
        'action_taken',
        'catatan',
        'doc_selesai',
        'reported_name',
        'reported_hp',
        'total_downtime',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'integer',
        'kategori_id' => 'integer',
        'status_id' => 'integer',
        'reported_by' => 'integer',
        'reported_date' => 'date',
        'proceed_date' => 'date',
        'resolved_date' => 'date',
        'total_downtime' => 'integer',
    ];

    /**
     * Get the puskesmas that owns the Keluhan
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function puskesmas(): BelongsTo
    {
        return $this->belongsTo(Puskesmas::class, 'puskesmas_id', 'id');
    }

    /**
     * Get the kategori that owns the Keluhan
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function kategoriKeluhan(): BelongsTo
    {
        return $this->belongsTo(KategoriKeluhan::class, 'kategori_id', 'id');
    }

    /**
     * Get the status that owns the Keluhan
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function statusKeluhan(): BelongsTo
    {
        return $this->belongsTo(StatusKeluhan::class, 'status_id', 'id');
    }

    /**
     * Get the user that owns the Keluhan
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function reporter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reported_by', 'id');
    }

    /**
     * Get all of the dokumentasiKeluhan for the Keluhan
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function dokumentasiKeluhan(): HasMany
    {
        return $this->hasMany(DokumentasiKeluhan::class, 'keluhan_id', 'id');
    }

    /**
     * Get the opsiKeluhan that owns the Keluhan
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function opsiKeluhan(): BelongsTo
    {
        return $this->belongsTo(OpsiKeluhan::class, 'opsi_keluhan_id', 'id');
    }

}
