<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Kalibrasi extends Model
{
    use HasFactory;
    protected $primaryKey = 'id';
    protected $table = 'kalibrasi';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id',
        'kalibrasi_master_id',
        'equipment_id',
        'puskesmas_id',
        'tgl_kalibrasi',
        'dokumentasi',
        'berita_acara',
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
        'kalibrasi_master_id' => 'integer',
        'equipment_id' => 'integer',
        'tgl_kalibrasi' => 'date',
    ];


    /**
     * Get the kalibrasiMaster that owns the Kalibrasi
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function kalibrasiMaster(): BelongsTo
    {
        return $this->belongsTo(KalibrasiMaster::class, 'kalibrasi_master_id', 'id');
    }

    /**
     * Get the puskesmas that owns the Kalibrasi
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function puskesmas(): BelongsTo
    {
        return $this->belongsTo(Puskesmas::class, 'puskesmas_id', 'id');
    }

    /**
     * Get the equipment that owns the Kalibrasi
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function equipment(): BelongsTo
    {
        return $this->belongsTo(Equipment::class, 'equipment_id', 'id');
    }

}
