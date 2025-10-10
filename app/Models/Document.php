<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Document extends Model
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
        'equipment_id',
        'basto',
        'is_verified_basto',
        'verified_at_basto',
        'kalibrasi',
        'is_verified_kalibrasi',
        'verified_at_kalibrasi',
        'bast',
        'is_verified_bast',
        'verified_at_bast',
        'aspak',
        'is_verified_aspak',
        'verified_at_aspak',
        'update_aspak',
        'verif_kemenkes',
        'tgl_verif_kemenkes',
        'verif_kemenkes_update_aspak',
        'tgl_verif_kemenkes_update_aspak',
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
        'verif_kemenkes' => 'boolean',
        'tgl_verif_kemenkes' => 'datetime',
        'verif_kemenkes_update_aspak' => 'boolean',
        'tgl_verif_kemenkes_update_aspak' => 'datetime',
        'is_verified_basto' => 'boolean',
        'verified_at_basto' => 'datetime',
        'is_verified_kalibrasi' => 'boolean',
        'verified_at_kalibrasi' => 'datetime',
        'is_verified_bast' => 'boolean',
        'verified_at_bast' => 'datetime',
        'is_verified_aspak' => 'boolean',
        'verified_at_aspak' => 'datetime',
        'created_by' => 'integer',
        'updated_by' => 'integer',
    ];

    /**
     * Get the puskesmas that owns the Document
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function puskesmas(): BelongsTo
    {
        return $this->belongsTo(Puskesmas::class, 'puskesmas_id', 'id');
    }

    /**
     * Get the user that owns the Document
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

    /**
     * Get the user that owns the Document
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by', 'id');
    }

    /**
     * Get the equipment that owns the Document
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function equipment(): BelongsTo
    {
        return $this->belongsTo(Equipment::class, 'equipment_id', 'id');
    }
}
