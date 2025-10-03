<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Pengiriman extends Model
{
    use HasFactory, SoftDeletes;
    protected $primaryKey = 'id';
    protected $table = 'pengiriman';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id',
        'puskesmas_id',
        'tgl_pengiriman',
        'eta',
        'resi',
        'tracking_link',
        'target_tgl',
        'catatan',
        'tgl_diterima',
        'nama_penerima',
        'instansi_penerima',
        'jabatan_penerima',
        'nomor_penerima',
        'link_tanda_terima',
        'tahapan_id',
        'verif_kemenkes',
        'tgl_verif_kemenkes',
        'created_by',
        'updated_by',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'integer',
        'tgl_pengiriman' => 'date',
        'eta' => 'integer',
        'target_tgl' => 'date',
        'tgl_diterima' => 'date',
        'tahapan_id' => 'integer',
        'verif_kemenkes' => 'boolean',
        'tgl_verif_kemenkes' => 'datetime',
        'created_by' => 'integer',
        'updated_by' => 'integer',
    ];

    /**
     * Get the puskesmas that owns the Pengiriman
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function puskesmas(): BelongsTo
    {
        return $this->belongsTo(Puskesmas::class, 'puskesmas_id', 'id');
    }

    /**
     * Get the tahapan that owns the Pengiriman
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function tahapan(): BelongsTo
    {
        return $this->belongsTo(Tahapan::class, 'tahapan_id', 'id');
    }

    /**
     * Get the user that owns the Pengiriman
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }
        public function equipment(): BelongsTo
    {
        return $this->belongsTo(Equipment::class, 'puskesmas_id', 'puskesmas_id');
    }

    /**
     * Get the user that owns the Pengiriman
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by', 'id');
    }


}
