<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Insiden extends Model
{
    use HasFactory, SoftDeletes;
    protected $primaryKey = 'id';
    protected $table = 'insiden';
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
        'kategori_id',
        'tgl_kejadian',
        'nama_korban',
        'bagian',
        'insiden',
        'kronologis',
        'tindakan',
        'tgl_selesai',
        'doc_selesai',
        'rencana_tindakan_koreksi',
        'pelaksana_tindakan_koreksi',
        'tgl_selesai_koreksi',
        'verifikasi_hasil_koreksi',
        'verifikasi_tgl_koreksi',
        'verifikasi_pelaksana_koreksi',
        'rencana_tindakan_korektif',
        'pelaksana_tindakan_korektif',
        'tgl_selesai_korektif',
        'verifikasi_hasil_korektif',
        'verifikasi_tgl_korektif',
        'verifikasi_pelaksana_korektif',
        'reported_by',
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
        'kategori_id' => 'integer',
        'tgl_kejadian' => 'date',
        'tgl_selesai' => 'date',
        'tgl_selesai_koreksi' => 'date',
        'verifikasi_tgl_koreksi' => 'date',
        'tgl_selesai_korektif' => 'date',
        'verifikasi_tgl_korektif' => 'date',
        'reported_by' => 'integer',
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
     * Get the kategoriInsiden that owns the Insiden
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function kategoriInsiden(): BelongsTo
    {
        return $this->belongsTo(KategoriInsiden::class, 'kategori_id', 'id');
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

    /**
     * Get all of the dokumentasiInsiden for the Insiden
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function dokumentasiInsiden(): HasMany
    {
        return $this->hasMany(DokumentasiInsiden::class, 'insiden_id', 'id');
    }

}
