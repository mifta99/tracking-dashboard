<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UjiFungsi extends Model
{
    use HasFactory, SoftDeletes;
    protected $primaryKey = 'id';
    protected $table = 'uji_fungsi';
    public $timestamps = true;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id',
        'puskesmas_id',
        'equipment_id',
        'target_tgl_uji_fungsi',
        'tgl_instalasi',
        'doc_instalasi',
        'is_verified_instalasi',
        'verified_at_instalasi',
        'tgl_pelatihan',
        'doc_pelatihan',
        'is_verified_pelatihan',
        'verified_at_pelatihan',
        'tgl_uji_fungsi',
        'doc_uji_fungsi',
        'is_verified_uji_fungsi',
        'verified_at_uji_fungsi',
        'catatan',
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
        'equipment_id' => 'integer',
        'target_tgl_uji_fungsi' => 'date',
        'tgl_instalasi' => 'date',
        'tgl_pelatihan' => 'date',
        'tgl_uji_fungsi' => 'date',
        'verif_kemenkes' => 'boolean',
        'tgl_verif_kemenkes' => 'datetime',
        'is_verified_instalasi' => 'boolean',
        'verified_at_instalasi' => 'datetime',
        'is_verified_pelatihan' => 'boolean',
        'verified_at_pelatihan' => 'datetime',
        'is_verified_uji_fungsi' => 'boolean',
        'verified_at_uji_fungsi' => 'datetime',
        'created_by' => 'integer',
        'updated_by' => 'integer',
    ];

    /**
     * Get the puskesmas that owns the UjiFungsi
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function puskesmas(): BelongsTo
    {
        return $this->belongsTo(Puskesmas::class, 'puskesmas_id', 'id');
    }

    /**
     * Get the user that owns the UjiFungsi
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

    /**
     * Get the user that owns the UjiFungsi
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by', 'id');
    }

    /**
     * Get the equipment that owns the UjiFungsi
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function equipment(): BelongsTo
    {
        return $this->belongsTo(Equipment::class, 'equipment_id', 'id');
    }

}

